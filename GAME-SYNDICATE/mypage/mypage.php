<?php
session_start();
$db_host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'gamesyndicate';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_id = $_SESSION['user_id'];

// デフォルト画像
$default_icon_url = 'https://placehold.co/400x400/1a1f3a/ff0078?text=USER';

try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass);
    
    // 1. 基本情報
    $stmt = $pdo->prepare("SELECT * FROM user WHERE id=?");
    $stmt->execute([$user_id]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    // アイコンパス解決
    if (empty($userData['user_icon']) || !file_exists($userData['user_icon'])) {
        $userData['user_icon'] = $default_icon_url;
    }

    // 2. ゲーム別プロフィール取得 (全てのカラムを取得)
    $stmtG = $pdo->prepare("SELECT * FROM user_game_profiles WHERE user_id=?");
    $stmtG->execute([$user_id]);
    $games = $stmtG->fetchAll(PDO::FETCH_ASSOC);
    
    // JSで使いやすい形に変換 { "valorant": {...}, "apex": {...} }
    $gameData = [];
    foreach($games as $g) { $gameData[$g['game_title']] = $g; }

    // 3. 所属チーム
    $stmtT = $pdo->prepare("SELECT t.*, tm.role as member_role FROM team_members tm JOIN team t ON tm.team_id=t.id WHERE tm.user_id=? AND tm.status='joined'");
    $stmtT->execute([$user_id]);
    $teams = $stmtT->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) { die('DB Error'); }

// データをJSON化してViewに渡す
$json_userData = json_encode($userData);
$json_gameData = json_encode($gameData);

require_once 'tpl/mypage.php';
?>