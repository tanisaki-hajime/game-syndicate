<?php
ob_start();
session_start();

if (!isset($_SESSION['user_id'])) {
    if (isset($_GET['api']) || isset($_POST['action'])) {
        ob_clean();
        echo json_encode(['success'=>false, 'message'=>'ログインしてください']);
        exit;
    }
    header('Location: ../mypage/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$db_host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'gamesyndicate';

try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) { 
    if (isset($_GET['api']) || isset($_POST['action'])) { ob_clean(); echo json_encode(['success'=>false, 'message'=>'DB Error']); exit; }
    die('DB Connection Error'); 
}

if (isset($_GET['api']) && $_GET['api'] === 'search_user') {
    ob_clean(); header('Content-Type: application/json');
    $query = $_GET['q'] ?? '';
    if (strlen($query) < 2) { echo json_encode([]); exit; }
    if (strpos($query, '@') !== 0) $query = '@' . $query;
    $stmt = $pdo->prepare("SELECT id, name, account_id, user_icon FROM user WHERE account_id LIKE ? AND id != ? LIMIT 5");
    $stmt->execute(["%$query%", $user_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)); exit;
}

if (isset($_GET['api']) && $_GET['api'] === 'get_recommended_users') {
    ob_clean(); header('Content-Type: application/json');
    $stmt = $pdo->prepare("SELECT id, name, account_id, user_icon FROM user WHERE id != ? ORDER BY RAND() LIMIT 6");
    $stmt->execute([$user_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)); exit;
}

// [POST] チーム作成
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    header('Content-Type: application/json; charset=utf-8');

    $mode = $_POST['mode'] ?? 'recruiting';
    $team_name = $_POST['team_name'] ?? '';
    $game_title = $_POST['game_title'] ?? 'valorant';
    $team_division = $_POST['team_division'] ?? 'unrated';
    $description = $_POST['description'] ?? '';
    $invite_msg = $_POST['invite_message'] ?? ''; 
    $activity_time_str = is_array($_POST['activity_times'] ?? []) ? implode(', ', array_filter($_POST['activity_times'])) : '';
    $invite_members = json_decode($_POST['members_data'] ?? '[]', true);

    if (empty($team_name)) { ob_clean(); echo json_encode(['success'=>false, 'message'=>'チーム名は必須です']); exit; }

    try {
        $stmtCheck = $pdo->prepare("SELECT count(*) FROM team_members tm JOIN team t ON tm.team_id=t.id WHERE tm.user_id=? AND t.game_title=? AND tm.status IN ('joined', 'owner')");
        $stmtCheck->execute([$user_id, $game_title]);
        if ($stmtCheck->fetchColumn() > 0) { ob_clean(); echo json_encode(['success'=>false, 'message'=>"「{$game_title}」では既に他のチームに所属しています。"]); exit; }

        $stmtName = $pdo->prepare("SELECT count(*) FROM team WHERE team_name=?");
        $stmtName->execute([$team_name]);
        if ($stmtName->fetchColumn() > 0) { ob_clean(); echo json_encode(['success'=>false, 'message'=>'そのチーム名は既に使用されています']); exit; }

        // ★修正: 保存先を「team」フォルダ内の「uploads」に統一
        $upload_base = __DIR__ . '/uploads';
        $upload_dir_icon = $upload_base . '/team_icons/';
        $upload_dir_head = $upload_base . '/team_headers/';
        
        if (!file_exists($upload_base)) { @mkdir($upload_base, 0777, true); }
        if (!file_exists($upload_dir_icon)) { @mkdir($upload_dir_icon, 0777, true); }
        if (!file_exists($upload_dir_head)) { @mkdir($upload_dir_head, 0777, true); }

        $icon_path = null; $header_path = null;

        if (isset($_FILES['team_icon']) && $_FILES['team_icon']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['team_icon']['name'], PATHINFO_EXTENSION);
            $fn = 'icon_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['team_icon']['tmp_name'], $upload_dir_icon . $fn)) {
                $icon_path = "uploads/team_icons/" . $fn; // DBには相対パスを保存
            } else {
                throw new Exception("アイコン画像の保存に失敗しました。");
            }
        }
        if (isset($_FILES['header_image']) && $_FILES['header_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['header_image']['name'], PATHINFO_EXTENSION);
            $fn = 'head_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['header_image']['tmp_name'], $upload_dir_head . $fn)) {
                $header_path = "uploads/team_headers/" . $fn;
            } else {
                throw new Exception("ヘッダー画像の保存に失敗しました。");
            }
        }

        $pdo->beginTransaction();

        $sql = "INSERT INTO team (team_name, game_title, team_status, team_icon, header_image, team_division, description, activity_time, team_count_member, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";
        $pdo->prepare($sql)->execute([$team_name, $game_title, $mode, $icon_path, $header_path, $team_division, $description, $activity_time_str]);
        $new_team_id = $pdo->lastInsertId();

        $pdo->prepare("INSERT INTO team_members (team_id, user_id, role, status) VALUES (?, ?, 'owner', 'joined')")->execute([$new_team_id, $user_id]);
        $pdo->prepare("UPDATE user SET team_id=? WHERE id=? AND team_id IS NULL")->execute([$new_team_id, $user_id]);

        $invited_count = 0;
        if (!empty($invite_members)) {
            $stmtAdd = $pdo->prepare("INSERT IGNORE INTO team_members (team_id, user_id, role, status) VALUES (?, ?, ?, 'pending')");
            $stmtNoti = $pdo->prepare("INSERT INTO notifications (target_user_id, sender_user_id, sender_team_id, type, title, message, link_id) VALUES (?, ?, ?, 'team_invite', ?, ?, ?)");
            foreach ($invite_members as $mem) {
                if ($mem['id'] != $user_id) {
                    $stmtAdd->execute([$new_team_id, $mem['id'], $mem['role']]);
                    $msg_body = "{$team_name} ({$game_title}) から招待が届いています。\n役職: {$mem['role']}" . (!empty($invite_msg) ? "\n\n【メッセージ】\n{$invite_msg}" : "");
                    $stmtNoti->execute([$mem['id'], $user_id, $new_team_id, "チーム招待: {$team_name}", $msg_body, $new_team_id]);
                    $invited_count++;
                }
            }
        }

        $pdo->commit();
        ob_clean(); // ★バッファをクリアして純粋なJSONだけを返す
        echo json_encode(['success'=>true, 'message'=>"チームを作成しました！", 'team_id'=>$new_team_id]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        ob_clean();
        echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
    }
    exit;
}

ob_end_flush();
$mode = $_GET['mode'] ?? '';
if ($mode === 'existing' || $mode === 'recruiting') { require_once 'tpl/team_form.php'; } 
else { require_once 'tpl/team_select.php'; }
?>