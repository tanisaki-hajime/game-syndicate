<?php
// ==========================================
// 1. ロジック (Controller)
// ==========================================
session_start();
$db_host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'gamesyndicate';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$room_id = $_GET['room_id'] ?? 0;

// 未ログインや部屋指定なしならTopへ
if ($user_id == 0 || $room_id == 0) {
    header("Location: ../top/top.php");
    exit;
}

try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass);
    
    // --------------------------------------------------
    // [API処理]
    // --------------------------------------------------
    if (isset($_GET['api'])) {
        header('Content-Type: application/json');
        
        // A. メッセージ送信
        if ($_GET['api'] === 'send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $msg = $_POST['message'];
            if (!empty($msg)) {
                $stmt = $pdo->prepare("INSERT INTO fast_party_messages (room_id, user_id, message) VALUES (?, ?, ?)");
                $stmt->execute([$room_id, $user_id, $msg]);
            }
            echo json_encode(['success'=>true]);
            exit;
        }
        
        // B. メッセージ取得 (ポーリング用)
        if ($_GET['api'] === 'get') {
            $stmt = $pdo->prepare("SELECT m.*, u.name, u.user_icon FROM fast_party_messages m JOIN user u ON m.user_id = u.id WHERE m.room_id=? ORDER BY m.created_at ASC");
            $stmt->execute([$room_id]);
            echo json_encode(['messages'=>$stmt->fetchAll(PDO::FETCH_ASSOC), 'my_id'=>$user_id]);
            exit;
        }

        // C. ★退出処理 (新規追加)
        if ($_GET['api'] === 'exit') {
            // 1. メンバーから自分を削除
            $delParams = [$room_id, $user_id];
            $stmt = $pdo->prepare("DELETE FROM fast_party_members WHERE room_id=? AND user_id=?");
            $stmt->execute($delParams);

            // 2. 残りの人数をカウント
            $cStmt = $pdo->prepare("SELECT COUNT(*) FROM fast_party_members WHERE room_id=?");
            $cStmt->execute([$room_id]);
            $count = $cStmt->fetchColumn();

            if ($count == 0) {
                // 誰もいなくなったら部屋を削除 (CASCADE設定によりメッセージも自動削除)
                $dStmt = $pdo->prepare("DELETE FROM fast_party_rooms WHERE id=?");
                $dStmt->execute([$room_id]);
            } else {
                // まだ人がいるなら人数を更新し、ステータスを「募集中」に戻す（空きができたため）
                $uStmt = $pdo->prepare("UPDATE fast_party_rooms SET current_members=?, status='recruiting' WHERE id=?");
                $uStmt->execute([$count, $room_id]);
            }
            
            echo json_encode(['success'=>true]);
            exit;
        }
    }

    // --------------------------------------------------
    // [画面表示準備] 部屋・メンバー情報取得
    // --------------------------------------------------
    // 部屋情報
    $stmt = $pdo->prepare("SELECT * FROM fast_party_rooms WHERE id=?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();
    
    // 部屋が存在しない（解散済みなど）場合はトップへ戻す
    if (!$room) { 
        header("Location: ../top/top.php"); 
        exit; 
    }

    // メンバー一覧
    $mStmt = $pdo->prepare("SELECT u.name, u.user_icon FROM fast_party_members m JOIN user u ON m.user_id = u.id WHERE m.room_id=?");
    $mStmt->execute([$room_id]);
    $members = $mStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die('Database Error');
}

// テンプレート読み込み
require_once './tpl/party_room.php';
?>