// Team Form Page JavaScript

class TeamFormManager {
    constructor() {
        this.members = [];
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.addTimeSlot(); // 初期の時間枠を追加
    }

    setupEventListeners() {
        // ユーザー検索
        const searchInput = document.getElementById('userSearchInput');
        const searchResults = document.getElementById('searchResults');

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.handleSearch(e.target.value);
            });

            // 検索結果外をクリックで閉じる
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.form-group') || e.target !== searchInput) {
                    searchResults.style.display = 'none';
                }
            });
        }

        // フォーム送信
        const form = document.getElementById('teamCreateForm');
        if (form) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
    }

    async handleSearch(query) {
        const searchResults = document.getElementById('searchResults');
        
        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`team_create.php?api=search_user&q=${encodeURIComponent(query)}`);
            const users = await response.json();

            searchResults.innerHTML = '';
            
            if (users.length > 0) {
                searchResults.style.display = 'block';
                users.forEach(user => {
                    const div = document.createElement('div');
                    div.className = 'search-item';
                    div.innerHTML = `
                        <img src="${user.user_icon || '../img/default.png'}" alt="${user.name}">
                        <span>${user.name} (${user.account_id})</span>
                    `;
                    div.onclick = () => this.addMember(user);
                    searchResults.appendChild(div);
                });
            } else {
                searchResults.style.display = 'none';
            }
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    addMember(user) {
        if (this.members.length >= 7) {
            this.showToast('最大7人までです', 'error');
            return;
        }

        if (this.members.find(m => m.id === user.id)) {
            this.showToast('既に追加されています', 'error');
            return;
        }

        this.members.push({
            id: user.id,
            name: user.name,
            role: 'roster'
        });

        this.renderMembers();
        
        // 検索フィールドをクリア
        const searchInput = document.getElementById('userSearchInput');
        const searchResults = document.getElementById('searchResults');
        if (searchInput) searchInput.value = '';
        if (searchResults) searchResults.style.display = 'none';
    }

    removeMember(index) {
        this.members.splice(index, 1);
        this.renderMembers();
    }

    updateRole(index, role) {
        this.members[index].role = role;
        this.renderMembers();
    }

    renderMembers() {
        const memberList = document.getElementById('memberList');
        if (!memberList) return;

        memberList.innerHTML = '';
        
        this.members.forEach((member, index) => {
            const div = document.createElement('div');
            div.className = 'dynamic-item';
            div.innerHTML = `
                <span style="flex:1; color: #fff;">${this.escapeHtml(member.name)}</span>
                <select onchange="teamFormManager.updateRole(${index}, this.value)" style="width:100px;">
                    <option value="roster" ${member.role === 'roster' ? 'selected' : ''}>Roster</option>
                    <option value="sub" ${member.role === 'sub' ? 'selected' : ''}>Sub</option>
                    <option value="coach" ${member.role === 'coach' ? 'selected' : ''}>Coach</option>
                </select>
                <button type="button" class="btn-remove" onclick="teamFormManager.removeMember(${index})">×</button>
            `;
            memberList.appendChild(div);
        });

        // Hidden fieldを更新
        document.getElementById('membersData').value = JSON.stringify(this.members);
    }

    addTimeSlot() {
        const timeList = document.getElementById('timeList');
        if (!timeList) return;

        const div = document.createElement('div');
        div.className = 'dynamic-item';
        div.innerHTML = `
            <select name="activity_times[]" style="width:30%; min-width:100px;">
                <option value="平日">平日</option>
                <option value="土日">土日</option>
                <option value="毎日">毎日</option>
            </select>
            <input type="text" name="activity_times[]" placeholder="例: 21:00-24:00" style="flex:1;">
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()">×</button>
        `;
        timeList.appendChild(div);
    }

    async handleSubmit(e) {
        e.preventDefault();

        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 処理中...';

        try {
            // 活動時間の収集
            const timeItems = document.querySelectorAll('#timeList .dynamic-item');
            const times = [];
            timeItems.forEach(item => {
                const select = item.querySelector('select');
                const input = item.querySelector('input');
                if (select && input && input.value) {
                    times.push(`${select.value} ${input.value}`);
                }
            });

            // FormDataの作成
            const formData = new FormData(e.target);
            
            // 既存のactivity_times[]を削除
            formData.delete('activity_times[]');
            
            // 新しい活動時間を追加
            times.forEach(time => {
                formData.append('activity_times[]', time);
            });

            // 送信
            const response = await fetch('team_create.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showToast(data.message || 'チームを作成しました');
                setTimeout(() => {
                    window.location.href = `team_page.php?id=${data.team_id}`;
                }, 1000);
            } else {
                this.showToast(data.message || 'エラーが発生しました', 'error');
                btn.disabled = false;
                btn.innerHTML = '作成する';
            }
        } catch (error) {
            console.error('Submit error:', error);
            this.showToast('通信エラーが発生しました', 'error');
            btn.disabled = false;
            btn.innerHTML = '作成する';
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.textContent = message;
        
        const bgColor = type === 'success' 
            ? 'linear-gradient(135deg, #2ed573, #26d07c)' 
            : 'linear-gradient(135deg, #ee5253, #ff6b6b)';
        
        toast.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: ${bgColor};
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            z-index: 10000;
            animation: slideUp 0.3s ease;
            font-weight: 500;
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideDown 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

// アニメーション定義
const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        from {
            transform: translateY(100px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    @keyframes slideDown {
        from {
            transform: translateY(0);
            opacity: 1;
        }
        to {
            transform: translateY(100px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// グローバル変数として定義（インラインのonclick属性から呼び出すため）
let teamFormManager;

// 初期化
document.addEventListener('DOMContentLoaded', () => {
    teamFormManager = new TeamFormManager();
});

// グローバル関数（既存のインラインコードとの互換性のため）
function addTimeSlot() {
    teamFormManager.addTimeSlot();
}