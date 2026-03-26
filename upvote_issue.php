<?php
/**
 * upvote_issue.php
 * Records a citizen upvote as an issue_subscription row.
 * Uses the UNIQUE constraint (issue_id, citizen_id) to prevent duplicates.
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$dsn      = "pgsql:host=ep-shiny-paper-amlezem9-pooler.c-5.us-east-1.aws.neon.tech;port=5432;dbname=bomalathu;sslmode=require";
$dbUser   = "neondb_owner";
$dbPass   = "npg_RBeaf6vDYc7V";

$issue_id = (int) ($_POST['issue_id'] ?? 0);
$phone    = trim(strip_tags($_POST['phone'] ?? ''));

if ($issue_id <= 0 || empty($phone)) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

try {
    $conn = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Resolve citizen
    $stmt = $conn->prepare("SELECT id FROM citizens WHERE phone = :phone LIMIT 1");
    $stmt->execute([':phone' => $phone]);
    $citizen = $stmt->fetch();

    if (!$citizen) {
        echo json_encode(['success' => false, 'error' => 'Citizen not found']);
        exit;
    }
    $citizen_id = (int) $citizen['id'];

    // Insert subscription — ON CONFLICT DO NOTHING (idempotent)
    $stmt = $conn->prepare("
        INSERT INTO issue_subscriptions (issue_id, citizen_id)
        VALUES (:issue_id, :citizen_id)
        ON CONFLICT (issue_id, citizen_id) DO NOTHING
    ");
    $stmt->execute([':issue_id' => $issue_id, ':citizen_id' => $citizen_id]);

    // Return current vote count
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM issue_subscriptions WHERE issue_id = :id");
    $stmt->execute([':id' => $issue_id]);
    $count = (int) $stmt->fetch()['cnt'];

    echo json_encode(['success' => true, 'vote_count' => $count]);

} catch (PDOException $e) {
    error_log('[CitizenConnect] DB error in upvote_issue.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
