<?php
session_start();
$db_host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'gamesyndicate';

$is_logged_in = isset($_SESSION['user_id']);
$viewer_id = $is_logged_in ? $_SESSION['user_id'] : 0;
$team_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$default_icon_url = 'https://placehold.co/400x400/1a1f3a/ff0078?text=USER';
$user_icon = $default_icon_url;

try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

    $stmt = $pdo->prepare("SELECT * FROM team WHERE id = ?");
    $stmt->execute([$team_id]);
    $team = $stmt->fetch();
    if (!$team) die("チームが見つかりません。<a href='team_search.php'>検索へ戻る</a>");

    if ($viewer_id > 0) {
        $uStmt = $pdo->prepare("SELECT user_icon FROM user WHERE id = ?");
        $uStmt->execute([$viewer_id]);
        $uData = $uStmt->fetch();
        if(!empty($uData['user_icon'])) {
            $user_icon = '../mypage/'.$uData['user_icon'];
        }
    }

    $is_owner = false;
    if ($is_logged_in) {
        $stmtChk = $pdo->prepare("SELECT role FROM team_members WHERE team_id = ? AND user_id = ? AND status = 'joined'");
        $stmtChk->execute([$team_id, $viewer_id]);
        $rd = $stmtChk->fetch();
        if ($rd && $rd['role'] === 'owner') $is_owner = true;
    }

    $sqlMem = "SELECT u.*, tm.role as team_role, 
                      ug.ingame_name, ug.current_rank, ug.highest_rank,
                      ug.main_role, ug.sub_role, ug.main_character,
                      ug.playstyle, ug.igl
               FROM team_members tm 
               JOIN user u ON tm.user_id = u.id 
               LEFT JOIN user_game_profiles ug ON u.id = ug.user_id AND ug.game_title = ?
               WHERE tm.team_id = ? AND tm.status = 'joined' 
               ORDER BY FIELD(tm.role, 'owner', 'manager', 'coach', 'analyst', 'main', 'sub', 'member'), u.id";
               
    $stmtMem = $pdo->prepare($sqlMem);
    $stmtMem->execute([$team['game_title'], $team_id]); 
    $all_members = $stmtMem->fetchAll();

} catch (PDOException $e) { die("DB Error: " . $e->getMessage()); }

// ★修正: 昔の「../uploads/」パスが入っていても、正しく表示されるように補正する
function getImg($path, $default) {
    if (empty($path)) return $default;
    $path = str_replace('../uploads/', 'uploads/', $path);
    return file_exists($path) ? htmlspecialchars($path) : $default;
}

$bg_image = getImg($team['header_image'], 'https://placehold.co/1200x400/000000/333333?text=Team+Header'); 
$icon_image = getImg($team['team_icon'], 'https://placehold.co/200x200/1a1f3a/ff0078?text=TEAM');

$groups = ['owner'=>[], 'manager'=>[], 'coach'=>[], 'analyst'=>[], 'main'=>[], 'sub'=>[]];
foreach ($all_members as $m) {
    $r = $m['team_role'];
    if(isset($groups[$r])) $groups[$r][] = $m;
    else $groups['main'][] = $m; 
}

require_once 'tpl/team_page.php';
?>