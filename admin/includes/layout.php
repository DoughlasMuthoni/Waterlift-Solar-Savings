<?php
function open_layout(string $title, string $active = ''): void {
    $base = ADMIN_BASE;
    $nav = [
        ['href' => "$base/dashboard.php",  'icon' => 'dashboard',    'label' => 'Dashboard',   'key' => 'dashboard'],
        ['href' => "$base/leads.php",      'icon' => 'group',        'label' => 'All Leads',   'key' => 'leads'],
        ['href' => "$base/packages.php",    'icon' => 'inventory_2',  'label' => 'Packages',      'key' => 'packages'],
        ['href' => "$base/use_cases.php",  'icon' => 'bolt',         'label' => 'Use Cases',     'key' => 'use_cases'],
        ['href' => "$base/testimonials.php",'icon'=> 'reviews',      'label' => 'Testimonials',  'key' => 'testimonials'],
        ['href' => "$base/api/export.php", 'icon' => 'download',     'label' => 'Export CSV',    'key' => 'export'],
    ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($title) ?> — Waterlift Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            navy: { DEFAULT: '#0f2d52', light: '#1e4d8c', dark: '#0a1e38' },
            brand: { orange: '#f97316', cyan: '#06b6d4', yellow: '#f59e0b' },
          },
          transitionProperty: { sidebar: 'transform' },
        }
      }
    }
  </script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
  <style>
    body { font-family: 'Inter', system-ui, sans-serif; }

    /* Sidebar drawer */
    #sidebar {
      transition: transform 0.28s cubic-bezier(.4,0,.2,1);
    }
    #sidebar-overlay {
      transition: opacity 0.28s ease;
    }

    /* Active nav link */
    .sidebar-link.active { background: rgba(249,115,22,0.15); color: #f97316; }
    .sidebar-link.active .material-icons { color: #f97316; }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: #f1f5f9; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

    /* Mobile table card rows */
    @media (max-width: 767px) {
      .mobile-card-table thead { display: none; }
      .mobile-card-table tbody tr {
        display: block;
        background: #fff;
        border-radius: 16px;
        margin-bottom: 12px;
        padding: 14px 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 4px rgba(0,0,0,.05);
      }
      .mobile-card-table tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px 0;
        font-size: 13px;
        border: none;
      }
      .mobile-card-table tbody td::before {
        content: attr(data-label);
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #94a3b8;
        flex-shrink: 0;
        margin-right: 12px;
      }
    }
  </style>
</head>
<body class="bg-slate-50 min-h-screen">

<!-- ── Sidebar overlay (mobile) ────────────────────────────────────────── -->
<div id="sidebar-overlay"
     class="fixed inset-0 bg-black/50 z-30 hidden opacity-0"
     onclick="closeSidebar()"></div>

<!-- ── Sidebar ─────────────────────────────────────────────────────────── -->
<aside id="sidebar"
       class="fixed top-0 left-0 h-full w-64 flex flex-col z-40 shadow-2xl -translate-x-full lg:translate-x-0"
       style="background:#0a1e38">

  <!-- Logo -->
  <div class="px-6 py-5 border-b border-white/10 flex items-center justify-between">
    <div>
      <div class="bg-white rounded-xl px-3 py-2 inline-block">
        <img src="<?= SITE_BASE ?>/frontend/public/images/logo.jpeg"
             onerror="this.style.display='none';this.nextElementSibling.style.display='block'"
             class="h-10 object-contain" alt="Waterlift Solar" />
        <span style="display:none;color:#0f2d52;font-weight:800;font-size:13px">Waterlift Solar</span>
      </div>
      <p class="text-white/40 text-[10px] mt-1.5 font-semibold tracking-widest uppercase">Admin Panel</p>
    </div>
    <!-- Close button (mobile only) -->
    <button onclick="closeSidebar()"
            class="lg:hidden w-8 h-8 rounded-lg flex items-center justify-center text-white/50 hover:bg-white/10">
      <span class="material-icons text-lg">close</span>
    </button>
  </div>

  <!-- Nav links -->
  <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
    <?php foreach ($nav as $item): ?>
    <a href="<?= $item['href'] ?>"
       class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-white/70 hover:bg-white/10 hover:text-white transition-all <?= $active === $item['key'] ? 'active' : '' ?>">
      <span class="material-icons text-xl" style="<?= $active === $item['key'] ? 'color:#f97316' : 'color:rgba(255,255,255,0.4)' ?>"><?= $item['icon'] ?></span>
      <?= $item['label'] ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <!-- Sign out -->
  <div class="px-3 pb-5 pt-4 border-t border-white/10">
    <div class="px-4 py-3 rounded-xl bg-white/5 mb-3">
      <p class="text-white/40 text-xs">Signed in as</p>
      <p class="text-white font-semibold text-sm mt-0.5">Administrator</p>
    </div>
    <a href="<?= $base ?>/logout.php"
       class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-white/50 hover:bg-red-500/20 hover:text-red-400 transition-all">
      <span class="material-icons text-xl">logout</span>
      Sign Out
    </a>
  </div>
</aside>

<!-- ── Main wrapper ─────────────────────────────────────────────────────── -->
<div class="lg:pl-64 min-h-screen flex flex-col">

  <!-- Top bar -->
  <header class="sticky top-0 z-20 bg-white border-b border-slate-100 shadow-sm px-4 sm:px-6 py-3 flex items-center gap-3">

    <!-- Hamburger (mobile) -->
    <button onclick="openSidebar()"
            class="lg:hidden w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 hover:bg-slate-100 transition-colors shrink-0">
      <span class="material-icons">menu</span>
    </button>

    <!-- Page title -->
    <h1 class="text-base sm:text-lg font-bold flex-1 truncate" style="color:#0f2d52">
      <?= htmlspecialchars($title) ?>
    </h1>

    <!-- Right side -->
    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
      <span class="hidden sm:block text-xs text-slate-400"><?= date('d M Y') ?></span>
      <a href="<?= $base ?>/leads.php"
         class="hidden sm:flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-xl text-white"
         style="background:#f97316">
        <span class="material-icons text-sm">add</span> New Lead
      </a>
      <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-black shrink-0"
           style="background:#f97316">A</div>
    </div>
  </header>

  <!-- Page content -->
  <main class="flex-1 p-4 sm:p-6 lg:p-8 pb-24 lg:pb-8">
<?php
}

function close_layout(): void {
?>
  </main>
</div><!-- end main wrapper -->

<!-- ── Mobile bottom nav ────────────────────────────────────────────────── -->
<nav class="lg:hidden fixed bottom-0 left-0 right-0 z-20 bg-white border-t border-slate-200 flex"
     style="padding-bottom:env(safe-area-inset-bottom)">
  <?php
  $base2 = ADMIN_BASE;
  $bnav = [
    ['href'=>"$base2/dashboard.php", 'icon'=>'dashboard',   'label'=>'Dashboard'],
    ['href'=>"$base2/leads.php",     'icon'=>'group',       'label'=>'Leads'],
    ['href'=>"$base2/packages.php",  'icon'=>'inventory_2', 'label'=>'Packages'],
    ['href'=>"$base2/api/export.php",'icon'=>'download',    'label'=>'Export'],
    ['href'=>"$base2/logout.php",    'icon'=>'logout',      'label'=>'Logout'],
  ];
  foreach ($bnav as $b):
  ?>
  <a href="<?= $b['href'] ?>"
     class="flex-1 flex flex-col items-center justify-center py-2 text-slate-400 hover:text-orange-500 transition-colors gap-0.5">
    <span class="material-icons text-xl"><?= $b['icon'] ?></span>
    <span class="text-[10px] font-medium"><?= $b['label'] ?></span>
  </a>
  <?php endforeach; ?>
</nav>

<script>
  const sidebar  = document.getElementById('sidebar');
  const overlay  = document.getElementById('sidebar-overlay');

  function openSidebar() {
    sidebar.classList.remove('-translate-x-full');
    overlay.classList.remove('hidden', 'opacity-0');
    requestAnimationFrame(() => overlay.classList.add('opacity-100'));
    document.body.style.overflow = 'hidden';
  }

  function closeSidebar() {
    sidebar.classList.add('-translate-x-full');
    overlay.classList.remove('opacity-100');
    overlay.classList.add('opacity-0');
    setTimeout(() => overlay.classList.add('hidden'), 280);
    document.body.style.overflow = '';
  }

  // Close on Escape key
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSidebar(); });
</script>

</body>
</html>
<?php
}
