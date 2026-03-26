<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';
check_auth();

$id  = (int)($_GET['id'] ?? 0);
$pkg = null;
$err = [];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
    $stmt->execute([$id]);
    $pkg = $stmt->fetch();
    if (!$pkg) { header('Location: ' . ADMIN_BASE . '/packages.php'); exit; }
}

// ── Gradient presets ──────────────────────────────────────────────────────────
$THEMES = [
    'navy'   => ['label'=>'Navy',   'gradient'=>'linear-gradient(135deg,#0f2d52 0%,#1e4d8c 100%)',  'preview'=>'#0f2d52'],
    'cyan'   => ['label'=>'Cyan',   'gradient'=>'linear-gradient(135deg,#0891b2 0%,#06b6d4 100%)',  'preview'=>'#06b6d4'],
    'orange' => ['label'=>'Orange', 'gradient'=>'linear-gradient(135deg,#c2410c 0%,#f97316 100%)',  'preview'=>'#f97316'],
    'purple' => ['label'=>'Purple', 'gradient'=>'linear-gradient(135deg,#7c3aed 0%,#a855f7 100%)',  'preview'=>'#9333ea'],
    'green'  => ['label'=>'Green',  'gradient'=>'linear-gradient(135deg,#15803d 0%,#22c55e 100%)',  'preview'=>'#22c55e'],
    'rose'   => ['label'=>'Rose',   'gradient'=>'linear-gradient(135deg,#be123c 0%,#f43f5e 100%)',  'preview'=>'#f43f5e'],
    'gold'   => ['label'=>'Gold',   'gradient'=>'linear-gradient(135deg,#b45309 0%,#f59e0b 100%)',  'preview'=>'#f59e0b'],
    'slate'  => ['label'=>'Slate',  'gradient'=>'linear-gradient(135deg,#334155 0%,#64748b 100%)',  'preview'=>'#64748b'],
];

// Helper: find current theme key from stored gradient
function detectTheme(string $g, array $themes): string {
    foreach ($themes as $key => $t) {
        if ($t['gradient'] === $g) return $key;
    }
    return 'navy';
}

// ── Handle POST ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name']       ?? '');
    $capacity   = trim($_POST['capacity']   ?? '');
    $cap_num    = (float)($_POST['capacity_num']   ?? 0);
    $icon       = trim($_POST['icon']       ?? '☀️');
    $tagline    = trim($_POST['tagline']    ?? '');
    $badge      = trim($_POST['badge']      ?? '');
    $themeKey   = $_POST['theme'] ?? 'navy';
    $gradient   = $THEMES[$themeKey]['gradient'] ?? $THEMES['navy']['gradient'];
    $accent     = trim($_POST['accent_color'] ?? '#06b6d4');
    $coverage   = min(1.0, max(0.1, (float)($_POST['coverage_fraction'] ?? 0.5)));
    $rent       = (int)($_POST['rent_monthly']  ?? 0);
    $rto        = (int)($_POST['rto_monthly']   ?? 0);
    $rto_mo     = max(1, (int)($_POST['rto_months'] ?? 72));
    $cash       = (int)($_POST['cash_price']    ?? 0);
    $roi        = trim($_POST['cash_roi']   ?? '');
    $savings    = trim($_POST['savings_label'] ?? '');
    $sort       = (int)($_POST['sort_order'] ?? 0);
    $active     = isset($_POST['is_active']) ? 1 : 0;

    // Features — collect up to 8 non-empty
    $feats = [];
    for ($i = 1; $i <= 8; $i++) {
        $f = trim($_POST["feature_$i"] ?? '');
        if ($f !== '') $feats[] = $f;
    }

    if ($name === '')      $err[] = 'Package name is required.';
    if ($capacity === '')  $err[] = 'Capacity label is required.';
    if ($cap_num <= 0)     $err[] = 'Capacity (kW number) must be > 0.';

    if (empty($err)) {
        $data = [
            ':name'             => $name,
            ':capacity'         => $capacity,
            ':capacity_num'     => $cap_num,
            ':icon'             => $icon,
            ':tagline'          => $tagline ?: null,
            ':badge'            => $badge   ?: null,
            ':gradient'         => $gradient,
            ':accent_color'     => $accent,
            ':coverage_fraction'=> $coverage,
            ':features'         => $feats ? json_encode($feats, JSON_UNESCAPED_UNICODE) : null,
            ':rent_monthly'     => $rent  ?: null,
            ':rto_monthly'      => $rto   ?: null,
            ':rto_months'       => $rto_mo,
            ':cash_price'       => $cash  ?: null,
            ':cash_roi'         => $roi   ?: null,
            ':savings_label'    => $savings ?: null,
            ':sort_order'       => $sort,
            ':is_active'        => $active,
        ];

        if ($id) {
            $sql = "UPDATE packages SET
                name=:name, capacity=:capacity, capacity_num=:capacity_num,
                icon=:icon, tagline=:tagline, badge=:badge,
                gradient=:gradient, accent_color=:accent_color,
                coverage_fraction=:coverage_fraction, features=:features,
                rent_monthly=:rent_monthly, rto_monthly=:rto_monthly, rto_months=:rto_months,
                cash_price=:cash_price, cash_roi=:cash_roi, savings_label=:savings_label,
                sort_order=:sort_order, is_active=:is_active
                WHERE id=:id";
            $data[':id'] = $id;
        } else {
            $sql = "INSERT INTO packages
                (name,capacity,capacity_num,icon,tagline,badge,gradient,accent_color,
                 coverage_fraction,features,rent_monthly,rto_monthly,rto_months,
                 cash_price,cash_roi,savings_label,sort_order,is_active)
                VALUES
                (:name,:capacity,:capacity_num,:icon,:tagline,:badge,:gradient,:accent_color,
                 :coverage_fraction,:features,:rent_monthly,:rto_monthly,:rto_months,
                 :cash_price,:cash_roi,:savings_label,:sort_order,:is_active)";
        }
        $pdo->prepare($sql)->execute($data);
        header('Location: ' . ADMIN_BASE . '/packages.php?saved=1');
        exit;
    }
}

// Pre-fill values
$v = [
    'name'             => $_POST['name']             ?? ($pkg['name']             ?? ''),
    'capacity'         => $_POST['capacity']         ?? ($pkg['capacity']         ?? ''),
    'capacity_num'     => $_POST['capacity_num']     ?? ($pkg['capacity_num']     ?? ''),
    'icon'             => $_POST['icon']             ?? ($pkg['icon']             ?? '☀️'),
    'tagline'          => $_POST['tagline']          ?? ($pkg['tagline']          ?? ''),
    'badge'            => $_POST['badge']            ?? ($pkg['badge']            ?? ''),
    'theme'            => $_POST['theme']            ?? detectTheme($pkg['gradient'] ?? '', $THEMES),
    'accent_color'     => $_POST['accent_color']     ?? ($pkg['accent_color']     ?? '#06b6d4'),
    'coverage_fraction'=> $_POST['coverage_fraction']?? ($pkg['coverage_fraction']?? '0.50'),
    'rent_monthly'     => $_POST['rent_monthly']     ?? ($pkg['rent_monthly']     ?? ''),
    'rto_monthly'      => $_POST['rto_monthly']      ?? ($pkg['rto_monthly']      ?? ''),
    'rto_months'       => $_POST['rto_months']       ?? ($pkg['rto_months']       ?? 72),
    'cash_price'       => $_POST['cash_price']       ?? ($pkg['cash_price']       ?? ''),
    'cash_roi'         => $_POST['cash_roi']         ?? ($pkg['cash_roi']         ?? ''),
    'savings_label'    => $_POST['savings_label']    ?? ($pkg['savings_label']    ?? ''),
    'sort_order'       => $_POST['sort_order']       ?? ($pkg['sort_order']       ?? 0),
    'is_active'        => isset($_POST['is_active']) ? true : ($pkg ? (bool)$pkg['is_active'] : true),
];

// Decode existing features
$existingFeats = [];
if ($pkg && $pkg['features']) {
    $dec = json_decode($pkg['features'], true);
    if (is_array($dec)) $existingFeats = $dec;
}

$pageTitle = $id ? "Edit Package — {$pkg['name']}" : 'Add New Package';
open_layout($pageTitle, 'packages');

$inputCls = 'w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-orange-400 bg-slate-50 focus:bg-white transition-colors';
?>

<!-- Back -->
<div class="mb-5">
  <a href="<?= ADMIN_BASE ?>/packages.php" class="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-orange-500">
    <span class="material-icons text-base">arrow_back</span> Back to Packages
  </a>
</div>

<?php if ($err): ?>
<div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-4 mb-5">
  <?php foreach ($err as $e): ?>
  <p class="text-sm text-red-600 flex items-center gap-1.5"><span class="material-icons text-base">error</span><?= htmlspecialchars($e) ?></p>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<form method="POST" class="grid grid-cols-1 xl:grid-cols-3 gap-5">

  <!-- ── LEFT: Main fields ─────────────────────────────────────────────── -->
  <div class="xl:col-span-2 space-y-5">

    <!-- Basic Info -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100" style="background:#f8fafc">
        <h3 class="font-bold text-sm" style="color:#0f2d52">Basic Information</h3>
      </div>
      <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">

        <div class="sm:col-span-2">
          <label class="block text-xs font-bold mb-1.5 uppercase tracking-wide text-slate-400">Package Name <span class="text-orange-500">*</span></label>
          <input type="text" name="name" value="<?= htmlspecialchars($v['name']) ?>"
            placeholder="e.g. Standard Pro" class="<?= $inputCls ?>" required />
        </div>

        <div>
          <label class="block text-xs font-bold mb-1.5 uppercase tracking-wide text-slate-400">Capacity Label <span class="text-orange-500">*</span></label>
          <input type="text" name="capacity" value="<?= htmlspecialchars($v['capacity']) ?>"
            placeholder="e.g. 3.0 kW" class="<?= $inputCls ?>" required />
        </div>

        <div>
          <label class="block text-xs font-bold mb-1.5 uppercase tracking-wide text-slate-400">Capacity (kW number) <span class="text-orange-500">*</span></label>
          <input type="number" name="capacity_num" value="<?= htmlspecialchars($v['capacity_num']) ?>"
            placeholder="3.0" step="0.1" min="0.1" class="<?= $inputCls ?>" required />
          <p class="text-xs text-slate-400 mt-1">Used for savings calculation</p>
        </div>

        <div>
          <label class="block text-xs font-bold mb-1.5 uppercase tracking-wide text-slate-400">Icon (emoji)</label>
          <input type="text" name="icon" value="<?= htmlspecialchars($v['icon']) ?>"
            placeholder="⚡" maxlength="5" class="<?= $inputCls ?>" />
        </div>

        <div>
          <label class="block text-xs font-bold mb-1.5 uppercase tracking-wide text-slate-400">Badge Label</label>
          <input type="text" name="badge" value="<?= htmlspecialchars($v['badge']) ?>"
            placeholder="Most Popular" class="<?= $inputCls ?>" />
          <p class="text-xs text-slate-400 mt-1">Displayed on card header (leave blank for none)</p>
        </div>

        <div class="sm:col-span-2">
          <label class="block text-xs font-bold mb-1.5 uppercase tracking-wide text-slate-400">Tagline</label>
          <input type="text" name="tagline" value="<?= htmlspecialchars($v['tagline']) ?>"
            placeholder="Perfect for medium homes & offices" class="<?= $inputCls ?>" />
        </div>

        <div class="sm:col-span-2">
          <label class="block text-xs font-bold mb-1.5 uppercase tracking-wide text-slate-400">Savings Label</label>
          <input type="text" name="savings_label" value="<?= htmlspecialchars($v['savings_label']) ?>"
            placeholder="Save up to KES 5,800/mo" class="<?= $inputCls ?>" />
        </div>
      </div>
    </div>

    <!-- Features -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100" style="background:#f8fafc">
        <h3 class="font-bold text-sm" style="color:#0f2d52">Package Features</h3>
        <p class="text-xs text-slate-400 mt-0.5">Up to 8 bullet points shown on the package card</p>
      </div>
      <div class="p-6 space-y-3">
        <?php for ($i = 1; $i <= 8; $i++):
          $val = htmlspecialchars($_POST["feature_$i"] ?? ($existingFeats[$i-1] ?? ''));
        ?>
        <div class="flex items-center gap-3">
          <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0 text-white"
                style="background:#f97316"><?= $i ?></span>
          <input type="text" name="feature_<?= $i ?>" value="<?= $val ?>"
            placeholder="e.g. Full home lighting"
            class="<?= $inputCls ?> flex-1" />
        </div>
        <?php endfor; ?>
      </div>
    </div>

    <!-- Pricing -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100" style="background:#f8fafc">
        <h3 class="font-bold text-sm" style="color:#0f2d52">Pricing (KES)</h3>
        <p class="text-xs text-slate-400 mt-0.5">Leave blank if a payment model is not offered for this package</p>
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

          <!-- Rent -->
          <div class="rounded-2xl border border-slate-100 p-4">
            <div class="flex items-center gap-2 mb-3">
              <span class="text-lg">📅</span>
              <span class="text-xs font-black uppercase tracking-wide" style="color:#0f2d52">Rent Only</span>
            </div>
            <label class="block text-xs font-bold mb-1 text-slate-400">Monthly (KES)</label>
            <input type="number" name="rent_monthly" value="<?= htmlspecialchars($v['rent_monthly']) ?>"
              placeholder="6500" min="0" class="<?= $inputCls ?>" />
          </div>

          <!-- RTO -->
          <div class="rounded-2xl border border-slate-100 p-4">
            <div class="flex items-center gap-2 mb-3">
              <span class="text-lg">🏠</span>
              <span class="text-xs font-black uppercase tracking-wide" style="color:#0f2d52">Rent-to-Own</span>
            </div>
            <label class="block text-xs font-bold mb-1 text-slate-400">Monthly (KES)</label>
            <input type="number" name="rto_monthly" value="<?= htmlspecialchars($v['rto_monthly']) ?>"
              placeholder="9500" min="0" class="<?= $inputCls ?> mb-2" />
            <label class="block text-xs font-bold mb-1 text-slate-400">Duration (months)</label>
            <input type="number" name="rto_months" value="<?= htmlspecialchars($v['rto_months']) ?>"
              placeholder="72" min="1" class="<?= $inputCls ?>" />
          </div>

          <!-- Cash -->
          <div class="rounded-2xl border border-slate-100 p-4">
            <div class="flex items-center gap-2 mb-3">
              <span class="text-lg">💰</span>
              <span class="text-xs font-black uppercase tracking-wide" style="color:#0f2d52">Pay Cash</span>
            </div>
            <label class="block text-xs font-bold mb-1 text-slate-400">Total Price (KES)</label>
            <input type="number" name="cash_price" value="<?= htmlspecialchars($v['cash_price']) ?>"
              placeholder="320000" min="0" class="<?= $inputCls ?> mb-2" />
            <label class="block text-xs font-bold mb-1 text-slate-400">ROI Timeline</label>
            <input type="text" name="cash_roi" value="<?= htmlspecialchars($v['cash_roi']) ?>"
              placeholder="3.2 yrs" class="<?= $inputCls ?>" />
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ── RIGHT: Appearance & Settings ─────────────────────────────────── -->
  <div class="space-y-5">

    <!-- Live preview -->
    <div id="preview" class="rounded-3xl overflow-hidden shadow-lg">
      <div id="previewHeader" class="px-6 pt-6 pb-8 relative" style="background:<?= htmlspecialchars($THEMES[$v['theme']]['gradient']) ?>">
        <div class="text-3xl mb-2" id="previewIcon"><?= htmlspecialchars($v['icon']) ?></div>
        <h3 class="text-xl font-extrabold text-white" id="previewName"><?= htmlspecialchars($v['name'] ?: 'Package Name') ?></h3>
        <p class="text-white/60 text-xs mt-1" id="previewTagline"><?= htmlspecialchars($v['tagline'] ?: 'Tagline goes here') ?></p>
      </div>
      <div class="bg-white px-5 py-4">
        <p class="text-xs text-slate-400 italic">Live preview updates as you type</p>
      </div>
    </div>

    <!-- Theme -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-100" style="background:#f8fafc">
        <h3 class="font-bold text-sm" style="color:#0f2d52">Card Theme</h3>
      </div>
      <div class="p-5">
        <div class="grid grid-cols-4 gap-2">
          <?php foreach ($THEMES as $key => $theme): ?>
          <label class="cursor-pointer">
            <input type="radio" name="theme" value="<?= $key ?>" class="sr-only peer"
              <?= $v['theme'] === $key ? 'checked' : '' ?> />
            <div class="rounded-xl h-10 ring-2 ring-offset-1 peer-checked:ring-orange-400 ring-transparent transition-all"
                 style="background:<?= $theme['preview'] ?>" title="<?= $theme['label'] ?>"></div>
            <p class="text-[10px] text-center text-slate-500 mt-1"><?= $theme['label'] ?></p>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Technical settings -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-100" style="background:#f8fafc">
        <h3 class="font-bold text-sm" style="color:#0f2d52">Settings</h3>
      </div>
      <div class="p-5 space-y-4">
        <div>
          <label class="block text-xs font-bold mb-1.5 uppercase tracking-wide text-slate-400">Coverage Fraction (0.1 – 1.0)</label>
          <input type="number" name="coverage_fraction" value="<?= htmlspecialchars($v['coverage_fraction']) ?>"
            step="0.05" min="0.1" max="1.0" class="<?= $inputCls ?>" />
          <p class="text-xs text-slate-400 mt-1">Fraction of bill covered (used in savings estimate)</p>
        </div>

        <div>
          <label class="block text-xs font-bold mb-1.5 uppercase tracking-wide text-slate-400">Accent Colour</label>
          <div class="flex items-center gap-3">
            <input type="color" name="accent_color" value="<?= htmlspecialchars($v['accent_color']) ?>"
              class="w-10 h-10 rounded-xl border border-slate-200 cursor-pointer p-1" />
            <input type="text" id="accentText" value="<?= htmlspecialchars($v['accent_color']) ?>"
              placeholder="#06b6d4"
              class="<?= $inputCls ?> flex-1 font-mono" readonly />
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold mb-1.5 uppercase tracking-wide text-slate-400">Sort Order</label>
          <input type="number" name="sort_order" value="<?= htmlspecialchars($v['sort_order']) ?>"
            min="0" placeholder="0" class="<?= $inputCls ?>" />
          <p class="text-xs text-slate-400 mt-1">Lower number = shown first</p>
        </div>

        <label class="flex items-center gap-3 cursor-pointer">
          <div class="relative">
            <input type="checkbox" name="is_active" class="sr-only peer" <?= $v['is_active'] ? 'checked' : '' ?> />
            <div class="w-11 h-6 rounded-full peer-checked:bg-orange-500 bg-slate-200 transition-colors"></div>
            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
          </div>
          <div>
            <p class="text-sm font-semibold text-slate-700">Active</p>
            <p class="text-xs text-slate-400">Visible on public website</p>
          </div>
        </label>
      </div>
    </div>

    <!-- Submit -->
    <button type="submit"
      class="w-full py-4 rounded-2xl font-extrabold text-white text-sm"
      style="background:linear-gradient(135deg,#f97316,#c2410c);box-shadow:0 8px 24px rgba(249,115,22,.3)">
      <?= $id ? '💾 Save Changes' : '✨ Create Package' ?>
    </button>

    <?php if ($id): ?>
    <a href="<?= ADMIN_BASE ?>/packages.php"
       class="block text-center py-3 rounded-2xl text-sm font-semibold border border-slate-200 text-slate-500 hover:bg-slate-50 transition-colors">
      Cancel
    </a>
    <?php endif; ?>
  </div>

</form>

<script>
// Live preview
const nameI    = document.querySelector('[name="name"]');
const iconI    = document.querySelector('[name="icon"]');
const taglineI = document.querySelector('[name="tagline"]');
const themeRs  = document.querySelectorAll('[name="theme"]');
const accentI  = document.querySelector('[name="accent_color"]');
const accentTx = document.getElementById('accentText');

const GRADIENTS = <?= json_encode(array_column($THEMES,'gradient',0)) ?>;
const THEME_MAP = {};
<?php foreach ($THEMES as $key => $t): ?>
THEME_MAP['<?= $key ?>'] = '<?= addslashes($t['gradient']) ?>';
<?php endforeach; ?>

function updatePreview() {
  document.getElementById('previewName').textContent    = nameI.value    || 'Package Name';
  document.getElementById('previewIcon').textContent    = iconI.value    || '☀️';
  document.getElementById('previewTagline').textContent = taglineI.value || 'Tagline goes here';
  const checked = document.querySelector('[name="theme"]:checked');
  if (checked) {
    document.getElementById('previewHeader').style.background = THEME_MAP[checked.value] || '';
  }
}

[nameI, iconI, taglineI].forEach(el => el.addEventListener('input', updatePreview));
themeRs.forEach(r => r.addEventListener('change', updatePreview));

accentI.addEventListener('input', e => { accentTx.value = e.target.value; });
</script>

<?php close_layout(); ?>
