<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>マイページ - GAME SYNDICATE</title>
    <script src="https://kit.fontawesome.com/659df936c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./css/style.css?v=<?php echo time(); ?>">
    <style>
        /* マイページ専用スタイル */
        .mp-tabs { display: flex; gap: 15px; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .mp-tab {
            padding: 12px 20px; cursor: pointer; color: #888; font-weight: bold; position: relative; transition: 0.3s;
        }
        .mp-tab:hover { color: #fff; }
        .mp-tab.active { color: #ff0078; text-shadow: 0 0 10px rgba(255,0,120,0.5); }
        .mp-tab.active::after {
            content: ''; position: absolute; bottom: -1px; left: 0; width: 100%; height: 3px; 
            background: #ff0078; box-shadow: 0 0 10px #ff0078;
        }
        
        .game-data-view { display: none; animation: fadeIn 0.4s ease; }
        .game-data-view.active { display: block; }
        .no-data-msg { color: #666; padding: 30px; text-align: center; border: 1px dashed #444; border-radius: 8px; }

        /* データ表示用 */
        .game-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .info-item label { font-size: 0.8rem; color: #888; display: block; margin-bottom: 5px; }
        .info-item p { font-size: 1.1rem; font-weight: bold; color: #fff; }
        
        .tag-container { display: flex; flex-wrap: wrap; gap: 8px; }
        .tag { padding: 4px 10px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; }
        .tag.main-role { background: rgba(255, 0, 120, 0.2); color: #ff0078; border: 1px solid rgba(255,0,120,0.3); }
        .tag.sub-role { background: rgba(74, 158, 255, 0.1); color: #4a9eff; border: 1px solid rgba(74,158,255,0.2); }
        .tag.char { background: #333; color: #ddd; border: 1px solid #444; }
        
        .rank-high { color: #ffd700; text-shadow: 0 0 5px rgba(255, 215, 0, 0.3); font-size: 0.9rem; margin-left: 10px; }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-left">
            <a href="../top/top.php" class="logo-link"><i class="fas fa-rocket"></i> GAME SYNDICATE</a>
            <nav class="nav">
                <a href="../team/team_create.php" class="nav-item">チーム作成</a>
                <a href="../team/team_search.php" class="nav-item">チーム検索</a>
                <a href="./mypage.php" class="nav-item active">マイページ</a>
            </nav>
        </div>
        <div class="header-right">
            <a href="notice.php" class="header-icon"><i class="fas fa-bell"></i></a>
            <a href="mypage.php" class="header-user">
                <img src="<?php echo !empty($userData['user_icon']) ? htmlspecialchars($userData['user_icon']) : '../img/default_user.png'; ?>">
            </a>
        </div>
    </header>

    <div class="container">
        <div class="profile-hero">
            <div class="profile-avatar-large">
                <img src="<?php echo !empty($userData['user_icon']) ? htmlspecialchars($userData['user_icon']) : '../img/default_user.png'; ?>">
            </div>
            <div class="profile-hero-info">
                <h1><?php echo htmlspecialchars($userData['name']); ?></h1>
                <p class="account-id">@<?php echo htmlspecialchars(str_replace('@', '', $userData['account_id'])); ?></p>
                <div class="profile-actions">
                    <a href="edit_profile.php" class="btn btn-primary"><i class="fas fa-edit"></i> プロフィール編集</a>
                    <a href="./logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> ログアウト</a>
                </div>
            </div>
        </div>

        <div class="profile-content" style="display:grid; grid-template-columns: 300px 1fr; gap:30px;">
            <div class="profile-sidebar">
                <div class="info-card" style="margin-bottom:20px;">
                    <div class="card-header"><h3><i class="fas fa-user-circle"></i> 基本情報</h3></div>
                    <div class="card-content">
                        <div style="margin-bottom:10px;"><label style="color:#888;font-size:0.8rem;">Email</label><p><?php echo htmlspecialchars($userData['mailadress']); ?></p></div>
                        <div><label style="color:#888;font-size:0.8rem;">Age</label><p><?php echo htmlspecialchars($userData['age'] ?? '-'); ?>歳</p></div>
                        <?php if(!empty($userData['discord_id'])): ?>
                            <div style="margin-top:10px;"><label style="color:#888;font-size:0.8rem;">Discord</label><p style="color:#7289da;"><?php echo htmlspecialchars($userData['discord_id']); ?></p></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-card">
                    <div class="card-header"><h3><i class="fas fa-link"></i> LINKS</h3></div>
                    <div class="card-content">
                        <?php if($userData['x_link']): ?><a href="<?php echo htmlspecialchars($userData['x_link']); ?>" target="_blank" class="sns-link" style="display:block;margin-bottom:10px;"><i class="fab fa-x-twitter"></i> X (Twitter)</a><?php endif; ?>
                        <?php if($userData['twitch_link']): ?><a href="<?php echo htmlspecialchars($userData['twitch_link']); ?>" target="_blank" class="sns-link" style="display:block;margin-bottom:10px;"><i class="fab fa-twitch"></i> Twitch</a><?php endif; ?>
                        <?php if($userData['youtube_link']): ?><a href="<?php echo htmlspecialchars($userData['youtube_link']); ?>" target="_blank" class="sns-link" style="display:block;"><i class="fab fa-youtube"></i> YouTube</a><?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="profile-main">
                <div class="info-card" style="margin-bottom:30px;">
                    <div class="card-header">
                        <h3><i class="fas fa-gamepad"></i> GAME STATUS</h3>
                    </div>
                    <div class="card-content">
                        <div class="mp-tabs">
                            <div class="mp-tab active" onclick="viewTab('valorant')">VALORANT</div>
                            <div class="mp-tab" onclick="viewTab('apex')">APEX</div>
                            <div class="mp-tab" onclick="viewTab('lol')">LoL</div>
                        </div>

                        <div id="view-valorant" class="game-data-view active"></div>
                        <div id="view-apex" class="game-data-view"></div>
                        <div id="view-lol" class="game-data-view"></div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="card-header"><h3><i class="fas fa-users"></i> TEAMS</h3></div>
                    <div class="card-content">
                        <?php if(count($teams) > 0): ?>
                            <?php foreach($teams as $team): ?>
                                <a href="../team/team_page.php?id=<?php echo $team['id']; ?>" class="team-badge" style="display:flex; align-items:center; gap:15px; padding:15px; background:rgba(255,255,255,0.05); border-radius:8px; margin-bottom:10px; text-decoration:none; color:#fff; border:1px solid rgba(255,255,255,0.1); transition:0.3s;">
                                    <div style="font-weight:bold; font-size:1.1rem;"><?php echo htmlspecialchars($team['team_name']); ?></div>
                                    <div style="font-size:0.8rem; color:#888; margin-left:auto;"><?php echo strtoupper($team['game_title']); ?> / <?php echo strtoupper($team['member_role']); ?></div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align:center; padding:30px; color:#888;">
                                <p>チームに所属していません</p>
                                <div style="margin-top:15px; display:flex; gap:10px; justify-content:center;">
                                    <a href="../team/team_create.php" class="btn btn-primary">チーム作成</a>
                                    <a href="../team/team_search.php" class="btn btn-secondary">チーム検索</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const gameData = <?php echo $json_gameData; ?>;

        function viewTab(game) {
            document.querySelectorAll('.mp-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.game-data-view').forEach(v => v.classList.remove('active'));
            
            const tabs = document.querySelectorAll('.mp-tab');
            if(game=='valorant') tabs[0].classList.add('active');
            if(game=='apex') tabs[1].classList.add('active');
            if(game=='lol') tabs[2].classList.add('active');

            const container = document.getElementById(`view-${game}`);
            container.classList.add('active');

            const data = gameData[game];
            
            if(!data) {
                container.innerHTML = '<div class="no-data-msg">このゲームのプロフィールは未設定です。<br><a href="edit_profile.php" style="color:#ff0078;">編集ページ</a>で設定してください。</div>';
            } else {
                // ロール表示の構築
                let rolesHTML = '';
                if(data.main_role) rolesHTML += `<span class="tag main-role">★ ${data.main_role}</span>`;
                if(data.sub_role) data.sub_role.split(',').forEach(r => {
                    if(r.trim()) rolesHTML += `<span class="tag sub-role">${r}</span>`;
                });
                if(!rolesHTML) rolesHTML = '<span style="color:#666">-</span>';

                // キャラ表示
                let charTags = '';
                if(data.main_character) data.main_character.split(',').forEach(c => {
                    if(c.trim()) charTags += `<span class="tag char">${c}</span>`;
                });
                else charTags = '<span style="color:#666">-</span>';

                // ランク表示
                let rankDisplay = `<span style="font-weight:bold; font-size:1.2rem;">${data.current_rank || 'Unrated'}</span>`;
                if(data.highest_rank) rankDisplay += `<span class="rank-high"><i class="fas fa-trophy"></i> Best: ${data.highest_rank}</span>`;

                // IGL表示
                let iglBadge = '';
                if(data.igl === 'yes') iglBadge = '<span style="background:#ff0078; color:#fff; padding:2px 8px; border-radius:4px; font-size:0.8rem; margin-left:10px;">IGL</span>';

                container.innerHTML = `
                    <div class="game-info-grid">
                        <div class="info-item"><label>GAME ID</label><p>${data.ingame_name || '-'}</p></div>
                        <div class="info-item"><label>RANK</label><p>${rankDisplay}</p></div>
                        <div class="info-item"><label>STYLE</label><p>${data.playstyle ? data.playstyle.toUpperCase() : '-'} ${iglBadge}</p></div>
                    </div>
                    
                    <div style="margin-bottom:20px;">
                        <label style="font-size:0.8rem; color:#888; display:block; margin-bottom:5px;">ROLES</label>
                        <div class="tag-container">${rolesHTML}</div>
                    </div>

                    <div>
                        <label style="font-size:0.8rem; color:#888; display:block; margin-bottom:5px;">CHARACTERS</label>
                        <div class="tag-container">${charTags}</div>
                    </div>
                `;
            }
        }

        // 初期表示
        viewTab('valorant');
    </script>
</body>
</html>