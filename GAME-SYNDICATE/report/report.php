<?php
session_start();
ini_set('display_errors', 0);
$db_host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'gamesyndicate';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // API: ユーザー検索 (★新規追加)
    if (isset($_GET['api']) && $_GET['api'] === 'search_user') {
        $q = $_GET['q'] ?? '';
        if (mb_strlen($q) < 2) exit(json_encode([])); // 2文字以上で検索

        $stmt = $pdo->prepare("SELECT id, name, user_icon, account_id FROM user WHERE name LIKE ? OR account_id LIKE ? LIMIT 10");
        $stmt->execute(["%$q%", "%$q%"]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    // チームメンバー取得 (既存機能)
    $team_id = $_GET['team_id'] ?? 0;
    $members = [];
    if ($team_id > 0) {
        $stmt = $pdo->prepare("SELECT u.id, u.name, u.user_icon, u.account_id FROM team_members tm JOIN user u ON tm.user_id = u.id WHERE tm.team_id = ?");
        $stmt->execute([$team_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // API: 通報実行 (変更なし)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['api']) && $_POST['api'] === 'submit_report') {
        if ($user_id == 0) { echo json_encode(['success'=>false, 'message'=>'ログインしてください']); exit; }
        
        $target_uid = $_POST['target_user_id'];
        $type = $_POST['type'];
        $details = $_POST['details'];

        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO reports (target_user_id, reporter_user_id, type, details) VALUES (?, ?, ?, ?)");
        $stmt->execute([$target_uid, $user_id, $type, $details]);
        $upd = $pdo->prepare("UPDATE user SET report_count = report_count + 1 WHERE id = ?");
        $upd->execute([$target_uid]);
        $pdo->commit();
        echo json_encode(['success'=>true]);
        exit;
    }

} catch (Exception $e) {
    if(isset($_POST['api']) || isset($_GET['api'])) { echo json_encode(['error'=>$e->getMessage()]); exit; }
}

require_once './tpl/report.php';
?>