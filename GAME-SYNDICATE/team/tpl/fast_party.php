<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>今すぐフルパ - GAME SYNDICATE</title>
    <script src="https://kit.fontawesome.com/659df936c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../mypage/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/search.css?v=<?php echo time(); ?>">
    <style>
        .party-container { max-width: 700px; margin: 80px auto; padding: 40px; background: #1a1f3a; border-radius: 16px; border: 1px solid rgba(255,255,255,0.1); }
        .party-title { text-align: center; margin-bottom: 20px; font-size: 2.2rem; color: #ff00cc; font-weight: 900; }
        .party-desc { text-align: center; color: #aaa; margin-bottom: 40px; line-height: 1.6; }
        
        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; color: #fff; margin-bottom: 10px; font-weight: bold; font-size: 1.1rem; }
        .form-group select, .form-group textarea { width: 100%; padding: 15px; background: #0a0e27; border: 1px solid #444; color: #fff; border-radius: 8px; font-size: 1rem; }
        
        /* ランク複数選択用 (チェックボックスグリッド) */
        .rank-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; }
        .rank-checkbox { display: none; }
        .rank-label { 
            display: block; padding: 12px; background: #0a0e27; border: 1px solid #444; border-radius: 8px; 
            color: #ccc; text-align: center; cursor: pointer; transition: 0.2s; font-weight: bold;
        }
        .rank-checkbox:checked + .rank-label { background: #ff00cc; color: #fff; border-color: #ff00cc; box-shadow: 0 0 10px rgba(255,0,204,0.4); }
        
        .btn-party { 
            width: 100%; background: linear-gradient(135deg, #ff00cc 0%, #333399 100%); color: #fff; 
            padding: 18px; border: none; border-radius: 50px; font-weight: 900; font-size: 1.3rem; 
            cursor: pointer; margin-top: 30px; box-shadow: 0 5px 20px rgba(255, 0, 204, 0.4); transition: 0.3s;
        }
        .btn-party:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(255, 0, 204, 0.6); }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-left">
            <a href="../top/top.php" class="logo-link"><i class="fas fa-rocket"></i> GAME SYNDICATE</a>
        </div>
        <div class="header-right">
            <a href="../top/top.php" class="nav-item">Topへ戻る</a>
        </div>
    </header>

    <div class="main-wrapper">
        <div class="party-container fade-in">
            <h1 class="party-title"><i class="fas fa-bolt"></i> FAST PARTY</h1>
            <p class="party-desc">
                ゲームとランクを選んでボタンを押すだけ。<br>
                条件に合う募集があれば即参加、なければ自動で部屋を作成してメンバーを待ちます。
            </p>

            <div class="form-group">
                <label>1. ゲームタイトル</label>
                <select id="p_game" onchange="updateRanks()">
                    <option value="valorant">VALORANT (5人)</option>
                    <option value="apex">APEX LEGENDS (3人)</option>
                    <option value="lol">League of Legends (5人)</option>
                    <option value="ow2">Overwatch 2 (5人)</option>
                </select>
            </div>

            <div class="form-group">
                <label>2. 希望ランク帯 (複数選択可)</label>
                <div id="rankArea" class="rank-grid">
                    </div>
            </div>

            <div class="form-group">
                <label>3. ひとことコメント (ホストになった場合のみ表示)</label>
                <textarea id="p_desc" rows="2" placeholder="例：VCありで楽しくやりましょう！初心者歓迎です。"></textarea>
            </div>

            <button class="btn-party" onclick="startFastParty()">
                <i class="fas fa-search"></i> マッチング開始
            </button>
        </div>
    </div>

    <script>
        const RANKS = {
            'valorant': ['Iron', 'Bronze', 'Silver', 'Gold', 'Platinum', 'Diamond', 'Ascendant', 'Immortal', 'Radiant'],
            'apex': ['Rookie', 'Bronze', 'Silver', 'Gold', 'Platinum', 'Diamond', 'Master', 'Predator'],
            'lol': ['Iron', 'Bronze', 'Silver', 'Gold', 'Platinum', 'Emerald', 'Diamond', 'Master', 'Grandmaster', 'Challenger'],
            'ow2': ['Bronze', 'Silver', 'Gold', 'Platinum', 'Diamond', 'Master', 'Grandmaster', 'Champion']
        };

        function updateRanks() {
            const game = document.getElementById('p_game').value;
            const area = document.getElementById('rankArea');
            area.innerHTML = '';

            // 「指定なし」オプション
            area.innerHTML += `
                <div>
                    <input type="checkbox" id="rank_any" class="rank-checkbox" value="any" checked>
                    <label for="rank_any" class="rank-label">指定なし / Any</label>
                </div>
            `;

            const list = RANKS[game] || RANKS['valorant'];
            list.forEach(r => {
                const id = 'rank_' + r.toLowerCase();
                area.innerHTML += `
                    <div>
                        <input type="checkbox" id="${id}" class="rank-checkbox" value="${r}">
                        <label for="${id}" class="rank-label">${r}</label>
                    </div>
                `;
            });
        }

        async function startFastParty() {
            const game = document.getElementById('p_game').value;
            const desc = document.getElementById('p_desc').value;
            
            // 選択されたランクを取得
            const checkboxes = document.querySelectorAll('.rank-checkbox:checked');
            let selectedRanks = [];
            checkboxes.forEach(cb => selectedRanks.push(cb.value));

            if(selectedRanks.length === 0) {
                alert('ランクを少なくとも1つ選択してください（指定なしでも可）');
                return;
            }

            const fd = new FormData();
            fd.append('api', 'start_match');
            fd.append('game', game);
            selectedRanks.forEach(r => fd.append('ranks[]', r));
            fd.append('description', desc);

            try {
                const res = await fetch('fast_party.php', { method: 'POST', body: fd });
                const data = await res.json();
                
                if(data.success) {
                    if(data.role === 'member') {
                        alert('条件に合う部屋が見つかりました！参加します。');
                    } else {
                        alert('新しい部屋を作成しました。メンバーを待ちましょう。');
                    }
                    window.location.href = `party_room.php?room_id=${data.room_id}`;
                } else {
                    alert(data.message);
                    if(data.message.includes('ログイン')) window.location.href = '../mypage/login.php';
                }
            } catch(e) {
                alert('エラーが発生しました');
            }
        }

        // 初期化
        updateRanks();
    </script>
</body>
</html>