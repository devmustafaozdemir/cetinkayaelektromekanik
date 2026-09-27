<?php
$id = (int)($_GET['id'] ?? 0);
$statuses = quote_statuses();

if ($a === 'delete' && $method === 'POST') {
    q('DELETE FROM quotes WHERE id = ?', [$id]);
    flash('success', 'Teklif talebi silindi.');
    redirect(admin_url('quotes'));
}

if ($a === 'edit') {
    $row = $id ? q_one('SELECT * FROM quotes WHERE id = ?', [$id]) : null;
    if ($id && !$row) redirect(admin_url('quotes'));
    $errors = [];
    if ($method === 'POST') {
        $d = [];
        foreach (['name' => 120, 'company' => 160, 'phone' => 40, 'email' => 160, 'city' => 60, 'category' => 120, 'product_name' => 200, 'quantity' => 120, 'message' => 5000, 'admin_note' => 5000] as $k => $max) {
            $d[$k] = post_str($k, $max);
        }
        $d['type'] = array_key_exists($_POST['type'] ?? '', quote_types()) ? $_POST['type'] : 'product';
        $d['product_id'] = (int)($_POST['product_id'] ?? 0) ?: null;
        if ($d['product_id']) {
            $d['product_name'] = (string)q_val('SELECT title FROM products WHERE id = ?', [$d['product_id']]);
        }
        $newStatus = array_key_exists($_POST['status'] ?? '', $statuses) ? $_POST['status'] : 'new';
        $logNote = post_str('log_note', 500);
        if (mb_strlen($d['name']) < 2) $errors['name'] = 'Müşteri adı zorunludur.';
        if (strlen(preg_replace('/\D/', '', $d['phone'])) < 10) $errors['phone'] = 'Geçerli bir telefon girin.';
        if (!$errors) {
            if ($row) {
                q("UPDATE quotes SET type=:type, name=:name, company=:company, phone=:phone, email=:email, city=:city, category=:category, product_id=:product_id,
                   product_name=:product_name, quantity=:quantity, message=:message, admin_note=:admin_note, status=:status, updated_at=now_tr() WHERE id=:id", $d + ['status' => $newStatus, 'id' => $id]);
                if ($newStatus !== $row['status'] || $logNote !== '') {
                    q('INSERT INTO quote_log(quote_id, status, note, created_at) VALUES(?, ?, ?, now_tr())', [$id, $newStatus, $logNote]);
                }
                flash('success', 'Teklif talebi güncellendi.');
            } else {
                $code = new_quote_code();
                q("INSERT INTO quotes(code, type, name, company, phone, email, city, category, product_id, product_name, quantity, message, admin_note, status, source, created_at, updated_at)
                   VALUES(:code, :type, :name, :company, :phone, :email, :city, :category, :product_id, :product_name, :quantity, :message, :admin_note, :status, 'manual', now_tr(), now_tr())", $d + ['code' => $code, 'status' => $newStatus]);
                $id = (int)db()->lastInsertId();
                q('INSERT INTO quote_log(quote_id, status, note, created_at) VALUES(?, ?, ?, now_tr())', [$id, $newStatus, $logNote ?: 'Kayıt panelden oluşturuldu.']);
                flash('success', "Teklif kaydı oluşturuldu: {$code}");
            }
            redirect(admin_url('quotes', ['a' => 'edit', 'id' => $id]));
        }
        $row = array_merge($row ?? [], $d, ['status' => $newStatus]);
    }
    admin_render('quote-edit', [
        'title'    => $id ? 'Teklif Talebi · ' . $row['code'] : 'Yeni Teklif Kaydı',
        'row'      => $row ?? ['type' => 'product', 'name' => '', 'company' => '', 'phone' => '', 'email' => '', 'city' => '', 'category' => '', 'product_id' => null, 'product_name' => '', 'quantity' => '', 'message' => '', 'admin_note' => '', 'status' => 'new', 'source' => 'manual'],
        'id'       => $id,
        'errors'   => $errors,
        'statuses' => $statuses,
        'log'      => $id ? q_all('SELECT * FROM quote_log WHERE quote_id = ? ORDER BY id DESC', [$id]) : [],
        'products' => q_all('SELECT p.id, p.title, c.name AS category FROM products p LEFT JOIN product_categories c ON c.id = p.category_id ORDER BY c.sort, p.sort, p.title'),
        'categories' => q_all('SELECT name FROM product_categories ORDER BY sort, id'),
    ]);
}

$where = ' WHERE 1=1';
$params = [];
$filter = (string)($_GET['durum'] ?? '');
if ($filter === 'acik') {
    $where .= " AND status IN ('new','contacted','quoted')";
} elseif (array_key_exists($filter, $statuses)) {
    $where .= ' AND status = ?';
    $params[] = $filter;
}
$search = trim((string)($_GET['q'] ?? ''));
if ($search !== '') {
    $where .= ' AND (code LIKE ? OR name LIKE ? OR company LIKE ? OR phone LIKE ? OR product_name LIKE ? OR category LIKE ? OR city LIKE ?)';
    array_push($params, ...array_fill(0, 7, '%' . $search . '%'));
}
$counts = ['' => 0, 'acik' => 0];
foreach (q_all('SELECT status, COUNT(*) c FROM quotes GROUP BY status') as $r) {
    $counts[$r['status']] = (int)$r['c'];
    $counts[''] += (int)$r['c'];
    if (in_array($r['status'], ['new', 'contacted', 'quoted'], true)) $counts['acik'] += (int)$r['c'];
}
admin_render('quotes', [
    'title'    => 'Teklif Talepleri',
    'rows'     => q_all('SELECT * FROM quotes' . $where . ' ORDER BY id DESC LIMIT 300', $params),
    'filter'   => $filter,
    'search'   => $search,
    'counts'   => $counts,
    'statuses' => $statuses,
]);
