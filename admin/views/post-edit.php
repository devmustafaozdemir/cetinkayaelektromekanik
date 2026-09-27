<form method="post" enctype="multipart/form-data" class="editor-layout" id="post-form">
  <?= csrf_field() ?>
  <div class="editor-main">
    <div class="card">
      <label class="field<?= isset($errors['title']) ? ' has-error' : '' ?>">
        <input type="text" name="title" value="<?= e($post['title']) ?>" placeholder="Yazı başlığı" class="input-title" required data-slug-source>
        <?php if (isset($errors['title'])): ?><em><?= e($errors['title']) ?></em><?php endif; ?>
      </label>
      <div class="slug-row"><span><?= e(base_url()) ?>/blog/</span><input type="text" name="slug" value="<?= e($post['slug']) ?>" placeholder="otomatik-olusturulur" data-slug-target></div>
      <div class="field<?= isset($errors['content']) ? ' has-error' : '' ?>">
        <div id="editor" class="editor"><?= $post['content'] ?></div>
        <input type="hidden" name="content" id="content-input">
        <?php if (isset($errors['content'])): ?><em><?= e($errors['content']) ?></em><?php endif; ?>
        <small class="muted" data-wordcount></small>
      </div>
    </div>
    <div class="card">
      <h3>Özet & SEO</h3>
      <label class="field"><span>Kısa özet <small class="muted">(liste ve paylaşımlarda görünür)</small></span><textarea name="excerpt" rows="3" maxlength="400" data-counter><?= e($post['excerpt']) ?></textarea></label>
      <div class="form-row">
        <label class="field"><span>SEO başlığı</span><input type="text" name="meta_title" value="<?= e($post['meta_title']) ?>" maxlength="120" placeholder="Boş bırakılırsa başlık kullanılır" data-counter="60"></label>
        <label class="field"><span>SEO açıklaması</span><input type="text" name="meta_desc" value="<?= e($post['meta_desc']) ?>" maxlength="300" placeholder="Boş bırakılırsa özet kullanılır" data-counter="160"></label>
      </div>
      <div class="serp">
        <small><?= e(parse_url(base_url(), PHP_URL_HOST)) ?> › blog › <span data-serp-slug><?= e($post['slug']) ?></span></small>
        <strong data-serp-title><?= e($post['meta_title'] ?: $post['title'] ?: 'Yazı başlığı') ?></strong>
        <p data-serp-desc><?= e($post['meta_desc'] ?: $post['excerpt'] ?: 'Yazının özeti burada görünecek.') ?></p>
      </div>
    </div>
  </div>
  <aside class="editor-side">
    <div class="card sticky">
      <h3>Yayın</h3>
      <div class="seg">
        <label><input type="radio" name="status" value="draft"<?= $post['status'] !== 'published' ? ' checked' : '' ?>><span>Taslak</span></label>
        <label><input type="radio" name="status" value="published"<?= $post['status'] === 'published' ? ' checked' : '' ?>><span>Yayında</span></label>
      </div>
      <label class="field"><span>Yayın tarihi</span><input type="datetime-local" name="published_at" value="<?= $post['published_at'] ? e(date('Y-m-d\TH:i', strtotime($post['published_at']))) : '' ?>"><small class="muted">İleri bir tarih seçerek zamanlayabilirsiniz.</small></label>
      <label class="field"><span>Kategori</span>
        <select name="category_id">
          <option value="">— Kategorisiz —</option>
          <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"<?= (int)$post['category_id'] === (int)$c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label class="switch"><input type="checkbox" name="featured" value="1"<?= $post['featured'] ? ' checked' : '' ?>><span class="switch__ui"></span><span>Öne çıkan yazı</span></label>
      <div class="btn-stack">
        <button class="btn btn--primary btn--block"><?= icon('check') ?> Kaydet</button>
        <?php if ($id): ?><a href="/blog/<?= e($post['slug']) ?>" target="_blank" class="btn btn--block"><?= icon('eye') ?> <?= $post['status'] === 'published' ? 'Görüntüle' : 'Önizle' ?></a><?php endif; ?>
      </div>
      <?php if ($id): ?><p class="muted small"><?= icon('eye') ?> <?= (int)$post['views'] ?> görüntülenme</p><?php endif; ?>
    </div>
    <div class="card">
      <h3>Kapak görseli</h3>
      <label class="dropzone<?= isset($errors['cover']) ? ' has-error' : '' ?>" data-dropzone>
        <input type="file" name="cover" accept="image/jpeg,image/png,image/webp,image/gif">
        <img src="<?= e(upload_url($post['cover'])) ?>" alt="" data-preview<?= $post['cover'] ? '' : ' hidden' ?>>
        <span class="dropzone__hint"<?= $post['cover'] ? ' hidden' : '' ?>><?= icon('plus') ?> Görsel seçin veya sürükleyin<small>JPG, PNG, WEBP · en fazla 8 MB</small></span>
      </label>
      <?php if (isset($errors['cover'])): ?><em class="error"><?= e($errors['cover']) ?></em><?php endif; ?>
      <?php if ($post['cover']): ?><label class="check"><input type="checkbox" name="remove_cover" value="1"> Kapak görselini kaldır</label><?php endif; ?>
    </div>
    <?php if ($id): ?>
    <div class="card card--danger">
      <button type="submit" form="delete-form" class="btn btn--danger-ghost btn--block"><?= icon('trash') ?> Yazıyı sil</button>
    </div>
    <?php endif; ?>
  </aside>
</form>
<?php if ($id): ?>
<form id="delete-form" method="post" action="<?= admin_url('posts', ['a' => 'delete', 'id' => $id]) ?>" data-confirm="Bu yazı kalıcı olarak silinsin mi?"><?= csrf_field() ?></form>
<?php endif; ?>
