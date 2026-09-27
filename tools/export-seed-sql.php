<?php
declare(strict_types=1);
/**
 * Generates supabase/seed.sql from the built-in sample content (app/seed.php).
 * Usage: php tools/export-seed-sql.php > supabase/seed.sql
 */
$tmp = sys_get_temp_dir() . '/cem-seed-' . bin2hex(random_bytes(4)) . '.sqlite';
putenv('APP_DB_PATH=' . $tmp);
$_SERVER['HTTP_HOST'] = 'localhost';
require dirname(__DIR__) . '/app/bootstrap.php';

$lit = function ($v): string {
    if ($v === null) return 'null';
    if (is_int($v) || (is_string($v) && preg_match('/^-?\d+$/', $v) && strlen($v) < 12)) return (string)$v;
    return "'" . str_replace("'", "''", (string)$v) . "'";
};
$ts = fn($v) => $v === null ? 'null' : "'" . $v . "+03'";

$tables = [
    'settings'           => ['key', 'value'],
    'categories'         => ['id', 'name', 'slug'],
    'product_categories' => ['id', 'name', 'slug', 'art', 'summary', 'sort', 'photo', 'photo_credit', 'photo_source'],
    'products'           => ['id', 'title', 'slug', 'category_id', 'brand', 'image', 'summary', 'content', 'specs', 'model', 'featured', 'active', 'sort', '@created_at', '@updated_at'],
    'posts'              => ['id', 'title', 'slug', 'excerpt', 'content', 'cover', 'category_id', 'status', 'featured', 'meta_title', 'meta_desc', '@published_at', '@created_at', '@updated_at'],
    'services'           => ['id', 'title', 'slug', 'icon', 'summary', 'content', 'sort', 'active'],
    'faqs'               => ['id', 'question', 'answer', 'sort', 'active'],
];
echo "-- Örnek içerik. schema.sql'den SONRA çalıştırın. Mevcut kayıtların üzerine yazmaz.\n";
echo "-- Oluşturan: php tools/export-seed-sql.php\n\nbegin;\n";
foreach ($tables as $t => $cols) {
    $plain = array_map(fn($c) => ltrim($c, '@'), $cols);
    foreach (q_all('SELECT ' . implode(', ', $plain) . " FROM {$t}") as $row) {
        $vals = [];
        foreach ($cols as $c) {
            $k = ltrim($c, '@');
            $vals[] = $c[0] === '@' ? $ts($row[$k]) : $lit($row[$k]);
        }
        $conflict = $t === 'settings' ? '(key)' : '(id)';
        $ov = $t !== 'settings' && in_array('id', $cols, true) ? ' overriding system value' : '';
        echo "insert into public.{$t} (" . implode(', ', $plain) . "){$ov} values (" . implode(', ', $vals) . ") on conflict {$conflict} do nothing;\n";
    }
    if (in_array('id', $cols, true)) {
        echo "select setval(pg_get_serial_sequence('public.{$t}', 'id'), coalesce((select max(id) from public.{$t}), 1));\n";
    }
    echo "\n";
}
echo "commit;\n";
@unlink($tmp); @unlink($tmp . '-wal'); @unlink($tmp . '-shm');
