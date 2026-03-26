<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/layout.php';
check_auth();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . ADMIN_BASE . '/leads.php'); exit; }

$lead = $pdo->prepare("SELECT * FROM leads WHERE id = ?");
$lead->execute([$id]);
$lead = $lead->fetch();
if (!$lead) { header('Location: ' . ADMIN_BASE . '/leads.php'); exit; }

// Handle notes + status update via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $pdo->prepare("UPDATE leads SET status=?, notes=? WHERE id=?")
        ->execute([$_POST['status'], trim($_POST['notes'] ?? ''), $id]);
    header('Location: ' . ADMIN_BASE . "/lead.php?id=$id&saved=1"); exit;
}

$status_colors = [
    'new'       => ['bg'=>'#dbeafe','text'=>'#1d4ed8'],
    'contacted' => ['bg'=>'#fef9c3','text'=>'#a16207'],
    'qualified' => ['bg'=>'#dcfce7','text'=>'#16a34a'],
    'converted' => ['bg'=>'#f0fdf4','text'=>'#15803d'],
    'lost'      => ['bg'=>'#fee2e2','text'=>'#dc2626'],
];
$sc = $status_colors[$lead['status']] ?? ['bg'=>'#f1f5f9','text'=>'#64748b'];

open_layout("Lead #$id — {$lead['name']}", 'leads');
?>

<!-- Back + breadcrumb -->
<div class="flex items-center gap-2 mb-6 text-sm">
  <a href="<?= ADMIN_BASE ?>/leads.php" class="text-slate-400 hover:text-orange-500">← All Leads</a>
  <span class="text-slate-300">/</span>
  <span class="font-semibold" style="color:#0f2d52"><?= htmlspecialchars($lead['name']) ?></span>
</div>

<?php if (isset($_GET['saved'])): ?>
<div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 text-sm rounded-2xl px-5 py-3 mb-5">
  ✅ Lead updated successfully.
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

  <!-- ── Left: Lead info (2 cols) ─────────────────────────────────────────── -->
  <div class="lg:col-span-2 space-y-5">

    <!-- Contact details -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="px-4 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between"
           style="background:linear-gradient(135deg,#0f2d52,#1e4d8c)">
        <h3 class="font-bold text-white">Contact Details</h3>
        <span class="text-xs font-semibold px-3 py-1 rounded-full"
              style="background:<?= $sc['bg'] ?>;color:<?= $sc['text'] ?>"><?= ucfirst($lead['status']) ?></span>
      </div>
      <div class="p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
        <?php
        $fields = [
          'Name'    => $lead['name'],
          'Phone'   => $lead['phone'],
          'Email'   => $lead['email'] ?: '—',
          'County'  => $lead['county'] ?: '—',
          'Address' => $lead['address'] ?: '—',
          'Source'  => str_replace('_',' ',ucfirst($lead['source'])),
        ];
        foreach ($fields as $label => $val):
        ?>
        <div>
          <p class="text-xs font-bold uppercase tracking-wide text-slate-400 mb-1"><?= $label ?></p>
          <?php if ($label === 'Phone'): ?>
          <div class="flex items-center gap-2">
            <a href="tel:<?= htmlspecialchars($val) ?>" class="font-semibold text-slate-700 hover:text-orange-500"><?= htmlspecialchars($val) ?></a>
            <a href="https://wa.me/<?= preg_replace('/\D/','',$val) ?>?text=Hello+<?= urlencode($lead['name']) ?>%2C+this+is+Waterlift+Solar+Savings..."
               target="_blank"
               class="text-xs font-bold px-2.5 py-1 rounded-full text-white" style="background:#25D366">WhatsApp</a>
          </div>
          <?php elseif ($label === 'Email' && $lead['email']): ?>
          <a href="mailto:<?= htmlspecialchars($val) ?>" class="font-semibold text-slate-700 hover:text-orange-500"><?= htmlspecialchars($val) ?></a>
          <?php else: ?>
          <p class="font-semibold text-slate-700"><?= htmlspecialchars($val) ?></p>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Wizard / Assessment data -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100">
        <h3 class="font-bold text-sm" style="color:#0f2d52">Solar Assessment Data</h3>
      </div>
      <div class="p-4 sm:p-6 grid grid-cols-2 sm:grid-cols-3 gap-4 sm:gap-5">
        <?php
        $prop_icons = ['residential'=>'🏠','commercial'=>'🏢','warehouse'=>'🏭','industrial'=>'⚙️'];
        $tier_styles = ['Essential'=>['#dbeafe','#1d4ed8'],'Standard'=>['#cffafe','#0e7490'],'Premium'=>['#ffedd5','#c2410c']];
        $assess = [
          ['Ownership',     ucfirst($lead['ownership_status'] ?? '—')],
          ['Property Type', isset($prop_icons[$lead['property_category']]) ? $prop_icons[$lead['property_category']].' '.ucfirst($lead['property_category']) : '—'],
          ['Bill Type',     ucfirst($lead['bill_type'] ?? '—')],
          ['Has Borehole',  $lead['has_borehole'] === null ? '—' : ($lead['has_borehole'] ? '✅ Yes' : '❌ No')],
          ['Monthly Bill',  $lead['monthly_bill'] ? 'KES '.number_format($lead['monthly_bill']) : '—'],
          ['Package Tier',  $lead['package_tier'] ?? '—'],
          ['Payment Model', $lead['payment_model'] ?? '—'],
        ];
        foreach ($assess as [$label, $val]):
          [$tbg,$tcol] = $tier_styles[$val] ?? [null,null];
        ?>
        <div>
          <p class="text-xs font-bold uppercase tracking-wide text-slate-400 mb-1"><?= $label ?></p>
          <?php if ($tbg): ?>
          <span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background:<?= $tbg ?>;color:<?= $tcol ?>"><?= htmlspecialchars($val) ?></span>
          <?php else: ?>
          <p class="font-semibold text-slate-700 text-sm"><?= htmlspecialchars($val) ?></p>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Map link -->
    <?php if ($lead['lat'] && $lead['lng']): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 sm:p-5 flex items-center gap-3 sm:gap-4">
      <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl" style="background:#eff6ff">📍</div>
      <div class="flex-1">
        <p class="text-xs font-bold uppercase tracking-wide text-slate-400 mb-0.5">GPS Location</p>
        <p class="font-semibold text-slate-700 text-sm"><?= $lead['lat'] ?>, <?= $lead['lng'] ?></p>
      </div>
      <a href="https://www.google.com/maps?q=<?= $lead['lat'] ?>,<?= $lead['lng'] ?>"
         target="_blank"
         class="text-xs font-bold px-4 py-2 rounded-xl text-white" style="background:#0f2d52">
        Open Map
      </a>
    </div>
    <?php endif; ?>
  </div>

  <!-- ── Right: Status + Notes ────────────────────────────────────────────── -->
  <div class="space-y-5">

    <!-- Quick actions (shown first on mobile) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 sm:p-5 lg:order-last">
      <h3 class="font-bold text-xs uppercase tracking-wide text-slate-400 mb-3 sm:mb-4">Quick Actions</h3>
      <div class="grid grid-cols-2 lg:grid-cols-1 gap-2">
        <a href="https://wa.me/<?= preg_replace('/\D/','',$lead['phone']) ?>?text=Hello+<?= urlencode($lead['name']) ?>%2C+this+is+Waterlift+Solar+Savings.+We'd+like+to+follow+up+on+your+solar+enquiry."
           target="_blank"
           class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-white font-bold text-sm col-span-2 lg:col-span-1"
           style="background:#25D366">
          <svg viewBox="0 0 32 32" class="w-4 h-4 fill-white shrink-0"><path d="M16 0C7.164 0 0 7.164 0 16c0 2.82.737 5.463 2.027 7.754L0 32l8.49-2.004A15.94 15.94 0 0016 32c8.836 0 16-7.164 16-16S24.836 0 16 0zm7.27 19.406c-.397-.199-2.353-1.162-2.718-1.295-.365-.132-.63-.199-.895.199-.265.397-1.028 1.295-1.26 1.56-.231.265-.463.298-.861.1-.397-.2-1.677-.618-3.194-1.97-1.18-1.053-1.977-2.352-2.208-2.75-.232-.397-.025-.612.174-.81.179-.178.397-.463.596-.695.199-.231.265-.397.397-.662.133-.265.067-.497-.033-.696-.1-.199-.895-2.155-1.227-2.95-.323-.773-.65-.668-.895-.68l-.762-.013c-.265 0-.696.1-1.061.497-.365.397-1.393 1.362-1.393 3.318s1.426 3.848 1.625 4.113c.199.265 2.806 4.282 6.797 6.006.95.41 1.691.655 2.269.839.953.303 1.82.26 2.506.157.764-.113 2.353-.963 2.686-1.893.331-.93.331-1.728.231-1.893-.099-.166-.364-.265-.762-.464z"/></svg>
          WhatsApp
        </a>
        <a href="tel:<?= htmlspecialchars($lead['phone']) ?>"
           class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-bold text-white text-sm"
           style="background:#f97316">
          <span class="material-icons text-base">call</span> Call
        </a>
        <?php if ($lead['email']): ?>
        <a href="mailto:<?= htmlspecialchars($lead['email']) ?>"
           class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-bold text-white text-sm"
           style="background:#0f2d52">
          <span class="material-icons text-base">email</span> Email
        </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Update form -->
    <form method="POST" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="px-4 sm:px-6 py-4 border-b border-slate-100" style="background:#f8fafc">
        <h3 class="font-bold text-sm" style="color:#0f2d52">Update Lead</h3>
      </div>
      <div class="p-4 sm:p-6 space-y-4">
        <div>
          <label class="block text-xs font-bold mb-2 uppercase tracking-wide text-slate-400">Status</label>
          <select name="status" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-orange-400 bg-slate-50">
            <?php foreach (['new','contacted','qualified','converted','lost'] as $s): ?>
            <option value="<?= $s ?>" <?= $lead['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold mb-2 uppercase tracking-wide text-slate-400">Internal Notes</label>
          <textarea name="notes" rows="6"
            placeholder="Add notes about this lead…"
            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-orange-400 bg-slate-50 resize-none"
            ><?= htmlspecialchars($lead['notes'] ?? '') ?></textarea>
        </div>

        <button type="submit"
          class="w-full py-3 rounded-2xl font-extrabold text-white text-sm"
          style="background:linear-gradient(135deg,#f97316,#c2410c)">
          Save Changes
        </button>
      </div>
    </form>

    <!-- Meta -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 text-xs text-slate-400 space-y-1.5">
      <div class="flex justify-between"><span>Lead ID</span><span class="font-semibold text-slate-600">#<?= $lead['id'] ?></span></div>
      <div class="flex justify-between"><span>Created</span><span class="font-semibold text-slate-600"><?= date('d M Y, H:i', strtotime($lead['created_at'])) ?></span></div>
      <div class="flex justify-between"><span>Updated</span><span class="font-semibold text-slate-600"><?= date('d M Y, H:i', strtotime($lead['updated_at'])) ?></span></div>
    </div>
  </div>
</div>

<?php close_layout(); ?>
