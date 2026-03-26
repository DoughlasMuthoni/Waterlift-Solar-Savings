<?php
/**
 * One-time script: updates image_url for Residential and Agriculture use cases.
 * Visit: http://localhost/waterlift_solat_savings/api/update_usecase_images.php
 * DELETE this file after running it.
 */
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../admin/includes/db.php';

if (!$pdo) {
    die('<b style="color:red">DB connection failed.</b>');
}

// Curated, royalty-free images from Unsplash
$updates = [
    [
        'match'     => ['Residential', 'Home', 'House', 'Villa', 'Apartment'],
        'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=800&h=500&fit=crop&q=80',
        'label'     => 'Residential',
    ],
    [
        'match'     => ['Agriculture', 'Farm', 'Agricultural', 'Irrigation'],
        'image_url' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=800&h=500&fit=crop&q=80',
        'label'     => 'Agriculture / Farm',
    ],
];

$log = [];

foreach ($updates as $u) {
    // Build a WHERE clause matching tag OR title (case-insensitive)
    $conditions = [];
    $params     = [];
    foreach ($u['match'] as $i => $keyword) {
        $conditions[] = "tag LIKE :t{$i} OR title LIKE :tt{$i}";
        $params[":t{$i}"]  = "%{$keyword}%";
        $params[":tt{$i}"] = "%{$keyword}%";
    }
    $where = implode(' OR ', $conditions);

    // Count matching rows first
    $countSql = "SELECT COUNT(*) FROM use_cases WHERE {$where}";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $count = (int) $countStmt->fetchColumn();

    if ($count === 0) {
        $log[] = "<li style='color:#b45309'>⚠️  No rows matched for <b>{$u['label']}</b> — check your tag/title values in the DB.</li>";
        continue;
    }

    // Perform update
    $updateSql  = "UPDATE use_cases SET image_url = :img WHERE {$where}";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute(array_merge([':img' => $u['image_url']], $params));
    $affected = $updateStmt->rowCount();

    $log[] = "<li style='color:#15803d'>✅  <b>{$u['label']}</b>: updated {$affected} row(s) → <a href='{$u['image_url']}' target='_blank'>preview image</a></li>";
}

// Show current state of all use cases
$all = $pdo->query("SELECT id, tag, title, image_url FROM use_cases ORDER BY sort_order ASC, id ASC")->fetchAll();
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Image Update</title></head>
<body style="font-family:sans-serif;max-width:860px;margin:40px auto;padding:0 20px">
  <h2>Use Case Image Updater</h2>
  <ul style="line-height:2"><?= implode('', $log) ?></ul>
  <hr>
  <h3>Current Use Cases</h3>
  <table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;width:100%;font-size:13px">
    <thead style="background:#f0f0f0">
      <tr><th>ID</th><th>Tag</th><th>Title</th><th>Image URL</th></tr>
    </thead>
    <tbody>
      <?php foreach ($all as $r): ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><?= htmlspecialchars($r['tag']) ?></td>
        <td><?= htmlspecialchars($r['title']) ?></td>
        <td style="font-size:11px;word-break:break-all"><?= htmlspecialchars($r['image_url'] ?? '—') ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <p style="color:red;margin-top:20px"><b>Delete this file once done:</b> <code>api/update_usecase_images.php</code></p>
</body>
</html>
