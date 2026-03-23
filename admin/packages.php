<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';
check_auth();

$packages = $pdo->query("SELECT * FROM packages ORDER BY sort_order ASC, id ASC")->fetchAll();

open_layout('Packages', 'packages');
?>

<!-- Header row -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
  <div>
    <p class="text-sm text-slate-500 mt-0.5">Manage your solar package catalogue. Changes are reflected live on the website.</p>
  </div>
  <a href="/waterlift_solat_savings/admin/package_form.php"
     class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-white text-sm shrink-0"
     style="background:linear-gradient(135deg,#f97316,#c2410c)">
    <span class="material-icons text-base">add</span> Add New Package
  </a>
</div>

<!-- Live preview notice -->
<div class="flex items-center gap-3 bg-blue-50 border border-blue-200 rounded-2xl px-5 py-3 mb-6 text-sm">
  <span class="material-icons text-blue-500">info</span>
  <p class="text-blue-700">
    Packages are served live via <code class="bg-blue-100 px-1.5 py-0.5 rounded font-mono text-xs">/api/get_packages.php</code>.
    Only <strong>Active</strong> packages appear on the public website.
  </p>
</div>

<!-- Package cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
  <?php foreach ($packages as $pkg):
    $features = [];
    if ($pkg['features']) {
      $dec = json_decode($pkg['features'], true);
      if (is_array($dec)) $features = $dec;
    }
  ?>
  <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex flex-col
              <?= !$pkg['is_active'] ? 'opacity-60' : '' ?>">

    <!-- Gradient header -->
    <div class="relative px-6 pt-6 pb-8" style="background:<?= htmlspecialchars($pkg['gradient']) ?>">

      <!-- Active/Inactive toggle -->
      <button onclick="togglePackage(<?= $pkg['id'] ?>, <?= $pkg['is_active'] ? 0 : 1 ?>, this)"
              class="absolute top-3 right-3 text-xs font-bold px-3 py-1 rounded-full transition-all"
              style="background:<?= $pkg['is_active'] ? 'rgba(34,197,94,0.2)' : 'rgba(255,255,255,0.15)' ?>;
                     color:<?= $pkg['is_active'] ? '#4ade80' : 'rgba(255,255,255,0.6)' ?>">
        <?= $pkg['is_active'] ? '● Active' : '○ Inactive' ?>
      </button>

      <?php if ($pkg['badge']): ?>
      <span class="absolute top-3 left-6 text-xs font-black px-2.5 py-1 rounded-full"
            style="background:#f59e0b;color:#0f2d52"><?= htmlspecialchars($pkg['badge']) ?></span>
      <?php endif; ?>

      <div class="mt-4 text-3xl mb-2"><?= htmlspecialchars($pkg['icon']) ?></div>
      <h3 class="text-xl font-extrabold text-white"><?= htmlspecialchars($pkg['name']) ?></h3>
      <div class="flex items-center gap-2 mt-1">
        <span class="text-xs font-bold px-2.5 py-1 rounded-full text-white" style="background:rgba(255,255,255,0.2)">
          <?= htmlspecialchars($pkg['capacity']) ?>
        </span>
        <span class="text-white/60 text-xs"><?= htmlspecialchars($pkg['tagline'] ?? '') ?></span>
      </div>
    </div>

    <!-- Pricing summary -->
    <div class="grid grid-cols-3 divide-x divide-slate-100 border-b border-slate-100 text-center">
      <?php
      $pricing = [
        ['Rent', $pkg['rent_monthly'] ? 'KES '.number_format($pkg['rent_monthly']).'/mo' : '—'],
        ['RTO',  $pkg['rto_monthly']  ? 'KES '.number_format($pkg['rto_monthly']).'/mo'  : '—'],
        ['Cash', $pkg['cash_price']   ? 'KES '.number_format($pkg['cash_price'])          : '—'],
      ];
      foreach ($pricing as [$label, $val]):
      ?>
      <div class="py-3 px-2">
        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400"><?= $label ?></p>
        <p class="text-xs font-extrabold text-slate-700 mt-0.5"><?= $val ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Features -->
    <div class="px-5 py-4 flex-1">
      <?php if ($features): ?>
      <ul class="space-y-1.5">
        <?php foreach (array_slice($features, 0, 4) as $f): ?>
        <li class="flex items-center gap-2 text-xs text-slate-600">
          <span class="w-4 h-4 rounded-full flex items-center justify-center shrink-0 text-[9px] font-bold"
                style="background:#fff7ed;color:#f97316">✓</span>
          <?= htmlspecialchars($f) ?>
        </li>
        <?php endforeach; ?>
        <?php if (count($features) > 4): ?>
        <li class="text-xs text-slate-400">+<?= count($features) - 4 ?> more…</li>
        <?php endif; ?>
      </ul>
      <?php else: ?>
      <p class="text-xs text-slate-400 italic">No features listed</p>
      <?php endif; ?>
    </div>

    <!-- Actions -->
    <div class="px-5 pb-5 flex gap-2">
      <a href="/waterlift_solat_savings/admin/package_form.php?id=<?= $pkg['id'] ?>"
         class="flex-1 text-center text-xs font-bold py-2.5 rounded-xl text-white"
         style="background:#0f2d52">
        <span class="material-icons text-sm align-middle mr-1">edit</span>Edit
      </a>
      <button onclick="deletePackage(<?= $pkg['id'] ?>, '<?= htmlspecialchars(addslashes($pkg['name'])) ?>')"
              class="text-xs font-bold px-4 py-2.5 rounded-xl text-red-500 border border-red-200 hover:bg-red-50 transition-colors">
        <span class="material-icons text-sm align-middle">delete</span>
      </button>
    </div>
  </div>
  <?php endforeach; ?>

  <!-- Add new card -->
  <a href="/waterlift_solat_savings/admin/package_form.php"
     class="border-2 border-dashed border-slate-200 rounded-3xl flex flex-col items-center justify-center p-10 text-slate-400 hover:border-orange-300 hover:text-orange-400 transition-all min-h-64">
    <span class="material-icons text-4xl mb-2">add_circle_outline</span>
    <span class="text-sm font-semibold">Add New Package</span>
  </a>
</div>

<script>
async function togglePackage(id, newActive, btn) {
  btn.disabled = true;
  const res  = await fetch('/waterlift_solat_savings/admin/api/toggle_package.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id, active: newActive }),
  });
  const json = await res.json();
  if (json.success) location.reload();
  else { alert('Failed to update.'); btn.disabled = false; }
}

async function deletePackage(id, name) {
  if (!confirm(`Delete the "${name}" package? This cannot be undone.`)) return;
  const res  = await fetch('/waterlift_solat_savings/admin/api/delete_package.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id }),
  });
  const json = await res.json();
  if (json.success) location.reload();
  else alert('Failed to delete.');
}
</script>

<?php close_layout(); ?>
