<?php
// ==========================================
// 1. ロジック (Controller)
// ==========================================

// セッション維持設定 (1週間)
$session_lifetime = 604800;
ini_set('session.gc_maxlifetime', $session_lifetime);
session_set_cookie_params($session_lifetime);

session_start();

// エラー設定 (画面に出さない)
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// データベース設定
$db_host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'gamesyndicate';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// ユーザー情報（アイコン等）の取得
$user_icon = '../img/default_user.png'; // デフォルト

if ($user_id > 0) {
    try {
        $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT user_icon FROM user WHERE id = ?");
        $stmt->execute([$user_id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($res && !empty($res['user_icon'])) {
            // パス調整: top.php から見た画像のパス
            if (file_exists(__DIR__ . '/../mypage/' . $res['user_icon'])) {
                $user_icon = '../mypage/' . $res['user_icon'];
            }
        }
    } catch (Exception $e) {
        // エラー時はデフォルトアイコンのまま続行
    }
}

// テンプレート読み込み
require_once './tpl/top.php';
?>