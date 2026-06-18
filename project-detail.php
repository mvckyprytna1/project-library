<?php
declare(strict_types=1);

require __DIR__ . '/config/app.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/csrf.php';
require __DIR__ . '/includes/auth.php';

require_login();

$pdo = db();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    'SELECT p.*, c.name AS category_name, c.color AS category_color
     FROM projects p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.id = ?
     LIMIT 1'
);
$stmt->execute([$id]);
$project = $stmt->fetch();

if (!$project) {
    set_flash('error', 'Project tidak ditemukan.');
    redirect('projects.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';

    if ($action === 'add_note') {
        $note = trim((string)($_POST['note'] ?? ''));

        if ($note !== '') {
            $insert = $pdo->prepare('INSERT INTO project_notes (project_id, note) VALUES (?, ?)');
            $insert->execute([$id, $note]);
            set_flash('success', 'Catatan timeline ditambahkan.');
        }

        redirect('project-detail.php?id=' . $id);
    }

    if ($action === 'delete_note') {
        $noteId = (int)($_POST['note_id'] ?? 0);
        $delete = $pdo->prepare('DELETE FROM project_notes WHERE id = ? AND project_id = ?');
        $delete->execute([$noteId, $id]);
        set_flash('success', 'Catatan timeline dihapus.');
        redirect('project-detail.php?id=' . $id);
    }
}

$notesStmt = $pdo->prepare('SELECT * FROM project_notes WHERE project_id = ? ORDER BY created_at DESC');
$notesStmt->execute([$id]);
$timelineNotes = $notesStmt->fetchAll();

$pageTitle = $project['name'];

require __DIR__ . '/includes/layout_top.php';
?>

<section class="detail-hero">
  <div class="card card-pad">
    <img class="detail-thumb" src="<?= e(thumbnail_url($project['thumbnail'])) ?>" alt="<?= e($project['name']) ?>">

    <div class="project-meta" style="margin-top:18px;">
      <span class="<?= e(status_class($project['status'])) ?>"><?= e(status_label($project['status'])) ?></span>
      <?php if ($project['category_name']): ?>
        <span class="badge">
          <span class="color-dot" style="background: <?= e($project['category_color']) ?>"></span>
          <?= e($project['category_name']) ?>
        </span>
      <?php endif; ?>
      <?php if ((int)$project['is_favorite'] === 1): ?><span class="badge badge-favorite">Favorite</span><?php endif; ?>
      <?php if ((int)$project['is_public'] === 1): ?><span class="badge badge-public">Public</span><?php endif; ?>
    </div>

    <h2><?= e($project['name']) ?></h2>
    <p class="project-desc"><?= nl2br(e($project['description'])) ?></p>

    <div class="actions">
      <?php if ($project['live_url']): ?>
        <a class="btn" target="_blank" rel="noopener" href="<?= e($project['live_url']) ?>">Live Demo</a>
      <?php endif; ?>
      <?php if ($project['github_url']): ?>
        <a class="btn btn-secondary" target="_blank" rel="noopener" href="<?= e($project['github_url']) ?>">GitHub</a>
      <?php endif; ?>
      <a class="btn btn-ghost" href="<?= e(url('project-edit.php?id=' . $project['id'])) ?>">Edit</a>
    </div>
  </div>

  <aside class="card card-pad">
    <h2>Info Project</h2>
    <div class="info-list">
      <div><span>Tech Stack</span><strong><?= e($project['tech_stack'] ?: '-') ?></strong></div>
      <div><span>Hosting</span><strong><?= e($project['hosting_platform'] ?: '-') ?></strong></div>
      <div><span>Created</span><strong><?= e(format_date($project['created_at'])) ?></strong></div>
      <div><span>Updated</span><strong><?= e(format_date($project['updated_at'] ?: $project['created_at'])) ?></strong></div>
      <div><span>Visibility</span><strong><?= (int)$project['is_public'] === 1 ? 'Public' : 'Private' ?></strong></div>
    </div>
  </aside>
</section>

<section class="grid" style="grid-template-columns: 1fr 1fr; margin-top:18px;">
  <div class="card card-pad">
    <h2>Catatan Pribadi</h2>
    <?php if ($project['notes']): ?>
      <p class="project-desc"><?= nl2br(e($project['notes'])) ?></p>
    <?php else: ?>
      <p class="help">Belum ada catatan pribadi.</p>
    <?php endif; ?>
  </div>

  <div class="card card-pad">
    <h2>Tambah Timeline Note</h2>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add_note">
      <div class="form-group">
        <label>Catatan</label>
        <textarea class="textarea" name="note" placeholder="Progress update, bug, ide fitur..."></textarea>
      </div>
      <div class="actions">
        <button class="btn" type="submit">Tambah Note</button>
      </div>
    </form>
  </div>
</section>

<section class="card card-pad" style="margin-top:18px;">
  <h2>Timeline Notes</h2>

  <?php if (!$timelineNotes): ?>
    <p class="help">Belum ada timeline note.</p>
  <?php else: ?>
    <?php foreach ($timelineNotes as $note): ?>
      <div class="note-item">
        <p><?= nl2br(e($note['note'])) ?></p>
        <div class="project-title-row">
          <small class="help"><?= e(format_date($note['created_at'])) ?></small>
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete_note">
            <input type="hidden" name="note_id" value="<?= e((string)$note['id']) ?>">
            <button class="btn btn-danger" type="submit" data-confirm="Hapus note ini?">Hapus</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
