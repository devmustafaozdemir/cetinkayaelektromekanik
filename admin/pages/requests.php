<?php
$id = (int)($_GET['id'] ?? 0);
$statuses = request_statuses();

if ($a === 'delete' && $method === 'POST') {
    q('DELETE FROM service_requests WHERE id = ?', [$id]);
    flash('success', 'Servis kaydı silindi.');
    redirect(admin_url('requests'));
}

if ($a === 'edit') {
    $req = $id ? q_one('SELECT * FROM service_requests WHERE id = ?', [$id]) : null;
    if ($id && !$req) redirect(admin_url('requests'));
    $errors = [];
    if ($method === 'POST') {
        $d = [];
        foreach (['name' => 120, 'phone' => 40, 'email' => 160, 'company' => 160, 'brand' => 60, 'device' => 120, 'model' => 120, 'issue' => 5000, 'admin_note' => 2000] as $k => $max) {
            $d[$k] = post_str($k, $max);
        }
        $d['warranty'] = !empty($_POST['warranty']) ? 1 : 0;
        $newStatus = array_key_exists($_POST['status'] ?? '', $statuses) ? $_POST['status'] : 'received';
        $logNote = post_str('log_note', 500);
        if (mb_strlen($d['name']) < 2) $errors['name'] = 'Müşteri adı zorunludur.';
        if (strlen(preg_replace('/\D/', '', $d['phone'])) < 10) $errors['phone'] = 'Geçerli bir telefon girin.';
        if ($d['device'] === '') $errors['device'] = 'Cihaz türü zorunludur.';
        if (!$errors) {
            if ($req) {
                q("UPDATE service_requests SET name=:name, phone=:phone, email=:email, company=:company, brand=:brand, device=:device, model=:model,
                   issue=:issue, admin_note=:admin_note, warranty=:warranty, status=:status, updated_at=now_tr() WHERE id=:id", $d + ['status' => $newStatus, 'id' => $id]);
                if ($newStatus !== $req['status'] || $logNote !== '') {
                    q('INSERT INTO request_log(request_id, status, note, created_at) VALUES(?, ?, ?, now_tr())', [$id, $newStatus, $logNote]);
                }
                flash('success', 'Servis kaydı güncellendi.');
            } else {
                $code = new_request_code();
                q('INSERT INTO service_requests(code, name, phone, email, company, brand, device, model, issue, admin_note, warranty, status, created_at, updated_at)
                   VALUES(:code, :name, :phone, :email, :company, :brand, :device, :model, :issue, :admin_note, :warranty, :status, now_tr(), now_tr())', $d + ['code' => $code, 'status' => $newStatus]);
                $id = (int)db()->lastInsertId();
                q('INSERT INTO request_log(request_id, status, note, created_at) VALUES(?, ?, ?, now_tr())', [$id, $newStatus, $logNote ?: 'Cihaz servise teslim alındı.']);
                flash('success', "Servis kaydı oluşturuldu. Takip kodu: {$code}");
            }
            redirect(admin_url('requests', ['a' => 'edit', 'id' => $id]));
        }
        $req = array_merge($req ?? [], $d, ['status' => $newStatus]);
    }
    admin_render('request-edit', [
        'title'    => $id ? 'Servis Kaydı · ' . $req['code'] : 'Yeni Servis Kaydı',
        'req'      => $req ?? ['name' => '', 'phone' => '', 'email' => '', 'company' => '', 'brand' => '', 'device' => '', 'model' => '', 'issue' => '', 'admin_note' => '', 'warranty' => 0, 'status' => 'received'],
        'id'       => $id,
        'errors'   => $errors,
        'statuses' => $statuses,
        'log'      => $id ? q_all('SELECT * FROM request_log WHERE request_id = ? ORDER BY id DESC', [$id]) : [],
        'brands'   => setting_lines('brands'),
    ]);
}

$where = ' WHERE 1=1';
$params = [];
$filter = (string)($_GET['durum'] ?? '');
if ($filter === 'acik') {
    $where .= " AND status NOT IN ('delivered','cancelled')";
} elseif (array_key_exists($filter, $statuses)) {
    $where .= ' AND status = ?';
    $params[] = $filter;
}
$search = trim((string)($_GET['q'] ?? ''));
if ($search !== '') {
    $where .= ' AND (code LIKE ? OR name LIKE ? OR phone LIKE ? OR device LIKE ? OR model LIKE ? OR company LIKE ?)';
    array_push($params, ...array_fill(0, 6, '%' . $search . '%'));
}
$counts = ['' => 0, 'acik' => 0];
foreach (q_all('SELECT status, COUNT(*) c FROM service_requests GROUP BY status') as $r) {
    $counts[$r['status']] = (int)$r['c'];
    $counts[''] += (int)$r['c'];
    if (!in_array($r['status'], ['delivered', 'cancelled'], true)) $counts['acik'] += (int)$r['c'];
}
admin_render('requests', [
    'title'    => 'Servis Talepleri',
    'rows'     => q_all('SELECT * FROM service_requests' . $where . ' ORDER BY id DESC LIMIT 300', $params),
    'filter'   => $filter,
    'search'   => $search,
    'counts'   => $counts,
    'statuses' => $statuses,
]);
