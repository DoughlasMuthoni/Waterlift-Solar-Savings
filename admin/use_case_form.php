<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';

$id   = (int)($_GET['id'] ?? 0);
$case = null;
if ($id) {
    $case = $pdo->prepare("SELECT * FROM use_cases WHERE id = ?")->execute([$id])
        ? $pdo->prepare("SELECT * FROM use_cases WHERE id = ?") : null;
    $stmt = $pdo->prepare("SELECT * FROM use_cases WHERE id = ?");
    $stmt->execute([$id]);
    $case = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$case) { header('Location: use_cases.php'); exit; }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title  = trim($_POST['title'] ?? '');
    $tag    = trim($_POST['tag'] ?? '');
    $desc   = trim($_POST['description'] ?? '');
    $stat   = trim($_POST['stat_label'] ?? '');
    $img    = trim($_POST['image_url'] ?? '');
    $order  = max(0, (int)($_POST['sort_order'] ?? 0));
    $active = isset($_POST['is_active']) ? 1 : 0;

    if (!$title || !$tag || !$desc) {
        $error = 'Title, tag, and description are required.';
    } else {
        if ($id) {
            $pdo->prepare(
                "UPDATE use_cases SET title=?, tag=?, description=?, stat_label=?, image_url=?, sort_order=?, is_active=? WHERE id=?"
            )->execute([$title, $tag, $desc, $stat ?: null, $img ?: null, $order, $active, $id]);
        } else {
            $pdo->prepare(
                "INSERT INTO use_cases (title, tag, description, stat_label, image_url, sort_order, is_active) VALUES (?,?,?,?,?,?,?)"
            )->execute([$title, $tag, $desc, $stat ?: null, $img ?: null, $order, $active]);
        }
        header('Location: use_cases.php');
        exit;
    }
}

$v = $case ?? ['title'=>'','tag'=>'','description'=>'','stat_label'=>'','image_url'=>'','sort_order'=>0,'is_active'=>1];
open_layout($id ? 'Edit Use Case' : 'Add Use Case', 'use_cases');
?>

<div class="max-w-2xl mx-auto">
  <div class="flex items-center gap-3 mb-6">
    <a href="use_cases.php" class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50">
      <span class="material-icons text-lg">arrow_back</span>
    </a>
    <div>
      <h2 class="text-xl font-extrabold" style="color:#0f2d52"><?= $id ? 'Edit Use Case' : 'Add New Use Case' ?></h2>
      <p class="text-xs text-slate-400"><?= $id ? "Editing ID #{$id}" : 'New card for the "What We Power" section' ?></p>
    </div>
  </div>

  <?php if ($error): ?>
  <div class="mb-5 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-5">

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
      <!-- Title -->
      <div class="sm:col-span-2">
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Title *</label>
        <input name="title" value="<?= htmlspecialchars($v['title']) ?>" required
               placeholder="e.g. Home Solar Installation"
               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" />
      </div>

      <!-- Tag -->
      <div>
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tag / Category *</label>
        <input name="tag" value="<?= htmlspecialchars($v['tag']) ?>" required
               placeholder="e.g. Residential"
               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" />
      </div>

      <!-- Stat label -->
      <div>
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Stat / Highlight</label>
        <input name="stat_label" value="<?= htmlspecialchars($v['stat_label'] ?? '') ?>"
               placeholder="e.g. From KES 3,500/mo"
               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" />
      </div>
    </div>

    <!-- Description -->
    <div>
      <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Description *</label>
      <textarea name="description" rows="3" required
                placeholder="Describe this use case in 1–2 sentences…"
                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400 resize-none"><?= htmlspecialchars($v['description']) ?></textarea>
    </div>

    <!-- Image URL -->
    <div>
      <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Image URL</label>
      <input name="image_url" id="imgUrl" value="<?= htmlspecialchars($v['image_url'] ?? '') ?>"
             oninput="document.getElementById('imgPreview').src=this.value"
             placeholder="/images/my-photo.jpg  or  https://…"
             class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" />
      <div class="mt-2 rounded-xl overflow-hidden h-32 bg-slate-100 flex items-center justify-center">
        <img id="imgPreview" src="<?= htmlspecialchars($v['image_url'] ?? '') ?>" alt=""
             class="w-full h-full object-cover"
             onerror="this.style.opacity=0" style="opacity:<?= $v['image_url'] ? 1 : 0 ?>" />
      </div>
    </div>

    <div class="grid grid-cols-2 gap-5">
      <!-- Sort order -->
      <div>
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Sort Order</label>
        <input name="sort_order" type="number" min="0" value="<?= (int)$v['sort_order'] ?>"
               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400" />
      </div>

      <!-- Active toggle -->
      <div class="flex flex-col justify-center">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_active" value="1"
                 <?= $v['is_active'] ? 'checked' : '' ?>
                 class="w-4 h-4 rounded accent-orange-500" />
          <span class="text-sm font-semibold text-slate-700">Visible on website</span>
        </label>
      </div>
    </div>

    <!-- Submit -->
    <div class="flex gap-3 pt-2">
      <button type="submit"
              class="flex-1 py-3 rounded-xl text-white font-bold text-sm hover:opacity-90 transition-opacity"
              style="background:#f97316">
        <?= $id ? 'Save Changes' : 'Create Use Case' ?>
      </button>
      <a href="use_cases.php"
         class="px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-50 transition-colors">
        Cancel
      </a>
    </div>
  </form>
</div>

<?php close_layout(); ?>
