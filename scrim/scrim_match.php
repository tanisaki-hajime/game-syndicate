<?php
session_start();
$db_host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'gamesyndicate';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

$opponent_id = $_GET['opponent_id'] ?? 0;
$game_filter = $_GET['game'] ?? '';

try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // API: スクリム申請
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['api']) && $_POST['api'] === 'apply_scrim') {
        if ($user_id == 0) exit(json_encode(['success'=>false, 'message'=>'ログインしてください']));
        
        $target_team_id = $_POST['target_team_id'];
        $my_team_id = $_POST['my_team_id'];
        $date = $_POST['date'];
        $msg = $_POST['message'];

        $chk = $pdo->prepare("SELECT id FROM scrim WHERE applicant_team_id=? AND target_team_id=? AND status='pending'");
        $chk->execute([$my_team_id, $target_team_id]);
        if($chk->fetch()) exit(json_encode(['success'=>false, 'message'=>'既に申請中です']));

        $stmt = $pdo->prepare("INSERT INTO scrim (applicant_team_id, target_team_id, scrim_date, status, memo) VALUES (?, ?, ?, 'pending', ?)");
        $stmt->execute([$my_team_id, $target_team_id, $date, $msg]);

        echo json_encode(['success'=>true]);
        exit;
    }

    // 自分のチーム一覧
    $myTeams = [];
    if ($user_id > 0) {
        $stmt = $pdo->prepare("SELECT t.id, t.team_name, t.game_title FROM team_members tm JOIN team t ON tm.team_id = t.id WHERE tm.user_id=? AND tm.status='joined'");
        $stmt->execute([$user_id]);
        $myTeams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- ★ページネーション処理 ---
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = 12; // 1ページあたりの表示数
    $offset = ($page - 1) * $limit;

    $baseSql = "FROM team WHERE 1=1"; 
    $params = [];
    
    // ゲームタイトルでの絞り込み
    if (!empty($game_filter)) {
        $baseSql .= " AND LOWER(game_title) = LOWER(?)";
        $params[] = $game_filter;
    }

    // 全件数取得
    $countStmt = $pdo->prepare("SELECT COUNT(*) $baseSql");
    $countStmt->execute($params);
    $totalTeams = $countStmt->fetchColumn();
    $totalPages = ceil($totalTeams / $limit);

    // データ取得
    $sql = "SELECT * $baseSql ORDER BY created_at DESC LIMIT $limit OFFSET $offset"; 
    $tStmt = $pdo->prepare($sql);
    $tStmt->execute($params);
    $teams = $tStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) { die($e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>スクリムマッチング - GAME SYNDICATE</title>
    <script src="https://kit.fontawesome.com/659df936c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./css/scrim.css">
    <style>
        body { background: #0a0e27; color: #fff; font-family: 'Helvetica Neue', Arial, sans-serif; }
        .scrim-container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        
        .page-header { text-align: center; margin-bottom: 30px; position: relative; }
        .page-header h1 { font-size: 2.5rem; color: #00d26a; margin-bottom: 10px; text-shadow: 0 0 20px rgba(0, 210, 106, 0.3); }
        .page-header p { color: #aaa; }

        /* ★戻るボタン */
        .btn-back-page { 
            display: inline-flex; align-items: center; gap: 8px; color: #aaa; text-decoration: none; 
            font-weight: bold; transition: 0.3s; padding: 10px 20px; background: rgba(255,255,255,0.05); 
            border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px;
        }
        .btn-back-page:hover { color: #fff; background: rgba(255,255,255,0.1); transform: translateX(-5px); }

        .team-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        
        .team-card { 
            background: #1a1f3a; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); overflow: hidden; 
            transition: 0.3s; position: relative;
        }
        .team-card:hover { transform: translateY(-5px); border-color: #00d26a; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        
        .card-bg { height: 80px; background: linear-gradient(135deg, #004d26, #000); }
        .card-content { padding: 20px; text-align: center; margin-top: -40px; }
        
        .team-icon { width: 70px; height: 70px; border-radius: 50%; border: 4px solid #1a1f3a; object-fit: cover; background: #000; }
        
        .team-name { font-size: 1.2rem; font-weight: bold; margin: 10px 0 5px; color: #fff; }
        .team-meta { font-size: 0.8rem; color: #888; display: flex; justify-content: center; gap: 10px; margin-bottom: 15px; }
        .meta-tag { background: rgba(255,255,255,0.1); padding: 3px 8px; border-radius: 4px; }
        
        .btn-apply { 
            width: 100%; background: #00d26a; color: #000; border: none; padding: 10px; 
            border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.2s; 
        }
        .btn-apply:hover { background: #00ff80; }

        /* ★ページネーション */
        .pagination-wrapper { margin-top: 40px; display: flex; justify-content: center; gap: 10px; padding-bottom: 40px; }
        .p-btn { 
            background: #1a1f3a; color: #a0a0a0; border: 1px solid rgba(255,255,255,0.1); 
            min-width: 45px; height: 45px; border-radius: 8px; font-weight: bold; font-size: 1rem; 
            cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; 
            justify-content: center; text-decoration: none;
        }
        .p-btn:hover { background: rgba(255,255,255,0.1); color: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
        .p-btn.active { background: linear-gradient(135deg, #00d26a 0%, #00a84f 100%); color: #fff; border: none; box-shadow: 0 0 15px rgba(0, 210, 106, 0.4); }

        /* モーダル */
        .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:999; justify-content:center; align-items:center; opacity:0; transition:0.3s; }
        .modal.show { display:flex; opacity:1; }
        .modal-content { background:#1a1f3a; padding:30px; border-radius:15px; width:90%; max-width:500px; border:1px solid #00d26a; transform:translateY(20px); transition:0.3s; }
        .modal.show .modal-content { transform:translateY(0); }
    </style>
</head>
<body>
    <header class="scrim-header">
        <div class="header-content">
            <a href="../top/top.php" class="logo"><i class="fas fa-handshake"></i> VALO SYNDICATE</a>
            <nav class="header-nav">
                <a href="#" onclick="history.back(); return false;" class="nav-link"><i class="fas fa-arrow-left"></i> 前のページに戻る</a>
            </nav>
        </div>
    </header>

    <div class="scrim-container">
        <a href="#" onclick="history.back(); return false;" class="btn-back-page"><i class="fas fa-arrow-left"></i> 戻る</a>

        <div class="page-header">
            <h1><i class="fas fa-crosshairs"></i> SCRIM MATCHING</h1>
            <p>対戦相手を見つけて、チームの実力を試そう。</p>
        </div>

        <div class="team-list">
            <?php if(empty($teams)): ?>
                <p style="color:#888; text-align:center; grid-column:1/-1;">該当するチームが見つかりません。</p>
            <?php else: ?>
                <?php foreach($teams as $team): ?>
                <div class="team-card">
                    <div class="card-bg"></div>
                    <div class="card-content">
                        <img src="<?php echo !empty($team['team_icon']) ? $team['team_icon'] : '../img/team.jpg'; ?>" class="team-icon">
                        <h3 class="team-name"><?php echo htmlspecialchars($team['team_name']); ?></h3>
                        <div class="team-meta">
                            <span class="meta-tag"><?php echo strtoupper($team['game_title']); ?></span>
                            <span class="meta-tag"><?php echo strtoupper($team['team_division']); ?></span>
                        </div>
                        <button class="btn-apply" onclick="openApplyModal(<?php echo $team['id']; ?>, '<?php echo htmlspecialchars($team['team_name']); ?>')">
                            対戦を申し込む
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if($totalPages > 1): ?>
        <div class="pagination-wrapper">
            <?php
            // 現在のURLからpageパラメータを除いたベースURLを作成
            $query_params = $_GET;
            unset($query_params['page']);
            $query_string = http_build_query($query_params);
            $base_url = "?$query_string" . ($query_string ? '&' : '');

            // 前へボタン
            if ($page > 1) {
                echo '<a href="'.$base_url.'page='.($page-1).'" class="p-btn"><i class="fas fa-chevron-left"></i></a>';
            }

            // ページ番号
            for ($i = 1; $i <= $totalPages; $i++) {
                $active = ($i == $page) ? 'active' : '';
                echo '<a href="'.$base_url.'page='.$i.'" class="p-btn '.$active.'">'.$i.'</a>';
            }

            // 次へボタン
            if ($page < $totalPages) {
                echo '<a href="'.$base_url.'page='.($page+1).'" class="p-btn"><i class="fas fa-chevron-right"></i></a>';
            }
            ?>
        </div>
        <?php endif; ?>
    </div>

    <div id="applyModal" class="modal">
        <div class="modal-content">
            <h2 style="color:#00d26a; margin-bottom:20px; text-align:center;">スクリム申請</h2>
            <p style="text-align:center; margin-bottom:20px;">To: <span id="targetName" style="font-weight:bold; color:#fff;"></span></p>
            
            <form onsubmit="submitScrim(event)">
                <input type="hidden" id="targetId" name="target_team_id">
                
                <div class="form-group">
                    <label>こちらのチーム</label>
                    <select name="my_team_id" required style="width:100%; padding:12px; background:#000; color:#fff; border:1px solid #444; border-radius:6px;">
                        <?php if(empty($myTeams)): ?>
                            <option value="">チームに所属していません</option>
                        <?php else: ?>
                            <?php foreach($myTeams as $mt): ?>
                                <option value="<?php echo $mt['id']; ?>"><?php echo htmlspecialchars($mt['team_name']); ?> (<?php echo $mt['game_title']; ?>)</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-top:15px;">
                    <label>希望日時</label>
                    <input type="datetime-local" name="date" required style="width:100%; padding:12px; background:#000; color:#fff; border:1px solid #444; border-radius:6px;">
                </div>

                <div class="form-group" style="margin-top:15px;">
                    <label>メッセージ</label>
                    <textarea name="message" rows="3" style="width:100%; padding:12px; background:#000; color:#fff; border:1px solid #444; border-radius:6px;" placeholder="マップ指定や条件など"></textarea>
                </div>

                <div style="margin-top:25px; display:flex; gap:10px;">
                    <button type="button" onclick="document.getElementById('applyModal').classList.remove('show')" class="btn" style="background:transparent; border:1px solid #444; color:#aaa; flex:1;">キャンセル</button>
                    <button type="submit" class="btn" style="background:#00d26a; color:#000; border:none; flex:1;">送信</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openApplyModal(id, name) {
            <?php if(empty($myTeams)): ?>
                alert('チームに所属していないため申請できません。\nまずはチームを作成または加入してください。');
                return;
            <?php endif; ?>
            document.getElementById('targetId').value = id;
            document.getElementById('targetName').innerText = name;
            document.getElementById('applyModal').classList.add('show');
        }

        async function submitScrim(e) {
            e.preventDefault();
            const form = e.target;
            const fd = new FormData(form);
            fd.append('api', 'apply_scrim');

            try {
                const res = await fetch('scrim_match.php', { method:'POST', body:fd });
                const data = await res.json();
                if(data.success) {
                    alert('申請を送信しました！');
                    document.getElementById('applyModal').classList.remove('show');
                } else {
                    alert(data.message);
                }
            } catch(e) { alert('エラー'); }
        }
    </script>
</body>
</html>