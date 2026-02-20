<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>プロフィール編集 - GAME SYNDICATE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="./css/edit.css?v=<?php echo time(); ?>">
    <style>
        .rank-select-group { display: flex; gap: 5px; }
        .rank-select-group select { flex: 1; padding: 10px; background: rgba(255,255,255,0.05); border: 1px solid #444; color: #fff; border-radius: 6px; }
        .toast { position: fixed; top: 90px; right: 20px; background: linear-gradient(135deg, #00b09b, #96c93d); color: #fff; padding: 15px 25px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); transform: translateX(120%); transition: 0.4s; z-index: 9999; }
        .toast.show { transform: translateX(0); }
        .toast.error { background: linear-gradient(135deg, #ff5f6d, #ffc371); }
        .game-tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid rgba(255,255,255,0.1); padding-bottom: 10px; }
        .game-tab { padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; color: #888; transition: 0.3s; background: rgba(255,255,255,0.05); }
        .game-tab.active { background: rgba(255, 0, 120, 0.1); color: #ff0078; border-color: #ff0078; }
        .game-form-section { display: none; }
        .game-form-section.active { display: block; }
        .checkbox-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 8px; margin-top: 5px; max-height: 400px; overflow-y: auto; }
        .cb-item label { display: block; padding: 10px; background: rgba(255,255,255,0.05); border-radius: 6px; text-align: center; font-size: 0.85rem; cursor: pointer; border: 1px solid transparent; transition: 0.2s; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .cb-item input { display: none; }
        .cb-item input:checked + label { background: rgba(74, 158, 255, 0.2); border-color: #4a9eff; color: #fff; font-weight: bold; }
        .radio-grid .cb-item input:checked + label { background: rgba(255, 0, 120, 0.2); border-color: #ff0078; }
        .team-leave-item { display: none; justify-content: space-between; align-items: center; background: rgba(255, 68, 68, 0.1); border: 1px solid rgba(255, 68, 68, 0.3); padding: 15px; border-radius: 8px; margin-bottom: 10px; }
        .team-leave-item.visible { display: flex; }
        .btn-leave { background: #ff4444; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; }
        .form-section-title { color: #ff0078; margin: 20px 0 10px; font-weight: bold; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .radio-group { display: flex; gap: 15px; margin-top: 5px; }
        .radio-label { cursor: pointer; color: #ccc; }
        .radio-label input { accent-color: #ff0078; margin-right: 5px; }
        .file-upload-box { border: 2px dashed rgba(255,255,255,0.2); padding: 20px; text-align: center; border-radius: 8px; background: rgba(0,0,0,0.2); margin-top: 5px; }
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
            <a href="mypage.php" class="header-user"><img src="<?php echo htmlspecialchars($user_icon); ?>"></a>
        </div>
    </header>

    <div class="container">
        <div class="edit-header">
            <a href="mypage.php" class="back-btn"><i class="fas fa-arrow-left"></i> 戻る</a>
            <h1><i class="fas fa-edit"></i> プロフィール編集</h1>
        </div>

        <div class="edit-wrapper">
            <form id="profileForm" onsubmit="saveProfile(event)" enctype="multipart/form-data">
                
                <div class="edit-form-container" style="margin-bottom: 30px;">
                    <div class="edit-section">
                        <h3><i class="fas fa-user"></i> 基本情報</h3>
                        <div class="form-group">
                            <label>アイコン画像</label>
                            <div class="file-upload-box">
                                <input type="file" id="user_icon" name="user_icon" accept="image/*" style="display:block; margin:0 auto; color:#fff;">
                            </div>
                        </div>
                        <div class="form-group"><label>プレイヤー名</label><input type="text" id="name" name="name" required></div>
                        <div class="form-group"><label>ID (@xxx)</label><input type="text" id="account_id" name="account_id" required></div>
                        <div class="form-group"><label>Email</label><input type="email" id="mailadress" name="mailadress" required></div>
                        <div class="form-group"><label>生年月日</label><input type="date" id="birthday" name="birthday"></div>
                    </div>
                    <div class="edit-section">
                        <h3><i class="fas fa-link"></i> 連携・SNS</h3>
                        <div class="form-group"><label style="color:#7289da;"><i class="fab fa-discord"></i> Discord ID</label><input type="text" id="discord_id" name="discord_id" placeholder="user#1234 or user"></div>
                        <div class="form-group"><label>X (Twitter)</label><input type="url" id="x_link" name="x_link"></div>
                        <div class="form-group"><label>Twitch</label><input type="url" id="twitch_link" name="twitch_link"></div>
                        <div class="form-group"><label>YouTube</label><input type="url" id="youtube_link" name="youtube_link"></div>
                    </div>
                </div>

                <div class="edit-form-container">
                    <h3><i class="fas fa-gamepad"></i> ゲーム設定</h3>
                    <div class="game-tabs">
                        <div class="game-tab active" onclick="switchTab('valorant')">VALORANT</div>
                        <div class="game-tab" onclick="switchTab('apex')">APEX</div>
                        <div class="game-tab" onclick="switchTab('lol')">LoL</div>
                    </div>
                    <input type="hidden" id="current_game_title" name="game_title" value="valorant">

                    <div id="form-valorant" class="game-form-section active">
                        <div class="edit-section">
                            <div class="form-group"><label>Riot ID</label><input type="text" name="ingame_name_valorant"></div>
                            <div class="form-group"><label>現在のランク</label><select name="current_rank_valorant" style="width:100%;padding:10px;background:rgba(255,255,255,0.05);border:1px solid #444;color:#fff;border-radius:6px;"><option value="">未設定</option><option value="Radiant">Radiant</option><option value="Immortal 3">Immortal 3</option><option value="Immortal 2">Immortal 2</option><option value="Immortal 1">Immortal 1</option><option value="Ascendant 3">Ascendant 3</option><option value="Ascendant 2">Ascendant 2</option><option value="Ascendant 1">Ascendant 1</option><option value="Diamond 3">Diamond 3</option><option value="Diamond 2">Diamond 2</option><option value="Diamond 1">Diamond 1</option><option value="Platinum 3">Platinum 3</option><option value="Platinum 2">Platinum 2</option><option value="Platinum 1">Platinum 1</option><option value="Gold 3">Gold 3</option><option value="Gold 2">Gold 2</option><option value="Gold 1">Gold 1</option><option value="Silver 3">Silver 3</option><option value="Silver 2">Silver 2</option><option value="Silver 1">Silver 1</option><option value="Bronze 3">Bronze 3</option><option value="Bronze 2">Bronze 2</option><option value="Bronze 1">Bronze 1</option><option value="Iron 3">Iron 3</option><option value="Iron 2">Iron 2</option><option value="Iron 1">Iron 1</option></select></div>
                            <div class="form-group"><label>最高ランク</label><input type="text" name="highest_rank_valorant" placeholder="例: Immortal 2"></div>
                            <div class="form-section-title">ロール・スタイル</div>
                            <div class="form-group"><label>IGL</label><div class="radio-group"><label class="radio-label"><input type="radio" name="igl_valorant" value="no" checked>不可</label><label class="radio-label"><input type="radio" name="igl_valorant" value="yes">可能</label></div></div>
                            <div class="form-group"><label>メインロール</label><div class="checkbox-grid radio-grid"><?php foreach(['Duelist','Initiator','Controller','Sentinel','Flex'] as $r) echo "<div class='cb-item'><input type='radio' name='main_role_valorant' id='vm_$r' value='$r'><label for='vm_$r'>$r</label></div>"; ?></div></div>
                            <div class="form-group"><label>サブロール</label><div class="checkbox-grid"><?php foreach(['Duelist','Initiator','Controller','Sentinel','Flex'] as $r) echo "<div class='cb-item'><input type='checkbox' name='sub_role_valorant[]' id='vs_$r' value='$r' class='sub-role-chk' data-game='valorant'><label for='vs_$r'>$r</label></div>"; ?></div></div>
                            <div class="form-group"><label>エージェント</label><div class="checkbox-grid"><?php foreach(['Jett','Raze','Reyna','Yoru','Phoenix','Neon','Iso','Sova','Fade','Skye','Breach','KAY/O','Gekko','Omen','Brimstone','Viper','Astra','Harbor','Clove','Sage','Cypher','Killjoy','Chamber','Deadlock','Vyse','Tejo'] as $a) echo "<div class='cb-item'><input type='checkbox' name='chara_valorant[]' id='vc_$a' value='$a'><label for='vc_$a'>$a</label></div>"; ?></div></div>
                        </div>
                    </div>

                    <div id="form-apex" class="game-form-section">
                        <div class="edit-section">
                            <div class="form-group"><label>EA ID</label><input type="text" name="ingame_name_apex"></div>
                            <div class="form-group">
                                <label>現在のランク</label>
                                <div class="rank-select-group">
                                    <select id="apex_rank_tier"><option value="">ランク帯</option><option value="Predator">Predator</option><option value="Master">Master</option><option value="Diamond">Diamond</option><option value="Platinum">Platinum</option><option value="Gold">Gold</option><option value="Silver">Silver</option><option value="Bronze">Bronze</option><option value="Rookie">Rookie</option></select>
                                    <select id="apex_rank_num"><option value="">-</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option></select>
                                    <input type="hidden" name="current_rank_apex" id="real_rank_apex">
                                </div>
                            </div>
                            <div class="form-group"><label>最高ランク</label><input type="text" name="highest_rank_apex"></div>
                            <div class="form-group"><label>IGL</label><div class="radio-group"><label class="radio-label"><input type="radio" name="igl_apex" value="no" checked>不可</label><label class="radio-label"><input type="radio" name="igl_apex" value="yes">可能</label></div></div>
                            <div class="form-group"><label>メインロール</label><div class="checkbox-grid radio-grid"><?php foreach(['Assault','Skirmisher','Recon','Support','Controller','Flex'] as $r) echo "<div class='cb-item'><input type='radio' name='main_role_apex' id='am_$r' value='$r'><label for='am_$r'>$r</label></div>"; ?></div></div>
                            <div class="form-group"><label>サブロール</label><div class="checkbox-grid"><?php foreach(['Assault','Skirmisher','Recon','Support','Controller','Flex'] as $r) echo "<div class='cb-item'><input type='checkbox' name='sub_role_apex[]' id='as_$r' value='$r' class='sub-role-chk' data-game='apex'><label for='as_$r'>$r</label></div>"; ?></div></div>
                            <div class="form-group"><label>レジェンド</label><div class="checkbox-grid"><?php foreach(['Wraith','Pathfinder','Horizon','Octane','Bloodhound','Gibraltar','Lifeline','Bangalore','Caustic','Mirage','Wattson','Crypto','Revenant','Loba','Rampart','Fuse','Valkyrie','Seer','Ash','Maggie','Newcastle','Vantage','Catalyst','Ballistic','Conduit','Alter'] as $a) echo "<div class='cb-item'><input type='checkbox' name='chara_apex[]' id='ac_$a' value='$a'><label for='ac_$a'>$a</label></div>"; ?></div></div>
                        </div>
                    </div>

                    <div id="form-lol" class="game-form-section">
                        <div class="edit-section">
                            <div class="form-group"><label>サモナーネーム</label><input type="text" name="ingame_name_lol"></div>
                            <div class="form-group">
                                <label>現在のランク</label>
                                <div class="rank-select-group">
                                    <select id="lol_rank_tier"><option value="">ランク帯</option><option value="Challenger">Challenger</option><option value="Grandmaster">Grandmaster</option><option value="Master">Master</option><option value="Diamond">Diamond</option><option value="Emerald">Emerald</option><option value="Platinum">Platinum</option><option value="Gold">Gold</option><option value="Silver">Silver</option><option value="Bronze">Bronze</option><option value="Iron">Iron</option></select>
                                    <select id="lol_rank_num"><option value="">-</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option></select>
                                    <input type="hidden" name="current_rank_lol" id="real_rank_lol">
                                </div>
                            </div>
                            <div class="form-group"><label>最高ランク</label><input type="text" name="highest_rank_lol"></div>
                            <div class="form-group"><label>IGL</label><div class="radio-group"><label class="radio-label"><input type="radio" name="igl_lol" value="no" checked>不可</label><label class="radio-label"><input type="radio" name="igl_lol" value="yes">可能</label></div></div>
                            <div class="form-group"><label>メインロール</label><div class="checkbox-grid radio-grid"><?php foreach(['Top','Jungle','Mid','ADC','Support','Flex'] as $r) echo "<div class='cb-item'><input type='radio' name='main_role_lol' id='lm_$r' value='$r'><label for='lm_$r'>$r</label></div>"; ?></div></div>
                            <div class="form-group"><label>サブロール</label><div class="checkbox-grid"><?php foreach(['Top','Jungle','Mid','ADC','Support','Flex'] as $r) echo "<div class='cb-item'><input type='checkbox' name='sub_role_lol[]' id='ls_$r' value='$r' class='sub-role-chk' data-game='lol'><label for='ls_$r'>$r</label></div>"; ?></div></div>
                            <div class="form-group"><label>チャンピオン</label><div class="checkbox-grid">
                                <?php 
                                    $lol_champs = ['Aatrox','Ahri','Akali','Akshan','Alistar','Amumu','Anivia','Annie','Aphelios','Ashe','Aurelion Sol','Azir','Bard','Bel\'Veth','Blitzcrank','Brand','Braum','Briar','Caitlyn','Camille','Cassiopeia','Cho\'Gath','Corki','Darius','Diana','Dr. Mundo','Draven','Ekko','Elise','Evelynn','Ezreal','Fiddlesticks','Fiora','Fizz','Galio','Gangplank','Garen','Gnar','Gragas','Graves','Gwen','Hecarim','Heimerdinger','Hwei','Illaoi','Irelia','Ivern','Janna','Jarvan IV','Jax','Jayce','Jhin','Jinx','K\'Sante','Kai\'Sa','Kalista','Karma','Karthus','Kassadin','Katarina','Kayle','Kayn','Kennen','Kha\'Zix','Kindred','Kled','Kog\'Maw','LeBlanc','Lee Sin','Leona','Lillia','Lissandra','Lucian','Lulu','Lux','Malphite','Malzahar','Maokai','Master Yi','Milio','Miss Fortune','Mordekaiser','Morgana','Naafiri','Nami','Nasus','Nautilus','Neeko','Nidalee','Nilah','Nocturne','Nunu & Willump','Olaf','Orianna','Ornn','Pantheon','Poppy','Pyke','Qiyana','Quinn','Rakan','Rammus','Rek\'Sai','Rell','Renata Glasc','Renekton','Rengar','Riven','Rumble','Ryze','Samira','Sejuani','Senna','Seraphine','Sett','Shaco','Shen','Shyvana','Singed','Sion','Sivir','Skarner','Smolder','Sona','Soraka','Swain','Sylas','Syndra','Tahm Kench','Taliyah','Talon','Taric','Teemo','Thresh','Tristana','Trundle','Tryndamere','Twisted Fate','Twitch','Udyr','Urgot','Varus','Vayne','Veigar','Vel\'Koz','Vex','Vi','Viego','Viktor','Vladimir','Volibear','Warwick','Wukong','Xayah','Xerath','Xin Zhao','Yasuo','Yone','Yorick','Yuumi','Zac','Zed','Zeri','Ziggs','Zilean','Zoe','Zyra'];
                                    foreach($lol_champs as $c) echo "<div class='cb-item'><input type='checkbox' name='chara_lol[]' id='lc_$c' value='$c'><label for='lc_$c'>$c</label></div>";
                                ?>
                            </div></div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top:20px;">変更を保存</button>
                </div>

                <div class="edit-form-container danger-zone">
                    <h3><i class="fas fa-users-slash"></i> 所属チームの管理</h3>
                    <div id="teamLeaveList">
                        <?php if (count($my_teams) > 0): ?>
                            <?php foreach($my_teams as $team): ?>
                                <div class="team-leave-item" data-game="<?php echo htmlspecialchars($team['game_title']); ?>">
                                    <div class="team-leave-info">
                                        <?php echo htmlspecialchars($team['team_name']); ?>
                                        <span class="team-leave-game"><?php echo strtoupper($team['game_title']); ?></span>
                                    </div>
                                    <button type="button" class="btn-leave" onclick="leaveTeam(<?php echo $team['id']; ?>)">脱退</button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color:#888;">所属しているチームはありません。</p>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="toast" class="toast"><i class="fas fa-check-circle"></i> <span>保存しました</span></div>

    <script src="js/edit.js?v=<?php echo time(); ?>"></script>
</body>
</html>