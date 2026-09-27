<?php
declare(strict_types=1);
/**
 * Local test double for the parts of Supabase this project uses (PostgREST, Auth,
 * Storage), backed by a real Postgres database that has supabase/schema.sql loaded,
 * so row-level security is enforced exactly as in production.
 *
 *   MOCK_PG="pgsql:host=/var/tmp;port=5433;dbname=sb" php -S 127.0.0.1:54321 tools/dev/supabase-mock.php
 *
 * Test users are created with: POST /auth/v1/admin/users {"email","password"}.
 * NOT for production use.
 */
$dsn = getenv('MOCK_PG') ?: 'pgsql:host=/var/tmp;port=5433;dbname=sb';
$storageDir = getenv('MOCK_STORAGE') ?: sys_get_temp_dir() . '/supabase-mock-storage';
$pdo = new PDO($dsn, 'postgres', null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$pdo->exec("create table if not exists auth.mock_passwords (email text primary key, hash text not null)");

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: ' . ($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ?? '*'));
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, PUT, OPTIONS');
header('Access-Control-Expose-Headers: Content-Range');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$body = file_get_contents('php://input');
$json = json_decode($body ?: 'null', true);

function out($data, int $code = 200, array $headers = []): never
{
    http_response_code($code);
    header('Content-Type: application/json');
    foreach ($headers as $k => $v) header("$k: $v");
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
function b64u(string $s): string { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }
function jwt(array $claims): string { return b64u('{"alg":"HS256","typ":"JWT"}') . '.' . b64u(json_encode($claims)) . '.' . b64u('mock'); }
function claims(): array
{
    $h = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s+(\S+)/', $h, $m)) return ['role' => 'anon'];
    $p = explode('.', $m[1]);
    $c = json_decode(base64_decode(strtr($p[1] ?? '', '-_', '+/')) ?: '[]', true) ?: [];
    return $c + ['role' => 'anon'];
}
/** Run callback inside a transaction with the caller's role and auth.uid(). */
function as_caller(PDO $pdo, callable $fn)
{
    $c = claims();
    $role = ($c['role'] ?? '') === 'authenticated' ? 'authenticated' : 'anon';
    $pdo->beginTransaction();
    try {
        $pdo->exec("set local role {$role}");
        $st = $pdo->prepare("select set_config('request.jwt.claim.sub', ?, true)");
        $st->execute([$c['sub'] ?? '']);
        $r = $fn();
        $pdo->commit();
        return $r;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $msg = $e->errorInfo[2] ?? $e->getMessage();
        $msg = preg_replace('/^ERROR:\s+/', '', explode("\n", $msg)[0]);
        out(['code' => $e->errorInfo[0] ?? '', 'message' => $msg, 'details' => null, 'hint' => null], str_contains($msg, 'row-level security') ? 403 : 400);
    }
}
function ident(string $s): string { if (!preg_match('/^[a-z_][a-z0-9_]*$/', $s)) out(['message' => "bad identifier $s"], 400); return '"' . $s . '"'; }
function user_json(array $u): array { return ['id' => $u['id'], 'aud' => 'authenticated', 'role' => 'authenticated', 'email' => $u['email'], 'app_metadata' => ['provider' => 'email'], 'user_metadata' => new stdClass(), 'created_at' => '2026-01-01T00:00:00Z']; }

/* ---------------- Auth ---------------- */
if ($path === '/auth/v1/admin/users' && $method === 'POST') {
    $id = sprintf('%08x-%04x-4%03x-a%03x-%012x', random_int(0, 0xffffffff), random_int(0, 0xffff), random_int(0, 0xfff), random_int(0, 0xfff), random_int(0, 0xffffffffffff));
    $pdo->prepare('insert into auth.users (id, email) values (?, ?)')->execute([$id, $json['email']]);
    $pdo->prepare('insert into auth.mock_passwords values (?, ?) on conflict (email) do update set hash = excluded.hash')->execute([$json['email'], password_hash($json['password'], PASSWORD_DEFAULT)]);
    out(user_json(['id' => $id, 'email' => $json['email']]));
}
if ($path === '/auth/v1/token' && $method === 'POST') {
    $st = $pdo->prepare('select u.id, u.email, p.hash from auth.users u join auth.mock_passwords p on p.email = u.email where u.email = ?');
    $st->execute([$json['email'] ?? '']);
    $u = $st->fetch();
    if (!$u || !password_verify($json['password'] ?? '', $u['hash'])) out(['error' => 'invalid_grant', 'error_description' => 'Invalid login credentials', 'code' => 'invalid_credentials', 'msg' => 'Invalid login credentials'], 400);
    $exp = time() + 3600;
    out(['access_token' => jwt(['sub' => $u['id'], 'email' => $u['email'], 'role' => 'authenticated', 'aud' => 'authenticated', 'exp' => $exp, 'iat' => time()]), 'token_type' => 'bearer', 'expires_in' => 3600, 'expires_at' => $exp, 'refresh_token' => 'mock-refresh', 'user' => user_json($u)]);
}
if ($path === '/auth/v1/user' && $method === 'GET') {
    $c = claims();
    if (($c['role'] ?? '') !== 'authenticated') out(['message' => 'not logged in'], 401);
    out(user_json(['id' => $c['sub'], 'email' => $c['email'] ?? '']));
}
if ($path === '/auth/v1/user' && $method === 'PUT') {
    $c = claims();
    if (($c['role'] ?? '') !== 'authenticated') out(['message' => 'not logged in'], 401);
    if (!empty($json['password'])) $pdo->prepare('update auth.mock_passwords set hash = ? where email = ?')->execute([password_hash($json['password'], PASSWORD_DEFAULT), $c['email'] ?? '']);
    out(user_json(['id' => $c['sub'], 'email' => $c['email'] ?? '']));
}
if ($path === '/auth/v1/recover') out(new stdClass());
if ($path === '/auth/v1/logout') { http_response_code(204); exit; }

/* ---------------- Storage ---------------- */
if (preg_match('#^/storage/v1/object/public/([a-z0-9_-]+)/(.+)$#', $path, $m) && $method === 'GET') {
    $f = $storageDir . '/' . $m[1] . '/' . $m[2];
    if (!is_file($f)) { http_response_code(404); exit; }
    header('Content-Type: ' . (mime_content_type($f) ?: 'application/octet-stream'));
    readfile($f);
    exit;
}
if (preg_match('#^/storage/v1/object/([a-z0-9_-]+)/(.+)$#', $path, $m) && in_array($method, ['POST', 'PUT'], true)) {
    [$all, $bucket, $name] = $m;
    as_caller($pdo, function () use ($pdo, $bucket, $name) {
        $pdo->prepare('insert into storage.objects (bucket_id, name) values (?, ?)')->execute([$bucket, $name]);
    });
    $f = $storageDir . '/' . $bucket . '/' . $name;
    @mkdir(dirname($f), 0777, true);
    file_put_contents($f, $body);
    out(['Key' => "$bucket/$name", 'Id' => bin2hex(random_bytes(8))]);
}
if (preg_match('#^/storage/v1/object/([a-z0-9_-]+)$#', $path, $m) && $method === 'DELETE') {
    out([]);
}

/* ---------------- PostgREST (subset) ---------------- */
if (preg_match('#^/rest/v1/rpc/([a-z_]+)$#', $path, $m)) {
    $fn = ident($m[1]);
    $res = as_caller($pdo, function () use ($pdo, $fn, $json) {
        if (!isset($json['payload'])) return $pdo->query("select public.{$fn}() as r")->fetchColumn();
        $st = $pdo->prepare("select public.{$fn}(?::jsonb) as r");
        $st->execute([json_encode($json['payload'], JSON_UNESCAPED_UNICODE)]);
        return $st->fetchColumn();
    });
    out(is_string($res) && ($res === 't' || $res === 'f') ? $res === 't' : $res);
}
if (!preg_match('#^/rest/v1/([a-z_]+)$#', $path, $m)) out(['message' => 'not found: ' . $path], 404);
$table = 'public.' . ident($m[1]);

// Filters: col=eq.val | neq | gt | gte | lt | lte | in.(a,b) | is.null | ilike
$where = []; $params = [];
parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
foreach ($qs as $k => $v) {
    if (in_array($k, ['select', 'order', 'limit', 'offset', 'on_conflict', 'columns'], true)) continue;
    if (!preg_match('/^(eq|neq|gt|gte|lt|lte|in|is|ilike|like)\.(.*)$/s', (string)$v, $f)) continue;
    $col = ident($k);
    switch ($f[1]) {
        case 'in':
            $vals = str_getcsv(trim($f[2], '()'));
            $where[] = "$col in (" . implode(',', array_fill(0, count($vals), '?')) . ')';
            array_push($params, ...$vals);
            break;
        case 'is': $where[] = "$col is " . ($f[2] === 'null' ? 'null' : ($f[2] === 'true' ? 'true' : 'false')); break;
        case 'ilike': case 'like': $where[] = "$col::text {$f[1]} ?"; $params[] = str_replace('*', '%', $f[2]); break;
        default:
            $op = ['eq' => '=', 'neq' => '<>', 'gt' => '>', 'gte' => '>=', 'lt' => '<', 'lte' => '<='][$f[1]];
            $where[] = "$col::text $op ?"; $params[] = $f[2];
            if (in_array($f[1], ['gt', 'gte', 'lt', 'lte'], true)) { array_pop($where); $where[] = "$col $op ?"; }
    }
}
$whereSql = $where ? ' where ' . implode(' and ', $where) : '';
$prefer = $_SERVER['HTTP_PREFER'] ?? '';
$single = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'vnd.pgrst.object');

if ($method === 'GET' || $method === 'HEAD') {
    $sql = "select * from {$table}{$whereSql}";
    if (!empty($qs['order'])) {
        $parts = [];
        foreach (explode(',', $qs['order']) as $o) {
            $bits = explode('.', $o);
            $parts[] = ident($bits[0]) . (in_array('desc', $bits, true) ? ' desc' : ' asc') . (in_array('nullslast', $bits, true) ? ' nulls last' : (in_array('nullsfirst', $bits, true) ? ' nulls first' : ''));
        }
        $sql .= ' order by ' . implode(', ', $parts);
    }
    $countSql = "select count(*) from {$table}{$whereSql}";
    if (isset($qs['limit'])) $sql .= ' limit ' . (int)$qs['limit'];
    if (isset($qs['offset'])) $sql .= ' offset ' . (int)$qs['offset'];
    [$rows, $total] = as_caller($pdo, function () use ($pdo, $sql, $countSql, $params) {
        $st = $pdo->prepare($sql); $st->execute($params);
        $c = $pdo->prepare($countSql); $c->execute($params);
        return [$st->fetchAll(), (int)$c->fetchColumn()];
    });
    $rows = array_map('cast_row', $rows);
    $range = '0-' . max(0, count($rows) - 1) . '/' . (str_contains($prefer, 'count=') ? $total : '*');
    if ($single) { if (count($rows) !== 1) out(['message' => 'JSON object requested, multiple (or no) rows returned', 'code' => 'PGRST116'], 406); out($rows[0], 200, ['Content-Range' => $range]); }
    if ($method === 'HEAD') { http_response_code(200); header('Content-Range: ' . $range); exit; }
    out($rows, 200, ['Content-Range' => $range]);
}
if ($method === 'POST') {
    $rows = is_array($json) && array_is_list($json) ? $json : [$json];
    $upsert = str_contains($prefer, 'resolution=merge-duplicates');
    $out = as_caller($pdo, function () use ($pdo, $table, $rows, $upsert, $qs) {
        $res = [];
        foreach ($rows as $r) {
            $cols = array_keys($r);
            $sql = "insert into {$table} (" . implode(',', array_map('ident', $cols)) . ') values (' . implode(',', array_fill(0, count($cols), '?')) . ')';
            if ($upsert) {
                $key = $qs['on_conflict'] ?? 'id';
                $sets = array_map(fn($c) => ident($c) . ' = excluded.' . ident($c), array_diff($cols, [$key]));
                $sql .= ' on conflict (' . ident($key) . ') do update set ' . implode(', ', $sets ?: [ident($key) . ' = excluded.' . ident($key)]);
            }
            $st = $pdo->prepare($sql . ' returning *');
            $st->execute(array_map(fn($v) => is_bool($v) ? (int)$v : $v, array_values($r)));
            $res[] = $st->fetch();
        }
        return $res;
    });
    $out = array_map('cast_row', $out);
    if (!str_contains($prefer, 'return=representation')) { http_response_code(201); exit; }
    out($single ? $out[0] : $out, 201);
}
if ($method === 'PATCH') {
    $cols = array_keys($json);
    $sql = "update {$table} set " . implode(', ', array_map(fn($c) => ident($c) . ' = ?', $cols)) . $whereSql . ' returning *';
    $out = as_caller($pdo, function () use ($pdo, $sql, $json, $params) {
        $st = $pdo->prepare($sql);
        $st->execute(array_merge(array_map(fn($v) => is_bool($v) ? (int)$v : $v, array_values($json)), $params));
        return $st->fetchAll();
    });
    $out = array_map('cast_row', $out);
    if (!str_contains($prefer, 'return=representation')) { http_response_code(204); exit; }
    out($single ? ($out[0] ?? null) : $out);
}
if ($method === 'DELETE') {
    $out = as_caller($pdo, function () use ($pdo, $table, $whereSql, $params) {
        $st = $pdo->prepare("delete from {$table}{$whereSql} returning *"); $st->execute($params); return $st->fetchAll();
    });
    if (!str_contains($prefer, 'return=representation')) { http_response_code(204); exit; }
    out(array_map('cast_row', $out));
}
out(['message' => 'unsupported'], 405);

function cast_row(array $r): array
{
    foreach ($r as $k => $v) {
        if (is_string($v) && preg_match('/^-?\d+$/', $v) && ($k === 'id' || str_ends_with($k, '_id') || in_array($k, ['sort', 'featured', 'active', 'views', 'is_read'], true))) $r[$k] = (int)$v;
    }
    return $r;
}
