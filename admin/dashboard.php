<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';
check_auth();

// ── Stats queries ─────────────────────────────────────────────────────────────
$total        = (int)$pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$today        = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE DATE(created_at)=CURDATE()")->fetchColumn();
$this_month   = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE YEAR(created_at)=YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW())")->fetchColumn();
$converted    = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE status='converted'")->fetchColumn();
$conv_rate    = $total > 0 ? round($converted / $total * 100, 1) : 0;

// Leads by status
$by_status = $pdo->query("SELECT status, COUNT(*) AS cnt FROM leads GROUP BY status ORDER BY cnt DESC")->fetchAll();

// Leads by package tier
$by_tier = $pdo->query("SELECT COALESCE(package_tier,'Unknown') AS tier, COUNT(*) AS cnt FROM leads GROUP BY package_tier ORDER BY cnt DESC")->fetchAll();

// Top counties
$by_county = $pdo->query("SELECT COALESCE(county,'Unknown') AS county, COUNT(*) AS cnt FROM leads GROUP BY county ORDER BY cnt DESC LIMIT 8")->fetchAll();

// Leads by property category
$by_prop = $pdo->query("SELECT COALESCE(property_category,'Unknown') AS cat, COUNT(*) AS cnt FROM leads GROUP BY property_category ORDER BY cnt DESC")->fetchAll();

// Recent leads
$recent = $pdo->query("SELECT id,name,phone,county,package_tier,payment_model,status,source,created_at FROM leads ORDER BY created_at DESC LIMIT 10")->fetchAll();

// 30-day trend (leads per day)
$trend = $pdo->query("SELECT DATE(created_at) AS d, COUNT(*) AS cnt FROM leads WHERE created_at >= DATE_SUB(NOW(),INTERVAL 30 DAY) GROUP BY d ORDER BY d")->fetchAll();

open_layout('Dashboard', 'dashboard');

$status_colors = [
    'new'       => ['bg'=>'#dbeafe','text'=>'#1d4ed8'],
    'contacted' => ['bg'=>'#fef9c3','text'=>'#a16207'],
    'qualified' => ['bg'=>'#dcfce7','text'=>'#16a34a'],
    'converted' => ['bg'=>'#f0fdf4','text'=>'#15803d'],
    'lost'      => ['bg'=>'#fee2e2','text'=>'#dc2626'],
];
?>

<!-- ── Stat Cards ──────────────────────────────────────────────────────────── -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5 mb-6 sm:mb-8">
  <?php
  $cards = [
    ['Total Leads',    $total,       '👥', '#0f2d52', '#dbeafe'],
    ['New Today',      $today,       '⚡', '#059669', '#dcfce7'],
    ['This Month',     $this_month,  '📅', '#9333ea', '#f3e8ff'],
    ['Conversion Rate',$conv_rate.'%','🎯', '#f97316', '#fff7ed'],
  ];
  foreach ($cards as [$label, $val, $icon, $color, $bg]):
  ?>
  <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-slate-100">
    <div class="flex items-center justify-between mb-2 sm:mb-3">
      <span class="text-xl sm:text-2xl"><?= $icon ?></span>
      <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wide px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full"
            style="background:<?= $bg ?>;color:<?= $color ?>"><?= $label ?></span>
    </div>
    <div class="text-2xl sm:text-3xl font-black" style="color:<?= $color ?>"><?= $val ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ── Charts row ─────────────────────────────────────────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">

  <!-- 30-day trend -->
  <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <h3 class="font-bold text-sm mb-4" style="color:#0f2d52">Leads — Last 30 Days</h3>
    <canvas id="trendChart" height="120"></canvas>
  </div>

  <!-- By status -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <h3 class="font-bold text-sm mb-4" style="color:#0f2d52">By Status</h3>
    <canvas id="statusChart" height="180"></canvas>
    <div class="mt-4 space-y-2">
      <?php foreach ($by_status as $row):
        $c = $status_colors[$row['status']] ?? ['bg'=>'#f1f5f9','text'=>'#475569'];
      ?>
      <div class="flex items-center justify-between text-xs">
        <span class="px-2 py-0.5 rounded-full font-semibold capitalize"
              style="background:<?= $c['bg'] ?>;color:<?= $c['text'] ?>"><?= $row['status'] ?></span>
        <span class="font-bold text-slate-700"><?= $row['cnt'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ── Bottom row ─────────────────────────────────────────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">

  <!-- Package tiers -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <h3 class="font-bold text-sm mb-4" style="color:#0f2d52">By Package Tier</h3>
    <?php
    $tier_styles = ['Essential'=>['#dbeafe','#1d4ed8'],'Standard'=>['#cffafe','#0e7490'],'Premium'=>['#ffedd5','#c2410c'],'Unknown'=>['#f1f5f9','#64748b']];
    foreach ($by_tier as $row):
      [$tbg,$tcol] = $tier_styles[$row['tier']] ?? ['#f1f5f9','#64748b'];
      $pct = $total > 0 ? round($row['cnt']/$total*100) : 0;
    ?>
    <div class="mb-3">
      <div class="flex justify-between text-xs mb-1">
        <span class="font-semibold px-2 py-0.5 rounded-full" style="background:<?= $tbg ?>;color:<?= $tcol ?>"><?= $row['tier'] ?></span>
        <span class="font-bold text-slate-600"><?= $row['cnt'] ?> (<?= $pct ?>%)</span>
      </div>
      <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
        <div class="h-full rounded-full transition-all" style="width:<?= $pct ?>%;background:<?= $tcol ?>"></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Property types -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <h3 class="font-bold text-sm mb-4" style="color:#0f2d52">By Property Type</h3>
    <?php
    $prop_icons = ['residential'=>'🏠','commercial'=>'🏢','warehouse'=>'🏭','industrial'=>'⚙️','Unknown'=>'❓'];
    foreach ($by_prop as $row):
      $pct = $total > 0 ? round($row['cnt']/$total*100) : 0;
    ?>
    <div class="flex items-center gap-3 mb-3">
      <span class="text-xl"><?= $prop_icons[$row['cat']] ?? '❓' ?></span>
      <div class="flex-1">
        <div class="flex justify-between text-xs mb-1">
          <span class="font-medium capitalize text-slate-600"><?= $row['cat'] ?></span>
          <span class="font-bold text-slate-600"><?= $row['cnt'] ?></span>
        </div>
        <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
          <div class="h-full rounded-full" style="width:<?= $pct ?>%;background:#f97316"></div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Top counties -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <h3 class="font-bold text-sm mb-4" style="color:#0f2d52">Top Counties</h3>
    <div class="space-y-2">
      <?php foreach ($by_county as $i => $row): ?>
      <div class="flex items-center gap-2 text-xs">
        <span class="w-5 h-5 rounded-full flex items-center justify-center font-bold text-white shrink-0"
              style="background:<?= $i === 0 ? '#f97316' : ($i < 3 ? '#0f2d52' : '#94a3b8') ?>;font-size:10px">
          <?= $i+1 ?>
        </span>
        <span class="flex-1 font-medium text-slate-700"><?= htmlspecialchars($row['county']) ?></span>
        <span class="font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600"><?= $row['cnt'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ── Recent Leads ────────────────────────────────────────────────────────── -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <div class="px-4 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between">
    <h3 class="font-bold text-sm" style="color:#0f2d52">Recent Leads</h3>
    <a href="/waterlift_solat_savings/admin/leads.php"
       class="text-xs font-bold px-3 sm:px-4 py-2 rounded-xl text-white"
       style="background:#f97316">View All →</a>
  </div>

  <!-- Desktop table -->
  <div class="hidden md:block overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-slate-50 text-xs uppercase tracking-wide text-slate-400">
          <th class="px-6 py-3 text-left font-semibold">Name</th>
          <th class="px-6 py-3 text-left font-semibold">Phone</th>
          <th class="px-6 py-3 text-left font-semibold">County</th>
          <th class="px-6 py-3 text-left font-semibold">Package</th>
          <th class="px-6 py-3 text-left font-semibold">Status</th>
          <th class="px-6 py-3 text-left font-semibold">Date</th>
          <th class="px-6 py-3 text-left font-semibold"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-50">
        <?php foreach ($recent as $lead):
          $sc = $status_colors[$lead['status']] ?? ['bg'=>'#f1f5f9','text'=>'#64748b'];
        ?>
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-6 py-3.5 font-semibold text-slate-800"><?= htmlspecialchars($lead['name']) ?></td>
          <td class="px-6 py-3.5 text-slate-600">
            <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="hover:text-orange-500"><?= htmlspecialchars($lead['phone']) ?></a>
          </td>
          <td class="px-6 py-3.5 text-slate-600 text-xs"><?= htmlspecialchars($lead['county'] ?? '—') ?></td>
          <td class="px-6 py-3.5">
            <?php if ($lead['package_tier']): ?>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-orange-50 text-orange-700"><?= $lead['package_tier'] ?></span>
            <?php else: echo '<span class="text-slate-400 text-xs">—</span>'; endif; ?>
          </td>
          <td class="px-6 py-3.5">
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full capitalize"
                  style="background:<?= $sc['bg'] ?>;color:<?= $sc['text'] ?>"><?= $lead['status'] ?></span>
          </td>
          <td class="px-6 py-3.5 text-xs text-slate-400"><?= date('d M Y', strtotime($lead['created_at'])) ?></td>
          <td class="px-6 py-3.5">
            <a href="/waterlift_solat_savings/admin/lead.php?id=<?= $lead['id'] ?>"
               class="text-xs font-bold px-3 py-1.5 rounded-lg text-white" style="background:#0f2d52">View</a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recent)): ?>
        <tr><td colspan="7" class="px-6 py-10 text-center text-slate-400 text-sm">No leads yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Mobile cards -->
  <div class="md:hidden p-3 space-y-3">
    <?php if (empty($recent)): ?>
    <p class="text-center text-slate-400 text-sm py-6">No leads yet.</p>
    <?php endif; ?>
    <?php foreach ($recent as $lead):
      $sc = $status_colors[$lead['status']] ?? ['bg'=>'#f1f5f9','text'=>'#64748b'];
    ?>
    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
      <div class="flex items-start justify-between mb-3">
        <div>
          <p class="font-bold text-slate-800"><?= htmlspecialchars($lead['name']) ?></p>
          <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="text-sm text-orange-500 font-medium"><?= htmlspecialchars($lead['phone']) ?></a>
        </div>
        <span class="text-xs font-semibold px-2.5 py-1 rounded-full capitalize shrink-0"
              style="background:<?= $sc['bg'] ?>;color:<?= $sc['text'] ?>"><?= $lead['status'] ?></span>
      </div>
      <div class="flex flex-wrap gap-2 mb-3">
        <?php if ($lead['county']): ?>
        <span class="text-xs bg-white border border-slate-200 rounded-full px-2.5 py-1 text-slate-600">📍 <?= htmlspecialchars($lead['county']) ?></span>
        <?php endif; ?>
        <?php if ($lead['package_tier']): ?>
        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-orange-50 text-orange-700">☀️ <?= $lead['package_tier'] ?></span>
        <?php endif; ?>
        <span class="text-xs bg-white border border-slate-200 rounded-full px-2.5 py-1 text-slate-400"><?= date('d M Y', strtotime($lead['created_at'])) ?></span>
      </div>
      <a href="/waterlift_solat_savings/admin/lead.php?id=<?= $lead['id'] ?>"
         class="block text-center text-xs font-bold py-2 rounded-xl text-white" style="background:#0f2d52">
        View Details →
      </a>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
// 30-day trend
const trendLabels = <?= json_encode(array_column($trend,'d')) ?>;
const trendData   = <?= json_encode(array_column($trend,'cnt')) ?>;
new Chart(document.getElementById('trendChart'), {
  type: 'line',
  data: {
    labels: trendLabels,
    datasets: [{
      label: 'Leads',
      data: trendData,
      borderColor: '#f97316',
      backgroundColor: 'rgba(249,115,22,0.08)',
      borderWidth: 2,
      fill: true,
      tension: 0.4,
      pointRadius: 3,
      pointBackgroundColor: '#f97316',
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { maxTicksLimit: 7, font: { size: 11 } } },
      y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: '#f1f5f9' } }
    }
  }
});

// Status doughnut
const statusLabels = <?= json_encode(array_column($by_status,'status')) ?>;
const statusData   = <?= json_encode(array_column($by_status,'cnt')) ?>;
const statusColors = { new:'#3b82f6', contacted:'#eab308', qualified:'#22c55e', converted:'#16a34a', lost:'#ef4444' };
new Chart(document.getElementById('statusChart'), {
  type: 'doughnut',
  data: {
    labels: statusLabels,
    datasets: [{
      data: statusData,
      backgroundColor: statusLabels.map(s => statusColors[s] || '#94a3b8'),
      borderWidth: 2,
      borderColor: '#fff',
    }]
  },
  options: {
    cutout: '65%',
    plugins: { legend: { display: false } }
  }
});
</script>

<?php close_layout(); ?>
