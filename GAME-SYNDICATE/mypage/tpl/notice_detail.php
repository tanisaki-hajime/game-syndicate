<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>チャット・詳細</title>
    <script src="https://kit.fontawesome.com/659df936c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="./css/notice.css?v=<?php echo time(); ?>">
</head>
<body class="detail-body">
    <header class="header">
        <div class="header-left">
            <a href="notice.php" class="back-link" style="color:#fff; font-weight:bold; text-decoration:none;"><i class="fas fa-arrow-left"></i> 戻る</a>
        </div>
        <div class="header-right">
            <a href="notice.php" class="header-icon" style="color:#ff0078;"><i class="fas fa-bell"></i></a>
            <a href="mypage.php" class="header-user">
                <img src="<?php echo htmlspecialchars($my_icon_url); ?>" alt="icon">
            </a>
        </div>
    </header>

    <div class="container detail-container">
        <div class="status-card">
            <h2><?php echo htmlspecialchars($notice['title']); ?></h2>
            <p class="status-text">ステータス: <span class="badge status"><?php echo htmlspecialchars($current_status); ?></span></p>
            <div class="original-message">
                <strong>申請メッセージ:</strong><br>
                <?php echo nl2br(htmlspecialchars($notice['message'])); ?>
            </div>

            <div class="action-buttons">
                <?php 
                    // 自分が「ターゲット（承認する側）」かつステータスが「pending」の場合
                    if ($notice['target_user_id'] == $user_id && $current_status === 'pending'): 
                ?>
                    <?php if (strpos($notice['type'], 'join') !== false): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="approve_join">
                            <button class="btn btn-primary">承認する</button>
                        </form>
                    <?php elseif (strpos($notice['type'], 'scrim') !== false): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="accept_scrim">
                            <button class="btn btn-primary">承諾する</button>
                        </form>
                    <?php endif; ?>
                    
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="reject">
                        <button class="btn btn-danger">拒否する</button>
                    </form>

                <?php 
                    // 自分が「送信者（申請した側）」かつステータスが「approved（承認済み）」の場合
                    elseif ($notice['sender_user_id'] == $user_id && $current_status === 'approved' && strpos($notice['type'], 'join') !== false): 
                ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="confirm_join">
                        <button class="btn btn-primary">参加を確定する</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="chat-area">
            <div class="chat-history" id="chatHistory">
                <?php if(empty($messages)): ?>
                    <p class="no-msg">メッセージ履歴はありません。</p>
                <?php else: ?>
                    <?php foreach($messages as $m): ?>
                        <div class="chat-msg <?php echo ($m['user_id'] == $user_id) ? 'mine' : 'other'; ?>">
                            <div class="chat-icon">
                                <img src="<?php echo htmlspecialchars(getImg($m['user_icon'])); ?>">
                            </div>
                            <div class="chat-bubble">
                                <div class="chat-name"><?php echo htmlspecialchars($m['name']); ?></div>
                                <div class="chat-text"><?php echo nl2br(htmlspecialchars($m['message'])); ?></div>
                                <div class="chat-time"><?php echo date('H:i', strtotime($m['created_at'])); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form method="POST" class="chat-input-form">
                <input type="hidden" name="action" value="send_message">
                <input type="text" name="message" class="chat-input" placeholder="メッセージを入力..." required autocomplete="off">
                <button class="btn-send"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </div>

    <script>
        // チャット最下部へスクロール
        const chatBox = document.getElementById('chatHistory');
        chatBox.scrollTop = chatBox.scrollHeight;
    </script>
</body>
</html>