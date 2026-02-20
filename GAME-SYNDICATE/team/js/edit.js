// Team Edit Page JavaScript

class TeamEditManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupDeleteButton();
        this.setupEditForm();
    }

    setupDeleteButton() {
        const deleteBtn = document.getElementById('deleteTeamBtn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => this.deleteTeam());
        }
    }

    async deleteTeam() {
        // 二重確認
        if (!confirm('本当にチームを削除しますか？この操作は取り消せません。')) {
            return;
        }

        if (!confirm('最終確認: チームとすべてのデータが完全に削除されます。本当によろしいですか？')) {
            return;
        }

        const btn = document.getElementById('deleteTeamBtn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 削除中...';
        btn.classList.add('loading');

        try {
            const urlParams = new URLSearchParams(window.location.search);
            const teamId = urlParams.get('id');

            const formData = new FormData();
            formData.append('action', 'delete');

            const response = await fetch(`team_edit.php?id=${teamId}`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccessMessage('チームを削除しました');
                
                setTimeout(() => {
                    window.location.href = data.redirect || '../mypage/mypage.php';
                }, 1500);
            } else {
                this.showErrorMessage(data.message || 'エラーが発生しました');
                btn.innerHTML = originalText;
                btn.classList.remove('loading');
            }
        } catch (error) {
            console.error('Delete error:', error);
            this.showErrorMessage('通信エラーが発生しました');
            btn.innerHTML = originalText;
            btn.classList.remove('loading');
        }
    }

    setupEditForm() {
        const form = document.getElementById('editTeamForm');
        if (form) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
    }

    async handleSubmit(e) {
        e.preventDefault();

        const btn = e.target.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 保存中...';
        btn.classList.add('loading');

        try {
            const formData = new FormData(e.target);
            formData.append('action', 'update');

            const urlParams = new URLSearchParams(window.location.search);
            const teamId = urlParams.get('id');

            const response = await fetch(`team_edit.php?id=${teamId}`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccessMessage('変更を保存しました');
                
                setTimeout(() => {
                    window.location.href = `team_page.php?id=${teamId}`;
                }, 1500);
            } else {
                this.showErrorMessage(data.message || 'エラーが発生しました');
                btn.innerHTML = originalText;
                btn.classList.remove('loading');
            }
        } catch (error) {
            console.error('Submit error:', error);
            this.showErrorMessage('通信エラーが発生しました');
            btn.innerHTML = originalText;
            btn.classList.remove('loading');
        }
    }

    showSuccessMessage(message) {
        const toast = document.createElement('div');
        toast.className = 'success-message';
        toast.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    showErrorMessage(message) {
        const toast = document.createElement('div');
        toast.className = 'success-message';
        toast.style.background = 'linear-gradient(135deg, #ee5253, #ff6b6b)';
        toast.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

// アニメーション追加
const style = document.createElement('style');
style.textContent = `
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// 初期化
document.addEventListener('DOMContentLoaded', () => {
    new TeamEditManager();
});

// グローバル関数（既存のインラインコードとの互換性のため）
function deleteTeam() {
    const manager = new TeamEditManager();
    manager.deleteTeam();
}