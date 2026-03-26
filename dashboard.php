<?php
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");

// ── DB credentials ──
$dsn    = "pgsql:host=ep-shiny-paper-amlezem9-pooler.c-5.us-east-1.aws.neon.tech;port=5432;dbname=bomalathu;sslmode=require";
$dbUser = "neondb_owner";
$dbPass = "npg_RBeaf6vDYc7V";

// ── AJAX: handle satisfaction rating POST ──
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) && $_POST['action'] === 'rate_issue'
) {
    header('Content-Type: application/json');
    $issueId = (int)($_POST['issue_id'] ?? 0);
    $rating  = trim($_POST['rating'] ?? '');

    if (!$issueId || !in_array($rating, ['satisfied', 'unsatisfied'], true)) {
        echo json_encode(['ok' => false, 'error' => 'invalid']);
        exit;
    }

    try {
        $conn = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        // Verify the issue is Resolved before allowing a rating
        $chk = $conn->prepare("SELECT status FROM issues WHERE id = :id LIMIT 1");
        $chk->execute([':id' => $issueId]);
        $row = $chk->fetch(PDO::FETCH_ASSOC);
        if (!$row || $row['status'] !== 'Resolved') {
            echo json_encode(['ok' => false, 'error' => 'not_resolved']);
            exit;
        }

        $col = $rating === 'satisfied' ? 'satisfied_count' : 'unsatisfied_count';
        $stmt = $conn->prepare("UPDATE issues SET {$col} = {$col} + 1 WHERE id = :id");
        $stmt->execute([':id' => $issueId]);

        // Return fresh counts
        $cnt = $conn->prepare("SELECT satisfied_count, unsatisfied_count FROM issues WHERE id = :id");
        $cnt->execute([':id' => $issueId]);
        $counts = $cnt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'ok'          => true,
            'satisfied'   => (int)$counts['satisfied_count'],
            'unsatisfied' => (int)$counts['unsatisfied_count'],
        ]);
    } catch (PDOException $e) {
        error_log('[LiwuConnect] rating: ' . $e->getMessage());
        echo json_encode(['ok' => false, 'error' => 'db_error']);
    }
    exit;
}

// ── Language toggle handler ──
if (isset($_GET['setlang']) && in_array($_GET['setlang'], ['en', 'ny'])) {
    setcookie('lang', $_GET['setlang'], time() + (365 * 24 * 3600), '/');
    $params = $_GET; unset($params['setlang']);
    $qs = !empty($params) ? '?' . http_build_query($params) : '';
    header('Location: dashboard.php' . $qs);
    exit;
}
$lang = (isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'ny') ? 'ny' : 'en';
$toggleLang = $lang === 'en' ? 'ny' : 'en';

// ── All UI strings ──
$s = [
    'page_title'        => ['en' => 'Public Dashboard — LiwuConnect',                             'ny' => 'Tsamba Lalikulu — LiwuConnect'],
    'nav_report'        => ['en' => 'Report Issue',                                                  'ny' => 'Fotokozani Vuto'],
    'nav_toggle'        => ['en' => '🇲🇼 Chichewa',                                                   'ny' => '🇬🇧 English'],
    'dash_title'        => ['en' => 'Public Issues Dashboard',                                       'ny' => 'Tsamba Lalikulu la Mavuto'],
    'dash_sub'          => ['en' => 'Track issues, follow progress, and hold your MP accountable.',  'ny' => 'Tsatirani mavuto, onani za patsogolo, ndi kuika MP pa udindo.'],
    'cons_label'        => ['en' => 'Select Constituency',                                           'ny' => 'Sankhani Bwaalo'],
    'cons_choose'       => ['en' => '— Choose a constituency —',                                     'ny' => '— Sankhani bwaalo —'],
    'filter_category'   => ['en' => 'Category',                                                      'ny' => 'Gulu'],
    'filter_status'     => ['en' => 'Status',                                                        'ny' => 'Mkhalidwe'],
    'filter_all'        => ['en' => 'All',                                                           'ny' => 'Zonse'],
    'cat_water'         => ['en' => '💧 Water',                                                       'ny' => '💧 Madzi'],
    'cat_health'        => ['en' => '❤️ Health',                                                      'ny' => '❤️ Zaumoyo'],
    'cat_roads'         => ['en' => '🛣️ Roads',                                                       'ny' => '🛣️ Misewu'],
    'cat_education'     => ['en' => '🎓 Education',                                                   'ny' => '🎓 Maphunziro'],
    'cat_electricity'   => ['en' => '⚡ Electricity',                                                 'ny' => '⚡ Magetsi'],
    'cat_other'         => ['en' => '⋯ Other',                                                       'ny' => '⋯ Zina'],
    'status_submitted'  => ['en' => '⏳ Submitted',                                                   'ny' => '⏳ Watumizidwa'],
    'status_inprogress' => ['en' => '🔧 In Progress',                                                 'ny' => '🔧 Akupitirira'],
    'status_resolved'   => ['en' => '✅ Resolved',                                                    'ny' => '✅ Zakhonzedwa'],
    'chip_submitted'    => ['en' => 'Submitted',                                                     'ny' => 'Watumizidwa'],
    'chip_inprogress'   => ['en' => 'In Progress',                                                   'ny' => 'Akupitirira'],
    'chip_resolved'     => ['en' => 'Resolved',                                                      'ny' => 'Zakhonzedwa'],
    'stat_total'        => ['en' => 'Total Issues',                                                  'ny' => 'Mavuto Onse'],
    'stat_submitted'    => ['en' => 'Submitted',                                                     'ny' => 'Atumizidwa'],
    'stat_inprogress'   => ['en' => 'In Progress',                                                   'ny' => 'Akupitirira'],
    'stat_resolved'     => ['en' => 'Resolved',                                                      'ny' => 'Asinthidwa'],
    'resolution_rate'   => ['en' => 'Resolution Rate',                                               'ny' => 'Mlingo wa Kusinthidwa'],
    'issues_addressed'  => ['en' => 'issues addressed',                                              'ny' => 'mavuto osinthidwa'],
    'of_word'           => ['en' => 'of',                                                            'ny' => 'mwa'],
    'search_ph'         => ['en' => 'Search issues…',                                               'ny' => 'Fufuzani mavuto…'],
    'showing'           => ['en' => 'Showing',                                                       'ny' => 'Kuonetsa'],
    'issue_word'        => ['en' => 'issue',                                                         'ny' => 'vuto'],
    'issues_word'       => ['en' => 'issues',                                                        'ny' => 'mavuto'],
    'read_more'         => ['en' => 'Read more',                                                     'ny' => 'Werengani zambiri'],
    'show_less'         => ['en' => 'Show less',                                                     'ny' => 'Onetsa zoochepa'],
    'mp_response'       => ['en' => 'MP Response',                                                   'ny' => 'Yankho la MP'],
    'updated'           => ['en' => 'Updated',                                                       'ny' => 'Zasinthidwa'],
    'no_issues'         => ['en' => 'No issues found',                                               'ny' => 'Palibe mavuto opezeka'],
    'try_filters'       => ['en' => 'Try removing some filters above.',                              'ny' => 'Yesani kuchotsa zosankha zina pamwamba.'],
    'report_first'      => ['en' => 'Be the first to report one!',                                   'ny' => 'Khalani woyamba kulonga imodzi!'],
    'cta_heading'       => ['en' => "Don't see your issue?",                                         'ny' => 'Simuwona vuto lanu?'],
    'cta_sub'           => ['en' => 'Be heard. Submit your concern and let your MP know.',           'ny' => 'Mverani. Tumizani nkhawa yanu ndi kuuza MP wanu.'],
    'report_new'        => ['en' => 'Report a New Issue',                                            'ny' => 'Fotokozani Vuto Latsopano'],
    'select_prompt'     => ['en' => 'Select your constituency',                                      'ny' => 'Sankhani dera lanu'],
    'select_sub'        => ['en' => 'Choose above to see all reported issues and their progress.',   'ny' => 'Sankhani pamwamba kuwona mavuto onse olongoseredwa ndi za patsogolo pawo.'],
    'feature_see'       => ['en' => 'See All Issues',                                                'ny' => 'Onani Mavuto Onse'],
    'feature_see_sub'   => ['en' => 'Every concern from your area',                                  'ny' => 'Nkhawa iliyonse kuchokera kudera lanu'],
    'feature_track'     => ['en' => 'Track Progress',                                                'ny' => 'Tsatirani Za Patsogolo'],
    'feature_track_sub' => ['en' => 'Follow resolution in real time',                                'ny' => 'Tsatirani kusinthidwa m\'nthawi zenizeni'],
    'feature_mp'        => ['en' => 'MP Responses',                                                  'ny' => 'Mayankho a MP'],
    'feature_mp_sub'    => ['en' => 'Read what your MP said',                                        'ny' => 'Werengani zomwe Phungu wanu adanena'],
    'feature_upvote'    => ['en' => 'Upvote Issues',                                                 'ny' => 'Voterani Mavuto'],
    'feature_upvote_sub'=> ['en' => 'Show support on the main form',                                 'ny' => 'Onetsa thandizo pa fomulo lachikulu'],
    'voices'            => ['en' => 'voices',                                                        'ny' => 'mauthenga'],
    'no_search'         => ['en' => 'No issues match your search.',                                  'ny' => 'Palibe mavuto ogwirizana ndi zomwe munalemba.'],
    'just_now'          => ['en' => 'Just now',                                                      'ny' => 'Tsopano'],
    'ago_m'             => ['en' => 'm ago',                                                         'ny' => 'min zapita'],
    'ago_h'             => ['en' => 'h ago',                                                         'ny' => 'maola apita'],
    'ago_d'             => ['en' => 'd ago',                                                         'ny' => 'masiku apita'],
    // ── Satisfaction rating strings ──
    'rate_prompt'       => ['en' => 'Was this truly resolved?',                                      'ny' => 'Kodi izi zinakhonzedwa kwenikweni?'],
    'rate_yes'          => ['en' => 'Yes, resolved',                                                 'ny' => 'Inde, zakhonzedwa'],
    'rate_no'           => ['en' => 'Not really',                                                    'ny' => 'Ayi, sizinakhonzedwe'],
    'rate_thanks'       => ['en' => 'Thanks for your feedback!',                                     'ny' => 'Zikomo pa maganizo anu!'],
];
$t = array_map(fn($v) => $v[$lang], $s);

// ── DB & filters ──
$selectedConstituency = trim(strip_tags($_GET['constituency'] ?? ''));
$selectedCategory     = trim(strip_tags($_GET['category']     ?? ''));
$selectedStatus       = trim(strip_tags($_GET['status']       ?? ''));

$allowedCategories = ['', 'Water', 'Health', 'Roads', 'Education', 'Electricity', 'Other'];
$allowedStatuses   = ['', 'Submitted', 'In Progress', 'Resolved'];
if (!in_array($selectedCategory, $allowedCategories, true)) $selectedCategory = '';
if (!in_array($selectedStatus,   $allowedStatuses,   true)) $selectedStatus   = '';

$constituencies = [];
$issues         = [];
$stats          = ['total'=>0,'submitted'=>0,'in_progress'=>0,'resolved'=>0];

try {
    $conn = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $constituencies = $conn->query("SELECT name FROM constituencies ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);

    if ($selectedConstituency !== '') {
        $stmt = $conn->prepare("SELECT id FROM constituencies WHERE name = :name LIMIT 1");
        $stmt->execute([':name' => $selectedConstituency]);
        $consRow = $stmt->fetch();
        if ($consRow) {
            $cid = (int)$consRow['id'];
            $where = ["i.constituency_id = :cid"]; $params = [':cid' => $cid];
            if ($selectedCategory !== '') { $where[] = "i.category = :cat";    $params[':cat']    = $selectedCategory; }
            if ($selectedStatus   !== '') { $where[] = "i.status = :status";   $params[':status'] = $selectedStatus; }
            $whereSQL = 'WHERE ' . implode(' AND ', $where);
            $stmt = $conn->prepare("
                SELECT i.id, i.title, i.description, i.category, i.status, i.mp_response, i.created_at, i.updated_at,
                       i.satisfied_count, i.unsatisfied_count,
                       COUNT(s.id) AS vote_count
                FROM issues i
                LEFT JOIN issue_subscriptions s ON s.issue_id = i.id
                $whereSQL
                GROUP BY i.id
                ORDER BY CASE i.status WHEN 'In Progress' THEN 1 WHEN 'Submitted' THEN 2 WHEN 'Resolved' THEN 3 ELSE 4 END,
                         vote_count DESC, i.created_at DESC LIMIT 100");
            $stmt->execute($params);
            $issues = $stmt->fetchAll();
            $stmt = $conn->prepare("SELECT COUNT(*) AS total, SUM(CASE WHEN status='Submitted' THEN 1 ELSE 0 END) AS submitted, SUM(CASE WHEN status='In Progress' THEN 1 ELSE 0 END) AS in_progress, SUM(CASE WHEN status='Resolved' THEN 1 ELSE 0 END) AS resolved FROM issues WHERE constituency_id = :cid");
            $stmt->execute([':cid' => $cid]);
            $row = $stmt->fetch();
            $stats = ['total'=>(int)($row['total']??0),'submitted'=>(int)($row['submitted']??0),'in_progress'=>(int)($row['in_progress']??0),'resolved'=>(int)($row['resolved']??0)];
        }
    }
} catch (PDOException $e) { error_log('[LiwuConnect] dashboard.php: '.$e->getMessage()); }

function esc(string $s): string { return htmlspecialchars($s, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }

function timeAgo(string $datetime, array $t): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return $t['just_now'];
    if ($diff < 3600)   return floor($diff/60)   . ' ' . $t['ago_m'];
    if ($diff < 86400)  return floor($diff/3600)  . ' ' . $t['ago_h'];
    if ($diff < 604800) return floor($diff/86400) . ' ' . $t['ago_d'];
    return date('d M Y', strtotime($datetime));
}

$catIcons = [
    'Water'       => ['icon'=>'fa-faucet',         'bg'=>'bg-blue-50',   'color'=>'text-blue-500'],
    'Health'      => ['icon'=>'fa-heart-pulse',    'bg'=>'bg-red-50',    'color'=>'text-red-500'],
    'Roads'       => ['icon'=>'fa-road',           'bg'=>'bg-orange-50', 'color'=>'text-orange-500'],
    'Education'   => ['icon'=>'fa-graduation-cap', 'bg'=>'bg-purple-50', 'color'=>'text-purple-500'],
    'Electricity' => ['icon'=>'fa-bolt',           'bg'=>'bg-yellow-50', 'color'=>'text-yellow-500'],
    'Other'       => ['icon'=>'fa-ellipsis',       'bg'=>'bg-gray-100',  'color'=>'text-gray-500'],
];

// Build toggle URL preserving current filters
$toggleParams = $_GET; unset($toggleParams['setlang']); $toggleParams['setlang'] = $toggleLang;
$toggleUrl = 'dashboard.php?' . http_build_query($toggleParams);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['page_title'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <script>
        tailwind.config = {
            theme: { extend: {
                colors: { yazaBlue:'#0B1D45', yazaGold:'#FFCC00', yazaCream:'#F8F4E1' },
                fontFamily: { sans: ['Plus Jakarta Sans','sans-serif'] }
            }}
        };
    </script>
    <style>
        :root { --blue:#0B1D45; --gold:#FFCC00; --cream:#F8F4E1; }
        body { background:var(--cream); color:var(--blue); }
        .issue-card { background:#fff; border:2px solid transparent; border-radius:1.25rem; transition:border-color .2s,box-shadow .2s,transform .2s; animation:slideUp .35s cubic-bezier(.22,.68,0,1.2) both; }
        .issue-card:hover { border-color:var(--gold); box-shadow:0 8px 28px rgba(11,29,69,.1); transform:translateY(-2px); }
        @keyframes slideUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
        .issue-card:nth-child(1){animation-delay:.04s} .issue-card:nth-child(2){animation-delay:.08s}
        .issue-card:nth-child(3){animation-delay:.12s} .issue-card:nth-child(4){animation-delay:.16s}
        .issue-card:nth-child(5){animation-delay:.20s} .issue-card:nth-child(6){animation-delay:.24s}
        .chip { display:inline-flex; align-items:center; gap:.3rem; font-size:.68rem; font-weight:800; padding:.25rem .7rem; border-radius:99px; text-transform:uppercase; letter-spacing:.04em; }
        .chip-submitted  { background:#FEF9C3; color:#854D0E; }
        .chip-inprogress { background:#DCFCE7; color:#166534; }
        .chip-resolved   { background:#DBEAFE; color:#1E40AF; }
        .stat-card { background:#fff; border-radius:1.25rem; padding:1.1rem 1rem; text-align:center; border:2px solid transparent; transition:border-color .2s; }
        .stat-card:hover { border-color:var(--gold); }
        .filter-pill { padding:.45rem 1rem; border-radius:99px; border:2px solid #e5e7eb; font-weight:700; font-size:.78rem; cursor:pointer; transition:all .15s; background:#fff; white-space:nowrap; }
        .filter-pill.active, .filter-pill:hover { border-color:var(--gold); background:#FFFBEA; color:var(--blue); }
        .field-select { appearance:none; background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%230B1D45' stroke-width='2' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 1rem center; border:2px solid transparent; border-radius:1rem; padding:.85rem 2.5rem .85rem 1rem; font-weight:700; font-size:.95rem; color:var(--blue); outline:none; width:100%; transition:border-color .2s; font-family:inherit; }
        .field-select:focus { border-color:var(--gold); box-shadow:0 0 0 4px rgba(255,204,0,.15); }
        .mp-response { background:#F0F9FF; border-left:3px solid var(--gold); border-radius:0 .75rem .75rem 0; padding:.75rem 1rem; margin-top:.75rem; }
        #searchInput { transition:border-color .2s,box-shadow .2s; }
        #searchInput:focus { border-color:var(--gold); box-shadow:0 0 0 4px rgba(255,204,0,.15); outline:none; }
        .resolve-bar { transition:width .8s cubic-bezier(.4,0,.2,1); }
        #backTop { position:fixed; bottom:1.5rem; right:1.5rem; width:44px; height:44px; background:var(--blue); color:var(--gold); border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 16px rgba(11,29,69,.3); cursor:pointer; opacity:0; pointer-events:none; transition:opacity .25s; font-size:1.1rem; }
        #backTop.visible { opacity:1; pointer-events:all; }
        .lang-btn { display:flex; align-items:center; gap:5px; font-size:12px; font-weight:800; background:rgba(255,204,0,.15); border:1.5px solid rgba(255,204,0,.4); color:#FFCC00; padding:6px 13px; border-radius:99px; cursor:pointer; transition:background .2s,border-color .2s; text-decoration:none; white-space:nowrap; }
        .lang-btn:hover { background:rgba(255,204,0,.3); border-color:rgba(255,204,0,.7); }

        /* ── Satisfaction rating styles ── */
        .rating-bar {
            margin-top:.9rem;
            padding-top:.9rem;
            border-top:1.5px dashed #e5e7eb;
        }
        .rating-prompt {
            font-size:.7rem;
            font-weight:800;
            text-transform:uppercase;
            letter-spacing:.06em;
            color:#9ca3af;
            margin-bottom:.55rem;
            display:flex;
            align-items:center;
            gap:.35rem;
        }
        .rating-btn {
            display:inline-flex;
            align-items:center;
            gap:.4rem;
            padding:.4rem .9rem;
            border-radius:99px;
            border:2px solid #e5e7eb;
            font-size:.76rem;
            font-weight:800;
            cursor:pointer;
            background:#fff;
            color:#374151;
            transition:border-color .2s, background .2s, transform .18s cubic-bezier(.22,.68,0,1.2), box-shadow .2s;
            user-select:none;
            font-family:inherit;
        }
        .rating-btn:hover:not(:disabled) { transform:scale(1.06); box-shadow:0 3px 10px rgba(0,0,0,.08); }
        .rating-btn:active:not(:disabled) { transform:scale(.95); }
        .rating-btn:disabled { cursor:default; opacity:.85; }

        /* Voted states */
        .rating-btn.btn-yes.voted {
            border-color:#16a34a;
            background:#f0fdf4;
            color:#15803d;
            box-shadow:0 0 0 3px rgba(22,163,74,.15);
        }
        .rating-btn.btn-no.voted {
            border-color:#dc2626;
            background:#fef2f2;
            color:#b91c1c;
            box-shadow:0 0 0 3px rgba(220,38,38,.15);
        }
        /* Unselected-but-disabled (the other button after voting) */
        .rating-btn.btn-yes.faded,
        .rating-btn.btn-no.faded {
            opacity:.38;
            border-color:#e5e7eb;
            background:#f9fafb;
        }

        .btn-icon { font-size:.95rem; display:inline-block; transition:transform .3s cubic-bezier(.22,.68,0,1.2); }
        .rating-btn.voted .btn-icon { transform:scale(1.35); }

        .rating-count {
            font-size:.72rem;
            font-weight:900;
            min-width:.8rem;
            text-align:center;
        }
        .rating-thanks {
            font-size:.72rem;
            font-weight:700;
            color:#6b7280;
            display:inline-flex;
            align-items:center;
            gap:.3rem;
            opacity:0;
            transform:translateY(4px);
            transition:opacity .35s ease, transform .35s ease;
            pointer-events:none;
        }
        .rating-thanks.show {
            opacity:1;
            transform:translateY(0);
        }

        @keyframes popIn {
            0%   { transform:scale(.6); }
            65%  { transform:scale(1.4); }
            100% { transform:scale(1.35); }
        }
        .rating-btn.voted .btn-icon { animation:popIn .35s cubic-bezier(.22,.68,0,1.2) forwards; }
    </style>
</head>
<body class="font-sans antialiased">

    <!-- ════ NAV ════ -->
    <nav class="bg-yazaBlue text-white py-4 px-5 sticky top-0 z-50 shadow-md">
        <div class="max-w-3xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-yazaGold rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-landmark text-yazaBlue text-lg"></i>
                </div>
                <div>
                    <span class="font-extrabold text-lg tracking-tight">Citizen<span class="text-yazaGold">Connect</span></span>
                    <span class="text-white/40 text-xs font-semibold ml-2 hidden sm:inline"><?= $t['dash_title'] ?></span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="index.php" class="flex items-center gap-1.5 bg-yazaGold text-yazaBlue font-extrabold text-xs px-3 py-1.5 rounded-full hover:opacity-90 transition-opacity shadow">
                    <i class="fas fa-plus text-xs"></i>
                    <span class="hidden sm:inline"><?= $t['nav_report'] ?></span>
                </a>
                <a href="<?= esc($toggleUrl) ?>" class="lang-btn">
                    <?= $t['nav_toggle'] ?>
                </a>
            </div>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto p-5 pb-24 space-y-6">

        <!-- Header -->
        <div class="pt-3 pb-1">
            <h1 class="text-2xl font-extrabold leading-tight">
                <?php if ($selectedConstituency): ?>
                    <span class="text-yazaBlue"><?= esc($selectedConstituency) ?></span>
                    <span class="text-gray-400 font-medium"> — <?= $t['dash_title'] ?></span>
                <?php else: ?>
                    <?= $t['dash_title'] ?>
                <?php endif; ?>
            </h1>
            <p class="text-sm text-gray-500 font-medium mt-1"><?= $t['dash_sub'] ?></p>
        </div>

        <!-- Constituency selector + filters -->
        <form method="GET" id="filterForm">
            <?php if (isset($_GET['setlang'])): ?>
                <input type="hidden" name="setlang" value="<?= esc($_GET['setlang']) ?>">
            <?php endif; ?>
            <div class="bg-white rounded-2xl shadow-sm p-4 space-y-4">
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-widest text-gray-400 mb-2">
                        <i class="fas fa-map-marker-alt mr-1"></i> <?= $t['cons_label'] ?>
                    </label>
                    <select name="constituency" class="field-select" onchange="this.form.submit()">
                        <option value=""><?= $t['cons_choose'] ?></option>
                        <?php foreach ($constituencies as $c): ?>
                            <option value="<?= esc($c) ?>" <?= $selectedConstituency === $c ? 'selected' : '' ?>><?= esc($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($selectedConstituency): ?>
                <div class="space-y-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-2"><?= $t['filter_category'] ?></p>
                        <div class="flex flex-wrap gap-2">
                            <?php
                            $catMap = [
                                ''            => $t['filter_all'],
                                'Water'       => $t['cat_water'],
                                'Health'      => $t['cat_health'],
                                'Roads'       => $t['cat_roads'],
                                'Education'   => $t['cat_education'],
                                'Electricity' => $t['cat_electricity'],
                                'Other'       => $t['cat_other'],
                            ];
                            foreach ($catMap as $val => $label):
                            ?>
                                <button type="button" onclick="setFilter('category','<?= esc($val) ?>')"
                                    class="filter-pill <?= $selectedCategory === $val ? 'active' : '' ?>">
                                    <?= $label ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-2"><?= $t['filter_status'] ?></p>
                        <div class="flex flex-wrap gap-2">
                            <?php
                            $statusMap = [
                                ''            => $t['filter_all'],
                                'Submitted'   => $t['status_submitted'],
                                'In Progress' => $t['status_inprogress'],
                                'Resolved'    => $t['status_resolved'],
                            ];
                            foreach ($statusMap as $val => $label):
                            ?>
                                <button type="button" onclick="setFilter('status','<?= esc($val) ?>')"
                                    class="filter-pill <?= $selectedStatus === $val ? 'active' : '' ?>">
                                    <?= $label ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="category" id="catInput"    value="<?= esc($selectedCategory) ?>">
                <input type="hidden" name="status"   id="statusInput" value="<?= esc($selectedStatus) ?>">
                <?php endif; ?>
            </div>
        </form>

        <?php if ($selectedConstituency): ?>

        <!-- Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="stat-card">
                <p class="text-2xl font-extrabold text-yazaBlue"><?= $stats['total'] ?></p>
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mt-0.5"><?= $t['stat_total'] ?></p>
            </div>
            <div class="stat-card border-yellow-200">
                <p class="text-2xl font-extrabold text-yellow-600"><?= $stats['submitted'] ?></p>
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mt-0.5"><?= $t['stat_submitted'] ?></p>
            </div>
            <div class="stat-card border-green-200">
                <p class="text-2xl font-extrabold text-green-600"><?= $stats['in_progress'] ?></p>
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mt-0.5"><?= $t['stat_inprogress'] ?></p>
            </div>
            <div class="stat-card border-blue-200">
                <p class="text-2xl font-extrabold text-blue-600"><?= $stats['resolved'] ?></p>
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mt-0.5"><?= $t['stat_resolved'] ?></p>
            </div>
        </div>

        <!-- Resolution bar -->
        <?php if ($stats['total'] > 0): $resolvedPct = round(($stats['resolved'] / $stats['total']) * 100); ?>
        <div class="bg-white rounded-2xl p-4 shadow-sm">
            <div class="flex justify-between items-center mb-2">
                <p class="text-xs font-black uppercase tracking-widest text-gray-400"><?= $t['resolution_rate'] ?></p>
                <p class="text-sm font-extrabold text-yazaBlue"><?= $resolvedPct ?>%</p>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2.5">
                <div class="resolve-bar bg-yazaGold h-2.5 rounded-full" style="width:<?= $resolvedPct ?>%"></div>
            </div>
            <p class="text-xs text-gray-400 font-semibold mt-1.5">
                <?= $stats['resolved'] ?> <?= $t['of_word'] ?> <?= $stats['total'] ?> <?= $t['issues_addressed'] ?>
            </p>
        </div>
        <?php endif; ?>

        <!-- Search -->
        <div class="relative">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" id="searchInput" placeholder="<?= $t['search_ph'] ?>"
                class="w-full pl-11 pr-4 py-3.5 bg-white border-2 border-transparent rounded-2xl font-semibold text-sm shadow-sm"
                oninput="filterCards(this.value)">
        </div>

        <!-- Issues list -->
        <div id="issuesContainer" class="space-y-4">
            <?php if (empty($issues)): ?>
                <div class="text-center py-16 text-gray-400">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-inbox text-4xl opacity-40"></i>
                    </div>
                    <p class="font-extrabold text-yazaBlue text-base"><?= $t['no_issues'] ?></p>
                    <p class="text-sm mt-1">
                        <?= ($selectedCategory || $selectedStatus) ? $t['try_filters'] : $t['report_first'] ?>
                    </p>
                    <a href="index.php?constituency=<?= urlencode($selectedConstituency) ?>"
                        class="inline-flex items-center gap-2 mt-5 bg-yazaBlue text-white font-bold text-sm px-5 py-3 rounded-2xl hover:opacity-90 transition-opacity">
                        <i class="fas fa-plus text-yazaGold"></i> <?= $t['report_new'] ?>
                    </a>
                </div>

            <?php else: ?>
                <p class="text-xs font-black uppercase tracking-widest text-gray-400 px-1">
                    <?= $t['showing'] ?> <?= count($issues) ?> <?= count($issues) !== 1 ? $t['issues_word'] : $t['issue_word'] ?>
                    <?= $selectedCategory ? ' · ' . esc($selectedCategory) : '' ?>
                    <?= $selectedStatus   ? ' · ' . esc($selectedStatus)   : '' ?>
                </p>

                <?php foreach ($issues as $issue):
                    $cat = $catIcons[$issue['category']] ?? $catIcons['Other'];
                    $statusClass = match($issue['status']) {
                        'In Progress' => 'chip-inprogress',
                        'Resolved'    => 'chip-resolved',
                        default       => 'chip-submitted',
                    };
                    $statusLabel = match($issue['status']) {
                        'In Progress' => '🔧 ' . $t['chip_inprogress'],
                        'Resolved'    => '✅ ' . $t['chip_resolved'],
                        default       => '⏳ ' . $t['chip_submitted'],
                    };
                    $isResolved       = $issue['status'] === 'Resolved';
                    $satisfiedCount   = (int)($issue['satisfied_count']   ?? 0);
                    $unsatisfiedCount = (int)($issue['unsatisfied_count'] ?? 0);
                ?>
                <div class="issue-card p-5"
                     data-title="<?= esc(strtolower($issue['title'])) ?>"
                     data-desc="<?= esc(strtolower($issue['description'])) ?>">

                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 <?= $cat['bg'] ?> rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas <?= $cat['icon'] ?> <?= $cat['color'] ?> text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="chip <?= $statusClass ?>"><?= $statusLabel ?></span>
                                <span class="text-[11px] text-gray-400 font-semibold"><?= esc($issue['category']) ?> · <?= timeAgo($issue['created_at'], $t) ?></span>
                            </div>
                            <h3 class="font-extrabold text-yazaBlue text-base leading-snug"><?= esc($issue['title']) ?></h3>
                            <p class="text-sm text-gray-500 mt-1 leading-relaxed line-clamp-2"><?= esc($issue['description']) ?></p>
                        </div>
                        <div class="flex flex-col items-center bg-yazaBlue/5 rounded-xl p-2.5 flex-shrink-0 min-w-[52px]">
                            <i class="fas fa-caret-up text-yazaBlue text-lg"></i>
                            <span class="text-yazaBlue font-extrabold text-sm leading-none mt-0.5"><?= (int)$issue['vote_count'] ?></span>
                            <span class="text-[9px] text-gray-400 font-bold uppercase mt-0.5"><?= $t['voices'] ?></span>
                        </div>
                    </div>

                    <?php if (!empty($issue['mp_response'])): ?>
                    <div class="mp-response">
                        <p class="text-[11px] font-black uppercase tracking-widest text-yazaBlue/60 mb-1">
                            <i class="fas fa-comment-dots mr-1 text-yazaGold"></i> <?= $t['mp_response'] ?>
                        </p>
                        <p class="text-sm text-yazaBlue font-semibold leading-relaxed"><?= esc($issue['mp_response']) ?></p>
                        <?php if ($issue['updated_at'] !== $issue['created_at']): ?>
                            <p class="text-[10px] text-gray-400 font-semibold mt-1"><?= $t['updated'] ?> <?= timeAgo($issue['updated_at'], $t) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (mb_strlen($issue['description']) > 120): ?>
                    <button onclick="toggleDesc(this)"
                        class="text-xs font-bold text-yazaBlue/50 hover:text-yazaBlue mt-2 flex items-center gap-1 transition-colors"
                        data-full="<?= esc($issue['description']) ?>"
                        data-short="<?= esc(mb_substr($issue['description'],0,120)) ?>…"
                        data-read-more="<?= esc($t['read_more']) ?>"
                        data-show-less="<?= esc($t['show_less']) ?>">
                        <i class="fas fa-chevron-down text-[10px]"></i> <?= $t['read_more'] ?>
                    </button>
                    <?php endif; ?>

                    <?php if ($isResolved): ?>
                    <!-- ── Satisfaction Rating — only on Resolved issues ── -->
                    <div class="rating-bar" data-issue-id="<?= $issue['id'] ?>">
                        <p class="rating-prompt">
                            <i class="fas fa-circle-question text-yazaGold"></i>
                            <?= $t['rate_prompt'] ?>
                        </p>
                        <div class="flex items-center flex-wrap gap-2">
                            <button type="button"
                                id="btn-yes-<?= $issue['id'] ?>"
                                class="rating-btn btn-yes"
                                onclick="submitRating(<?= $issue['id'] ?>, 'satisfied')">
                                <span class="btn-icon">👍</span>
                                <span><?= $t['rate_yes'] ?></span>
                                <span class="rating-count" id="yes-count-<?= $issue['id'] ?>"><?= $satisfiedCount > 0 ? $satisfiedCount : '' ?></span>
                            </button>
                            <button type="button"
                                id="btn-no-<?= $issue['id'] ?>"
                                class="rating-btn btn-no"
                                onclick="submitRating(<?= $issue['id'] ?>, 'unsatisfied')">
                                <span class="btn-icon">👎</span>
                                <span><?= $t['rate_no'] ?></span>
                                <span class="rating-count" id="no-count-<?= $issue['id'] ?>"><?= $unsatisfiedCount > 0 ? $unsatisfiedCount : '' ?></span>
                            </button>
                            <span class="rating-thanks" id="thanks-<?= $issue['id'] ?>">
                                <i class="fas fa-check-circle text-green-500"></i>
                                <?= $t['rate_thanks'] ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
                <?php endforeach; ?>

                <div id="noSearchResults" class="hidden text-center py-10 text-gray-400">
                    <i class="fas fa-search text-3xl mb-3 block opacity-40"></i>
                    <p class="font-bold text-sm"><?= $t['no_search'] ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- CTA -->
        <div class="bg-yazaBlue rounded-3xl p-6 text-center shadow-xl">
            <div class="w-14 h-14 bg-yazaGold rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg">
                <i class="fas fa-bullhorn text-yazaBlue text-2xl"></i>
            </div>
            <h3 class="font-extrabold text-white text-lg"><?= $t['cta_heading'] ?></h3>
            <p class="text-blue-200 text-sm mt-1 mb-4"><?= $t['cta_sub'] ?></p>
            <a href="index.php?constituency=<?= urlencode($selectedConstituency) ?>"
                class="inline-flex items-center gap-2 bg-yazaGold text-yazaBlue font-extrabold text-sm px-6 py-3 rounded-2xl hover:opacity-90 transition-opacity shadow-lg">
                <i class="fas fa-plus"></i> <?= $t['report_new'] ?>
            </a>
        </div>

        <?php else: ?>

        <!-- Landing (no constituency selected) -->
        <div class="text-center py-14 space-y-4">
            <div class="w-24 h-24 bg-yazaBlue rounded-3xl flex items-center justify-center mx-auto shadow-2xl">
                <i class="fas fa-map-marked-alt text-yazaGold text-4xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-extrabold text-yazaBlue"><?= $t['select_prompt'] ?></h2>
                <p class="text-gray-500 text-sm mt-1"><?= $t['select_sub'] ?></p>
            </div>
            <div class="grid grid-cols-2 gap-3 max-w-sm mx-auto pt-4 text-left">
                <div class="bg-white rounded-2xl p-4 shadow-sm">
                    <i class="fas fa-eye text-yazaGold text-xl mb-2 block"></i>
                    <p class="font-extrabold text-sm"><?= $t['feature_see'] ?></p>
                    <p class="text-xs text-gray-500 mt-0.5"><?= $t['feature_see_sub'] ?></p>
                </div>
                <div class="bg-white rounded-2xl p-4 shadow-sm">
                    <i class="fas fa-chart-line text-yazaBlue text-xl mb-2 block"></i>
                    <p class="font-extrabold text-sm"><?= $t['feature_track'] ?></p>
                    <p class="text-xs text-gray-500 mt-0.5"><?= $t['feature_track_sub'] ?></p>
                </div>
                <div class="bg-white rounded-2xl p-4 shadow-sm">
                    <i class="fas fa-comment-dots text-blue-500 text-xl mb-2 block"></i>
                    <p class="font-extrabold text-sm"><?= $t['feature_mp'] ?></p>
                    <p class="text-xs text-gray-500 mt-0.5"><?= $t['feature_mp_sub'] ?></p>
                </div>
                <div class="bg-white rounded-2xl p-4 shadow-sm">
                    <i class="fas fa-thumbs-up text-green-500 text-xl mb-2 block"></i>
                    <p class="font-extrabold text-sm"><?= $t['feature_upvote'] ?></p>
                    <p class="text-xs text-gray-500 mt-0.5"><?= $t['feature_upvote_sub'] ?></p>
                </div>
            </div>
        </div>

        <?php endif; ?>

    </main>

    <div id="backTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">
        <i class="fas fa-chevron-up"></i>
    </div>

    <script>
        // ── Existing helpers (unchanged) ──
        function setFilter(field, value) {
            document.getElementById(field === 'category' ? 'catInput' : 'statusInput').value = value;
            document.getElementById('filterForm').submit();
        }

        function filterCards(q) {
            const term  = q.toLowerCase().trim();
            const cards = document.querySelectorAll('#issuesContainer .issue-card');
            let visible = 0;
            cards.forEach(card => {
                const match = !term || card.dataset.title.includes(term) || card.dataset.desc.includes(term);
                card.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            const noRes = document.getElementById('noSearchResults');
            if (noRes) noRes.classList.toggle('hidden', visible > 0 || !term);
        }

        function toggleDesc(btn) {
            const p = btn.previousElementSibling;
            if (btn.dataset.expanded === '1') {
                p.textContent = btn.dataset.short;
                btn.innerHTML = `<i class="fas fa-chevron-down text-[10px]"></i> ${btn.dataset.readMore}`;
                btn.dataset.expanded = '0';
            } else {
                p.textContent = btn.dataset.full;
                btn.innerHTML = `<i class="fas fa-chevron-up text-[10px]"></i> ${btn.dataset.showLess}`;
                btn.dataset.expanded = '1';
            }
        }

        const backTop = document.getElementById('backTop');
        window.addEventListener('scroll', () => backTop.classList.toggle('visible', window.scrollY > 300));

        // ── Satisfaction rating logic ──
        const LS_KEY = 'cc_ratings_v1';

        function getStoredRatings() {
            try { return JSON.parse(localStorage.getItem(LS_KEY) || '{}'); }
            catch(e) { return {}; }
        }

        function storeRating(issueId, rating) {
            const r = getStoredRatings();
            r[issueId] = rating;
            localStorage.setItem(LS_KEY, JSON.stringify(r));
        }

        // Lock the UI for a voted issue — called on page load (restore) and after voting
        function lockUI(issueId, votedFor, animate) {
            const yesBtn   = document.getElementById(`btn-yes-${issueId}`);
            const noBtn    = document.getElementById(`btn-no-${issueId}`);
            const thanksEl = document.getElementById(`thanks-${issueId}`);
            if (!yesBtn || !noBtn) return;

            const activeBtn = votedFor === 'satisfied' ? yesBtn : noBtn;
            const fadedBtn  = votedFor === 'satisfied' ? noBtn  : yesBtn;

            activeBtn.classList.add('voted');
            activeBtn.disabled = true;
            fadedBtn.classList.add('faded');
            fadedBtn.disabled = true;

            if (thanksEl) {
                if (animate) {
                    setTimeout(() => thanksEl.classList.add('show'), 350);
                } else {
                    thanksEl.classList.add('show');
                }
            }
        }

        function updateCounts(issueId, satisfied, unsatisfied) {
            const yc = document.getElementById(`yes-count-${issueId}`);
            const nc = document.getElementById(`no-count-${issueId}`);
            if (yc) yc.textContent = satisfied   > 0 ? satisfied   : '';
            if (nc) nc.textContent = unsatisfied > 0 ? unsatisfied : '';
        }

        async function submitRating(issueId, rating) {
            // Already voted on this device? Ignore.
            if (getStoredRatings()[issueId]) return;

            // 1. Immediately lock UI (optimistic)
            lockUI(issueId, rating, true);

            // 2. Persist locally so refresh remembers the vote
            storeRating(issueId, rating);

            // 3. Send to server
            try {
                const fd = new FormData();
                fd.append('action',   'rate_issue');
                fd.append('issue_id', issueId);
                fd.append('rating',   rating);

                const res  = await fetch('dashboard.php', { method: 'POST', body: fd });
                const data = await res.json();

                if (data.ok) {
                    // Update counts with live DB values
                    updateCounts(issueId, data.satisfied, data.unsatisfied);
                }
            } catch (e) {
                // Silent fail — UI is already locked, localStorage records vote
                console.warn('[LiwuConnect] rating sync failed:', e);
            }
        }

        // On page load: restore any votes this browser has previously made
        (function restoreVotes() {
            const stored = getStoredRatings();
            Object.entries(stored).forEach(([id, rating]) => {
                lockUI(parseInt(id), rating, false);
            });
        })();
    </script>
</body>
</html>
