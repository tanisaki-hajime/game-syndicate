<?php
session_start();

// セッション変数を全て解除
$_SESSION = [];

// セッションクッキーの削除
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// セッションの破壊
session_destroy();

// 【ここを修正しました】
// 以前の絶対パス(/自主制作/VALORANT/...)ではなく、
// 現在の場所から見た「相対パス」で正しいログイン画面へ飛ばします。
header('Location: ../mypage/login.php');
exit;
?>