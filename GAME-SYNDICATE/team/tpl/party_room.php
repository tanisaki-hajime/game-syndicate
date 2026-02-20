<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>パーティルーム - GAME SYNDICATE</title>
    <script src="https://kit.fontawesome.com/659df936c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../mypage/css/style.css">
    <style>
        body { background: #0a0e27; color: #fff; overflow: hidden; }
        .chat-container { max-width: 1000px; margin: 40px auto; display: flex; gap: 20px; height: 85vh; padding: 0 20px; }
        
        /* 左サイドバー: メンバーリスト */
        .member-list { 
            width: 250px; background: rgba(255,255,255,0.05); padding: 25px; border-radius: 16px; 
            border: 1px solid rgba(255,255,255,0.1); display: flex; flex-direction: column;
        }
        .member-header { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .game-label { color: #ff00cc; font-weight: 900; font-size: 1.2rem; display: block; margin-top: 5px; }
        .member-item { display: flex; align-items: center; gap: 12px; margin-bottom: 15px; font-weight: bold; }
        .member-item img { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid #333; }
        
        /* 右メイン: チャットエリア */
        .chat-area { 
            flex: 1; display: flex; flex-direction: column; background: rgba(0,0,0,0.3); 
            border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); 
        }
        .messages { flex: 1; padding: 25px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; }
        
        /* メッセージ吹き出し */
        .msg { padding: 12px 18px; border-radius: 12px; max-width: 70%; line-height: 1.5; position: relative; }
        .msg.mine { align-self: flex-end; background: linear-gradient(135deg, #333399 0%, #222266 100%); color: #fff; border-bottom-right-radius: 2px; }
        .msg.other { align-self: flex-start; background: #2a2a3a; border: 1px solid #444; border-bottom-left-radius: 2px; }
        .msg-user { font-size: 0.75rem; color: #888; margin-bottom: 4px; display: block; }
        
        /* 入力エリア */
        .input-area { padding: 20px; background: rgba(255,255,255,0.05); display: flex; gap: 10px; border-top: 1px solid rgba(255,255,255,0.1); }
        .input-area input { 
            flex: 1; padding: 15px; border-radius: 8px; border: 1px solid #444; 
            background: #111; color: #fff; outline: none; transition: 0.3s;
        }
        .input-area input:focus { border-color: #ff00cc; }
        .btn-send { 
            background: #ff00cc; border: none; color: #fff; padding: 0 25px; 
            border-radius: 8px; cursor: pointer; font-size: 1.2rem; transition: 0.2s;
        }
        .btn-send:hover { background: #d900ad; }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="member-list">
            <div class="member-header">
                <i class="fas fa-users"></i> <span>PARTY MEMBERS</span>
                <span class="game-label"><?php echo strtoupper($room['game_title']); ?></span>
            </div>
            
            <?php foreach($members as $m): ?>
                <div class="member-item">
                    <img src="<?php echo $m['user_icon'] ? $m['user_icon'] : '../img/default_user.png'; ?>">
                    <span><?php echo htmlspecialchars($m['name']); ?></span>
                </div>
            <?php endforeach; ?>
            
            <div style="margin-top:auto; padding-top:20px; text-align:center;">
                <a href="#" onclick="exitParty(); return false;" style="color:#aaa; text-decoration:none; font-size:0.9rem; transition:0.3s;">
                    <i class="fas fa-sign-out-alt"></i> パーティを退出
                </a>
            </div>
        </div>

        <div class="chat-area">
            <div class="messages" id="msgBox"></div>
            <div class="input-area">
                <input type="text" id="chatInput" placeholder="メッセージを入力して Enter...">
                <button class="btn-send" onclick="sendMsg()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    <script>
        const roomId = <?php echo $room_id; ?>;
        
        // メッセージ送信
        async function sendMsg() {
            const input = document.getElementById('chatInput');
            if(!input.value.trim()) return;
            
            const fd = new FormData();
            fd.append('message', input.value);
            
            await fetch(`party_room.php?room_id=${roomId}&api=send`, { method:'POST', body:fd });
            input.value = '';
            loadMsg();
        }

        // メッセージ取得
        async function loadMsg() {
            const res = await fetch(`party_room.php?room_id=${roomId}&api=get`);
            const data = await res.json();
            const box = document.getElementById('msgBox');
            
            box.innerHTML = '';
            data.messages.forEach(m => {
                const isMe = m.user_id == data.my_id;
                const html = `
                    <div class="msg ${isMe ? 'mine' : 'other'}">
                        ${!isMe ? `<span class="msg-user">${m.name}</span>` : ''}
                        <span>${m.message}</span>
                    </div>
                `;
                box.innerHTML += html;
            });
            // 自動スクロール
            // box.scrollTop = box.scrollHeight; // 毎回スクロールすると読みにくいので、最新投稿時のみにする等の調整も可
        }

        // ★退出処理
        async function exitParty() {
            if(!confirm('パーティを退出しますか？\n(最後の一人が退出すると部屋は削除されます)')) return;
            
            await fetch(`party_room.php?room_id=${roomId}&api=exit`);
            window.location.href = '../top/top.php';
        }

        setInterval(loadMsg, 3000);
        loadMsg();
        
        document.getElementById('chatInput').addEventListener('keypress', (e) => {
            if(e.key === 'Enter') sendMsg();
        });
    </script>
</body>
</html>