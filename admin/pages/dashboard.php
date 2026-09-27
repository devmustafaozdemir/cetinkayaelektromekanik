<?php
$stats = [
    'open'     => (int)q_val("SELECT COUNT(*) FROM quotes WHERE status IN ('new','contacted','quoted')"),
    'new'      => (int)q_val("SELECT COUNT(*) FROM quotes WHERE status = 'new'"),
    'won'      => (int)q_val("SELECT COUNT(*) FROM quotes WHERE status = 'won' AND updated_at >= date(now_tr(),'start of month')"),
    'month'    => (int)q_val("SELECT COUNT(*) FROM quotes WHERE created_at >= date(now_tr(),'start of month')"),
    'unread'   => (int)q_val('SELECT COUNT(*) FROM messages WHERE is_read = 0'),
    'products' => (int)q_val('SELECT COUNT(*) FROM products WHERE active = 1'),
    'views'    => (int)q_val('SELECT COALESCE(SUM(views),0) FROM posts'),
];
$closed = (int)q_val("SELECT COUNT(*) FROM quotes WHERE status IN ('won','lost')");
$stats['rate'] = $closed ? (int)round(q_val("SELECT COUNT(*) FROM quotes WHERE status = 'won'") / $closed * 100) : null;
$days = [];
for ($i = 13; $i >= 0; $i--) {
    $days[date('Y-m-d', strtotime("-{$i} days"))] = 0;
}
foreach (q_all("SELECT date(created_at) d, COUNT(*) c FROM quotes WHERE created_at >= date(now_tr(),'-13 days') GROUP BY d") as $r) {
    if (isset($days[$r['d']])) $days[$r['d']] = (int)$r['c'];
}
$byStatus = [];
foreach (q_all('SELECT status, COUNT(*) c FROM quotes GROUP BY status') as $r) {
    $byStatus[$r['status']] = (int)$r['c'];
}
admin_render('dashboard', [
    'title'    => 'Genel Bakış',
    'stats'    => $stats,
    'days'     => $days,
    'byStatus' => $byStatus,
    'quotes'   => q_all('SELECT * FROM quotes ORDER BY id DESC LIMIT 6'),
    'messages' => q_all('SELECT * FROM messages ORDER BY id DESC LIMIT 4'),
    'top'      => q_all("SELECT COALESCE(NULLIF(product_name,''), NULLIF(category,''), 'Belirtilmedi') AS name, COUNT(*) c FROM quotes GROUP BY name ORDER BY c DESC LIMIT 5"),
]);
