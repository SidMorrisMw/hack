<?php
/**
 * get_issues.php
 * Returns JSON array of issues for a constituency + category.
 * Also flags whether the requesting phone number already subscribed (upvoted).
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$dsn      = "pgsql:host=ep-shiny-paper-amlezem9-pooler.c-5.us-east-1.aws.neon.tech;port=5432;dbname=bomalathu;sslmode=require";
$dbUser   = "neondb_owner";
$dbPass   = "npg_RBeaf6vDYc7V";

$constituency = trim(strip_tags($_GET['constituency'] ?? ''));
$category     = trim(strip_tags($_GET['category']     ?? ''));
$phone        = trim(strip_tags($_GET['phone']        ?? ''));

$allowedCategories = ['Water', 'Health', 'Roads', 'Education', 'Electricity', 'Other'];

if (empty($constituency) || !in_array($category, $allowedCategories, true)) {
    echo json_encode([]);
    exit;
}

try {
    $conn = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Resolve constituency → ID
    $stmt = $conn->prepare("SELECT id FROM constituencies WHERE name = :name LIMIT 1");
    $stmt->execute([':name' => $constituency]);
    $consRow = $stmt->fetch();

    if (!$consRow) { echo json_encode([]); exit; }
    $constituency_id = (int) $consRow['id'];

    // Resolve citizen phone → ID (optional — for already_voted flag)
    $citizen_id = null;
    if ($phone !== '') {
        $stmt = $conn->prepare("SELECT id FROM citizens WHERE phone = :phone LIMIT 1");
        $stmt->execute([':phone' => $phone]);
        $cit = $stmt->fetch();
        if ($cit) $citizen_id = (int) $cit['id'];
    }

    // Fetch issues with subscription (upvote) count
    $stmt = $conn->prepare("
        SELECT
            i.id,
            i.title,
            i.description,
            i.status,
            i.created_at,
            COUNT(s.id) AS vote_count
        FROM issues i
        LEFT JOIN issue_subscriptions s ON s.issue_id = i.id
        WHERE i.constituency_id = :cid
          AND i.category        = :cat
        GROUP BY i.id
        ORDER BY vote_count DESC, i.created_at DESC
        LIMIT 30
    ");
    $stmt->execute([':cid' => $constituency_id, ':cat' => $category]);
    $issues = $stmt->fetchAll();

    // Flag already_voted for each issue if we know the citizen
    if ($citizen_id) {
        $ids = array_column($issues, 'id');
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $vStmt = $conn->prepare("
                SELECT issue_id FROM issue_subscriptions
                WHERE citizen_id = ? AND issue_id IN ($placeholders)
            ");
            $vStmt->execute(array_merge([$citizen_id], $ids));
            $voted = array_flip(array_column($vStmt->fetchAll(), 'issue_id'));

            foreach ($issues as &$issue) {
                $issue['already_voted'] = isset($voted[$issue['id']]);
            }
            unset($issue);
        }
    } else {
        foreach ($issues as &$issue) {
            $issue['already_voted'] = false;
        }
        unset($issue);
    }

    echo json_encode(array_values($issues));

} catch (PDOException $e) {
    error_log('[CitizenConnect] DB error in get_issues.php: ' . $e->getMessage());
    echo json_encode([]);
}
