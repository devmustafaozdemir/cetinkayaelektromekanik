<?php
$id = (int)($_GET['id'] ?? 0);
if ($a === 'sort' && $method === 'POST') {
    foreach ((array)($_POST['order'] ?? []) as $i => $fid) q('UPDATE faqs SET sort = ? WHERE id = ?', [$i + 1, (int)$fid]);
    header('Content-Type: application/json');
    exit('{"ok":true}');
}
if ($method === 'POST') {
    if ($a === 'delete') {
        q('DELETE FROM faqs WHERE id = ?', [$id]);
        flash('success', 'Soru silindi.');
    } else {
        $question = post_str('question', 300);
        $answer = post_str('answer', 3000);
        $active = !empty($_POST['active']) ? 1 : 0;
        if ($question === '' || $answer === '') {
            flash('error', 'Soru ve cevap zorunludur.');
            redirect(admin_url('faqs', $id ? ['id' => $id] : []));
        }
        if ($id) q('UPDATE faqs SET question = ?, answer = ?, active = ? WHERE id = ?', [$question, $answer, $active, $id]);
        else q('INSERT INTO faqs(question, answer, active, sort) VALUES(?, ?, ?, (SELECT COALESCE(MAX(sort),0)+1 FROM faqs))', [$question, $answer, $active]);
        flash('success', 'Soru kaydedildi.');
    }
    redirect(admin_url('faqs'));
}
admin_render('faqs', [
    'title' => 'Sıkça Sorulan Sorular',
    'rows'  => q_all('SELECT * FROM faqs ORDER BY sort, id'),
    'edit'  => $id ? q_one('SELECT * FROM faqs WHERE id = ?', [$id]) : null,
]);
