<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>チーム活動選択 - GAME SYNDICATE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/team.css">
    <link rel="stylesheet" href="css/select.css">
    <style>
        .select-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #0a0e27; z-index: -1; }
        .select-container { display: flex; justify-content: center; gap: 2rem; max-width: 1000px; margin: 4rem auto; padding: 0 20px; }
        .select-card { flex: 1; background: rgba(30, 30, 50, 0.9); border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 3rem 2rem; text-align: center; transition: 0.3s; cursor: pointer; text-decoration: none; color: #fff; }
        .select-card:hover { transform: translateY(-10px); border-color: #ff0078; box-shadow: 0 10px 30px rgba(255, 0, 120, 0.3); }
        .card-icon { font-size: 4rem; margin-bottom: 1.5rem; color: #ff0078; }
        .select-card h2 { font-size: 1.8rem; margin-bottom: 1rem; }
        .select-card p { color: #b0b0b0; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="select-bg"></div>
    <header class="team-header">
        <div class="header-content">
            <a href="../top/top.php" class="logo"><i class="fas fa-rocket"></i> GAME SYNDICATE</a>
            <nav class="header-nav"><a href="../mypage/mypage.php" class="nav-link">マイページ</a></nav>
        </div>
    </header>
    <div class="container">
        <section class="team-hero">
            <h1 class="hero-title">Select Your Path</h1>
            <p class="hero-subtitle">あなたのチーム活動をここから始めましょう</p>
        </section>
        <div class="select-container">
            <a href="team_create.php?mode=existing" class="select-card">
                <div class="card-icon"><i class="fas fa-users"></i></div>
                <h2>既存チームのサイト作成</h2>
                <p>既に活動中のチーム向け。実績やメンバー紹介ページを作成します。</p>
            </a>
            <a href="team_create.php?mode=recruiting" class="select-card">
                <div class="card-icon"><i class="fas fa-flag"></i></div>
                <h2>新規チーム立ち上げ</h2>
                <p>これからメンバーを集めたい人向け。募集要項を掲げて仲間を探します。</p>
            </a>
        </div>
    </div>
</body>
<script src="js/select.js"></script>
</html>