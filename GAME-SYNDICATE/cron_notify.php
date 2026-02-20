<?php
// cron_notify.php
$db_host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'gamesyndicate';
try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass);
    $now = date('H:i:00'); $today = date('Y-m-d');

    $stmt = $pdo->prepare("SELECT * FROM team WHERE notification_time = ? AND discord_webhook IS NOT NULL");
    $stmt->execute([$now]);
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($teams as $team) {
        $cntStmt = $pdo->prepare("SELECT count(*) FROM team_schedules WHERE team_id = ? AND schedule_date = ? AND status = 'ok'");
        $cntStmt->execute([$team['id'], $today]);
        $okCount = $cntStmt->fetchColumn();
        
        $req = $team['notification_threshold'];
        if ($okCount >= $req) $msg = "✅ **【本日活動あり】**\n現在: {$okCount}名\n時間: " . substr($team['activity_start_time'],0,5) . "〜 @everyone";
        else $msg = "⚠️ **【人数不足】**\n現在: {$okCount}名 (必要:{$req})";
        
        sendDiscord($team['discord_webhook'], $msg);
    }
} catch (Exception $e) {}

function sendDiscord($url, $content) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["content" => $content]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_exec($ch); curl_close($ch);
}
?>