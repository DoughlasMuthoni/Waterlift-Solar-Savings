<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';
open_layout('Testimonials', 'testimonials');

$filter = $_GET['filter'] ?? 'pending';  // pending | approved | all
$where  = match($filter) {
    'approved' => 'WHERE is_approved = 1',
    'pending'  => 'WHERE is_approved = 0',
    default    => '',
};

$rows = $pdo->query("SELECT * FROM testimonials $where ORDER BY created_at DESC")
            ->fetchAll(PDO::FETCH_ASSOC);

$counts = $pdo->query(
    "SELECT
        SUM(is_approved = 0) AS pending,
        SUM(is_approved = 1) AS approved,
        COUNT(*) AS total
     FROM testimonials"
)->fetch(PDO::FETCH_ASSOC);
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <h2 class="text-xl font-extrabold" style="color:#0f2d52">Testimonials</h2>
    <p class="text-sm text-slate-400 mt-0.5">Approve customer reviews before they appear on the website.</p>
  </div>
</div>

<!-- Filter tabs -->
<div class="flex gap-2 mb-6 flex-wrap">
  <?php
  $tabs = [
    ['key'=>'pending',  'label'=>'Pending',  'count'=>(int)$counts['pending'],  'color'=>'#f97316'],
    ['key'=>'approved', 'label'=>'Approved', 'count'=>(int)$counts['approved'], 'color'=>'#16a34a'],
    ['key'=>'all',      'label'=>'All',      'count'=>(int)$counts['total'],    'color'=>'#0f2d52'],
  ];
  foreach ($tabs as $tab):
    $active = $filter === $tab['key'];
  ?>
  <a href="?filter=<?= $tab['key'] ?>"
     class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-bold border transition-all"
     style="<?= $active ? "background:{$tab['color']};color:white;border-color:{$tab['color']}" : 'background:white;color:#64748b;border-color:#e2e8f0' ?>">
    <?= $tab['label'] ?>
    <span class="text-xs px-1.5 py-0.5 rounded-full"
          style="background:<?= $active ? 'rgba(255,255,255,0.25)' : '#f1f5f9' ?>;color:<?= $active ? 'white' : '#64748b' ?>">
      <?= $tab['count'] ?>
    </span>
  </a>
  <?php endforeach; ?>
</div>

<!-- Testimonial cards -->
<div class="space-y-4">
<?php if (empty($rows)): ?>
  <div class="text-center py-20 text-slate-400">
    <span class="material-icons text-5xl mb-3 block">reviews</span>
    <p class="font-semibold">No <?= $filter === 'all' ? '' : $filter ?> testimonials.</p>
  </div>
<?php endif; ?>

<?php foreach ($rows as $r): ?>
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex flex-col sm:flex-row gap-4">
  <!-- Avatar -->
  <div class="shrink-0">
    <?php if ($r['avatar_url']): ?>
      <img src="<?= htmlspecialchars($r['avatar_url']) ?>" alt="" class="w-12 h-12 rounded-full object-cover" />
    <?php else: ?>
      <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-black text-lg shrink-0"
           style="background:#0f2d52">
        <?= strtoupper(mb_substr($r['name'], 0, 1)) ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Content -->
  <div class="flex-1 min-w-0">
    <div class="flex flex-wrap items-center gap-2 mb-1">
      <span class="font-extrabold text-sm" style="color:#0f2d52"><?= htmlspecialchars($r['name']) ?></span>
      <?php if ($r['location']): ?>
        <span class="text-xs text-slate-400">· <?= htmlspecialchars($r['location']) ?></span>
      <?php endif; ?>
      <?php if ($r['package_label']): ?>
        <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#fff7ed;color:#f97316">
          <?= htmlspecialchars($r['package_label']) ?>
        </span>
      <?php endif; ?>
      <!-- Status badge -->
      <span class="text-xs font-bold px-2 py-0.5 rounded-full ml-auto
        <?= $r['is_approved'] ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' ?>">
        <?= $r['is_approved'] ? '✓ Approved' : '⏳ Pending' ?>
      </span>
    </div>

    <!-- Stars -->
    <div class="flex gap-0.5 mb-2">
      <?php for ($s = 1; $s <= 5; $s++): ?>
        <span style="color:<?= $s <= $r['stars'] ? '#f59e0b' : '#e2e8f0' ?>">★</span>
      <?php endfor; ?>
    </div>

    <p class="text-slate-600 text-sm leading-relaxed">"<?= htmlspecialchars($r['message']) ?>"</p>

    <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-slate-100 text-xs text-slate-400">
      <span><?= date('d M Y, H:i', strtotime($r['created_at'])) ?></span>
      <?php if ($r['is_featured']): ?>
        <span class="font-bold text-amber-500">★ Featured</span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Actions -->
  <div class="flex sm:flex-col gap-2 shrink-0 sm:items-end">
    <?php if (!$r['is_approved']): ?>
    <button onclick="approveTestimonial(<?= $r['id'] ?>)"
            class="flex items-center gap-1 text-xs font-bold px-3 py-2 rounded-xl text-white hover:opacity-90 transition-opacity"
            style="background:#16a34a">
      <span class="material-icons text-sm">check_circle</span> Approve
    </button>
    <?php else: ?>
    <button onclick="unapproveTestimonial(<?= $r['id'] ?>)"
            class="flex items-center gap-1 text-xs font-bold px-3 py-2 rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition-colors">
      <span class="material-icons text-sm">unpublished</span> Unpublish
    </button>
    <?php endif; ?>

    <button onclick="toggleFeatured(<?= $r['id'] ?>, <?= $r['is_featured'] ? 0 : 1 ?>)"
            class="flex items-center gap-1 text-xs font-bold px-3 py-2 rounded-xl border transition-colors
              <?= $r['is_featured'] ? 'border-amber-200 text-amber-600 hover:bg-amber-50' : 'border-slate-200 text-slate-500 hover:bg-slate-50' ?>">
      <span class="material-icons text-sm">star</span>
      <?= $r['is_featured'] ? 'Unfeature' : 'Feature' ?>
    </button>

    <button onclick="deleteTestimonial(<?= $r['id'] ?>, '<?= addslashes(htmlspecialchars($r['name'])) ?>')"
            class="flex items-center gap-1 text-xs font-bold px-3 py-2 rounded-xl border border-red-100 text-red-400 hover:bg-red-50 transition-colors">
      <span class="material-icons text-sm">delete</span>
    </button>
  </div>
</div>
<?php endforeach; ?>
</div>

<script>
async function approveTestimonial(id) {
  const res = await fetch('api/approve_testimonial.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id, approved: 1 })
  })
  if ((await res.json()).success) location.reload()
  else alert('Failed.')
}

async function unapproveTestimonial(id) {
  const res = await fetch('api/approve_testimonial.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id, approved: 0 })
  })
  if ((await res.json()).success) location.reload()
  else alert('Failed.')
}

async function toggleFeatured(id, featured) {
  const res = await fetch('api/approve_testimonial.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id, featured })
  })
  if ((await res.json()).success) location.reload()
  else alert('Failed.')
}

async function deleteTestimonial(id, name) {
  if (!confirm(`Delete testimonial from "${name}"? This cannot be undone.`)) return
  const res = await fetch('api/delete_testimonial.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id })
  })
  if ((await res.json()).success) location.reload()
  else alert('Failed.')
}
</script>

<?php close_layout(); ?>
