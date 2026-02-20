<?php
$is_existing = ($mode === 'existing');
$title_text = $is_existing ? "チームサイト作成" : "新規チーム立ち上げ";
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title_text; ?> - GAME SYNDICATE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/team.css">
    <link rel="stylesheet" href="css/form.css">
    <style>
        .dynamic-list { margin-bottom: 15px; }
        .dynamic-item { display: flex; gap: 10px; margin-bottom: 10px; align-items: center; background: rgba(255,255,255,0.05); padding: 10px; border-radius: 6px; }
        .search-results { position: absolute; background: #1a1f3a; width: 100%; border: 1px solid #444; max-height: 150px; overflow-y: auto; z-index: 10; display: none; }
        .search-item { padding: 10px; cursor: pointer; display: flex; align-items: center; gap: 10px; }
        .search-item:hover { background: #ff0078; }
        .search-item img { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; }
        
        .btn-add { background: #444; border: 1px dashed #aaa; width: 100%; padding: 8px; color: #ccc; cursor: pointer; }
        .btn-add:hover { background: #555; color: #fff; }
        .btn-remove { background: #ff4444; color: #fff; border:none; border-radius:4px; padding:5px 10px; cursor:pointer; }

        /* おすすめユーザー表示用 */
        .recommended-users { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 10px; margin-bottom: 10px; }
        .rec-user { min-width: 60px; text-align: center; cursor: pointer; opacity: 0.7; transition: 0.2s; }
        .rec-user:hover { opacity: 1; transform: translateY(-2px); }
        .rec-user img { width: 40px; height: 40px; border-radius: 50%; border: 2px solid #444; object-fit: cover; }
        .rec-user span { display: block; font-size: 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 60px; }
    </style>
</head>
<body>
    <div class="team-background">
        <div class="bg-circle circle-1"></div>
        <div class="bg-circle circle-2"></div>
    </div>
    <header class="team-header">
        <div class="header-content">
            <a href="../top/top.php" class="logo"><i class="fas fa-rocket"></i> GAME SYNDICATE</a>
            <nav class="header-nav"><a href="team_create.php" class="nav-link active">戻る</a></nav>
        </div>
    </header>
    <div class="container">
        <section class="team-hero"><h1 class="hero-title"><?php echo $title_text; ?></h1></section>
        <div class="team-main">
            <section class="team-form-section">
                <form id="teamCreateForm" onsubmit="handleCreate(event)">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); ?>">
                    <input type="hidden" name="members_data" id="membersData">

                    <div class="form-group">
                        <label>ゲームタイトル <span class="required">*</span></label>
                        <select name="game_title" id="gameSelect" onchange="updateRanks()" required>
                            <option value="valorant">VALORANT</option>
                            <option value="apex">Apex Legends</option>
                            <option value="lol">League of Legends</option>
                            <option value="ow2">Overwatch 2</option>
                        </select>
                        <p style="font-size:0.8rem; color:#ff4444; margin-top:5px;">※1つのゲームにつき、1つのチームにしか所属できません。</p>
                    </div>
                    
                    <div class="form-group">
                        <label>チーム名 <span class="required">*</span></label>
                        <input type="text" name="team_name" placeholder="チーム名" required>
                    </div>
                    
                    <div class="form-group">
                        <label>平均ランク帯</label>
                        <select name="team_division" id="rankSelect">
                            <option value="unrated">未設定</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>活動時間</label>
                        <div id="timeList" class="dynamic-list"></div>
                        <button type="button" class="btn-add" onclick="addTimeSlot()">+ 時間枠を追加</button>
                    </div>

                    <div class="form-group">
                        <label>メンバー招待 (最大7名)</label>
                        
                        <div style="font-size:0.8rem; color:#aaa; margin-bottom:5px;">おすすめユーザー (クリックで追加)</div>
                        <div id="recUsers" class="recommended-users"></div>

                        <div style="position:relative;">
                            <input type="text" id="userSearchInput" placeholder="アカウントID (@...) で検索" autocomplete="off">
                            <div id="searchResults" class="search-results"></div>
                        </div>

                        <div id="memberList" class="dynamic-list" style="margin-top:10px;"></div>
                        
                        <textarea name="invite_message" rows="2" placeholder="招待メンバーへのメッセージ（任意） 例: 一緒に大会目指そう！" style="margin-top:10px; font-size:0.9rem;"></textarea>
                    </div>

                    <div class="form-group">
                        <label><?php echo $is_existing ? 'チーム紹介' : '募集メッセージ'; ?></label>
                        <textarea name="description" rows="5"></textarea>
                    </div>

                    <div class="form-row" style="display:flex; gap:15px;">
                        <div class="form-group" style="flex:1;"><label>アイコン</label><input type="file" name="team_icon"></div>
                        <div class="form-group" style="flex:1;"><label>ヘッダー画像</label><input type="file" name="header_image"></div>
                    </div>

                    <button type="submit" class="btn btn-primary">作成する</button>
                </form>
            </section>
        </div>
    </div>

    <script>
        // --- ランクデータ定義 ---
        const RANK_DATA = {
            'valorant': ['Radiant','Immortal','Ascendant','Diamond','Platinum','Gold','Silver','Bronze','Iron','Unrated'],
            'apex': ['Predator','Master','Diamond','Platinum','Gold','Silver','Bronze','Rookie','Unrated'],
            'lol': ['Challenger','Grandmaster','Master','Diamond','Emerald','Platinum','Gold','Silver','Bronze','Iron','Unrated'],
            'ow2': ['Top500','Grandmaster','Master','Diamond','Platinum','Gold','Silver','Bronze','Unrated']
        };

        // --- ランク選択肢更新 ---
        function updateRanks() {
            const game = document.getElementById('gameSelect').value;
            const select = document.getElementById('rankSelect');
            select.innerHTML = '';
            
            const ranks = RANK_DATA[game] || RANK_DATA['valorant'];
            ranks.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.toLowerCase();
                opt.innerText = r;
                select.appendChild(opt);
            });
        }

        // --- おすすめユーザー取得 ---
        fetch('team_create.php?api=get_recommended_users')
            .then(r => r.json())
            .then(users => {
                const container = document.getElementById('recUsers');
                if(users.length === 0) { container.style.display = 'none'; return; }
                users.forEach(u => {
                    const div = document.createElement('div');
                    div.className = 'rec-user';
                    div.innerHTML = `<img src="${u.user_icon || '../img/default.png'}"><span>${u.name}</span>`;
                    div.onclick = () => addMember(u);
                    container.appendChild(div);
                });
            });

        // --- メンバー管理 ---
        let members = [];
        const searchInput = document.getElementById('userSearchInput');
        const searchResults = document.getElementById('searchResults');

        searchInput.addEventListener('input', function() {
            const query = this.value;
            if(query.length < 2) { searchResults.style.display='none'; return; }
            fetch(`team_create.php?api=search_user&q=${query}`).then(r=>r.json()).then(users=>{
                searchResults.innerHTML = '';
                if(users.length > 0) {
                    searchResults.style.display = 'block';
                    users.forEach(u => {
                        const div = document.createElement('div');
                        div.className = 'search-item';
                        div.innerHTML = `<img src="${u.user_icon || '../img/default.png'}"> <span>${u.name} (${u.account_id})</span>`;
                        div.onclick = () => addMember(u);
                        searchResults.appendChild(div);
                    });
                } else searchResults.style.display = 'none';
            });
        });

        function addMember(user) {
            if(members.length >= 7) { alert('最大7名までです'); return; }
            if(members.find(m => m.id === user.id)) { alert('既に追加されています'); return; }
            members.push({ id: user.id, name: user.name, role: 'roster' });
            renderMembers();
            searchInput.value = ''; searchResults.style.display = 'none';
        }
        function removeMember(idx) { members.splice(idx, 1); renderMembers(); }
        function updateRole(idx, val) { members[idx].role = val; renderMembers(); }
        function renderMembers() {
            const list = document.getElementById('memberList'); list.innerHTML = '';
            members.forEach((m, idx) => {
                list.innerHTML += `
                    <div class="dynamic-item">
                        <span style="flex:1; font-weight:bold;">${m.name}</span>
                        <select onchange="updateRole(${idx},this.value)" style="width:100px; background:#222; color:#fff; border:1px solid #555; padding:5px;">
                            <option value="roster">Roster</option>
                            <option value="sub">Sub</option>
                            <option value="coach">Coach</option>
                            <option value="analyst">Analyst</option>
                            <option value="manager">Manager</option>
                        </select>
                        <button type="button" class="btn-remove" onclick="removeMember(${idx})">×</button>
                    </div>`;
            });
            document.getElementById('membersData').value = JSON.stringify(members);
        }

        // --- 時間枠追加 ---
        function addTimeSlot() {
            document.getElementById('timeList').insertAdjacentHTML('beforeend', `<div class="dynamic-item"><select name="activity_times[]" style="width:30%; background:#222; color:#fff; border:1px solid #555;"><option value="平日">平日</option><option value="土日">土日</option><option value="毎日">毎日</option></select><input type="text" name="activity_times[]" placeholder="21:00-24:00" style="flex:1;"><button type="button" class="btn-remove" onclick="this.parentElement.remove()">×</button></div>`);
        }

        // --- 送信処理 ---
        function handleCreate(e) {
            e.preventDefault();
            const timeItems = document.querySelectorAll('#timeList .dynamic-item');
            const times = [];
            timeItems.forEach(item => {
                const sel = item.querySelector('select').value;
                const inp = item.querySelector('input').value;
                if(inp) times.push(`${sel} ${inp}`);
            });
            
            const formData = new FormData(e.target);
            formData.delete('activity_times[]'); 
            times.forEach(t => formData.append('activity_times[]', t));

            const btn = e.target.querySelector('button[type="submit"]');
            btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 作成中...';

            fetch('team_create.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if(data.success) { alert(data.message); window.location.href = `team_page.php?id=${data.team_id}`; }
                else { alert(data.message); btn.disabled = false; btn.innerHTML = '作成する'; }
            }).catch(e => { alert('通信エラーが発生しました'); btn.disabled = false; btn.innerHTML = '作成する'; });
        }

        // 初期化
        addTimeSlot();
        updateRanks(); // 初期ランクセット
    </script>
</body>
</html>