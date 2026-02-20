<?php
ob_start();
session_start();

if (isset($_POST['action'])) { 
    ini_set('display_errors', 0); 
    header('Content-Type: application/json; charset=utf-8'); 
}

$db_host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'gamesyndicate';

if (!isset($_SESSION['user_id'])) { 
    if (isset($_POST['action'])) { ob_clean(); exit(json_encode(['success'=>false, 'message'=>'ログインしてください'])); }
    header('Location: ../mypage/login.php'); exit; 
}

$user_id = $_SESSION['user_id'];
$team_id = $_GET['id'] ?? 0;

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

    $stmt = $pdo->prepare("SELECT role FROM team_members WHERE team_id = ? AND user_id = ? AND status = 'joined'");
    $stmt->execute([$team_id, $user_id]);
    if ($stmt->fetchColumn() !== 'owner') { 
        if(isset($_POST['action'])) { ob_clean(); exit(json_encode(['success'=>false, 'message'=>'権限がありません'])); }
        die('権限なし'); 
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'update') {
            try {
                $w_roles = isset($_POST['wanted_roles']) && is_array($_POST['wanted_roles']) ? implode(',', $_POST['wanted_roles']) : '';
                $w_agents = isset($_POST['wanted_agents']) && is_array($_POST['wanted_agents']) ? implode(',', $_POST['wanted_agents']) : '';

                // ★修正: フォルダパスを統一
                $upload_base = __DIR__ . '/uploads';
                $upload_dir_icon = $upload_base . '/team_icons/';
                $upload_dir_head = $upload_base . '/team_headers/';
                
                if (!file_exists($upload_base)) { @mkdir($upload_base, 0777, true); }
                if (!file_exists($upload_dir_icon)) { @mkdir($upload_dir_icon, 0777, true); }
                if (!file_exists($upload_dir_head)) { @mkdir($upload_dir_head, 0777, true); }

                $icon_path = null; 
                $header_path = null;

                if (isset($_FILES['team_icon']) && $_FILES['team_icon']['error'] === UPLOAD_ERR_OK) {
                    $fn = 'icon_' . time() . '_' . uniqid() . '.' . pathinfo($_FILES['team_icon']['name'], PATHINFO_EXTENSION);
                    if (move_uploaded_file($_FILES['team_icon']['tmp_name'], $upload_dir_icon . $fn)) {
                        $icon_path = "uploads/team_icons/" . $fn;
                    } else {
                        throw new Exception("アイコン画像の保存に失敗しました。");
                    }
                }
                
                if (isset($_FILES['header_image']) && $_FILES['header_image']['error'] === UPLOAD_ERR_OK) {
                    $fn = 'head_' . time() . '_' . uniqid() . '.' . pathinfo($_FILES['header_image']['name'], PATHINFO_EXTENSION);
                    if (move_uploaded_file($_FILES['header_image']['tmp_name'], $upload_dir_head . $fn)) {
                        $header_path = "uploads/team_headers/" . $fn;
                    } else {
                        throw new Exception("ヘッダー画像の保存に失敗しました。");
                    }
                }

                $req_members = !empty($_POST['required_members']) ? (int)$_POST['required_members'] : 5;
                $st_time = !empty($_POST['activity_start_time']) ? $_POST['activity_start_time'] : '21:00:00';
                $ed_time = !empty($_POST['activity_end_time']) ? $_POST['activity_end_time'] : '23:59:00';
                if($ed_time === '24:00' || $ed_time === '24:00:00') $ed_time = '23:59:00';

                $sql = "UPDATE team SET 
                        team_name=?, game_title=?, team_division=?, 
                        description=?, team_status=?, recruitment_text=?,
                        wanted_roles=?, wanted_agents=?,
                        discord_webhook=?, required_members=?, notification_time=?,
                        activity_start_time=?, activity_end_time=?";
                
                $params = [
                    $_POST['team_name'] ?? 'No Name', 
                    $_POST['game_title'] ?? 'valorant', 
                    $_POST['team_division'] ?? 'unrated',
                    $_POST['description'] ?? '', 
                    $_POST['team_status'] ?? 'existing',
                    $_POST['recruitment_text'] ?? '', 
                    $w_roles, 
                    $w_agents,
                    $_POST['discord_webhook'] ?? '',
                    $req_members,
                    $_POST['notification_time'] ?? '20:00:00',
                    $st_time,
                    $ed_time
                ];

                if ($icon_path) { $sql .= ", team_icon=?"; $params[] = $icon_path; }
                if ($header_path) { $sql .= ", header_image=?"; $params[] = $header_path; }

                $sql .= " WHERE id=?";
                $params[] = $team_id;
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                ob_clean(); // ★出力バッファをクリア
                echo json_encode(['success'=>true, 'message'=>'保存しました']);
            } catch (Exception $e) {
                ob_clean();
                echo json_encode(['success'=>false, 'message'=>'エラー: ' . $e->getMessage()]);
            }
            exit;
        }
    }

    $stmt = $pdo->prepare("SELECT * FROM team WHERE id = ?");
    $stmt->execute([$team_id]);
    $team = $stmt->fetch();

} catch (Exception $e) { 
    if (isset($_POST['action'])) { ob_clean(); exit(json_encode(['success'=>false, 'message'=>'エラー: ' . $e->getMessage()])); }
    die('Error: ' . $e->getMessage()); 
}

// 過去の ../uploads/ のパスを修正して表示
function getImg($path, $default) { 
    if (empty($path)) return $default;
    $path = str_replace('../uploads/', 'uploads/', $path);
    return file_exists($path) ? htmlspecialchars($path) : $default; 
}

$team_icon = getImg($team['team_icon'], 'https://placehold.co/200x200/1a1f3a/ff0078?text=TEAM');
$header_img = getImg($team['header_image'], 'https://placehold.co/1200x400/000000/333333?text=Team+Header');

$saved_roles = isset($team['wanted_roles']) ? explode(',', $team['wanted_roles']) : [];
$saved_agents = isset($team['wanted_agents']) ? explode(',', $team['wanted_agents']) : [];

ob_end_flush();
require_once 'tpl/team_edit.php';
?>