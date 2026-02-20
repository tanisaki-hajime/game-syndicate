<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>チーム編集 - GAME SYNDICATE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../mypage/css/style.css">
    <link rel="stylesheet" href="../mypage/css/edit.css">
    <style>
        /* チェックボックス等のスタイル（既存維持） */
        .checkbox-item input[type="checkbox"] { width: auto !important; height: auto !important; margin-right: 8px !important; display: inline-block !important; box-shadow: none !important; }
        .team-checkbox-grid, .team-agent-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; margin-top: 10px; }
        .team-agent-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); }
        .checkbox-item { display: flex; align-items: center; padding: 10px 12px; background: rgba(255, 0, 120, 0.05); border: 1px solid rgba(255, 0, 120, 0.3); border-radius: 8px; cursor: pointer; transition: all 0.2s ease; }
        .checkbox-item:hover { background: rgba(255, 0, 120, 0.15); border-color: #ff0078; }
        .checkbox-item:has(input:checked) { background: rgba(255, 0, 120, 0.25); border-color: #ff0078; box-shadow: 0 0 5px rgba(255, 0, 120, 0.5); }
        .checkbox-item label { cursor: pointer; margin: 0 !important; font-size: 0.9rem; color: #e0e0e0; flex: 1; }
        .header-preview { width: 100%; height: 150px; border-radius: 12px; background-size: cover; background-position: center; border: 2px solid #4a9eff; margin-bottom: 20px; position: relative; overflow: hidden; }
        .danger-zone h3 { color: #ff4444 !important; }
        .btn-danger { background: #ff4444; color: white; border: none; width: 100%; padding: 12px; border-radius: 6px; font-weight: bold; cursor: pointer; }
        @media (max-width: 480px) { .team-checkbox-grid, .team-agent-grid { grid-template-columns: repeat(2, 1fr); } }
    </style>
</head>
<body>
    <div class="container">
        <div class="edit-header">
            <a href="team_page.php?id=<?php echo $team_id; ?>" class="back-btn"><i class="fas fa-arrow-left"></i> チームページへ</a>
            <h1><i class="fas fa-users-cog"></i> チーム編集</h1>
        </div>

        <div class="edit-wrapper">
            <form id="editTeamForm" class="edit-form">
                <input type="hidden" name="action" value="update">

                <div class="edit-preview">
                    <div class="preview-card">
                        <h3><i class="fas fa-eye"></i> プレビュー</h3>
                        <div class="header-preview" id="previewHeader" style="background-image: url('<?php echo $header_img; ?>');"></div>
                        <div class="preview-avatar" id="previewIcon"><img src="<?php echo $team_icon; ?>" style="width:100%; height:100%; object-fit:cover;"></div>
                        <h4 id="previewName"><?php echo htmlspecialchars($team['team_name']); ?></h4>
                        <p id="previewDivision" style="color:#4a9eff; font-weight:bold; margin-bottom:10px;">
                            AVG: <?php echo htmlspecialchars(strtoupper($team['team_division'])); ?>
                        </p>
                        <div class="preview-info">
                            <div class="preview-item"><span class="label">GAME</span><span class="value" id="previewGame"><?php echo strtoupper($team['game_title']); ?></span></div>
                            <div class="preview-item"><span class="label">ステータス</span><span class="value" id="previewStatus"><?php echo ($team['team_status'] == 'recruiting') ? '募集中' : '活動中'; ?></span></div>
                        </div>
                    </div>
                </div>

                <div class="edit-form-container">
                    
                    <div class="edit-section">
                        <h3><i class="fas fa-flag"></i> 基本情報</h3>
                        <div class="form-group">
                            <label>チーム名</label>
                            <input type="text" name="team_name" id="team_name" value="<?php echo htmlspecialchars($team['team_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>ゲームタイトル</label>
                            <select name="game_title" id="game_title" onchange="updateGameOptions()">
                                <option value="valorant" <?php if($team['game_title']=='valorant') echo 'selected'; ?>>VALORANT</option>
                                <option value="apex" <?php if($team['game_title']=='apex') echo 'selected'; ?>>APEX LEGENDS</option>
                                <option value="lol" <?php if($team['game_title']=='lol') echo 'selected'; ?>>League of Legends</option>
                                <option value="ow2" <?php if($team['game_title']=='ow2') echo 'selected'; ?>>Overwatch 2</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>平均ランク帯</label>
                            <select name="team_division" id="team_division">
                                </select>
                        </div>

                        <div class="form-group">
                            <label>チームアイコン</label>
                            <div class="file-upload">
                                <input type="file" name="team_icon" id="team_icon" accept="image/*" onchange="previewImage(this, 'previewIcon')">
                                <label for="team_icon" class="file-upload-label"><i class="fas fa-upload"></i> 画像を選択</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>ヘッダー画像</label>
                            <div class="file-upload">
                                <input type="file" name="header_image" id="header_image" accept="image/*" onchange="previewBg(this, 'previewHeader')">
                                <label for="header_image" class="file-upload-label"><i class="fas fa-image"></i> 画像を選択</label>
                            </div>
                        </div>
                    </div>

                    <div class="edit-section">
                        <h3><i class="fas fa-bullhorn"></i> 募集設定</h3>
                        
                        <div class="form-group">
                            <label>募集ステータス</label>
                            <div class="button-group-horizontal">
                                <label><input type="radio" name="team_status" value="existing" <?php if($team['team_status']!='recruiting') echo 'checked'; ?> style="display:none;"><span class="radio-btn">活動中 (募集なし)</span></label>
                                <label><input type="radio" name="team_status" value="recruiting" <?php if($team['team_status']=='recruiting') echo 'checked'; ?> style="display:none;"><span class="radio-btn" style="color:#ff0078;">募集中</span></label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>募集ロール</label>
                            <div class="team-checkbox-grid" id="roles_container">
                                </div>
                        </div>

                        <div class="form-group">
                            <label>募集キャラクター / エージェント</label>
                            <div class="team-agent-grid" id="agents_container">
                                </div>
                        </div>

                        <div class="form-group">
                            <label>募集要項</label>
                            <textarea name="recruitment_text" rows="4" style="width:100%; padding:10px; background:rgba(255,0,120,0.05); border:1px solid rgba(255,0,120,0.3); border-radius:8px; color:#fff;"><?php echo htmlspecialchars($team['recruitment_text'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="edit-section">
                        <h3><i class="fas fa-info-circle"></i> 活動詳細</h3>
                        <div class="form-group">
                            <label>チーム紹介文</label>
                            <textarea name="description" rows="4" style="width:100%; padding:10px; background:rgba(255,0,120,0.05); border:1px solid rgba(255,0,120,0.3); border-radius:8px; color:#fff;"><?php echo htmlspecialchars($team['description'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="edit-section">
                        <h3><i class="fas fa-calendar-alt"></i> スケジュール・通知設定</h3>
                        <div class="form-group">
                            <label>活動時間 (カレンダー表示用)</label>
                            <div style="display:flex; gap:10px; align-items:center;">
                                <?php 
                                    // 24:00 を 23:59 に変換してエラー回避
                                    $st = substr($team['activity_start_time'] ?? '21:00', 0, 5);
                                    $et = substr($team['activity_end_time'] ?? '23:59', 0, 5);
                                    if ($st === '24:00') $st = '23:59';
                                    if ($et === '24:00') $et = '23:59';
                                ?>
                                <input type="time" name="activity_start_time" value="<?php echo $st; ?>" class="form-control" style="background:#151a30;border:1px solid #444;color:#fff;padding:10px;border-radius:6px;">
                                <span style="color:#fff;">〜</span>
                                <input type="time" name="activity_end_time" value="<?php echo $et; ?>" class="form-control" style="background:#151a30;border:1px solid #444;color:#fff;padding:10px;border-radius:6px;">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>活動成立人数 (この人数で通知)</label>
                            <input type="number" name="required_members" value="<?php echo htmlspecialchars($team['required_members'] ?? 5); ?>" class="form-control" min="1" style="width:100%;padding:10px;background:#151a30;border:1px solid #444;color:#fff;border-radius:6px;">
                        </div>
                        <div class="form-group">
                            <label>Discord Webhook URL</label>
                            <input type="url" name="discord_webhook" value="<?php echo htmlspecialchars($team['discord_webhook']??''); ?>" class="form-control" placeholder="https://discord.com/api/webhooks/..." style="width:100%;padding:10px;background:#151a30;border:1px solid #444;color:#fff;border-radius:6px;">
                        </div>
                        <div class="form-group">
                            <label>定期通知時間</label>
                            <input type="time" name="notification_time" value="<?php echo substr($team['notification_time'] ?? '20:00', 0, 5); ?>" class="form-control" style="background:#151a30;border:1px solid #444;color:#fff;padding:10px;border-radius:6px;">
                        </div>
                    </div>
                    
                    <div class="edit-section danger-zone">
                        <h3><i class="fas fa-trash"></i> チーム削除</h3>
                        <p style="font-size:0.9rem; color:#b0b0b0; margin-bottom:1rem;">チームを解散・削除します。この操作は取り消せません。</p>
                        <button type="button" class="btn btn-danger" id="deleteTeamBtn">チームを削除する</button>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> 変更を保存</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="js/edit.js"></script>
    <script>
        // 画像プレビュー
        function previewImage(i,t){if(i.files&&i.files[0]){var r=new FileReader();r.onload=function(e){document.getElementById(t).innerHTML='<img src="'+e.target.result+'" style="width:100%;height:100%;object-fit:cover;">'};r.readAsDataURL(i.files[0]);}}
        function previewBg(i,t){if(i.files&&i.files[0]){var r=new FileReader();r.onload=function(e){document.getElementById(t).style.backgroundImage='url("'+e.target.result+'")'};r.readAsDataURL(i.files[0]);}}

        // --- ★ゲーム別データ定義 ---
        const GAME_DATA = {
            'valorant': {
                ranks: ['Radiant', 'Immortal 3', 'Immortal 2', 'Immortal 1', 'Ascendant', 'Diamond', 'Platinum', 'Gold', 'Silver', 'Bronze', 'Iron', 'Unrated'],
                roles: {'duelist':'デュエリスト', 'initiator':'イニシエーター', 'controller':'コントローラー', 'sentinel':'センチネル'},
                agents: ['Jett', 'Raze', 'Reyna', 'Yoru', 'Phoenix', 'Neon', 'Iso', 'Sova', 'Fade', 'Skye', 'Breach', 'Kayo', 'Gekko', 'Omen', 'Brimstone', 'Viper', 'Astra', 'Harbor', 'Clove', 'Sage', 'Cypher', 'Killjoy', 'Chamber', 'Deadlock', 'Vyse']
            },
            'apex': {
                ranks: ['Predator', 'Master', 'Diamond', 'Platinum', 'Gold', 'Silver', 'Bronze', 'Rookie', 'Unrated'],
                roles: {'assault':'アサルト', 'skirmisher':'スカーミッシャー', 'recon':'リコン', 'support':'サポート', 'controller':'コントローラー'},
                agents: ['Bangalore', 'Fuse', 'Ash', 'Mad Maggie', 'Ballistic', 'Pathfinder', 'Wraith', 'Octane', 'Revenant', 'Horizon', 'Valkyrie', 'Alter', 'Bloodhound', 'Crypto', 'Seer', 'Vantage', 'Gibraltar', 'Lifeline', 'Mirage', 'Loba', 'Newcastle', 'Conduit', 'Caustic', 'Wattson', 'Rampart', 'Catalyst']
            },
            'lol': {
                ranks: ['Challenger', 'Grandmaster', 'Master', 'Diamond', 'Emerald', 'Platinum', 'Gold', 'Silver', 'Bronze', 'Iron', 'Unrated'],
                roles: {'top':'TOP', 'jungle':'JUNGLE', 'mid':'MID', 'adc':'ADC', 'support':'SUPPORT'},
                agents: ['Aatrox', 'Ahri', 'Akali', 'Darius', 'Ezreal', 'Jinx', 'Kai\'Sa', 'Lee Sin', 'Lux', 'Thresh', 'Yasuo', 'Yone', 'Zed'] // 省略
            },
            'ow2': {
                ranks: ['Champion', 'Grandmaster', 'Master', 'Diamond', 'Platinum', 'Gold', 'Silver', 'Bronze', 'Unrated'],
                roles: {'tank':'タンク', 'damage':'ダメージ', 'support':'サポート'},
                agents: ['D.Va', 'Reinhardt', 'Winston', 'Zarya', 'Genji', 'Tracer', 'Cassidy', 'Pharah', 'Ana', 'Mercy', 'Kiriko', 'Lucio']
            }
        };

        // 保存済みデータ（PHPから渡す）
        const savedDivision = "<?php echo htmlspecialchars($team['team_division']); ?>";
        const savedRoles = <?php echo json_encode($saved_roles); ?>;
        const savedAgents = <?php echo json_encode($saved_agents); ?>;

        function updateGameOptions() {
            const game = document.getElementById('game_title').value;
            const data = GAME_DATA[game] || GAME_DATA['valorant'];

            // 1. ランク更新
            const divSelect = document.getElementById('team_division');
            divSelect.innerHTML = '';
            data.ranks.forEach(r => {
                const val = r.toLowerCase().replace(/\s/g, ''); // "Immortal 3" -> "immortal3"
                // シンプル化のため、DB保存値と比較しやすい形に整形するか、表示名をそのまま使う
                // ここではシンプルに小文字化して比較
                const optVal = r.toLowerCase().split(' ')[0]; // "immortal 3" -> "immortal"
                const isSelected = (optVal === savedDivision || r.toLowerCase() === savedDivision) ? 'selected' : '';
                divSelect.innerHTML += `<option value="${optVal}" ${isSelected}>${r}</option>`;
            });

            // 2. ロール更新
            const roleContainer = document.getElementById('roles_container');
            roleContainer.innerHTML = '';
            for (const [key, label] of Object.entries(data.roles)) {
                const isChecked = savedRoles.includes(key) ? 'checked' : '';
                roleContainer.innerHTML += `
                    <div class="checkbox-item">
                        <input type="checkbox" id="role_${key}" name="wanted_roles[]" value="${key}" ${isChecked}>
                        <label for="role_${key}">${label}</label>
                    </div>`;
            }

            // 3. エージェント更新
            const agentContainer = document.getElementById('agents_container');
            agentContainer.innerHTML = '';
            data.agents.forEach(ag => {
                const isChecked = savedAgents.includes(ag) ? 'checked' : '';
                agentContainer.innerHTML += `
                    <div class="checkbox-item">
                        <input type="checkbox" id="ag_${ag}" name="wanted_agents[]" value="${ag}" ${isChecked}>
                        <label for="ag_${ag}">${ag}</label>
                    </div>`;
            });
            
            // プレビューのゲーム名更新
            document.getElementById('previewGame').innerText = game.toUpperCase();
        }

        // 初期化
        updateGameOptions();
    </script>
</body>
</html>