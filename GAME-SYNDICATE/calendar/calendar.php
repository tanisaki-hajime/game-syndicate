<?php
session_start();
ini_set('display_errors', 0);
$db_host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'gamesyndicate';

// ログイン確認
if (!isset($_SESSION['user_id'])) { header('Location: ../mypage/login.php'); exit; }
$user_id = $_SESSION['user_id'];

try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass);
    
    // 1. チームIDの取得 (必須)
    // team_page.php から ?team_id=XX で渡されてくる
    $target_team_id = isset($_GET['team_id']) ? (int)$_GET['team_id'] : 0;

    // IDがない場合はエラー（勝手に別のチームを表示しない）
    if ($target_team_id === 0) {
        die("チームが指定されていません。<a href='../team/team_search.php'>チーム検索へ</a>");
    }

    // 2. 権限チェック (メンバーかどうか)
    // 指定された team_id のメンバーリストに、自分の user_id が含まれているか確認
    $stmtCheck = $pdo->prepare("
        SELECT t.*, tm.role as member_role 
        FROM team_members tm 
        JOIN team t ON tm.team_id = t.id 
        WHERE tm.user_id = ? AND tm.team_id = ? AND tm.status = 'joined'
    ");
    $stmtCheck->execute([$user_id, $target_team_id]);
    $team = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    // 所属していない場合
    if (!$team) {
        die("このチームのスケジュールを閲覧・編集する権限がありません。<br><a href='../mypage/mypage.php'>マイページへ戻る</a>");
    }

    // 3. チームメンバー一覧取得 (プルダウン用)
    $memStmt = $pdo->prepare("SELECT u.id, u.name FROM team_members tm JOIN user u ON tm.user_id = u.id WHERE tm.team_id = ? AND tm.status = 'joined'");
    $memStmt->execute([$target_team_id]);
    $members = $memStmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. スケジュールデータ取得
    $month = isset($_GET['m']) ? $_GET['m'] : date('Y-m');
    $schStmt = $pdo->prepare("SELECT * FROM team_schedules WHERE team_id = ? AND DATE_FORMAT(schedule_date, '%Y-%m') = ?");
    $schStmt->execute([$target_team_id, $month]);
    $schedules = $schStmt->fetchAll(PDO::FETCH_ASSOC);

    // データ整形 (JSON用)
    $schJson = [];
    foreach($schedules as $s) {
        $schJson[$s['schedule_date']][$s['user_id']] = [
            'status' => $s['status'] == 'ok' ? '◯' : ($s['status'] == 'tentative' ? '△' : '×'),
            'comment' => $s['comment']
        ];
    }

    // 表示用変数
    $team_name = $team['team_name'];
    $user_role = $team['member_role'];
    $required_members = $team['notification_threshold'] ?? 5;
    
    // 時間の秒をカット
    $start_time = substr($team['activity_start_time'], 0, 5);
    $end_time = substr($team['activity_end_time'], 0, 5);
    
    $members_map = array_column($members, 'name', 'id');

} catch (Exception $e) { die("System Error: " . $e->getMessage()); }

require_once 'tpl/calendar.php';
?>