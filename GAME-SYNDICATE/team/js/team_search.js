// --- ゲームデータ定義 ---
const GAME_DATA = {
    'valorant': {
        ranks: ['Radiant', 'Immortal 3', 'Immortal 2', 'Immortal 1', 'Ascendant 3', 'Ascendant 2', 'Ascendant 1', 'Diamond 3', 'Diamond 2', 'Diamond 1', 'Platinum 3', 'Platinum 2', 'Platinum 1', 'Gold 3', 'Gold 2', 'Gold 1', 'Silver 3', 'Silver 2', 'Silver 1', 'Bronze 3', 'Bronze 2', 'Bronze 1', 'Iron 3', 'Iron 2', 'Iron 1', 'Unrated'],
        roles: ['Duelist', 'Initiator', 'Controller', 'Sentinel', 'Flex'],
        characters: ['Jett', 'Raze', 'Reyna', 'Yoru', 'Phoenix', 'Neon', 'Iso', 'Sova', 'Fade', 'Skye', 'Breach', 'Kayo', 'Gekko', 'Omen', 'Brimstone', 'Viper', 'Astra', 'Harbor', 'Clove', 'Sage', 'Cypher', 'Killjoy', 'Chamber', 'Deadlock', 'Vyse']
    },
    'apex': {
        ranks: ['Predator', 'Master', 'Diamond 1', 'Diamond 2', 'Diamond 3', 'Diamond 4', 'Platinum 1', 'Platinum 2', 'Platinum 3', 'Platinum 4', 'Gold 1', 'Gold 2', 'Gold 3', 'Gold 4', 'Silver 1', 'Silver 2', 'Silver 3', 'Silver 4', 'Bronze 1', 'Bronze 2', 'Bronze 3', 'Bronze 4', 'Rookie 1', 'Rookie 2', 'Rookie 3', 'Rookie 4', 'Unrated'],
        roles: ['Assault', 'Skirmisher', 'Recon', 'Support', 'Controller'],
        characters: ['Bangalore', 'Fuse', 'Ash', 'Mad Maggie', 'Ballistic', 'Pathfinder', 'Wraith', 'Octane', 'Revenant', 'Horizon', 'Valkyrie', 'Alter', 'Bloodhound', 'Crypto', 'Seer', 'Vantage', 'Gibraltar', 'Lifeline', 'Mirage', 'Loba', 'Newcastle', 'Conduit', 'Caustic', 'Wattson', 'Rampart', 'Catalyst']
    },
    'lol': {
        ranks: ['Challenger', 'Grandmaster', 'Master', 'Diamond 1', 'Diamond 2', 'Diamond 3', 'Diamond 4', 'Emerald 1', 'Emerald 2', 'Emerald 3', 'Emerald 4', 'Platinum 1', 'Platinum 2', 'Platinum 3', 'Platinum 4', 'Gold 1', 'Gold 2', 'Gold 3', 'Gold 4', 'Silver 1', 'Silver 2', 'Silver 3', 'Silver 4', 'Bronze 1', 'Bronze 2', 'Bronze 3', 'Bronze 4', 'Iron 1', 'Iron 2', 'Iron 3', 'Iron 4', 'Unrated'],
        roles: ['Top', 'Jungle', 'Mid', 'ADC', 'Support'],
        characters: ['Aatrox', 'Ahri', 'Akali', 'Akshan', 'Alistar', 'Ambessa', 'Amumu', 'Anivia', 'Annie', 'Aphelios', 'Ashe', 'Aurelion Sol', 'Aurora', 'Azir', 'Bard', 'Bel\'Veth', 'Blitzcrank', 'Brand', 'Braum', 'Briar', 'Caitlyn', 'Camille', 'Cassiopeia', 'Cho\'Gath', 'Corki', 'Darius', 'Diana', 'Dr. Mundo', 'Draven', 'Ekko', 'Elise', 'Evelynn', 'Ezreal', 'Fiddlesticks', 'Fiora', 'Fizz', 'Galio', 'Gangplank', 'Garen', 'Gnar', 'Gragas', 'Graves', 'Gwen', 'Hecarim', 'Heimerdinger', 'Hwei', 'Illaoi', 'Irelia', 'Ivern', 'Janna', 'Jarvan IV', 'Jax', 'Jayce', 'Jhin', 'Jinx', 'K\'Sante', 'Kai\'Sa', 'Kalista', 'Karma', 'Karthus', 'Kassadin', 'Katarina', 'Kayle', 'Kayn', 'Kennen', 'Kha\'Zix', 'Kindred', 'Kled', 'Kog\'Maw', 'LeBlanc', 'Lee Sin', 'Leona', 'Lillia', 'Lissandra', 'Lucian', 'Lulu', 'Lux', 'Malphite', 'Malzahar', 'Maokai', 'Master Yi', 'Milio', 'Miss Fortune', 'Mordekaiser', 'Morgana', 'Naafiri', 'Nami', 'Nasus', 'Nautilus', 'Neeko', 'Nidalee', 'Nilah', 'Nocturne', 'Nunu & Willump', 'Olaf', 'Orianna', 'Ornn', 'Pantheon', 'Poppy', 'Pyke', 'Qiyana', 'Quinn', 'Rakan', 'Rammus', 'Rek\'Sai', 'Rell', 'Renata Glasc', 'Renekton', 'Rengar', 'Riven', 'Rumble', 'Ryze', 'Samira', 'Sejuani', 'Senna', 'Seraphine', 'Sett', 'Shaco', 'Shen', 'Shyvana', 'Singed', 'Sion', 'Sivir', 'Skarner', 'Smolder', 'Sona', 'Soraka', 'Swain', 'Sylas', 'Syndra', 'Tahm Kench', 'Taliyah', 'Talon', 'Taric', 'Teemo', 'Thresh', 'Tristana', 'Trundle', 'Tryndamere', 'Twisted Fate', 'Twitch', 'Udyr', 'Urgot', 'Varus', 'Vayne', 'Veigar', 'Vel\'Koz', 'Vex', 'Vi', 'Viego', 'Viktor', 'Vladimir', 'Volibear', 'Warwick', 'Wukong', 'Xayah', 'Xerath', 'Xin Zhao', 'Yasuo', 'Yone', 'Yorick', 'Yuumi', 'Zac', 'Zed', 'Zeri', 'Ziggs', 'Zilean', 'Zoe', 'Zyra']
    },
    'ow2': { ranks: [], roles: [], characters: [] },
    'other': { ranks: ['Unrated'], roles:[], characters:[] },
    'all': { ranks: ['Unrated'], roles:[], characters:[] }
};

document.addEventListener('DOMContentLoaded', () => {
    const game = document.getElementById('s_game');
    if (game && game.value) {
        updateSearchFilters(game.value);
        searchTeams(1);
    }
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.onclick = () => document.querySelectorAll('.modal').forEach(m => m.classList.remove('show'));
    });
});

function updateSearchFilters(gameKey) {
    const data = GAME_DATA[gameKey] || GAME_DATA['valorant'];
    
    const divSelect = document.getElementById('s_division');
    divSelect.innerHTML = '<option value="">指定なし</option>';
    data.ranks.forEach(r => divSelect.innerHTML += `<option value="${r.toLowerCase()}">${r}</option>`);

    const roleSelect = document.getElementById('s_role');
    roleSelect.innerHTML = '<option value="">指定なし</option>';
    if(data.roles) data.roles.forEach(r => roleSelect.innerHTML += `<option value="${r}">${r}</option>`);

    const charSelect = document.getElementById('s_character');
    charSelect.innerHTML = '<option value="">指定なし</option>';
    if(data.characters) data.characters.forEach(c => charSelect.innerHTML += `<option value="${c}">${c}</option>`);
}

async function searchTeams(page = 1) {
    const game = document.getElementById('s_game').value;
    const q = encodeURIComponent(document.getElementById('s_keyword').value);
    const div = encodeURIComponent(document.getElementById('s_division').value);
    
    // ★修正: 検索条件を小文字にして判定用に保持
    const searchRole = document.getElementById('s_role').value.toLowerCase();
    const searchChar = document.getElementById('s_character').value.toLowerCase();

    const list = document.getElementById('teamList');
    const pager = document.getElementById('paginationArea');
    
    list.innerHTML = '<div style="color:#888; text-align:center; grid-column:1/-1;">検索中...</div>';
    pager.innerHTML = '';

    try {
        const res = await fetch(`team_search.php?api=search&game=${game}&q=${q}&division=${div}&role=${encodeURIComponent(searchRole)}&character=${encodeURIComponent(searchChar)}&page=${page}`);
        const data = await res.json();
        list.innerHTML = '';
        
        if (!data.teams || data.teams.length === 0) {
            list.innerHTML = '<p style="color:#888; text-align:center; grid-column:1/-1; padding:30px;">条件に一致するチームは見つかりませんでした</p>';
            return;
        }

        data.teams.forEach(team => {
            const card = document.createElement('div');
            card.className = 'team-card';
            
            // ★修正: 画像パスに古いものがあれば補正。無ければプレースホルダー。
            let iconPath = team.team_icon ? team.team_icon.replace('../uploads/', 'uploads/') : '';
            let icon = iconPath !== '' ? iconPath : 'https://placehold.co/200x200/1a1f3a/ff0078?text=TEAM';
            const memberCount = parseInt(team.real_member_count) || 1;
            
            let infoTags = [];
            const wantedRoles = team.wanted_roles ? team.wanted_roles.split(',') : [];
            const wantedAgents = team.wanted_agents ? team.wanted_agents.split(',') : [];
            const memberComp = team.member_composition ? team.member_composition.split('|') : [];
            
            // 1. 募集ロール (検索にヒットすれば赤く光る)
            wantedRoles.forEach(r => {
                if(!r.trim()) return;
                const isHit = searchRole && r.toLowerCase().includes(searchRole);
                const style = isHit ? 'border: 1px solid #ff0078; box-shadow: 0 0 10px #ff0078; color: #fff; background: rgba(255,0,120,0.3);' : 'border: 1px solid rgba(255,0,120,0.5); color: #ff0078; background: rgba(255,0,120,0.1);';
                infoTags.push(`<span class="tag wanted" style="${style}"><i class="fas fa-bullhorn"></i> ${r}</span>`);
            });

            // 2. 募集エージェント (検索にヒットすれば赤く光る)
            wantedAgents.forEach(a => {
                if(!a.trim()) return;
                const isHit = searchChar && a.toLowerCase().includes(searchChar);
                const style = isHit ? 'border: 1px solid #ff0078; box-shadow: 0 0 10px #ff0078; color: #fff; background: rgba(255,0,120,0.3);' : 'border: 1px solid rgba(255,0,120,0.5); color: #ff0078; background: rgba(255,0,120,0.1);';
                infoTags.push(`<span class="tag wanted" style="${style}"><i class="fas fa-bullhorn"></i> ${a}</span>`);
            });

            // 3. 所属メンバーのエージェント (検索にヒットすれば緑に光る)
            if (memberComp && memberComp.length > 0) {
                memberComp.slice(0, 5).forEach(c => {
                    const parts = c.split(':');
                    const char = parts[1];
                    const role = parts[0];
                    if(char && char.trim()) {
                        const isHit = (searchChar && char.toLowerCase().includes(searchChar)) || (searchRole && role && role.toLowerCase().includes(searchRole));
                        const style = isHit ? 'border: 1px solid #00d26a; box-shadow: 0 0 10px #00d26a; color: #fff; background: rgba(0,210,106,0.3);' : 'background: rgba(255,255,255,0.05); color: #ccc; border: 1px solid #444;';
                        infoTags.push(`<span class="tag" style="${style}"><i class="fas fa-user"></i> ${char}</span>`);
                    }
                });
            }

            if(infoTags.length === 0) infoTags.push('<span class="tag">情報なし</span>');

            const infoTagsHtml = infoTags.join('');
            const safeTeamName = team.team_name.replace(/'/g, "\\'").replace(/"/g, '&quot;');
            
            const gameKey = team.game_title.toLowerCase();
            let actionBtn = '';
            if (data.my_team_ids && data.my_team_ids[gameKey]) {
                if (data.my_team_ids[gameKey] == team.id) {
                    actionBtn = `<span class="my-team-label">所属中</span>`;
                } else {
                    const safeTags = infoTagsHtml.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                    actionBtn = `<button class="btn-scrim" onclick="openScrim(${team.id}, '${safeTeamName}', '${safeTags}')"><i class="fas fa-handshake"></i> スクリム申請</button>`;
                }
            } else {
                if (memberCount < 10) {
                    const safeTags = infoTagsHtml.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                    actionBtn = `<button class="btn-join" onclick="openJoin(${team.id}, '${safeTeamName}', '${safeTags}')"><i class="fas fa-user-plus"></i> 加入申請</button>`;
                } else {
                    actionBtn = `<span class="full-label">満員</span>`;
                }
            }

            card.innerHTML = `
                <div class="team-visual">
                    <img src="${icon}" alt="icon" onerror="this.src='https://placehold.co/200x200/1a1f3a/ff0078?text=TEAM'">
                    <span class="rank-badge">${team.team_division ? team.team_division.toUpperCase() : 'UNRATED'}</span>
                </div>
                <div class="team-info">
                    <h3>${team.team_name}</h3>
                    <div class="team-meta">
                        <span><i class="fas fa-users"></i> ${memberCount}名</span>
                        <span><i class="fas fa-gamepad"></i> ${team.game_title.toUpperCase()}</span>
                    </div>
                    <div class="match-info">
                        <div class="tag-container">${infoTagsHtml}</div>
                    </div>
                    <div class="team-desc">${team.description || '紹介文なし'}</div>
                    <div class="team-actions">
                        ${actionBtn}
                        <a href="team_page.php?id=${team.id}" class="btn-detail">詳細</a>
                    </div>
                </div>
            `;
            list.appendChild(card);
        });

        if (data.pagination && data.pagination.total_pages > 1) {
            renderPagination(data.pagination);
        }

    } catch (e) { 
        console.error(e);
        list.innerHTML = '<p style="color:red; text-align:center; grid-column:1/-1;">エラーが発生しました</p>'; 
    }
}

function renderPagination(pg) {
    const pager = document.getElementById('paginationArea');
    let html = '';
    if(pg.current_page > 1) {
        html += `<button class="p-btn" onclick="searchTeams(${pg.current_page - 1})"><i class="fas fa-chevron-left"></i></button>`;
    }
    for(let i=1; i<=pg.total_pages; i++) {
        const active = i === pg.current_page ? 'active' : '';
        html += `<button class="p-btn ${active}" onclick="searchTeams(${i})">${i}</button>`;
    }
    if(pg.current_page < pg.total_pages) {
        html += `<button class="p-btn" onclick="searchTeams(${pg.current_page + 1})"><i class="fas fa-chevron-right"></i></button>`;
    }
    pager.innerHTML = html;
}

function openJoin(teamId, teamName, tagsHtml) {
    document.getElementById('applyTeamId').value = teamId;
    document.getElementById('applyTeamName').innerText = teamName;
    document.getElementById('wantedInfo').innerHTML = tagsHtml || '<span style="color:#888;">特になし</span>';
    document.getElementById('applyModal').classList.add('show');
}
function openScrim(teamId, teamName, tagsHtml) {
    document.getElementById('scrimTeamId').value = teamId;
    document.getElementById('scrimTeamName').innerText = teamName;
    document.getElementById('compositionInfo').innerHTML = tagsHtml || '<span style="color:#888;">情報なし</span>';
    document.getElementById('scrimModal').classList.add('show');
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type === 'error' ? 'error' : ''}`;
    toast.innerHTML = `<i class="fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i> <span>${message}</span>`;
    document.body.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('show'));
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 400); }, 3000);
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('show');
    }
}

function confirmAction(type) {
    if(!confirm('本当に送信しますか？')) return;
    submitRequest(type);
}

async function submitRequest(type) {
    const fd = new FormData();
    fd.append('api', 1);
    fd.append('type', type);

    if (type === 'join') {
        fd.append('team_id', document.getElementById('applyTeamId').value);
        fd.append('role', document.getElementById('applyRole').value);
        fd.append('message', document.getElementById('applyMessage').value);
    } else {
        fd.append('team_id', document.getElementById('scrimTeamId').value);
        fd.append('message', document.getElementById('scrimMessage').value);
    }

    try {
        const res = await fetch('team_search.php', { method: 'POST', body: fd });
        const data = await res.json();
        document.querySelectorAll('.modal').forEach(m => m.classList.remove('show'));
        
        if (data.success) {
            showToast('申請を送信しました！');
        } else {
            showToast(data.message || 'エラーが発生しました', 'error');
        }
    } catch (e) {
        showToast('通信エラーが発生しました', 'error');
    }
}