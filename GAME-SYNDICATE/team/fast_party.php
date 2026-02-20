<?php
// ==========================================
// 1. ロジック (Controller)
// ==========================================
session_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$db_host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'gamesyndicate';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // API処理
    if (isset($_REQUEST['api'])) {
        header('Content-Type: application/json; charset=utf-8');

        // ★マッチング開始 (Auto Match)
        if ($_REQUEST['api'] === 'start_match' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($user_id == 0) exit(json_encode(['success'=>false, 'message'=>'ログインしてください']));
            
            $game = $_POST['game'];
            $ranks = isset($_POST['ranks']) ? $_POST['ranks'] : []; // 配列
            $desc = $_POST['description'] ?? '';

            // ランク配列をカンマ区切り文字列に (例: "gold,platinum")
            $rankStr = empty($ranks) ? 'any' : implode(',', $ranks);

            // ゲームごとの定員
            $maxMembers = ($game === 'apex') ? 3 : 5;

            $pdo->beginTransaction();

            // 1. まず条件に合う部屋を探す (自分が参加者になるパターン)
            // 条件: 同じゲーム、募集中、定員割れ、ランク条件が合致(またはany)
            $sql = "SELECT id, target_ranks FROM fast_party_rooms 
                    WHERE game_title = ? 
                    AND status = 'recruiting' 
                    AND current_members < ? 
                    ORDER BY created_at DESC FOR UPDATE"; // ロックして取得
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$game, $maxMembers]);
            $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $matchedRoomId = null;

            foreach ($rooms as $room) {
                // ランク条件のマッチングロジック
                // 部屋の募集ランク($room['target_ranks']) と 自分の希望ランク($rankStr) の共通部分があればOK
                // ※ 'any' が含まれていれば無条件OKとする簡易判定
                $roomRanks = explode(',', $room['target_ranks']);
                
                // 共通項があるか、どちらかがanyならマッチ成立
                $isMatch = false;
                if (in_array('any', $roomRanks) || $rankStr === 'any') {
                    $isMatch = true;
                } else {
                    // 交差チェック
                    $myRanks = explode(',', $rankStr);
                    if (count(array_intersect($roomRanks, $myRanks)) > 0) {
                        $isMatch = true;
                    }
                }

                if ($isMatch) {
                    $matchedRoomId = $room['id'];
                    break; // 最初に見つけた部屋に入る
                }
            }

            if ($matchedRoomId) {
                // --- A. 既存の部屋に参加 ---
                // 重複参加チェック
                $chk = $pdo->prepare("SELECT id FROM fast_party_members WHERE room_id=? AND user_id=?");
                $chk->execute([$matchedRoomId, $user_id]);
                if (!$chk->fetch()) {
                    $pdo->prepare("INSERT INTO fast_party_members (room_id, user_id) VALUES (?, ?)")
                        ->execute([$matchedRoomId, $user_id]);
                    
                    // 人数更新 & 満員ならstatus変更
                    $pdo->prepare("UPDATE fast_party_rooms SET current_members = current_members + 1 WHERE id=?")
                        ->execute([$matchedRoomId]);
                    
                    // 満員チェック
                    $pdo->prepare("UPDATE fast_party_rooms SET status = 'full' WHERE id=? AND current_members >= max_members")
                        ->execute([$matchedRoomId]);
                }
                $pdo->commit();
                echo json_encode(['success'=>true, 'room_id'=>$matchedRoomId, 'role'=>'member']);
                exit;

            } else {
                // --- B. 新しく部屋を作成 (自分がホスト) ---
                $stmt = $pdo->prepare("INSERT INTO fast_party_rooms (host_user_id, game_title, target_ranks, description, max_members) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $game, $rankStr, $desc, $maxMembers]);
                $newRoomId = $pdo->lastInsertId();

                // 自分をメンバーに追加
                $pdo->prepare("INSERT INTO fast_party_members (room_id, user_id) VALUES (?, ?)")
                    ->execute([$newRoomId, $user_id]);
                
                $pdo->commit();
                echo json_encode(['success'=>true, 'room_id'=>$newRoomId, 'role'=>'host']);
                exit;
            }
        }
    }

} catch (Exception $e) {
    if(isset($_REQUEST['api'])) {
        echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
        exit;
    }
}

// テンプレート表示
require_once './tpl/fast_party.php';
?>