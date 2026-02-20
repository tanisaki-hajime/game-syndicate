<?php
session_start();
ini_set('display_errors', 0);

$db_host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'gamesyndicate';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_id = $_SESSION['user_id'];
$notice_id = $_GET['id'] ?? 0;

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);

    function getImg($path) {
        if (empty($path)) return 'https://placehold.co/100x100/1a1f3a/ff0078?text=User';
        if (file_exists($path)) return $path;
        if (file_exists('../' . $path)) return '../' . $path;
        return 'https://placehold.co/100x100/1a1f3a/ff0078?text=User';
    }

    // ヘッダー用アイコン
    $stmtU = $pdo->prepare("SELECT user_icon FROM user WHERE id = ?");
    $stmtU->execute([$user_id]);
    $myIcon = $stmtU->fetchColumn();
    $my_icon_url = getImg($myIcon);

    // 1. 通知詳細取得
    $stmt = $pdo->prepare("SELECT n.*, u.name as sender_name, u.user_icon as sender_icon FROM notifications n LEFT JOIN user u ON n.sender_user_id = u.id WHERE n.id = ? AND (n.target_user_id = ? OR n.sender_user_id = ?)");
    $stmt->execute([$notice_id, $user_id, $user_id]);
    $notice = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$notice) die("通知が見つかりません");

    // 既読化 (受信者の場合のみ)
    if ($notice['target_user_id'] == $user_id && $notice['is_read'] == 0) {
        $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?")->execute([$notice_id]);
    }

    // ステータス取得
    $current_status = '-';
    if (strpos($notice['type'], 'join') !== false) {
        $st = $pdo->prepare("SELECT status FROM team_members WHERE id = ?");
        $st->execute([$notice['link_id']]);
        $current_status = $st->fetchColumn();
    } elseif (strpos($notice['type'], 'scrim') !== false) {
        $st = $pdo->prepare("SELECT status FROM scrim WHERE id = ?");
        $st->execute([$notice['link_id']]);
        $current_status = $st->fetchColumn();
    }

    // 4. POST処理 (チャット・承認)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $linkId = $notice['link_id'];

        $pdo->beginTransaction();

        if ($action === 'send_message' && !empty($_POST['message'])) {
            $msg = $_POST['message'];
            // メッセージ保存
            $pdo->prepare("INSERT INTO notification_messages (notification_id, user_id, message) VALUES (?, ?, ?)")->execute([$notice_id, $user_id, $msg]);
            
            // 相手側を「未読」に戻して通知に気づかせる
            $pdo->prepare("UPDATE notifications SET is_read = 0 WHERE id = ?")->execute([$notice_id]);
        }
        elseif ($action === 'approve_join') {
            $pdo->prepare("UPDATE team_members SET status='approved' WHERE id=?")->execute([$linkId]);
            // 申請者に通知 (同じスレッドにシステムメッセージとして追加も可能だが、ここではステータス変更のみ)
            $pdo->prepare("INSERT INTO notification_messages (notification_id, user_id, message) VALUES (?, ?, ?)")->execute([$notice_id, $user_id, "【システム】加入申請を承認しました。"]);
        }
        elseif ($action === 'confirm_join') {
            $pdo->prepare("UPDATE team_members SET status='joined', joined_at=NOW() WHERE id=?")->execute([$linkId]);
            $tm = $pdo->query("SELECT user_id, team_id FROM team_members WHERE id=$linkId")->fetch();
            $pdo->prepare("UPDATE user SET team_id=? WHERE id=? AND team_id IS NULL")->execute([$tm['team_id'], $user_id]);
            $pdo->prepare("INSERT INTO notification_messages (notification_id, user_id, message) VALUES (?, ?, ?)")->execute([$notice_id, $user_id, "【システム】チームに参加しました！"]);
        }
        elseif ($action === 'accept_scrim') {
            $pdo->prepare("UPDATE scrim SET status='accepted' WHERE id=?")->execute([$linkId]);
            $pdo->prepare("INSERT INTO notification_messages (notification_id, user_id, message) VALUES (?, ?, ?)")->execute([$notice_id, $user_id, "【システム】スクリムを承諾しました。"]);
        }
        elseif ($action === 'reject') {
            if(strpos($notice['type'], 'join')!==false) $pdo->prepare("UPDATE team_members SET status='rejected' WHERE id=?")->execute([$linkId]);
            if(strpos($notice['type'], 'scrim')!==false) $pdo->prepare("UPDATE scrim SET status='rejected' WHERE id=?")->execute([$linkId]);
            $pdo->prepare("INSERT INTO notification_messages (notification_id, user_id, message) VALUES (?, ?, ?)")->execute([$notice_id, $user_id, "【システム】申請を拒否しました。"]);
        }

        $pdo->commit();
        header("Location: notice_detail.php?id=" . $notice_id);
        exit;
    }

    // チャット履歴
    $msgStmt = $pdo->prepare("SELECT m.*, u.name, u.user_icon FROM notification_messages m JOIN user u ON m.user_id = u.id WHERE m.notification_id = ? ORDER BY m.created_at ASC");
    $msgStmt->execute([$notice_id]);
    $messages = $msgStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) { die($e->getMessage()); }

require_once 'tpl/notice_detail.php';
?>