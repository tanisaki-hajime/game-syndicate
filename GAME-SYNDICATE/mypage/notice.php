<?php
session_start();
ini_set('display_errors', 0);

$db_host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'gamesyndicate';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_id = $_SESSION['user_id'];

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    
    // 画像パス解決
    function getImgPath($path) {
        if (empty($path)) return 'https://placehold.co/100x100/1a1f3a/ff0078?text=User';
        if (file_exists($path)) return $path;
        if (file_exists('../' . $path)) return '../' . $path;
        return 'https://placehold.co/100x100/1a1f3a/ff0078?text=User';
    }

    // ヘッダー用アイコン
    $stmtU = $pdo->prepare("SELECT user_icon FROM user WHERE id = ?");
    $stmtU->execute([$user_id]);
    $myIcon = $stmtU->fetchColumn();
    $my_icon_url = getImgPath($myIcon);

    // ★重要: 自分宛て(target) または 自分が送った(sender) 通知を取得
    $sql = "SELECT n.*, 
            CASE 
                WHEN n.type LIKE 'join%' THEN (SELECT status FROM team_members WHERE id=n.link_id)
                WHEN n.type LIKE 'scrim%' THEN (SELECT status FROM scrim WHERE id=n.link_id)
                ELSE NULL 
            END as real_status,
            s.name as sender_name, s.user_icon as sender_icon,
            t.name as target_name, t.user_icon as target_icon
            FROM notifications n
            LEFT JOIN user s ON n.sender_user_id = s.id
            LEFT JOIN user t ON n.target_user_id = t.id
            WHERE n.target_user_id = ? OR n.sender_user_id = ? 
            ORDER BY n.created_at DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $user_id]);
    $notices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 表示データの整形 (相手の情報を表示するように調整)
    foreach ($notices as &$n) {
        if ($n['sender_user_id'] == $user_id) {
            // 自分が送信者の場合 -> 相手(Target)の情報を表示
            $n['display_name'] = $n['target_name'];
            $n['display_icon'] = getImgPath($n['target_icon']);
            $n['direction'] = 'To'; // 自分から
        } else {
            // 自分が受信者の場合 -> 相手(Sender)の情報を表示
            $n['display_name'] = $n['sender_name'];
            $n['display_icon'] = getImgPath($n['sender_icon']);
            $n['direction'] = 'From'; // 相手から
        }
    }

} catch (Exception $e) { die("Error: " . $e->getMessage()); }

require_once 'tpl/notice.php';
?>