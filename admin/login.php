<?php
require_once __DIR__ . '/includes/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . ADMIN_BASE . '/dashboard.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    if (attempt_login($user, $pass)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user']      = $user;
        header('Location: ' . ADMIN_BASE . '/dashboard.php'); exit;
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login — Waterlift Solar</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center" style="background:linear-gradient(135deg,#0a1e38 0%,#0f2d52 100%)">

  <div class="w-full max-w-sm">
    <!-- Logo card -->
    <div class="text-center mb-8">
      <div class="inline-block bg-white rounded-2xl px-5 py-3 mb-4 shadow-lg">
        <img src="<?= SITE_BASE ?>/frontend/public/images/logo.jpeg"
             onerror="this.style.display='none';this.nextElementSibling.style.display='block'"
             class="h-16 object-contain" alt="Waterlift Solar" />
        <span style="display:none;color:#0f2d52;font-weight:800">Waterlift Solar</span>
      </div>
      <p class="text-white/60 text-sm">Admin Dashboard</p>
    </div>

    <!-- Login form -->
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
      <div class="px-8 py-5" style="background:linear-gradient(135deg,#0f2d52,#1e4d8c)">
        <h2 class="text-lg font-extrabold text-white">Sign In</h2>
        <p class="text-white/60 text-xs mt-0.5">Waterlift Solar — Staff Access Only</p>
      </div>

      <form method="POST" class="p-8 space-y-5">
        <?php if ($error): ?>
        <div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl px-4 py-3">
          <span>⚠️</span> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <div>
          <label class="block text-xs font-bold mb-1.5 uppercase tracking-wide text-slate-500">Username</label>
          <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
            autocomplete="username" required
            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm outline-none bg-slate-50 focus:border-orange-400 focus:bg-white transition-colors"
            placeholder="admin" />
        </div>

        <div>
          <label class="block text-xs font-bold mb-1.5 uppercase tracking-wide text-slate-500">Password</label>
          <input type="password" name="password" autocomplete="current-password" required
            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm outline-none bg-slate-50 focus:border-orange-400 focus:bg-white transition-colors"
            placeholder="••••••••" />
        </div>

        <button type="submit"
          class="w-full py-3.5 rounded-2xl font-extrabold text-white text-sm transition-opacity hover:opacity-90"
          style="background:linear-gradient(135deg,#f97316,#c2410c);box-shadow:0 8px 24px rgba(249,115,22,.35)">
          Sign In →
        </button>
      </form>
    </div>

    <p class="text-center text-white/30 text-xs mt-6">
      Waterlift Solar Savings &copy; <?= date('Y') ?>
    </p>
  </div>

</body>
</html>
