<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お知らせ一覧</title>
    <script src="https://kit.fontawesome.com/659df936c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="./css/notice.css?v=<?php echo time(); ?>">
</head>
<body>
    <header class="header">
        <div class="header-left">
            <a href="../top/top.php" class="logo-link"><i class="fas fa-rocket"></i> GAME SYNDICATE</a>
        </div>
        <div class="header-right">
            <a href="notice.php" class="header-icon" style="color:#ff0078;"><i class="fas fa-bell"></i></a>
            <a href="mypage.php" class="header-user">
                <img src="<?php echo htmlspecialchars($my_icon_url); ?>" alt="icon">
            </a>
        </div>
    </header>

    <div class="container">
        <div class="notice-container">
            <div class="notice-header">
                <h1><i class="fas fa-comments"></i> NOTIFICATIONS & CHAT</h1>
            </div>

            <div class="notice-list">
                <?php if (empty($notices)): ?>
                    <div class="empty-state">お知らせはありません</div>
                <?php else: ?>
                    <?php foreach ($notices as $n): ?>
                        <a href="notice_detail.php?id=<?php echo $n['id']; ?>" class="notice-item <?php echo $n['is_read'] == 0 ? 'unread' : ''; ?>">
                            <div class="notice-icon">
                                <img src="<?php echo htmlspecialchars($n['display_icon']); ?>" alt="icon">
                            </div>
                            <div class="notice-content">
                                <div class="n-top">
                                    <span class="n-title">
                                        <span style="color:#888;font-size:0.8rem;margin-right:5px;"><?php echo $n['direction']; ?>:</span>
                                        <?php echo htmlspecialchars($n['display_name']); ?>
                                    </span>
                                    <span class="n-date"><?php echo date('m/d H:i', strtotime($n['created_at'])); ?></span>
                                </div>
                                <div class="n-msg">
                                    <span style="font-weight:bold;"><?php echo htmlspecialchars($n['title']); ?></span> - 
                                    <?php echo mb_strimwidth(htmlspecialchars($n['message']), 0, 40, '...'); ?>
                                </div>
                                <div class="n-status">
                                    <?php if($n['is_read']==0 && $n['target_user_id'] == $user_id): ?><span class="badge new">NEW</span><?php endif; ?>
                                    <span class="badge status"><?php echo htmlspecialchars($n['real_status'] ?? '-'); ?></span>
                                </div>
                            </div>
                            <div class="notice-arrow"><i class="fas fa-chevron-right"></i></div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>