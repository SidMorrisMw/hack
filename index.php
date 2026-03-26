<?php
// ── Security headers ──
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: strict-origin-when-cross-origin");

// ── Language toggle handler ──
if (isset($_GET['setlang']) && in_array($_GET['setlang'], ['en', 'ny'])) {
    setcookie('lang', $_GET['setlang'], time() + (365 * 24 * 3600), '/');
    header('Location: index.php');
    exit;
}
$lang = (isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'ny') ? 'ny' : 'en';
$toggleLang = $lang === 'en' ? 'ny' : 'en';

// ── All UI strings ──
$s = [
    'page_title'        => ['en' => 'LiwuLathu — Report & Track Issues',                    'ny' => 'CitizenConnect — Longa & Tsatira Mavuto'],
    'nav_dashboard'     => ['en' => 'Dashboard',                                                 'ny' => 'Tsamba Lalikulu'],
    'nav_toggle'        => ['en' => '🇲🇼 Chichewa',                                               'ny' => '🇬🇧 English'],
    's1_label'          => ['en' => 'Step 1 of 4',                                               'ny' => 'Gawo 1 mwa 4'],
    's1_heading'        => ['en' => 'Submit your issue',                                              'ny' => 'Yambani Kufotokoza'],
    's1_sub'            => ['en' => 'Please Enter Your Phone Number.',      'ny' => 'Chonde Lowetsani Nambala Yanu ya Foni.'],
    'phone_label'       => ['en' => 'Enter your Phone Number',                                              'ny' => 'Nambala ya Foni'],
    'phone_placeholder' => ['en' => 'e.g. 0998877665',                                          'ny' => 'mwachitsanzo 0998877665'],
    'phone_err'         => ['en' => 'Please enter a valid phone number.',                        'ny' => 'Lowetsani nambala yabwino ya foni.'],
    'cons_label'        => ['en' => 'Enter your Constituency',                                              'ny' => 'Dera'],
    'cons_placeholder'  => ['en' => 'Search your constituency…',                                 'ny' => 'Fufuzani dera lanu…'],
    'cons_err'          => ['en' => 'Please select a constituency from the list.',               'ny' => 'Sankhani dera lanu kuchokera pamndandanda.'],
    'cons_no_results'   => ['en' => 'No results found',                                          'ny' => 'Palibe zopezeka'],
    'continue_btn'      => ['en' => 'CONTINUE',                                                  'ny' => 'PITIRIRA'],
    's2_label'          => ['en' => 'Step 2 of 4',                                               'ny' => 'Gawo 2 mwa 4'],
    's2_heading'        => ['en' => 'What would you like to do?',                                          'ny' => 'Mungakonde kutani?'],
    'report_title'      => ['en' => 'Report a New Issue',                                        'ny' => 'Fotokozani Vuto Latsopano'],
    'report_sub'        => ['en' => 'Log a problem in your constituency.',                       'ny' => 'Lemba vuto mu dera lanu.'],
    'upvote_title'      => ['en' => 'Support an Existing Issue',                                 'ny' => 'Thandizani Vuto Linalipo'],
    'upvote_sub'        => ['en' => 'Upvote issues already reported.',                           'ny' => 'Votani mavuto omwe adalongoseredwa.'],
    'go_back'           => ['en' => 'Go Back',                                                   'ny' => 'Bwererani'],
    's3_label'          => ['en' => 'Step 3 of 4',                                               'ny' => 'Gawo 3 mwa 4'],
    's3_heading'        => ['en' => 'Select a Category',                                         'ny' => 'Sankhani Gulu'],
    's3_sub'            => ['en' => 'What type of issue is this about?',                         'ny' => 'Vuto ili la mtundu wanji?'],
    'cat_water'         => ['en' => 'Water',                                                     'ny' => 'Madzi'],
    'cat_health'        => ['en' => 'Health',                                                    'ny' => 'Zaumoyo'],
    'cat_roads'         => ['en' => 'Roads',                                                     'ny' => 'Misewu'],
    'cat_education'     => ['en' => 'Education',                                                 'ny' => 'Maphunziro'],
    'cat_electricity'   => ['en' => 'Electricity',                                               'ny' => 'Magetsi'],
    'cat_other'         => ['en' => 'Other',                                                     'ny' => 'Zina'],
    's4_label'          => ['en' => 'Step 4 of 4',                                               'ny' => 'Gawo 4 mwa 4'],
    's4_heading'        => ['en' => 'Describe the Issue',                                        'ny' => 'Fotokozani Vuto'],
    's4_sub'            => ['en' => 'Be specific — it helps the MP act faster.',                 'ny' => 'Khalani owonekera — zimathandiza MP kugwira ntchito msanga.'],
    'title_label'       => ['en' => 'Issue Title',                                               'ny' => 'Mutu wa Vuto'],
    'title_placeholder' => ['en' => 'Short summary of the problem…',                            'ny' => 'Chifupichiri cha vuto…'],
    'desc_label'        => ['en' => 'Full Description',                                          'ny' => 'Kufotokoza Kwathunthu'],
    'desc_placeholder'  => ['en' => "Explain where it is, how long it's been a problem, who it affects…", 'ny' => 'Fotokozani kumene kulili, nthawi yaitali bwanji, ndani amene akukhudzidwa…'],
    'chars'             => ['en' => 'characters',                                                'ny' => 'zilembo'],
    'submit_btn'        => ['en' => 'SUBMIT TO MP',                                    'ny' => 'TUMIZANI KWA MP'],
    'submitting'        => ['en' => 'Submitting…',                                               'ny' => 'Kutumiza…'],
    'change_cat'        => ['en' => 'Change Category',                                           'ny' => 'Sinthani Gulu'],
    's4b_heading'       => ['en' => 'Ongoing Issues',                                            'ny' => 'Mavuto Omwe Akupitirira'],
    's4b_sub'           => ['en' => 'Issues from your constituency',                             'ny' => 'Mavuto ochokera ku bwaalo lanu'],
    'loading'           => ['en' => 'Loading issues…',                                           'ny' => 'Kukonza mavuto…'],
    'no_issues'         => ['en' => 'No issues found in this category.',                         'ny' => 'Palibe mavuto opezeka mu gulu ili.'],
    'be_first'          => ['en' => 'Be the first to report one!',                               'ny' => 'Khalani woyamba kulonga imodzi!'],
    'load_err'          => ['en' => 'Could not load issues.',                                    'ny' => 'Zinalephera kukonza mavuto.'],
    'success_heading'   => ['en' => 'Issue Submitted!',                                          'ny' => 'Vuto Latumizidwa!'],
    'success_p1'        => ['en' => 'Your concern has been recorded. Be assured your',           'ny' => 'Nkhawa yanu yalembedwa. Khalani okhulupirira kuti'],
    'success_mp'        => ['en' => 'Member of Parliament',                                      'ny' => 'Membala wa Nyumba ya Malamulo'],
    'success_p2'        => ['en' => 'will see this.',                                            'ny' => 'adzaona izi.'],
    'your_submission'   => ['en' => 'Your submission',                                           'ny' => 'Zomwe mwatumiza'],
    'status_submitted'  => ['en' => 'Status: Submitted',                                         'ny' => 'Mkhalidwe: Watumizidwa'],
    'view_dashboard'    => ['en' => 'View Public Dashboard',                                     'ny' => 'Onani Tsamba Lalikulu'],
    'submit_another'    => ['en' => 'Submit Another Issue',                                      'ny' => 'Tumizani Vuto Lina'],
    'js_issues_in'      => ['en' => 'issues in',                                                 'ny' => 'mavuto mu'],
    'js_sub_err'        => ['en' => 'Submission failed. Please try again.',                      'ny' => 'Zotumiza zanalephera. Chonderani yesaninso.'],
    'js_net_err'        => ['en' => 'Network error. Please check your connection.',              'ny' => 'Vuto la netiweki. Onani kulumikizana kwanu.'],
    'js_chip_sub'       => ['en' => 'Submitted',                                                 'ny' => 'Watumizidwa'],
    'js_chip_inp'       => ['en' => 'In Progress',                                               'ny' => 'Akupitirira'],
    'js_chip_res'       => ['en' => 'Resolved',                                                  'ny' => 'Wasinthidwa'],
    'js_voted'          => ['en' => 'Voted',                                                     'ny' => 'Yavotedwa'],
    'js_voices'         => ['en' => 'voices',                                                    'ny' => 'mauthenga'],
];
$t = array_map(fn($v) => $v[$lang], $s);

// ── DB ──
$dsn    = "pgsql:host=ep-shiny-paper-amlezem9-pooler.c-5.us-east-1.aws.neon.tech;port=5432;dbname=bomalathu;sslmode=require";
$dbUser = "neondb_owner";
$dbPass = "npg_RBeaf6vDYc7V";
$constituencyNames = [];
try {
    $db = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $constituencyNames = $db->query("SELECT name FROM constituencies ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['page_title'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
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
        * { box-sizing:border-box; }
        body { background-color:var(--cream); color:var(--blue); font-size:16px; }
        .step-layer { display:none; }
        .step-layer.active { display:block; animation:fadeUp .35s cubic-bezier(.22,.68,0,1.2) both; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(16px) scale(.98)} to{opacity:1;transform:translateY(0) scale(1)} }
        #progress-bar { transition:width .4s cubic-bezier(.4,0,.2,1); }
        .field { width:100%; padding:1rem 1.1rem; background:#fff; border:2px solid transparent; border-radius:1rem; font-weight:700; font-size:1.05rem; outline:none; transition:border-color .2s,box-shadow .2s; }
        .field:focus { border-color:var(--gold); box-shadow:0 0 0 4px rgba(255,204,0,.15); }
        .cat-card { background:#fff; padding:1.5rem; border-radius:1.5rem; border:2px solid transparent; cursor:pointer; transition:border-color .2s,transform .15s,box-shadow .2s; display:flex; flex-direction:column; align-items:center; gap:.75rem; }
        .cat-card:hover  { border-color:var(--gold); transform:translateY(-2px); box-shadow:0 8px 24px rgba(11,29,69,.1); }
        .cat-card.selected { border-color:var(--gold); background:#FFFBEA; box-shadow:0 0 0 4px rgba(255,204,0,.18); }
        .option-card { border:2px solid transparent; transition:all .2s ease-in-out; }
        .option-card:hover { border-color:var(--gold); }
        .option-card:active { transform:scale(.98); }
        .vote-btn { transition:all .25s ease; }
        .vote-btn.voted { background:#059669 !important; }
        .vote-btn.voted .vote-icon { display:none; }
        .vote-btn.voted .check-icon { display:block !important; }
        .chip-submitted  { background:#FEF9C3; color:#854D0E; }
        .chip-inprogress { background:#DCFCE7; color:#166534; }
        .chip-resolved   { background:#DBEAFE; color:#1E40AF; }
        #constituencyList { position:absolute; width:100%; z-index:50; background:#fff; border:2px solid var(--gold); border-radius:1rem; margin-top:.3rem; max-height:220px; overflow-y:auto; box-shadow:0 12px 40px rgba(11,29,69,.15); }
        .success-ring { animation:pop .5s cubic-bezier(.22,.68,0,1.3) .1s both; }
        @keyframes pop { from{transform:scale(0);opacity:0} to{transform:scale(1);opacity:1} }
        .spinner { width:22px; height:22px; border:3px solid rgba(255,255,255,.3); border-top-color:#fff; border-radius:50%; animation:spin .7s linear infinite; display:none; }
        @keyframes spin { to{transform:rotate(360deg)} }
        .list-item { padding:.75rem 1rem; cursor:pointer; font-weight:600; color:var(--blue); border-bottom:1px solid #f3f4f6; transition:background .15s; }
        .list-item:last-child { border:none; }
        .list-item:hover { background:rgba(255,204,0,.2); }
        .lang-btn { display:flex; align-items:center; gap:5px; font-size:12px; font-weight:800; background:rgba(255,204,0,.15); border:1.5px solid rgba(255,204,0,.4); color:#FFCC00; padding:6px 13px; border-radius:99px; cursor:pointer; transition:background .2s,border-color .2s; text-decoration:none; white-space:nowrap; }
        .lang-btn:hover { background:rgba(255,204,0,.3); border-color:rgba(255,204,0,.7); }
    </style>
</head>
<body class="font-sans antialiased">

    <!-- ════ NAV ════ -->
    <nav class="bg-yazaBlue text-white py-4 px-5 sticky top-0 z-50 shadow-md">
        <div class="max-w-xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-yazaGold rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-landmark text-yazaBlue text-lg"></i>
                </div>
                <span class="font-extrabold text-xl tracking-tight">Liwu<span class="text-yazaGold">Lathu</span></span>
            </div>
            <div class="flex items-center gap-2">
                <a href="dashboard.php" class="text-[12px] font-bold bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-full flex items-center gap-1.5 transition-colors">
                    <i class="fas fa-chart-bar text-yazaGold text-xs"></i>
                    <span class="hidden sm:inline"><?= $t['nav_dashboard'] ?></span>
                </a>
                <a href="?setlang=<?= $toggleLang ?>" class="lang-btn">
                    <?= $t['nav_toggle'] ?>
                </a>
            </div>
        </div>
        <div class="max-w-xl mx-auto mt-3">
            <div class="w-full bg-white/10 rounded-full h-1.5">
                <div id="progress-bar" class="bg-yazaGold h-1.5 rounded-full" style="width:25%"></div>
            </div>
        </div>
    </nav>

    <main class="max-w-xl mx-auto p-5 pb-28 space-y-0">

        <!-- ══ STEP 1 ══ -->
        <div id="step-1" class="step-layer active space-y-7 pt-4">
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-gray-500 mb-1"><?= $t['s1_label'] ?></p>
                <h2 class="text-2xl font-extrabold leading-tight"><?= $t['s1_heading'] ?></h2>
                <p class="text-gray-500 font-medium mt-1 text-sm"><?= $t['s1_sub'] ?></p>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-widest text-gray-400 mb-2 ml-1">
                        <i class="fas fa-phone-alt mr-1 text-yazaBlue/50"></i> <?= $t['phone_label'] ?>
                    </label>
                    <input type="tel" id="phoneInput" placeholder="<?= $t['phone_placeholder'] ?>" class="field" maxlength="15" inputmode="tel">
                    <p id="phone-err" class="text-red-500 text-xs font-bold mt-1 ml-1 hidden"><?= $t['phone_err'] ?></p>
                </div>
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-widest text-gray-400 mb-2 ml-1">
                        <i class="fas fa-map-marker-alt mr-1 text-yazaBlue/50"></i> <?= $t['cons_label'] ?>
                    </label>
                    <div class="relative">
                        <input type="text" id="constituencyInput" placeholder="<?= $t['cons_placeholder'] ?>" class="field pr-10" autocomplete="off">
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                        <div id="constituencyList" class="hidden"></div>
                    </div>
                    <input type="hidden" id="constituencySelected">
                    <p id="cons-err" class="text-red-500 text-xs font-bold mt-1 ml-1 hidden"><?= $t['cons_err'] ?></p>
                </div>
            </div>
            <button onclick="validateStep1()" class="w-full bg-yazaBlue text-white py-4 rounded-2xl font-extrabold text-base shadow-xl hover:opacity-90 flex items-center justify-center gap-2 transition-opacity">
                <?= $t['continue_btn'] ?> <i class="fas fa-arrow-right text-yazaGold"></i>
            </button>
        </div>

        <!-- ══ STEP 2 ══ -->
        <div id="step-2" class="step-layer space-y-5 pt-4">
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-gray-500 mb-1"><?= $t['s2_label'] ?></p>
                <h2 class="text-2xl font-extrabold leading-tight"><?= $t['s2_heading'] ?></h2>
            </div>
            <div class="space-y-4">
                <button onclick="setAction('report')" class="option-card w-full bg-white p-5 rounded-3xl text-left flex items-center gap-5 shadow-sm">
                    <div class="w-14 h-14 bg-yazaGold/15 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-file-circle-plus text-2xl text-yazaBlue"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base"><?= $t['report_title'] ?></h3>
                        <p class="text-sm text-gray-500 mt-0.5"><?= $t['report_sub'] ?></p>
                    </div>
                    <i class="fas fa-chevron-right text-gray-300 ml-auto"></i>
                </button>
                <button onclick="setAction('upvote')" class="option-card w-full bg-yazaBlue p-5 rounded-3xl text-left flex items-center gap-5 shadow-lg">
                    <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-thumbs-up text-2xl text-yazaGold"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base text-white"><?= $t['upvote_title'] ?></h3>
                        <p class="text-sm text-blue-200 mt-0.5"><?= $t['upvote_sub'] ?></p>
                    </div>
                    <i class="fas fa-chevron-right text-white/30 ml-auto"></i>
                </button>
            </div>
            <button onclick="goToStep(1)" class="w-full text-center py-3 text-gray-800 font-bold uppercase text-xs tracking-widest">
                <i class="fas fa-arrow-left mr-1"></i> <?= $t['go_back'] ?>
            </button>
        </div>

        <!-- ══ STEP 3 ══ -->
        <div id="step-3" class="step-layer space-y-5 pt-4">
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1"><?= $t['s3_label'] ?></p>
                <h2 class="text-2xl font-extrabold leading-tight"><?= $t['s3_heading'] ?></h2>
                <p class="text-gray-500 text-sm mt-1"><?= $t['s3_sub'] ?></p>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <button class="cat-card" onclick="selectCategory('Water',this)">
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center"><i class="fas fa-faucet text-2xl text-blue-500"></i></div>
                    <span class="font-bold text-sm"><?= $t['cat_water'] ?></span>
                </button>
                <button class="cat-card" onclick="selectCategory('Health',this)">
                    <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center"><i class="fas fa-heart-pulse text-2xl text-red-500"></i></div>
                    <span class="font-bold text-sm"><?= $t['cat_health'] ?></span>
                </button>
                <button class="cat-card" onclick="selectCategory('Roads',this)">
                    <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center"><i class="fas fa-road text-2xl text-orange-500"></i></div>
                    <span class="font-bold text-sm"><?= $t['cat_roads'] ?></span>
                </button>
                <button class="cat-card" onclick="selectCategory('Education',this)">
                    <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center"><i class="fas fa-graduation-cap text-2xl text-purple-500"></i></div>
                    <span class="font-bold text-sm"><?= $t['cat_education'] ?></span>
                </button>
                <button class="cat-card" onclick="selectCategory('Electricity',this)">
                    <div class="w-12 h-12 bg-yellow-50 rounded-xl flex items-center justify-center"><i class="fas fa-bolt text-2xl text-yellow-500"></i></div>
                    <span class="font-bold text-sm"><?= $t['cat_electricity'] ?></span>
                </button>
                <button class="cat-card" onclick="selectCategory('Other',this)">
                    <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center"><i class="fas fa-ellipsis text-2xl text-gray-500"></i></div>
                    <span class="font-bold text-sm"><?= $t['cat_other'] ?></span>
                </button>
            </div>
            <button onclick="goToStep(2)" class="w-full text-center py-3 text-gray-800 font-bold uppercase text-xs tracking-widest">
                <i class="fas fa-arrow-left mr-1"></i> <?= $t['go_back'] ?>
            </button>
        </div>

        <!-- ══ STEP 4a: Report Form ══ -->
        <div id="step-report-final" class="step-layer space-y-5 pt-4">
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1"><?= $t['s4_label'] ?></p>
                <h2 class="text-2xl font-extrabold leading-tight"><?= $t['s4_heading'] ?></h2>
                <p class="text-gray-500 text-sm mt-1"><?= $t['s4_sub'] ?></p>
            </div>
            <div class="flex items-center gap-2">
                <span class="bg-yazaBlue text-white text-xs font-black px-3 py-1 rounded-full uppercase tracking-wide" id="catBadge">—</span>
                <span class="text-xs text-gray-500 font-semibold" id="constBadge"></span>
            </div>
            <form id="reportForm" action="submit_issue.php" method="POST" class="space-y-4" onsubmit="return handleSubmit(event)">
                <input type="hidden" name="phone"        id="phoneField">
                <input type="hidden" name="constituency" id="constituencyField">
                <input type="hidden" name="category"     id="categoryField">
                <div>
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2"><?= $t['title_label'] ?></label>
                    <input type="text" name="title" id="titleInput" required placeholder="<?= $t['title_placeholder'] ?>" class="field text-base">
                </div>
                <div>
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2"><?= $t['desc_label'] ?></label>
                    <textarea name="description" id="descInput" rows="5" required placeholder="<?= $t['desc_placeholder'] ?>" class="field resize-none leading-relaxed text-base"></textarea>
                    <p class="text-right text-xs text-gray-400 mt-1 font-semibold"><span id="charCount">0</span> <?= $t['chars'] ?></p>
                </div>
                <button type="submit" id="submitBtn" class="w-full bg-yazaGold text-yazaBlue py-4 rounded-2xl font-black text-base shadow-xl hover:opacity-90 flex items-center justify-center gap-3 transition-opacity">
                    <span id="submitLabel"><?= $t['submit_btn'] ?></span>
                    <div class="spinner" id="submitSpinner"></div>
                    <i class="fas fa-paper-plane" id="submitIcon"></i>
                </button>
            </form>
            <button onclick="goToStep(3)" class="w-full text-center py-3 text-gray-800 font-bold uppercase text-xs tracking-widest">
                <i class="fas fa-arrow-left mr-1"></i> <?= $t['change_cat'] ?>
            </button>
        </div>

        <!-- ══ STEP 4b: Upvote ══ -->
        <div id="step-upvote-final" class="step-layer space-y-4 pt-4">
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1"><?= $t['s4_label'] ?></p>
                <h2 class="text-2xl font-extrabold leading-tight"><?= $t['s4b_heading'] ?></h2>
                <p class="text-sm text-gray-500 mt-1" id="upvoteSubtitle"><?= $t['s4b_sub'] ?></p>
            </div>
            <div id="issuesList" class="space-y-3">
                <div class="text-center py-12 text-gray-400">
                    <i class="fas fa-circle-notch fa-spin text-3xl mb-3 block"></i>
                    <p class="font-semibold text-sm"><?= $t['loading'] ?></p>
                </div>
            </div>
            <button onclick="goToStep(3)" class="w-full text-center py-3 text-gray-800 font-bold uppercase text-xs tracking-widest">
                <i class="fas fa-arrow-left mr-1"></i> <?= $t['change_cat'] ?>
            </button>
        </div>

        <!-- ══ SUCCESS ══ -->
        <div id="step-success" class="step-layer text-center space-y-6 pt-8 pb-6 px-2">
            <div class="success-ring w-28 h-28 bg-yazaGold rounded-full flex items-center justify-center mx-auto shadow-2xl">
                <i class="fas fa-check-circle text-yazaBlue text-5xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-extrabold text-yazaBlue leading-tight"><?= $t['success_heading'] ?></h2>
                <p class="text-gray-600 font-medium mt-2 text-sm leading-relaxed">
                    <?= $t['success_p1'] ?><br>
                    <strong class="text-yazaBlue"><?= $t['success_mp'] ?></strong> <?= $t['success_p2'] ?>
                </p>
            </div>
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-yazaGold/30 text-left space-y-3">
                <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest"><?= $t['your_submission'] ?></p>
                <p class="font-extrabold text-yazaBlue text-lg" id="successTitle"></p>
                <div class="flex flex-wrap gap-2">
                    <span class="bg-yazaBlue/5 text-yazaBlue text-xs font-bold px-3 py-1 rounded-full" id="successCat"></span>
                    <span class="bg-yazaGold/20 text-yazaBlue text-xs font-bold px-3 py-1 rounded-full" id="successCons"></span>
                </div>
                <div class="flex items-center gap-2 text-emerald-600">
                    <i class="fas fa-circle-check text-sm"></i>
                    <span class="text-xs font-bold"><?= $t['status_submitted'] ?></span>
                </div>
            </div>
            <div class="space-y-3 pt-2">
                <a href="dashboard.php" id="dashboardBtn" class="w-full bg-yazaBlue text-white py-4 rounded-2xl font-extrabold text-base shadow-xl flex items-center justify-center gap-2 hover:opacity-90 transition-opacity">
                    <i class="fas fa-chart-bar text-yazaGold"></i> <?= $t['view_dashboard'] ?>
                </a>
                <button onclick="resetAll()" class="w-full border-2 border-yazaBlue/20 text-yazaBlue py-4 rounded-2xl font-bold text-sm hover:bg-yazaBlue/5 transition-colors">
                    <?= $t['submit_another'] ?>
                </button>
            </div>
        </div>

    </main>

    <script>
        const T = <?= json_encode([
            'issues_in'  => $t['js_issues_in'],
            'sub_err'    => $t['js_sub_err'],
            'net_err'    => $t['js_net_err'],
            'submit_btn' => $t['submit_btn'],
            'submitting' => $t['submitting'],
            'loading'    => $t['loading'],
            'no_issues'  => $t['no_issues'],
            'be_first'   => $t['be_first'],
            'load_err'   => $t['load_err'],
            'chip_sub'   => $t['js_chip_sub'],
            'chip_inp'   => $t['js_chip_inp'],
            'chip_res'   => $t['js_chip_res'],
            'voted'      => $t['js_voted'],
            'voices'     => $t['js_voices'],
            'change_cat' => $t['change_cat'],
            'no_results' => $t['cons_no_results'],
        ], JSON_HEX_TAG | JSON_HEX_AMP) ?>;

        const constituencies = <?= json_encode($constituencyNames, JSON_HEX_TAG | JSON_HEX_AMP) ?>;

        let currentAction = '', selectedCat = '', citizenPhone = '', citizenConstituency = '';

        const progressMap = {'1':'25%','2':'50%','3':'75%','report':'100%','upvote':'100%','success':'100%'};

        function goToStep(step) {
            document.querySelectorAll('.step-layer').forEach(el => el.classList.remove('active'));
            const el = document.getElementById(`step-${step}`);
            if (el) el.classList.add('active');
            document.getElementById('progress-bar').style.width = progressMap[step] || '25%';
            window.scrollTo({ top:0, behavior:'smooth' });
        }

        function validateStep1() {
            let ok = true;
            const phone = document.getElementById('phoneInput').value.trim();
            const phoneErr = document.getElementById('phone-err');
            if (!/^0\d{8,14}$/.test(phone)) { phoneErr.classList.remove('hidden'); ok = false; }
            else phoneErr.classList.add('hidden');
            const cons = document.getElementById('constituencySelected').value.trim();
            const consErr = document.getElementById('cons-err');
            if (!cons) { consErr.classList.remove('hidden'); ok = false; }
            else consErr.classList.add('hidden');
            if (ok) { citizenPhone = phone; citizenConstituency = cons; goToStep(2); }
        }

        function setAction(action) { currentAction = action; goToStep(3); }

        function selectCategory(name, btn) {
            selectedCat = name;
            document.getElementById('phoneField').value        = citizenPhone;
            document.getElementById('constituencyField').value = citizenConstituency;
            document.getElementById('categoryField').value     = name;
            const catBadge = document.getElementById('catBadge');
            const constBadge = document.getElementById('constBadge');
            if (catBadge)   catBadge.innerText   = name;
            if (constBadge) constBadge.innerText = citizenConstituency;
            document.querySelectorAll('.step-layer').forEach(el => el.classList.remove('active'));
            if (currentAction === 'report') {
                document.getElementById('step-report-final').classList.add('active');
            } else {
                document.getElementById('step-upvote-final').classList.add('active');
                loadIssues(name);
            }
            document.getElementById('progress-bar').style.width = '100%';
            window.scrollTo({ top:0, behavior:'smooth' });
        }

        document.getElementById('descInput').addEventListener('input', function(){
            document.getElementById('charCount').textContent = this.value.length;
        });

        function handleSubmit(e) {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const spinner = document.getElementById('submitSpinner');
            const icon  = document.getElementById('submitIcon');
            const label = document.getElementById('submitLabel');
            btn.disabled = true;
            spinner.style.display = 'block';
            icon.style.display    = 'none';
            label.textContent     = T.submitting;
            fetch('submit_issue.php', { method:'POST', body:new FormData(document.getElementById('reportForm')) })
                .then(r => r.text())
                .then(resp => {
                    if (resp.includes('success') || resp.includes('✅')) {
                        document.getElementById('successTitle').textContent = document.getElementById('titleInput').value;
                        document.getElementById('successCat').textContent   = selectedCat;
                        document.getElementById('successCons').textContent  = citizenConstituency;
                        document.getElementById('dashboardBtn').href = `dashboard.php?constituency=${encodeURIComponent(citizenConstituency)}`;
                        goToStep('success');
                    } else {
                        alert('❌ ' + T.sub_err + '\n\n' + resp);
                        btn.disabled = false; spinner.style.display = 'none'; icon.style.display = ''; label.textContent = T.submit_btn;
                    }
                })
                .catch(() => {
                    alert(T.net_err);
                    btn.disabled = false; spinner.style.display = 'none'; icon.style.display = ''; label.textContent = T.submit_btn;
                });
            return false;
        }

        function loadIssues(category) {
            const container = document.getElementById('issuesList');
            document.getElementById('upvoteSubtitle').textContent = `${category} ${T.issues_in} ${citizenConstituency}`;
            container.innerHTML = `<div class="text-center py-10 text-gray-400"><i class="fas fa-circle-notch fa-spin text-3xl mb-3 block"></i><p class="font-semibold text-sm">${T.loading}</p></div>`;
            fetch(`get_issues.php?constituency=${encodeURIComponent(citizenConstituency)}&category=${encodeURIComponent(category)}&phone=${encodeURIComponent(citizenPhone)}`)
                .then(r => r.json())
                .then(issues => renderIssues(issues, container))
                .catch(() => { container.innerHTML = `<div class="text-center py-10 text-red-400 font-semibold text-sm"><i class="fas fa-exclamation-circle text-2xl mb-2 block"></i> ${T.load_err}</div>`; });
        }

        function renderIssues(issues, container) {
            if (!issues.length) {
                container.innerHTML = `<div class="text-center py-12 text-gray-400"><i class="fas fa-inbox text-4xl mb-3 block opacity-40"></i><p class="font-bold text-sm">${T.no_issues}</p><p class="text-xs mt-1">${T.be_first}</p></div>`;
                return;
            }
            const statusChip = s => {
                if (s === 'In Progress') return `<span class="chip-inprogress text-[10px] font-black px-2.5 py-0.5 rounded-full">${T.chip_inp}</span>`;
                if (s === 'Resolved')    return `<span class="chip-resolved text-[10px] font-black px-2.5 py-0.5 rounded-full">${T.chip_res}</span>`;
                return `<span class="chip-submitted text-[10px] font-black px-2.5 py-0.5 rounded-full">${T.chip_sub}</span>`;
            };
            container.innerHTML = issues.map(issue => `
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-start gap-4">
                    <div class="flex-1 min-w-0">
                        <h4 class="font-extrabold text-sm leading-snug text-yazaBlue">${escHtml(issue.title)}</h4>
                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">${escHtml(issue.description)}</p>
                        <div class="mt-2">${statusChip(issue.status)}</div>
                    </div>
                    <button onclick="handleVote(this,${issue.id})" class="vote-btn flex-shrink-0 flex flex-col items-center bg-yazaBlue text-white p-3 rounded-xl min-w-[60px] shadow-sm ${issue.already_voted?'voted':''}">
                        <i class="fas fa-caret-up text-yazaGold text-xl vote-icon ${issue.already_voted?'hidden':''}"></i>
                        <i class="fas fa-check text-white text-sm check-icon ${issue.already_voted?'':'hidden'}"></i>
                        <span class="text-xs font-black mt-0.5 vote-count">${issue.vote_count??0}</span>
                    </button>
                </div>`).join('');
        }

        function handleVote(btn, issueId) {
            if (btn.classList.contains('voted')) return;
            btn.classList.add('voted');
            const countEl = btn.querySelector('.vote-count');
            countEl.textContent = parseInt(countEl.textContent) + 1;
            fetch('upvote_issue.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`issue_id=${issueId}&phone=${encodeURIComponent(citizenPhone)}` })
                .then(r => r.json()).then(d => { if (d.vote_count !== undefined) countEl.textContent = d.vote_count; }).catch(()=>{});
        }

        function resetAll() {
            ['phoneInput','constituencyInput','titleInput','descInput'].forEach(id => document.getElementById(id).value = '');
            document.getElementById('constituencySelected').value = '';
            document.getElementById('charCount').textContent = '0';
            document.getElementById('submitBtn').disabled = false;
            document.getElementById('submitLabel').textContent = T.submit_btn;
            document.getElementById('submitSpinner').style.display = 'none';
            document.getElementById('submitIcon').style.display = '';
            currentAction = ''; selectedCat = ''; citizenPhone = ''; citizenConstituency = '';
            goToStep(1);
        }

        // ── Constituency dropdown ──
        const input = document.getElementById('constituencyInput');
        const list  = document.getElementById('constituencyList');
        const sel   = document.getElementById('constituencySelected');
        input.addEventListener('focus', () => renderDropdown(''));
        input.addEventListener('input', function() { sel.value = ''; renderDropdown(this.value.trim().toLowerCase()); });
        function renderDropdown(q) {
            const matches = q ? constituencies.filter(c => c.toLowerCase().includes(q)) : constituencies;
            list.innerHTML = '';
            if (!matches.length) {
                list.innerHTML = `<div class="list-item text-gray-400 text-sm">${T.no_results}</div>`;
            } else {
                matches.forEach(c => {
                    const div = document.createElement('div');
                    div.className = 'list-item'; div.textContent = c;
                    div.addEventListener('mousedown', () => { input.value = c; sel.value = c; list.classList.add('hidden'); });
                    list.appendChild(div);
                });
            }
            list.classList.remove('hidden');
        }
        document.addEventListener('click', e => { if (!input.contains(e.target) && !list.contains(e.target)) list.classList.add('hidden'); });
        function escHtml(str) { return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    </script>
</body>
</html>
