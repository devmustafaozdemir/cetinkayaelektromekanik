<?php
$stats = [
    'open'      => (int)q_val("SELECT COUNT(*) FROM service_requests WHERE status NOT IN ('delivered','cancelled')"),
    'new'       => (int)q_val("SELECT COUNT(*) FROM service_requests WHERE status = 'received'"),
    'ready'     => (int)q_val("SELECT COUNT(*) FROM service_requests WHERE status = 'ready'"),
    'unread'    => (int)q_val('SELECT COUNT(*) FROM messages WHERE is_read = 0'),
    'posts'     => (int)q_val("SELECT COUNT(*) FROM posts WHERE status = 'published'"),
    'drafts'    => (int)q_val("SELECT COUNT(*) FROM posts WHERE status = 'draft'"),
    'views'     => (int)q_val('SELECT COALESCE(SUM(views),0) FROM posts'),
    'month'     => (int)q_val("SELECT COUNT(*) FROM service_requests WHERE created_at >= date(now_tr(),'start of month')"),
];
$days = [];
for ($i = 13; $i >= 0; $i--) {
    $days[date('Y-m-d', strtotime("-{$i} days"))] = 0;
}
foreach (q_all("SELECT date(created_at) d, COUNT(*) c FROM service_requests WHERE created_at >= date(now_tr(),'-13 days') GROUP BY d") as $r) {
    if (isset($days[$r['d']])) $days[$r['d']] = (int)$r['c'];
}
$byStatus = [];
foreach (q_all("SELECT status, COUNT(*) c FROM service_requests WHERE status NOT IN ('delivered','cancelled') GROUP BY status") as $r) {
    $byStatus[$r['status']] = (int)$r['c'];
}
admin_render('dashboard', [
    'title'    => 'Genel Bakış',
    'stats'    => $stats,
    'days'     => $days,
    'byStatus' => $byStatus,
    'requests' => q_all('SELECT * FROM service_requests ORDER BY id DESC LIMIT 6'),
    'messages' => q_all('SELECT * FROM messages ORDER BY id DESC LIMIT 5'),
    'top'      => q_all("SELECT id, title, slug, views FROM posts WHERE status = 'published' ORDER BY views DESC LIMIT 5"),
]);
