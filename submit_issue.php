<?php
/**
 * submit_issue.php
 * Handles issue submission with:
 *  - Input sanitisation & validation
 *  - CSRF-style POST-only gate
 *  - Parameterised queries (no SQL injection)
 *  - Schema: citizens(phone, national_id, constituency_id), issues(title, description, category, constituency_id)
 */

// ── Only accept POST ──
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// ── Security headers ──
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// ── DB config ──
$dsn      = "pgsql:host=ep-shiny-paper-amlezem9-pooler.c-5.us-east-1.aws.neon.tech;port=5432;dbname=bomalathu;sslmode=require";
$user     = "neondb_owner";
$password = "npg_RBeaf6vDYc7V";

// ── Helper: send JSON response ──
function respond(bool $success, string $message, array $extra = []): void {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

// ── Collect & sanitise input ──
$phone            = trim(strip_tags($_POST['phone']        ?? ''));
$category         = trim(strip_tags($_POST['category']     ?? ''));
$title            = trim(strip_tags($_POST['title']        ?? ''));
$description      = trim(strip_tags($_POST['description']  ?? ''));
$constituencyName = trim(strip_tags($_POST['constituency'] ?? ''));

// ── Validate required fields ──
$errors = [];

if (!preg_match('/^0\d{8,14}$/', $phone)) {
    $errors[] = 'Invalid phone number format.';
}

$allowedCategories = ['Water', 'Health', 'Roads', 'Education', 'Electricity', 'Other'];
if (!in_array($category, $allowedCategories, true)) {
    $errors[] = 'Invalid category selected.';
}

if (mb_strlen($title) < 5 || mb_strlen($title) > 255) {
    $errors[] = 'Title must be between 5 and 255 characters.';
}

if (mb_strlen($description) < 10) {
    $errors[] = 'Description is too short.';
}

if (empty($constituencyName)) {
    $errors[] = 'Constituency is required.';
}

if (!empty($errors)) {
    respond(false, implode(' ', $errors));
}

// ── DB operations ──
try {
    $conn = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // 1. Resolve constituency name → ID
    $stmt = $conn->prepare("SELECT id FROM constituencies WHERE name = :name LIMIT 1");
    $stmt->execute([':name' => $constituencyName]);
    $row = $stmt->fetch();

    if (!$row) {
        respond(false, 'Constituency not found. Please go back and select a valid constituency.');
    }
    $constituency_id = (int) $row['id'];

    // 2. Check if citizen exists (by phone)
    $stmt = $conn->prepare("SELECT id FROM citizens WHERE phone = :phone");
    $stmt->execute([':phone' => $phone]);
    $citizen = $stmt->fetch();

    // 3. Create citizen if not found
    if (!$citizen) {
        // national_id is UNIQUE NOT NULL — generate a placeholder so we don't violate the constraint.
        // In production, collect national_id from the form.
        $placeholderNatId = 'PENDING_' . strtoupper(substr(md5($phone . time()), 0, 8));

        $stmt = $conn->prepare("
            INSERT INTO citizens (phone, national_id, constituency_id)
            VALUES (:phone, :national_id, :constituency_id)
            RETURNING id
        ");
        $stmt->execute([
            ':phone'           => $phone,
            ':national_id'     => $placeholderNatId,
            ':constituency_id' => $constituency_id,
        ]);
        $citizen = $stmt->fetch();
    }

    if (!$citizen) {
        respond(false, 'Could not identify or create citizen record.');
    }

    // 4. Insert issue
    $stmt = $conn->prepare("
        INSERT INTO issues (title, description, category, constituency_id)
        VALUES (:title, :description, :category, :constituency_id)
        RETURNING id
    ");
    $stmt->execute([
        ':title'           => $title,
        ':description'     => $description,
        ':category'        => $category,
        ':constituency_id' => $constituency_id,
    ]);
    $issue = $stmt->fetch();

    if (!$issue) {
        respond(false, 'Issue could not be saved. Please try again.');
    }

    respond(true, '✅ Issue submitted successfully!', ['issue_id' => $issue['id']]);

} catch (PDOException $e) {
    // Log the real error server-side; never expose raw SQL errors to clients
    error_log('[LiwuConnect] DB error in submit_issue.php: ' . $e->getMessage());
    respond(false, 'A database error occurred. Please try again later.');
}
