<?php
session_start();
// APIレスポンス設定
if (isset($_GET['api'])) { 
    ini_set('display_errors', 0); 
    header('Content-Type: application/json'); 
}

$db_host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'gamesyndicate';

if (!isset($_SESSION['user_id'])) {
    if (isset($_GET['api'])) exit(json_encode(['success'=>false, 'message'=>'ログインしてください']));
    header('Location: login.php'); exit;
}
$user_id = $_SESSION['user_id'];

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { 
    if (isset($_GET['api'])) exit(json_encode(['success'=>false, 'message'=>'DB Error']));
    die('DB Error'); 
}

// ==========================================
// [API] プロフィール保存処理
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api']) && $_GET['api'] === 'save_profile') {
    try {
        $pdo->beginTransaction();

        // 1. 基本情報更新
        $newId = str_replace('@', '', $_POST['account_id']);
        if (!preg_match('/^[a-zA-Z0-9]+$/', $newId)) throw new Exception('IDは半角英数字のみです');
        
        $chk = $pdo->prepare("SELECT id FROM user WHERE account_id=? AND id!=?");
        $chk->execute([$newId, $user_id]);
        if($chk->fetch()) throw new Exception('そのIDは使用されています');

        // 画像保存
        $iconPath = null;
        if (isset($_FILES['user_icon']) && $_FILES['user_icon']['error'] === UPLOAD_ERR_OK) {
            $dir = __DIR__.'/uploads/user_icons/';
            if(!file_exists($dir)) mkdir($dir, 0777, true);
            $ext = pathinfo($_FILES['user_icon']['name'], PATHINFO_EXTENSION);
            $fname = $user_id.'_'.time().'.'.$ext;
            if(move_uploaded_file($_FILES['user_icon']['tmp_name'], $dir.$fname)) $iconPath = 'uploads/user_icons/'.$fname;
        }

        $age = (!empty($_POST['birthday'])) ? (new DateTime())->diff(new DateTime($_POST['birthday']))->y : null;
        
        // 基本情報SQL
        $sqlBase = "UPDATE user SET 
            name=?, account_id=?, mailadress=?, birthday=?, age=?, 
            x_link=?, twitch_link=?, youtube_link=?, discord_id=?,
            show_mail=?, show_age=?";
        
        $paramsBase = [
            $_POST['name'], $newId, $_POST['mailadress'], $_POST['birthday']?:null, $age,
            $_POST['x_link'], $_POST['twitch_link'], $_POST['youtube_link'], $_POST['discord_id'],
            isset($_POST['show_mail'])?1:0, isset($_POST['show_age'])?1:0
        ];

        if ($iconPath) {
            $sqlBase .= ", user_icon=?";
            $paramsBase[] = $iconPath;
        }
        $sqlBase .= " WHERE id=?";
        $paramsBase[] = $user_id;

        $pdo->prepare($sqlBase)->execute($paramsBase);

        // 2. ゲーム情報更新 (現在選択されているタブのゲームのみ保存)
        $gTitle = $_POST['game_title'];
        
        if ($gTitle) {
            $sqlGame = "INSERT INTO user_game_profiles 
                        (user_id, game_title, ingame_name, current_rank, highest_rank, main_role, sub_role, main_character, playstyle, igl) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        ingame_name=?, current_rank=?, highest_rank=?, main_role=?, sub_role=?, main_character=?, playstyle=?, igl=?";
            
            // 配列で来るサブロールとキャラクターをカンマ区切り文字列に変換
            $subRole = isset($_POST['sub_role']) && is_array($_POST['sub_role']) ? implode(',', $_POST['sub_role']) : '';
            $char = isset($_POST['main_character']) && is_array($_POST['main_character']) ? implode(',', $_POST['main_character']) : '';
            
            $paramsGame = [
                $user_id, $gTitle, 
                $_POST['ingame_name']??'', $_POST['current_rank']??'', $_POST['highest_rank']??'', 
                $_POST['main_role']??'', $subRole, $char, 
                $_POST['playstyle']??'casual', $_POST['igl']??'no',
                // UPDATE用
                $_POST['ingame_name']??'', $_POST['current_rank']??'', $_POST['highest_rank']??'', 
                $_POST['main_role']??'', $subRole, $char, 
                $_POST['playstyle']??'casual', $_POST['igl']??'no'
            ];
            $pdo->prepare($sqlGame)->execute($paramsGame);
        }

        $pdo->commit();
        echo json_encode(['success'=>true, 'message'=>'保存しました']);

    } catch(Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
    }
    exit;
}

// 脱退処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api']) && $_GET['api'] === 'leave_team') {
    $tid = $_POST['team_id'];
    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM team_members WHERE user_id=? AND team_id=?")->execute([$user_id, $tid]);
        $pdo->prepare("UPDATE team SET team_count_member = GREATEST(team_count_member - 1, 0) WHERE id=?")->execute([$tid]);
        // team_idカラムがuserテーブルにある場合、メインチームならNULLにする
        $pdo->prepare("UPDATE user SET team_id = NULL WHERE id=? AND team_id=?")->execute([$user_id, $tid]);
        $pdo->commit();
        echo json_encode(['success'=>true]);
    } catch(Exception $e) {
        $pdo->rollBack(); echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
    }
    exit;
}

// データ取得 (初期表示)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['api']) && $_GET['api'] === 'get_profile') {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE id=?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if($user) unset($user['password']);
    
    $stmtG = $pdo->prepare("SELECT * FROM user_game_profiles WHERE user_id=?");
    $stmtG->execute([$user_id]);
    $games = [];
    foreach($stmtG->fetchAll(PDO::FETCH_ASSOC) as $g) {
        $games[$g['game_title']] = $g;
    }
    
    echo json_encode(['basic' => $user, 'games' => $games]);
    exit;
}

// HTML表示用データ取得
$stmtT = $pdo->prepare("SELECT t.id, t.team_name, t.game_title FROM team_members tm JOIN team t ON tm.team_id = t.id WHERE tm.user_id = ? AND tm.status = 'joined'");
$stmtT->execute([$user_id]);
$my_teams = $stmtT->fetchAll(PDO::FETCH_ASSOC);

$stmtU = $pdo->prepare("SELECT user_icon FROM user WHERE id = ?");
$stmtU->execute([$user_id]);
$uData = $stmtU->fetch();
$user_icon = (!empty($uData['user_icon']) && file_exists($uData['user_icon'])) ? $uData['user_icon'] : '../img/default_user.png';

require_once 'tpl/mypage_edit.php';
?>