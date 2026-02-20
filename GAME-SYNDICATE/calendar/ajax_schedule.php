<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');

$db_host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'gamesyndicate';
if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false, 'error'=>'Login required']); exit; }

try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass);
    
    $team_id = $_POST['team_id'];
    $target_uid = $_POST['user_id'];

    $stmt = $pdo->prepare("SELECT t.*, tm.role FROM team_members tm JOIN team t ON tm.team_id = t.id WHERE tm.user_id = ? AND tm.team_id = ? AND tm.status = 'joined'");
    $stmt->execute([$_SESSION['user_id'], $team_id]);
    $team = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$team) throw new Exception("Team permissions denied");
    if ($target_uid != $_SESSION['user_id'] && $team['role'] !== 'owner') throw new Exception("権限がありません");

    // 一括保存
    if (isset($_POST['dates'])) {
        $dates = json_decode($_POST['dates']);
        $status = $_POST['status'];
        $sql = "INSERT INTO team_schedules (team_id, user_id, schedule_date, status, comment) VALUES (?, ?, ?, ?, '') ON DUPLICATE KEY UPDATE status = ?";
        $stmt = $pdo->prepare($sql);
        foreach ($dates as $date) $stmt->execute([$team_id, $target_uid, $date, $status, $status]);
    } 
    // 単体保存
    else {
        $date = $_POST['date'];
        $status = $_POST['status'];
        $comment = $_POST['comment'];
        $sql = "INSERT INTO team_schedules (team_id, user_id, schedule_date, status, comment) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = ?, comment = ?";
        $pdo->prepare($sql)->execute([$team_id, $target_uid, $date, $status, $comment, $status, $comment]);
        
        // Discord通知 (単体保存時のみチェック)
        if ($team['discord_webhook'] && $status == 'ok') {
            $cntStmt = $pdo->prepare("SELECT count(*) FROM team_schedules WHERE team_id = ? AND schedule_date = ? AND status = 'ok'");
            $cntStmt->execute([$team_id, $date]);
            $okCount = $cntStmt->fetchColumn();
            
            if ($okCount == $team['notification_threshold']) {
                $msg = "🎉 **メンバーが集まりました！**\n";
                $msg .= "日付: {$date}\n時間: " . substr($team['activity_start_time'], 0, 5) . " - " . substr($team['activity_end_time'], 0, 5) . "\n現在の参加者: {$okCount}名";
                sendDiscord($team['discord_webhook'], $msg);
            }
        }
    }

    echo json_encode(['success'=>true]);

} catch (Exception $e) { echo json_encode(['success'=>false, 'error'=>$e->getMessage()]); }

function sendDiscord($url, $content) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["content" => $content]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_exec($ch); curl_close($ch);
}
?>