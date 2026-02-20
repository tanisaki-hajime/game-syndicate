<?php
// ==========================================
// 1. ロジックファイル (Controller)
// ==========================================
ob_start();

session_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$db_host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'gamesyndicate';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --------------------------------------------------
    // [API処理]
    // --------------------------------------------------
    if (isset($_GET['api']) || isset($_POST['api'])) {
        ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        // --- GET: チーム検索 ---
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            try {
                $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                $limit = 10;
                $offset = ($page - 1) * $limit;

                // 検索条件
                $where = "WHERE 1=1";
                $params = [];

                // ゲームタイトル
                $game = isset($_GET['game']) ? trim($_GET['game']) : '';
                if (!empty($game) && $game !== 'all' && $game !== 'other') { 
                    $where .= " AND LOWER(game_title) = LOWER(?)"; 
                    $params[] = $game; 
                }
                
                $q = isset($_GET['q']) ? trim($_GET['q']) : '';
                if (!empty($q)) { 
                    $where .= " AND team_name LIKE ?"; 
                    $params[] = '%'.$q.'%'; 
                }

                $division = isset($_GET['division']) ? trim($_GET['division']) : '';
                if (!empty($division) && $division !== 'unrated' && $division !== '指定なし') {
                    $divBase = trim(preg_replace('/[\d\s]+$/', '', $division));
                    if(empty($divBase)) $divBase = $division;
                    $where .= " AND (team_division LIKE ? OR team_division LIKE ?)"; 
                    $params[] = '%'.$division.'%';
                    $params[] = '%'.$divBase.'%';
                }

                $role = isset($_GET['role']) && $_GET['role'] !== '指定なし' ? trim($_GET['role']) : '';
                $char = isset($_GET['character']) && $_GET['character'] !== '指定なし' ? trim($_GET['character']) : '';

                // ★修正: チームの募集要項（wanted_roles, wanted_agents）から検索する
                if (!empty($role)) { 
                    $where .= " AND LOWER(wanted_roles) LIKE LOWER(?)"; 
                    $params[] = '%'.$role.'%'; 
                }
                if (!empty($char)) { 
                    $where .= " AND LOWER(wanted_agents) LIKE LOWER(?)"; 
                    $params[] = '%'.$char.'%'; 
                }

                // チーム一覧取得
                $sql = "SELECT * FROM team $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // 全件数
                $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM team $where");
                $stmtCount->execute($params);
                $totalTeams = $stmtCount->fetchColumn();
                $totalPages = ceil($totalTeams / $limit);

                // 追加情報
                foreach ($teams as &$team) {
                    $cStmt = $pdo->prepare("SELECT COUNT(*) FROM team_members WHERE team_id = ? AND status = 'joined'");
                    $cStmt->execute([$team['id']]);
                    $team['real_member_count'] = $cStmt->fetchColumn();

                    try {
                        $compSql = "SELECT ug.main_role, ug.sub_role, ug.main_character 
                                    FROM team_members tm
                                    JOIN user u ON tm.user_id = u.id
                                    LEFT JOIN user_game_profiles ug ON u.id = ug.user_id AND ug.game_title = ?
                                    WHERE tm.team_id = ? AND tm.status = 'joined'";
                        $compStmt = $pdo->prepare($compSql);
                        $compStmt->execute([$team['game_title'], $team['id']]);
                        $profiles = $compStmt->fetchAll(PDO::FETCH_ASSOC);
                        $compStrings = [];
                        foreach ($profiles as $p) {
                            $r = $p['main_role'] ?? ''; $c = $p['main_character'] ?? '';
                            if($r || $c) $compStrings[] = "$r:$c";
                        }
                        $team['member_composition'] = implode('|', $compStrings);
                    } catch (Exception $e) { $team['member_composition'] = ''; }
                }

                // 所属チーム情報を「ゲームタイトルごと」に取得
                $myTeamIds = [];
                if ($user_id > 0) {
                    $chkSql = "SELECT t.id, LOWER(t.game_title) as game_key
                               FROM team_members tm 
                               JOIN team t ON tm.team_id = t.id
                               WHERE tm.user_id = ? AND tm.status = 'joined'";
                    $chk = $pdo->prepare($chkSql);
                    $chk->execute([$user_id]);
                    $rows = $chk->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($rows as $r) {
                        $myTeamIds[$r['game_key']] = $r['id'];
                    }
                }

                echo json_encode([
                    'success' => true,
                    'teams' => $teams,
                    'my_team_ids' => $myTeamIds, 
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => $totalPages,
                        'total_items' => $totalTeams
                    ]
                ]);
                exit;

            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                exit;
            }
        }

        // --- POST: 申請処理 ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($user_id == 0) { echo json_encode(['success'=>false, 'message'=>'ログインしてください']); exit; }
            $tid = $_POST['team_id'];
            $type = $_POST['type'];
            $msg = $_POST['message'] ?? '';
            $role = $_POST['role'] ?? 'member';

            $pdo->beginTransaction();
            try {
                $stmtM = $pdo->prepare("SELECT user_id FROM team_members WHERE team_id=? AND status='joined'");
                $stmtM->execute([$tid]);
                $members = $stmtM->fetchAll(PDO::FETCH_COLUMN);

                if ($type === 'join') {
                    $chk = $pdo->prepare("SELECT id FROM team_members WHERE team_id=? AND user_id=? AND status IN ('joined', 'pending', 'owner')");
                    $chk->execute([$tid, $user_id]);
                    if($chk->fetch()) throw new Exception('既にこのチームに所属しているか、申請済みです');

                    $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role, status) VALUES (?, ?, ?, 'pending')");
                    $stmt->execute([$tid, $user_id, $role]);
                    $reqId = $pdo->lastInsertId();

                    if(!empty($members)) {
                        $noti = $pdo->prepare("INSERT INTO notifications (target_user_id, sender_user_id, sender_team_id, type, title, message, link_id) VALUES (?, ?, ?, 'join_request', '加入申請', ?, ?)");
                        foreach($members as $m) $noti->execute([$m, $user_id, $tid, "希望: $role\n$msg", $reqId]);
                    }

                } elseif ($type === 'scrim') {
                    $chk = $pdo->prepare("SELECT id FROM scrim WHERE target_team_id=? AND created_at > (NOW() - INTERVAL 1 DAY)");
                    $chk->execute([$tid]);
                    if($chk->fetch()) throw new Exception('1日1回までです');

                    $targetStmt = $pdo->prepare("SELECT game_title FROM team WHERE id = ?");
                    $targetStmt->execute([$tid]);
                    $targetGame = $targetStmt->fetchColumn();

                    $myTeamStmt = $pdo->prepare("SELECT t.id, t.team_name FROM team_members tm 
                                                 JOIN team t ON tm.team_id = t.id 
                                                 WHERE tm.user_id = ? AND tm.status = 'joined' 
                                                 AND LOWER(t.game_title) = LOWER(?) LIMIT 1");
                    $myTeamStmt->execute([$user_id, $targetGame]);
                    $myTeam = $myTeamStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if(!$myTeam) throw new Exception("このゲーム ($targetGame) のチームに所属していません");
                    if ($myTeam['id'] == $tid) throw new Exception("自分のチームには申請できません");

                    $stmt = $pdo->prepare("INSERT INTO scrim (applicant_team_id, target_team_id, status, memo) VALUES (?, ?, 'pending', ?)");
                    $stmt->execute([$myTeam['id'], $tid, $msg]);
                    $scrimId = $pdo->lastInsertId();

                    if(!empty($members)) {
                        $noti = $pdo->prepare("INSERT INTO notifications (target_user_id, sender_team_id, sender_user_id, type, title, message, link_id) VALUES (?, ?, ?, 'scrim_request', 'スクリム申込', ?, ?)");
                        foreach($members as $m) $noti->execute([$m, $myTeam['id'], $user_id, "チーム「{$myTeam['team_name']}」より\n$msg", $scrimId]);
                    }
                }
                $pdo->commit();
                echo json_encode(['success'=>true]);

            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
            }
            exit;
        }
    }

} catch (Exception $e) {
    if(isset($_GET['api']) || isset($_POST['api'])) {
        ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success'=>false, 'message' => 'Sys Error: ' . $e->getMessage()]);
        exit;
    }
    die('Database Error');
}

$user_icon = '../img/default_user.png';
if($user_id > 0) {
    $s = $pdo->prepare("SELECT user_icon FROM user WHERE id=?"); $s->execute([$user_id]);
    $u = $s->fetch();
    if(!empty($u['user_icon']) && file_exists(__DIR__.'/../mypage/'.$u['user_icon'])) {
        $user_icon = '../mypage/'.$u['user_icon'];
    }
}
$selected_game = $_GET['game'] ?? '';
$view_mode = empty($selected_game) ? 'select_mode' : 'search_mode';

require_once __DIR__ . '/tpl/team_search.php';
ob_end_flush();
?>