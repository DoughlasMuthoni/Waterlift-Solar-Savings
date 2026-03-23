<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
check_auth();

// Re-use same filters as leads.php
$search   = trim($_GET['q']        ?? '');
$fStatus  = $_GET['status']        ?? '';
$fTier    = $_GET['tier']          ?? '';
$fProp    = $_GET['prop']          ?? '';
$fSource  = $_GET['source']        ?? '';

$where  = ['1=1'];
$params = [];

if ($search !== '') {
    $where[]      = '(name LIKE :q OR phone LIKE :q OR email LIKE :q)';
    $params[':q'] = "%$search%";
}
if ($fStatus) { $where[] = 'status = :status';          $params[':status'] = $fStatus; }
if ($fTier)   { $where[] = 'package_tier = :tier';      $params[':tier']   = $fTier;   }
if ($fProp)   { $where[] = 'property_category = :prop'; $params[':prop']   = $fProp;   }
if ($fSource) { $where[] = 'source = :source';          $params[':source'] = $fSource; }

$where_sql = implode(' AND ', $where);
$stmt = $pdo->prepare("SELECT * FROM leads WHERE $where_sql ORDER BY created_at DESC");
$stmt->execute($params);
$leads = $stmt->fetchAll();

$filename = 'waterlift_leads_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');

// BOM for Excel UTF-8
fwrite($out, "\xEF\xBB\xBF");

// Header row
fputcsv($out, [
    'ID','Name','Phone','Email','County','Address',
    'Ownership','Property Type','Bill Type','Has Borehole','Monthly Bill (KES)',
    'Package Tier','Payment Model','Status','Source','Notes','Created At',
]);

foreach ($leads as $r) {
    fputcsv($out, [
        $r['id'],
        $r['name'],
        $r['phone'],
        $r['email']            ?? '',
        $r['county']           ?? '',
        $r['address']          ?? '',
        $r['ownership_status'] ?? '',
        $r['property_category']?? '',
        $r['bill_type']        ?? '',
        $r['has_borehole'] === null ? '' : ($r['has_borehole'] ? 'Yes' : 'No'),
        $r['monthly_bill']     ?? '',
        $r['package_tier']     ?? '',
        $r['payment_model']    ?? '',
        $r['status'],
        $r['source'],
        $r['notes']            ?? '',
        $r['created_at'],
    ]);
}

fclose($out);
exit;
