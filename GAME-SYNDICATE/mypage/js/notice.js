document.addEventListener('DOMContentLoaded', () => {
    loadNotices();
    
    // タブ切り替え
    document.querySelectorAll('.tab').forEach(btn => {
        btn.addEventListener('click', (e) => {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            e.target.classList.add('active');
            loadNotices(e.target.dataset.filter);
        });
    });

    // モーダル閉じる
    document.querySelector('.close-modal').onclick = () => {
        document.getElementById('detailModal').classList.remove('show');
    };
    
    // チャット送信
    document.getElementById('btnSendChat').onclick = sendMessage;
});

let allNotices = [];
let currentNoticeId = 0;
// ★修正: HTML側で定義した変数を使用
const currentUserId = window.currentUserId; 

async function loadNotices(filter = 'all') {
    const list = document.getElementById('noticeList');
    list.innerHTML = '<div style="text-align:center;color:#888;padding:20px;">読み込み中...</div>';

    try {
        const res = await fetch('notice.php?api=get_notices');
        const data = await res.json();
        
        if (!Array.isArray(data)) {
            list.innerHTML = '<div class="empty">お知らせはありません</div>';
            return;
        }

        allNotices = data;
        renderList(filter);

    } catch (e) {
        list.innerHTML = '<div class="empty">通信エラーが発生しました</div>';
    }
}

function renderList(filter) {
    const list = document.getElementById('noticeList');
    list.innerHTML = '';

    let filtered = allNotices;
    if (filter === 'unread') filtered = allNotices.filter(n => n.is_read == 0);
    if (filter === 'join') filtered = allNotices.filter(n => n.type && (n.type.includes('join') || n.type.includes('scrim')));

    if (filtered.length === 0) {
        list.innerHTML = '<div style="text-align:center;padding:30px;color:#888;">お知らせはありません</div>';
        return;
    }

    filtered.forEach(n => {
        const item = document.createElement('div');
        item.className = `notice-item ${n.is_read == 0 ? 'unread' : ''}`;
        
        let icon = '<i class="fas fa-info-circle"></i>';
        if(n.type && n.type.includes('join')) icon = '<i class="fas fa-user-plus" style="color:#ff0078;"></i>';
        if(n.type && n.type.includes('scrim')) icon = '<i class="fas fa-handshake" style="color:#4a9eff;"></i>';

        // ★修正: 「詳細を見る」ボタンを追加
        item.innerHTML = `
            <div class="notice-main" onclick="openDetailById(${n.id})">
                <div class="n-title">${icon} ${n.title} <span style="font-size:0.8rem;color:#888;margin-left:10px;">${n.created_at}</span></div>
                <div class="n-preview">${n.message.substring(0, 40)}...</div>
            </div>
            <button class="btn-open-detail" onclick="openDetailById(${n.id})">詳細</button>
        `;
        list.appendChild(item);
    });
}

// IDから詳細を開く関数
window.openDetailById = function(id) {
    const n = allNotices.find(item => item.id == id);
    if(n) openDetail(n);
};

function openDetail(n) {
    currentNoticeId = n.id;
    const modal = document.getElementById('detailModal');
    document.getElementById('mTitle').innerText = n.title;
    document.getElementById('mBody').innerText = n.message;
    
    let actions = '';
    if (n.type === 'join_request') {
        if (n.real_status === 'pending') {
            actions = `<button class="btn btn-primary" onclick="act('approve_join', ${n.id})">承認する</button>
                       <button class="btn btn-danger" onclick="act('reject', ${n.id})">拒否する</button>`;
        } else {
            actions = `<span class="status-badge">${n.real_status}</span>`;
        }
    } else if (n.type === 'join_approved') {
        if (n.real_status === 'approved') {
            actions = `<button class="btn btn-primary" onclick="act('confirm_join', ${n.id})">チームに参加する</button>`;
        } else {
            actions = `<span class="status-badge">完了</span>`;
        }
    } else if (n.type === 'scrim_request') {
        if (n.real_status === 'pending') {
            actions = `<button class="btn btn-primary" onclick="act('accept_scrim', ${n.id})">承諾する</button>
                       <button class="btn btn-danger" onclick="act('reject', ${n.id})">拒否する</button>`;
        } else {
            actions = `<span class="status-badge">${n.real_status}</span>`;
        }
    }
    document.getElementById('mActions').innerHTML = actions;

    if(n.is_read == 0) act('read', n.id, false);
    loadChat(n.id);
    modal.classList.add('show');
}

async function loadChat(nid) {
    const chatBox = document.getElementById('chatHistory');
    chatBox.innerHTML = '<div style="text-align:center;color:#888;">読み込み中...</div>';
    
    try {
        const res = await fetch(`notice.php?api=get_messages&notification_id=${nid}`);
        const msgs = await res.json();
        chatBox.innerHTML = '';
        
        if(msgs.length === 0) {
            chatBox.innerHTML = '<div style="text-align:center;color:#666;font-size:0.8rem;padding:20px;">メッセージ履歴はありません</div>';
        }

        msgs.forEach(m => {
            const div = document.createElement('div');
            div.className = `chat-msg ${m.user_id == currentUserId ? 'mine' : ''}`;
            div.innerHTML = `<div class="chat-bubble">${m.message}</div>`;
            chatBox.appendChild(div);
        });
        chatBox.scrollTop = chatBox.scrollHeight;
    } catch(e) {
        chatBox.innerHTML = '読み込みエラー';
    }
}

async function sendMessage() {
    const txt = document.getElementById('chatInput');
    if(!txt.value.trim()) return;
    await fetch('notice.php', {
        method: 'POST', body: JSON.stringify({ action: 'send_message', notification_id: currentNoticeId, message: txt.value })
    });
    txt.value = '';
    loadChat(currentNoticeId);
}

window.act = async function(action, nid, reload=true) {
    if(action !== 'read' && !confirm('実行しますか？')) return;
    await fetch('notice.php', { 
        method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ action: action, notification_id: nid }) 
    });
    if(reload) { 
        document.getElementById('detailModal').classList.remove('show'); 
        loadNotices(); 
        if(action.includes('confirm') || action.includes('approve') || action.includes('accept')) {
            alert('処理が完了しました');
            location.reload(); 
        }
    }
}