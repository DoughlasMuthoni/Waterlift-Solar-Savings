<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';
check_auth();

// ── Filters ───────────────────────────────────────────────────────────────────
$search   = trim($_GET['q']        ?? '');
$fStatus  = $_GET['status']        ?? '';
$fTier    = $_GET['tier']          ?? '';
$fProp    = $_GET['prop']          ?? '';
$fCounty  = $_GET['county']        ?? '';
$fSource  = $_GET['source']        ?? '';
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;

$where  = ['1=1'];
$params = [];

if ($search !== '') {
    $where[]  = '(name LIKE :q OR phone LIKE :q OR email LIKE :q)';
    $params[':q'] = "%$search%";
}
if ($fStatus)  { $where[] = 'status = :status';              $params[':status'] = $fStatus; }
if ($fTier)    { $where[] = 'package_tier = :tier';          $params[':tier']   = $fTier;   }
if ($fProp)    { $where[] = 'property_category = :prop';     $params[':prop']   = $fProp;   }
if ($fCounty)  { $where[] = 'county LIKE :county';           $params[':county'] = "%$fCounty%"; }
if ($fSource)  { $where[] = 'source = :source';              $params[':source'] = $fSource; }

$where_sql = implode(' AND ', $where);

// Count
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE $where_sql");
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();
$total_pages = max(1, (int)ceil($total_rows / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

// Fetch
$sql = "SELECT id,name,phone,email,county,ownership_status,property_category,
               package_tier,payment_model,monthly_bill,has_borehole,
               status,source,created_at
        FROM leads WHERE $where_sql
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit',  $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
$stmt->execute();
$leads = $stmt->fetchAll();

// Distinct counties for filter dropdown
$counties = $pdo->query("SELECT DISTINCT county FROM leads WHERE county IS NOT NULL ORDER BY county")->fetchAll(PDO::FETCH_COLUMN);

$status_colors = [
    'new'       => ['bg'=>'#dbeafe','text'=>'#1d4ed8'],
    'contacted' => ['bg'=>'#fef9c3','text'=>'#a16207'],
    'qualified' => ['bg'=>'#dcfce7','text'=>'#16a34a'],
    'converted' => ['bg'=>'#f0fdf4','text'=>'#15803d'],
    'lost'      => ['bg'=>'#fee2e2','text'=>'#dc2626'],
];

function qs(array $override = []): string {
    $p = array_merge($_GET, $override);
    return '?' . http_build_query(array_filter($p, fn($v) => $v !== ''));
}

open_layout('All Leads', 'leads');
?>

<!-- ── Toolbar ────────────────────────────────────────────────────────────── -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 sm:p-5 mb-5">

  <!-- Mobile: search + toggle filters row -->
  <form method="GET" id="filterForm">
    <div class="flex gap-2 mb-3">
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
        placeholder="Search name, phone or email…"
        class="flex-1 border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-orange-400 bg-slate-50 focus:bg-white transition-colors" />
      <button type="button" onclick="toggleFilters()"
        class="sm:hidden flex items-center gap-1.5 px-3 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50">
        <span class="material-icons text-base">tune</span>
        <?php if ($fStatus || $fTier || $fProp || $fSource): ?>
        <span class="w-2 h-2 rounded-full bg-orange-500 inline-block"></span>
        <?php endif; ?>
      </button>
    </div>

    <!-- Filters (always shown on sm+, toggled on mobile) -->
    <div id="filterPanel" class="<?= ($fStatus || $fTier || $fProp || $fSource) ? '' : 'hidden' ?> sm:block">
      <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2 sm:gap-3 mb-3">

        <div class="col-span-1">
          <label class="block text-[10px] font-bold mb-1 text-slate-400 uppercase tracking-wide">Status</label>
          <select name="status" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-orange-400 bg-slate-50">
            <option value="">All Status</option>
            <?php foreach (['new','contacted','qualified','converted','lost'] as $s): ?>
            <option value="<?= $s ?>" <?= $fStatus===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-span-1">
          <label class="block text-[10px] font-bold mb-1 text-slate-400 uppercase tracking-wide">Tier</label>
          <select name="tier" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-orange-400 bg-slate-50">
            <option value="">All Tiers</option>
            <?php foreach (['Essential','Standard','Premium'] as $t): ?>
            <option value="<?= $t ?>" <?= $fTier===$t?'selected':'' ?>><?= $t ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-span-1">
          <label class="block text-[10px] font-bold mb-1 text-slate-400 uppercase tracking-wide">Property</label>
          <select name="prop" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-orange-400 bg-slate-50">
            <option value="">All Types</option>
            <?php foreach (['residential','commercial','warehouse','industrial'] as $p): ?>
            <option value="<?= $p ?>" <?= $fProp===$p?'selected':'' ?>><?= ucfirst($p) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-span-1">
          <label class="block text-[10px] font-bold mb-1 text-slate-400 uppercase tracking-wide">Source</label>
          <select name="source" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-orange-400 bg-slate-50">
            <option value="">All Sources</option>
            <?php foreach (['contact_form','wizard_then_form','whatsapp'] as $src): ?>
            <option value="<?= $src ?>" <?= $fSource===$src?'selected':'' ?>><?= str_replace('_',' ',ucfirst($src)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <!-- Action buttons -->
    <div class="flex flex-wrap gap-2">
      <button type="submit"
        class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl font-bold text-white text-sm"
        style="background:#0f2d52">
        <span class="material-icons text-sm align-middle mr-1">search</span>Filter
      </button>
      <a href="<?= ADMIN_BASE ?>/leads.php"
        class="flex-1 sm:flex-none text-center px-5 py-2.5 rounded-xl font-bold text-sm border border-slate-200 text-slate-500 hover:bg-slate-50 transition-colors">
        Reset
      </a>
      <a href="<?= ADMIN_BASE ?>/api/export.php<?= qs() ?>"
        class="flex-1 sm:flex-none text-center px-5 py-2.5 rounded-xl font-bold text-white text-sm flex items-center justify-center gap-1.5"
        style="background:#16a34a">
        <span class="material-icons text-base">download</span> CSV
      </a>
    </div>
  </form>
</div>

<!-- ── Results info ────────────────────────────────────────────────────────── -->
<div class="flex items-center justify-between mb-3">
  <p class="text-xs sm:text-sm text-slate-500">
    Showing <strong class="text-slate-700"><?= count($leads) ?></strong> of
    <strong class="text-slate-700"><?= $total_rows ?></strong> leads
    <?= $search ? ' matching "<em>'.htmlspecialchars($search).'</em>"' : '' ?>
  </p>
</div>

<!-- ── Desktop Table ──────────────────────────────────────────────────────── -->
<div class="hidden md:block bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-5">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-slate-50 text-xs uppercase tracking-wide text-slate-400 border-b border-slate-100">
          <th class="px-5 py-3 text-left font-semibold">Name</th>
          <th class="px-5 py-3 text-left font-semibold">Phone</th>
          <th class="px-5 py-3 text-left font-semibold">County</th>
          <th class="px-5 py-3 text-left font-semibold">Property</th>
          <th class="px-5 py-3 text-left font-semibold">Bill (KES)</th>
          <th class="px-5 py-3 text-left font-semibold">Package</th>
          <th class="px-5 py-3 text-left font-semibold">Model</th>
          <th class="px-5 py-3 text-left font-semibold">Status</th>
          <th class="px-5 py-3 text-left font-semibold">Date</th>
          <th class="px-5 py-3 text-left font-semibold">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-50">
        <?php foreach ($leads as $lead):
          $sc = $status_colors[$lead['status']] ?? ['bg'=>'#f1f5f9','text'=>'#64748b'];
        ?>
        <tr class="hover:bg-orange-50/40 transition-colors">
          <td class="px-5 py-3.5">
            <div class="font-semibold text-slate-800"><?= htmlspecialchars($lead['name']) ?></div>
            <?php if ($lead['email']): ?>
            <div class="text-xs text-slate-400"><?= htmlspecialchars($lead['email']) ?></div>
            <?php endif; ?>
          </td>
          <td class="px-5 py-3.5 text-slate-600">
            <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="hover:text-orange-500 font-medium"><?= htmlspecialchars($lead['phone']) ?></a>
          </td>
          <td class="px-5 py-3.5 text-slate-600 text-xs"><?= htmlspecialchars($lead['county'] ?? '—') ?></td>
          <td class="px-5 py-3.5 text-xs capitalize text-slate-500"><?= $lead['property_category'] ?? '—' ?></td>
          <td class="px-5 py-3.5 text-xs font-medium text-slate-700">
            <?= $lead['monthly_bill'] ? 'KES '.number_format($lead['monthly_bill']) : '—' ?>
          </td>
          <td class="px-5 py-3.5">
            <?php if ($lead['package_tier']): ?>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-orange-50 text-orange-700"><?= $lead['package_tier'] ?></span>
            <?php else: echo '<span class="text-slate-300 text-xs">—</span>'; endif; ?>
          </td>
          <td class="px-5 py-3.5 text-xs text-slate-500"><?= $lead['payment_model'] ?? '—' ?></td>
          <td class="px-5 py-3.5">
            <select onchange="updateStatus(<?= $lead['id'] ?>, this.value, this)"
                    class="text-xs font-semibold rounded-full px-2.5 py-1 border-0 outline-none cursor-pointer capitalize"
                    style="background:<?= $sc['bg'] ?>;color:<?= $sc['text'] ?>">
              <?php foreach (['new','contacted','qualified','converted','lost'] as $s): ?>
              <option value="<?= $s ?>" <?= $lead['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td class="px-5 py-3.5 text-xs text-slate-400 whitespace-nowrap"><?= date('d M Y', strtotime($lead['created_at'])) ?></td>
          <td class="px-5 py-3.5">
            <div class="flex items-center gap-2">
              <a href="<?= ADMIN_BASE ?>/lead.php?id=<?= $lead['id'] ?>"
                 class="text-xs font-bold px-3 py-1.5 rounded-lg text-white" style="background:#0f2d52">View</a>
              <a href="https://wa.me/<?= preg_replace('/\D/','',$lead['phone']) ?>?text=Hello+<?= urlencode($lead['name']) ?>%2C+this+is+Waterlift+Solar..."
                 target="_blank"
                 class="text-xs font-bold px-3 py-1.5 rounded-lg text-white" style="background:#25D366">WA</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($leads)): ?>
        <tr><td colspan="10" class="px-6 py-12 text-center text-slate-400">No leads found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ── Mobile Cards ────────────────────────────────────────────────────────── -->
<div class="md:hidden space-y-3 mb-5">
  <?php if (empty($leads)): ?>
  <div class="bg-white rounded-2xl p-8 text-center text-slate-400 text-sm border border-slate-100">No leads found.</div>
  <?php endif; ?>
  <?php foreach ($leads as $lead):
    $sc = $status_colors[$lead['status']] ?? ['bg'=>'#f1f5f9','text'=>'#64748b'];
  ?>
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
    <!-- Header row -->
    <div class="flex items-start justify-between mb-2">
      <div>
        <p class="font-bold text-slate-800"><?= htmlspecialchars($lead['name']) ?></p>
        <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="text-sm font-medium" style="color:#f97316">
          <?= htmlspecialchars($lead['phone']) ?>
        </a>
      </div>
      <select onchange="updateStatus(<?= $lead['id'] ?>, this.value, this)"
              class="text-xs font-semibold rounded-full px-2.5 py-1 border-0 outline-none cursor-pointer capitalize shrink-0"
              style="background:<?= $sc['bg'] ?>;color:<?= $sc['text'] ?>">
        <?php foreach (['new','contacted','qualified','converted','lost'] as $s): ?>
        <option value="<?= $s ?>" <?= $lead['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <!-- Tags row -->
    <div class="flex flex-wrap gap-1.5 mb-3">
      <?php if ($lead['county']): ?>
      <span class="text-xs bg-slate-50 border border-slate-200 rounded-full px-2.5 py-0.5 text-slate-500">📍 <?= htmlspecialchars($lead['county']) ?></span>
      <?php endif; ?>
      <?php if ($lead['package_tier']): ?>
      <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-orange-50 text-orange-700">☀️ <?= $lead['package_tier'] ?></span>
      <?php endif; ?>
      <?php if ($lead['monthly_bill']): ?>
      <span class="text-xs bg-slate-50 border border-slate-200 rounded-full px-2.5 py-0.5 text-slate-500">KES <?= number_format($lead['monthly_bill']) ?>/mo</span>
      <?php endif; ?>
      <span class="text-xs bg-slate-50 border border-slate-200 rounded-full px-2.5 py-0.5 text-slate-400"><?= date('d M Y', strtotime($lead['created_at'])) ?></span>
    </div>
    <!-- Actions -->
    <div class="flex gap-2">
      <a href="<?= ADMIN_BASE ?>/lead.php?id=<?= $lead['id'] ?>"
         class="flex-1 text-center text-xs font-bold py-2 rounded-xl text-white" style="background:#0f2d52">View Details</a>
      <a href="https://wa.me/<?= preg_replace('/\D/','',$lead['phone']) ?>?text=Hello+<?= urlencode($lead['name']) ?>%2C+this+is+Waterlift+Solar..."
         target="_blank"
         class="flex-1 text-center text-xs font-bold py-2 rounded-xl text-white" style="background:#25D366">WhatsApp</a>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ── Pagination ─────────────────────────────────────────────────────────── -->
<?php if ($total_pages > 1): ?>
<div class="flex justify-center flex-wrap gap-1">
  <?php if ($page > 1): ?>
  <a href="<?= qs(['page' => $page-1]) ?>" class="px-3 sm:px-4 py-2 rounded-xl text-sm font-medium border border-slate-200 hover:bg-slate-50">← Prev</a>
  <?php endif; ?>
  <?php for ($i = max(1,$page-2); $i <= min($total_pages,$page+2); $i++): ?>
  <a href="<?= qs(['page' => $i]) ?>"
     class="px-3 sm:px-4 py-2 rounded-xl text-sm font-bold <?= $i===$page ? 'text-white' : 'border border-slate-200 hover:bg-slate-50 text-slate-600' ?>"
     style="<?= $i===$page ? 'background:#f97316' : '' ?>"><?= $i ?></a>
  <?php endfor; ?>
  <?php if ($page < $total_pages): ?>
  <a href="<?= qs(['page' => $page+1]) ?>" class="px-3 sm:px-4 py-2 rounded-xl text-sm font-medium border border-slate-200 hover:bg-slate-50">Next →</a>
  <?php endif; ?>
</div>
<?php endif; ?>

<script>
function toggleFilters() {
  const p = document.getElementById('filterPanel');
  p.classList.toggle('hidden');
}

const STATUS_COLORS = {
  new:       {bg:'#dbeafe',text:'#1d4ed8'},
  contacted: {bg:'#fef9c3',text:'#a16207'},
  qualified: {bg:'#dcfce7',text:'#16a34a'},
  converted: {bg:'#f0fdf4',text:'#15803d'},
  lost:      {bg:'#fee2e2',text:'#dc2626'},
};

async function updateStatus(id, status, el) {
  el.disabled = true;
  try {
    const res = await fetch('<?= ADMIN_BASE ?>/api/update_lead.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, status }),
    });
    const json = await res.json();
    if (json.success) {
      const c = STATUS_COLORS[status] || {bg:'#f1f5f9',text:'#64748b'};
      el.style.background = c.bg;
      el.style.color       = c.text;
    } else {
      alert('Could not update status. Please try again.');
      el.value = el.dataset.original || el.value;
    }
  } catch {
    alert('Network error.');
  } finally {
    el.disabled = false;
  }
}
</script>

<?php close_layout(); ?>
