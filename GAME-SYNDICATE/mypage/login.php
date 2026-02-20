<?php
// ==========================================
// ★追加：セッション有効期限を1週間に設定
// ==========================================
$session_lifetime = 604800; // 1週間 (60 * 60 * 24 * 7)
ini_set('session.gc_maxlifetime', $session_lifetime);
session_set_cookie_params($session_lifetime);

session_start();
if (isset($_GET['api'])) { ini_set('display_errors', 0); header('Content-Type: application/json'); }

$db_host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'gamesyndicate';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
} catch (PDOException $e) { 
    if(isset($_GET['api'])) exit(json_encode(['success'=>false,'message'=>'DB Error']));
}

// ログイン
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api']) && $_GET['api'] === 'login') {
    $aid = str_replace('@', '', $_POST['account_id'] ?? '');
    
    $stmt = $pdo->prepare('SELECT * FROM user WHERE account_id = ?');
    $stmt->execute([$aid]);
    $user = $stmt->fetch();

    if ($user && password_verify($_POST['password'], $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        echo json_encode(['success' => true, 'redirect' => 'mypage.php']);
    } else {
        echo json_encode(['success' => false, 'message' => 'IDまたはパスワードが違います']);
    }
    exit;
}

// 新規登録
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api']) && $_GET['api'] === 'register') {
    // @を除去
    $aid = str_replace('@', '', $_POST['account_id'] ?? '');
    
    // 半角英数字チェック
    if (!preg_match('/^[a-zA-Z0-9]+$/', $aid)) {
        echo json_encode(['success' => false, 'message' => 'IDは半角英数字のみで入力してください(@不要)']);
        exit;
    }

    // 重複チェック
    $stmt = $pdo->prepare("SELECT id FROM user WHERE account_id = ? OR mailadress = ?");
    $stmt->execute([$aid, $_POST['mailadress']]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'そのIDまたはメールアドレスは既に使用されています']);
        exit;
    }

    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO user (name, account_id, mailadress, password) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['name'], $aid, $_POST['mailadress'], $hash]);
    
    $_SESSION['user_id'] = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'redirect' => 'mypage.php']);
    exit;
}

require_once 'tpl/login.php';
?>