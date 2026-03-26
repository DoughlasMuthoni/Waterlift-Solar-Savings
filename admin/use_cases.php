<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';
open_layout('Use Cases', 'use_cases');

// Normalize a stored image_url for display in the admin
function admin_img(string $url): string {
    if (!$url) return '';
    if (str_starts_with($url, 'http')) return $url;
    if (!str_starts_with($url, '/')) $url = '/images/use-cases/' . $url;
    return SITE_BASE . $url;
}

$cases = $pdo->query(
    "SELECT * FROM use_cases ORDER BY sort_order ASC, id ASC"
)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex items-center justify-between mb-6">
  <div>
    <h2 class="text-xl font-extrabold" style="color:#0f2d52">Use Cases</h2>
    <p class="text-sm text-slate-400 mt-0.5">Manage the "What We Power" cards shown on the website.</p>
  </div>
  <a href="use_case_form.php"
     class="flex items-center gap-1.5 text-sm font-bold px-4 py-2.5 rounded-xl text-white shadow-sm hover:opacity-90 transition-opacity"
     style="background:#f97316">
    <span class="material-icons text-sm">add</span> Add Use Case
  </a>
</div>

<!-- Cards grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">

  <?php foreach ($cases as $c): ?>
  <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100 flex flex-col">
    <!-- Image preview -->
    <div class="relative h-44 bg-slate-100 overflow-hidden">
      <?php if ($c['image_url']): ?>
        <img src="<?= htmlspecialchars(admin_img($c['image_url'])) ?>" alt=""
             class="w-full h-full object-cover"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
      <?php endif; ?>
      <div class="<?= $c['image_url'] ? 'hidden' : 'flex' ?> absolute inset-0 items-center justify-center text-slate-300">
        <span class="material-icons text-5xl">image</span>
      </div>
      <!-- Status badge -->
      <span class="absolute top-3 left-3 text-xs font-bold px-2.5 py-1 rounded-full
        <?= $c['is_active'] ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-500' ?>">
        <?= $c['is_active'] ? 'Active' : 'Hidden' ?>
      </span>
      <!-- Tag badge -->
      <span class="absolute top-3 right-3 text-xs font-bold px-2.5 py-1 rounded-full text-white"
            style="background:#f97316">
        <?= htmlspecialchars($c['tag']) ?>
      </span>
    </div>

    <div class="p-5 flex-1 flex flex-col gap-2">
      <h3 class="font-extrabold text-base" style="color:#0f2d52"><?= htmlspecialchars($c['title']) ?></h3>
      <p class="text-slate-500 text-xs leading-relaxed flex-1"><?= htmlspecialchars($c['description']) ?></p>
      <?php if ($c['stat_label']): ?>
        <p class="text-xs font-bold" style="color:#f97316"><?= htmlspecialchars($c['stat_label']) ?></p>
      <?php endif; ?>

      <!-- Actions -->
      <div class="flex items-center gap-2 mt-3 pt-3 border-t border-slate-100">
        <a href="use_case_form.php?id=<?= $c['id'] ?>"
           class="flex-1 text-center text-xs font-bold py-2 rounded-xl bg-slate-100 hover:bg-slate-200 transition-colors" style="color:#0f2d52">
          Edit
        </a>
        <button onclick="toggleCase(<?= $c['id'] ?>, <?= $c['is_active'] ? 0 : 1 ?>)"
                class="flex-1 text-xs font-bold py-2 rounded-xl border transition-colors
                  <?= $c['is_active'] ? 'border-slate-200 text-slate-500 hover:bg-slate-50' : 'border-green-200 text-green-600 hover:bg-green-50' ?>">
          <?= $c['is_active'] ? 'Hide' : 'Show' ?>
        </button>
        <button onclick="deleteCase(<?= $c['id'] ?>, '<?= addslashes(htmlspecialchars($c['title'])) ?>')"
                class="w-9 h-9 flex items-center justify-center rounded-xl border border-red-100 text-red-400 hover:bg-red-50 transition-colors shrink-0">
          <span class="material-icons text-base">delete</span>
        </button>
      </div>
    </div>
  </div>
  <?php endforeach; ?>

  <!-- Add new card -->
  <a href="use_case_form.php"
     class="flex flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-slate-200 p-10 text-slate-400 hover:border-orange-300 hover:text-orange-400 transition-colors min-h-[260px]">
    <span class="material-icons text-4xl">add_circle_outline</span>
    <span class="text-sm font-semibold">Add Use Case</span>
  </a>
</div>

<?php if (empty($cases)): ?>
<div class="text-center py-20 text-slate-400">
  <span class="material-icons text-5xl mb-3 block">bolt</span>
  <p class="font-semibold">No use cases yet.</p>
  <a href="use_case_form.php" class="mt-4 inline-block text-sm font-bold px-5 py-2.5 rounded-xl text-white" style="background:#f97316">Add Your First Use Case</a>
</div>
<?php endif; ?>

<script>
async function toggleCase(id, active) {
  const res = await fetch('api/toggle_use_case.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id, active })
  })
  if ((await res.json()).success) location.reload()
  else alert('Failed to update.')
}

async function deleteCase(id, title) {
  if (!confirm(`Delete "${title}"? This cannot be undone.`)) return
  const res = await fetch('api/delete_use_case.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id })
  })
  if ((await res.json()).success) location.reload()
  else alert('Failed to delete.')
}
</script>

<?php close_layout(); ?>
