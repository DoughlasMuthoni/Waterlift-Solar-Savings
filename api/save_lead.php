<?php
// ── CORS ─────────────────────────────────────────────────────────────────────
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

// ── Parse JSON body ───────────────────────────────────────────────────────────
$body = json_decode(file_get_contents('php://input'), true);

if (!is_array($body)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON body.']);
    exit;
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function str_val(array $d, string $k): string  { return isset($d[$k]) ? trim((string)$d[$k]) : ''; }
function opt_str(array $d, string $k): ?string { $v = str_val($d, $k); return $v !== '' ? $v : null; }
function float_val(array $d, string $k): ?float { return isset($d[$k]) && $d[$k] !== '' ? (float)$d[$k] : null; }
function bool_val(array $d, string $k): ?int   { return isset($d[$k]) ? ($d[$k] ? 1 : 0) : null; }

// ── Extract & sanitise ────────────────────────────────────────────────────────
// Contact info
$name    = str_val($body, 'name');
$phone   = str_val($body, 'phone');
$email   = opt_str($body, 'email');
$message = opt_str($body, 'message');

// Location
$lat     = float_val($body, 'lat');
$lng     = float_val($body, 'lng');
$county  = opt_str($body, 'county');
$address = opt_str($body, 'address');

// Wizard answers
$ownership_status  = opt_str($body, 'ownership');
$property_category = opt_str($body, 'propertyType');
$bill_type         = opt_str($body, 'billType');
$has_borehole      = bool_val($body, 'hasBorehole');
$monthly_bill      = float_val($body, 'monthlyBill');

// Recommended package (calculated on frontend, stored for reference)
$package_tier  = opt_str($body, 'packageTier');
$payment_model = opt_str($body, 'paymentModel');

// Source
$source = opt_str($body, 'source') ?? 'contact_form';

// ── Validation ────────────────────────────────────────────────────────────────
$errors = [];
if ($name  === '') $errors[] = 'name is required.';
if ($phone === '') $errors[] = 'phone is required.';
if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';

$allowed_ownership  = ['owner', 'tenant', null];
$allowed_property   = ['residential', 'commercial', 'warehouse', 'industrial', null];
$allowed_bill_type  = ['prepaid', 'monthly', null];
$allowed_tiers      = ['Essential', 'Standard', 'Premium', null];
$allowed_models     = ['Rent Only', 'Rent-to-Own', 'Pay Cash', null];

if (!in_array($ownership_status, $allowed_ownership, true))  $errors[] = 'Invalid ownership_status.';
if (!in_array($property_category, $allowed_property, true))  $errors[] = 'Invalid property_category.';
if (!in_array($bill_type, $allowed_bill_type, true))          $errors[] = 'Invalid bill_type.';
if (!in_array($package_tier, $allowed_tiers, true))           $errors[] = 'Invalid package_tier.';
if (!in_array($payment_model, $allowed_models, true))         $errors[] = 'Invalid payment_model.';

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// ── Insert ────────────────────────────────────────────────────────────────────
require_once __DIR__ . '/db_connect.php';

$sql = "
    INSERT INTO leads (
        name, phone, email, message,
        lat, lng, county, address,
        ownership_status, property_category, bill_type, has_borehole, monthly_bill,
        package_tier, payment_model, source
    ) VALUES (
        :name, :phone, :email, :message,
        :lat, :lng, :county, :address,
        :ownership_status, :property_category, :bill_type, :has_borehole, :monthly_bill,
        :package_tier, :payment_model, :source
    )
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name'              => $name,
        ':phone'             => $phone,
        ':email'             => $email,
        ':message'           => $message,
        ':lat'               => $lat,
        ':lng'               => $lng,
        ':county'            => $county,
        ':address'           => $address,
        ':ownership_status'  => $ownership_status,
        ':property_category' => $property_category,
        ':bill_type'         => $bill_type,
        ':has_borehole'      => $has_borehole,
        ':monthly_bill'      => $monthly_bill,
        ':package_tier'      => $package_tier,
        ':payment_model'     => $payment_model,
        ':source'            => $source,
    ]);

    http_response_code(201);
    echo json_encode(['success' => true, 'id' => (int)$pdo->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not save lead. Please try again.']);
}
