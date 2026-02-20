let profileData = {};

document.addEventListener('DOMContentLoaded', () => {
    loadData();
    
    // サブロールの選択数制限
    document.querySelectorAll('.sub-role-chk').forEach(cb => {
        cb.addEventListener('change', function() {
            const game = this.dataset.game;
            const checked = document.querySelectorAll(`input[name="sub_role_${game}[]"]:checked`);
            if (checked.length > 3) { this.checked = false; alert('サブロールは3つまでです'); }
        });
    });

    // ランク結合イベント (APEX/LoL)
    ['apex', 'lol'].forEach(g => {
        const tier = document.getElementById(`${g}_rank_tier`);
        const num = document.getElementById(`${g}_rank_num`);
        if(tier && num) {
            const combine = () => {
                const tVal = tier.value;
                const nVal = num.value;
                const real = document.getElementById(`real_rank_${g}`);
                if(['Predator','Master','Challenger','Grandmaster'].includes(tVal)) {
                    real.value = tVal;
                } else if(tVal && nVal) {
                    real.value = `${tVal} ${nVal}`;
                } else {
                    real.value = tVal;
                }
            };
            tier.addEventListener('change', combine);
            num.addEventListener('change', combine);
        }
    });
});

async function loadData() {
    try {
        const res = await fetch('edit_profile.php?api=get_profile');
        const data = await res.json();
        profileData = data;
        const basic = data.basic;
        
        if(basic) {
            const setVal = (id, v) => { if(document.getElementById(id)) document.getElementById(id).value = v || ''; };
            setVal('name', basic.name); 
            setVal('account_id', basic.account_id); 
            setVal('mailadress', basic.mailadress);
            setVal('birthday', basic.birthday); 
            setVal('x_link', basic.x_link);
            setVal('twitch_link', basic.twitch_link); 
            setVal('youtube_link', basic.youtube_link);
            setVal('discord_id', basic.discord_id);
        }
        
        // 各ゲームのデータをフォームに反映
        fillGameForm('valorant'); 
        fillGameForm('apex'); 
        fillGameForm('lol');
        
        // チーム一覧のフィルタリング
        filterTeams('valorant');
    } catch(e) {}
}

function fillGameForm(game) {
    const gData = profileData.games && profileData.games[game] ? profileData.games[game] : {};
    
    // テキスト入力
    const setGVal = (name, val) => { const el = document.querySelector(`[name="${name}"]`); if(el) el.value = val || ''; };
    setGVal(`ingame_name_${game}`, gData.ingame_name);
    setGVal(`highest_rank_${game}`, gData.highest_rank);

    // ランク
    if(game === 'valorant') {
        setGVal(`current_rank_${game}`, gData.current_rank);
    } else if(gData.current_rank) {
        // Apex/LoLのランク分解
        const parts = gData.current_rank.split(' ');
        const tier = document.getElementById(`${game}_rank_tier`);
        const num = document.getElementById(`${game}_rank_num`);
        if(tier) tier.value = parts[0];
        if(num && parts[1]) num.value = parts[1];
        document.getElementById(`real_rank_${game}`).value = gData.current_rank;
    }

    // ラジオボタン (IGL, MainRole)
    const setRadio = (name, val) => { const r = document.querySelector(`input[name="${name}"][value="${val}"]`); if(r) r.checked = true; };
    setRadio(`igl_${game}`, gData.igl);
    setRadio(`main_role_${game}`, gData.main_role);

    // チェックボックス (SubRole, Character)
    const setChecks = (name, valStr) => {
        if(!valStr) return;
        valStr.split(',').forEach(v => {
            const cb = document.querySelector(`input[name="${name}[]"][value="${v}"]`);
            if(cb) cb.checked = true;
        });
    };
    setChecks(`sub_role_${game}`, gData.sub_role);
    setChecks(`chara_${game}`, gData.main_character);
}

window.switchTab = function(game) {
    document.querySelectorAll('.game-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.game-form-section').forEach(s => s.classList.remove('active'));
    
    const tabs = document.querySelectorAll('.game-tab');
    if(game==='valorant') tabs[0].classList.add('active');
    if(game==='apex') tabs[1].classList.add('active');
    if(game==='lol') tabs[2].classList.add('active');
    
    document.getElementById(`form-${game}`).classList.add('active');
    document.getElementById('current_game_title').value = game;
    
    filterTeams(game);
};

function filterTeams(game) {
    document.querySelectorAll('.team-leave-item').forEach(item => {
        if(item.dataset.game === game) item.classList.add('visible');
        else item.classList.remove('visible');
    });
}

window.saveProfile = async function(e) {
    e.preventDefault();
    const form = document.getElementById('profileForm');
    const fd = new FormData(form);
    const game = document.getElementById('current_game_title').value;
    
    // 画像
    const fileInput = document.getElementById('user_icon');
    if(fileInput.files.length > 0) fd.set('user_icon', fileInput.files[0]);

    // そのゲームの入力値を取得
    const getVal = (name) => { const el = document.querySelector(`[name="${name}"]:checked`); return el ? el.value : ''; };
    const getChecks = (name) => Array.from(document.querySelectorAll(`input[name="${name}[]"]:checked`)).map(c=>c.value);

    // 明示的にキーを指定して追加（PHP側で受け取るため）
    fd.append('ingame_name', document.querySelector(`[name="ingame_name_${game}"]`)?.value || '');
    fd.append('highest_rank', document.querySelector(`[name="highest_rank_${game}"]`)?.value || '');
    
    if(game === 'valorant') {
        fd.append('current_rank', document.querySelector(`[name="current_rank_${game}"]`)?.value || '');
    } else {
        fd.append('current_rank', document.getElementById(`real_rank_${game}`)?.value || '');
    }

    fd.append('igl', getVal(`igl_${game}`));
    fd.append('main_role', getVal(`main_role_${game}`));
    
    // 配列データを個別にappend
    const subRoles = getChecks(`sub_role_${game}`);
    subRoles.forEach(v => fd.append('sub_role[]', v));
    
    const chars = getChecks(`chara_${game}`);
    chars.forEach(v => fd.append('main_character[]', v));

    try {
        const res = await fetch('edit_profile.php?api=save_profile', { method:'POST', body:fd });
        const d = await res.json();
        const toast = document.getElementById('toast');
        
        if(d.success) {
            toast.className = 'toast show';
            toast.querySelector('span').innerText = '保存しました！';
            setTimeout(() => window.location.href = 'mypage.php', 1500);
        } else {
            toast.className = 'toast error show';
            toast.querySelector('span').innerText = d.message;
        }
        setTimeout(() => toast.classList.remove('show'), 3000);
    } catch(e) { 
        alert('通信エラーが発生しました'); 
    }
};

window.leaveTeam = async function(teamId) {
    if(!confirm('本当に脱退しますか？')) return;
    const fd = new FormData(); fd.append('team_id', teamId);
    try {
        const res = await fetch('edit_profile.php?api=leave_team', { method:'POST', body:fd });
        const d = await res.json();
        if(d.success) location.reload();
    } catch(e) {}
};