<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GAME SYNDICATE - Team Portal</title>
    <script src="https://kit.fontawesome.com/659df936c7.js" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Space+Grotesk:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/top.css">
    <style>
        /* --- ヘッダー修正: 黒い背景を削除して透明化 --- */
        .header-portal {
            background: transparent !important; /* 黒背景を強制削除 */
            border-bottom: none !important;
            /* 文字が見やすいように上部だけうっすらグラデーション */
            /* background: linear-gradient(to bottom, rgba(0,0,0,0.8) 0%, transparent 100%) !important; */
            position: absolute;
            top: 0; left: 0; width: 100%;
            z-index: 1000;
        }

        /* 今すぐフルパ 特設セクション */
        .fast-party-section {
            background: linear-gradient(135deg, #1a0b2e 0%, #000000 100%);
            padding: 80px 20px;
            margin: 60px 0;
            border-top: 1px solid rgba(255, 0, 204, 0.3);
            border-bottom: 1px solid rgba(255, 0, 204, 0.3);
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        .fast-party-bg {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at center, rgba(255, 0, 204, 0.15) 0%, transparent 70%);
            pointer-events: none;
        }
        .fp-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 3rem;
            color: #fff;
            text-shadow: 0 0 20px rgba(255, 0, 204, 0.8);
            margin-bottom: 15px;
        }
        .fp-desc {
            font-size: 1.2rem; color: #ccc; margin-bottom: 40px; max-width: 600px; margin-left: auto; margin-right: auto;
        }
        .btn-fast-party-lg {
            display: inline-flex; align-items: center; justify-content: center; gap: 15px;
            background: linear-gradient(90deg, #ff00cc, #333399);
            color: #fff; text-decoration: none;
            padding: 20px 60px; border-radius: 50px;
            font-size: 1.5rem; font-weight: 900;
            box-shadow: 0 0 30px rgba(255, 0, 204, 0.6);
            transition: 0.3s; border: 2px solid rgba(255,255,255,0.2);
        }
        .btn-fast-party-lg:hover {
            transform: scale(1.05);
            box-shadow: 0 0 50px rgba(255, 0, 204, 0.9);
        }

        /* フッター */
        .footer-portal { padding: 60px 20px 20px; background: #050510; border-top: 1px solid #222; }
        .footer-content {
            display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 40px;
            max-width: 1200px; margin: 0 auto 40px;
        }
        .footer-section h4 { color: #fff; margin-bottom: 20px; font-size: 1.1rem; border-left: 3px solid #ff00cc; padding-left: 10px; }
        .footer-section ul { list-style: none; padding: 0; }
        .footer-section ul li { margin-bottom: 12px; }
        .footer-section ul li a { color: #888; text-decoration: none; transition: 0.2s; }
        .footer-section ul li a:hover { color: #ff00cc; padding-left: 5px; }
        
        .btn-create {
            background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff; backdrop-filter: blur(5px);
        }
        .btn-create:hover { background: rgba(255, 255, 255, 0.2); }
    </style>
</head>
<body>
    <div class="bg-animated"></div>

    <header class="header-portal">
        <nav class="navbar">
            <div class="logo-section">
                <div class="logo">
                    <i class="fas fa-rocket"></i>
                    <span>GAME SYNDICATE</span>
                </div>
            </div>
            <div class="nav-buttons">
                <?php if($user_id > 0): ?>
                    <a href="../mypage/mypage.php" class="btn btn-ghost">
                        <img src="<?php echo htmlspecialchars($user_icon); ?>" style="width:24px; height:24px; border-radius:50%; margin-right:8px; vertical-align:middle;">
                        マイページ
                    </a>
                <?php else: ?>
                    <button class="btn btn-ghost" onclick="window.location.href='../mypage/login.php'">
                        <i class="fas fa-sign-in-alt"></i> ログイン
                    </button>
                    <button class="btn btn-primary" onclick="window.location.href='../mypage/login.php?mode=register'">
                        <i class="fas fa-user-plus"></i> 会員登録
                    </button>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <div class="container">
        <section class="hero">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">
                        <span class="gradient-text">チームサイト作成</span><br>
                        ×<br>
                        <span class="gradient-text-alt">チーム運営管理</span>
                    </h1>
                    <p class="hero-subtitle">
                        メンバー募集からスクリム管理、スケジュール調整まで。<br>
                        eスポーツチームの活動に必要なすべてを、ひとつの場所で。
                    </p>
                    <div class="hero-buttons">
                        <button class="btn btn-create hero-btn" onclick="window.location.href='../team/team_create.php'">
                            <span>チームを作成</span>
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="btn btn-ghost hero-btn" onclick="window.location.href='../team/team_search.php'">
                            <span>チームを探す</span>
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="glowing-circle"></div>
                </div>
            </div>
        </section>

        <section class="fast-party-section">
            <div class="fast-party-bg"></div>
            <h2 class="fp-title">FAST PARTY</h2>
            <p class="fp-desc">
                待っている時間はない。<br>
                条件を指定して、今すぐ最高のチームメイトとマッチングしよう。
            </p>
            <a href="../team/fast_party.php" class="btn-fast-party-lg">
                <i class="fas fa-bolt"></i> 今すぐフルパを始める
            </a>
        </section>

        <section class="games-section">
            <h2 class="section-title">対応ゲーム</h2>
            <div class="games-grid">
                <div class="game-card active" onclick="window.location.href='../team/team_search.php?game=valorant'">
                    <div class="game-card-bg"></div>
                    <div class="game-card-content">
                        <div class="game-icon"><img src="./img/valorant.jpg" alt="VALORANT"></div>
                        <h3>VALORANT</h3><p>5v5タクティカルシューター</p>
                        <span class="badge badge-available"><span class="status-dot"></span> 利用可能</span>
                    </div>
                </div>
                <div class="game-card active" onclick="window.location.href='../team/team_search.php?game=apex'">
                    <div class="game-card-bg"></div>
                    <div class="game-card-content">
                        <div class="game-icon"><img src="./img/apex.jpg" alt="APEX"></div>
                        <h3>Apex Legends</h3><p>3人チームバトルロイヤル</p>
                        <span class="badge badge-available"><span class="status-dot"></span> 利用可能</span>
                    </div>
                </div>
                <div class="game-card active" onclick="window.location.href='../team/team_search.php?game=lol'">
                    <div class="game-card-bg"></div>
                    <div class="game-card-content">
                        <div class="game-icon"><img src="./img/lol.jpg" alt="LoL"></div>
                        <h3>League of Legends</h3><p>5v5 MOBA</p>
                        <span class="badge badge-available"><span class="status-dot"></span> 利用可能</span>
                    </div>
                </div>
                <div class="game-card coming-soon">
                    <div class="game-card-bg"></div>
                    <div class="game-card-content">
                        <div class="game-icon"><img src="./img/ow2.jpg" alt="OW2"></div>
                        <h3>Overwatch 2</h3><p>5v5チームアクション</p>
                        <span class="badge badge-soon"><i class="fas fa-clock"></i> 近日公開</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="features">
            <h2 class="section-title">チーム運営を加速させる機能</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon-container"><i class="fas fa-users"></i></div>
                    <h3>メンバー募集・管理</h3>
                    <p>専用ページでメンバーを募集。役割分担や権限管理もスムーズに。</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon-container"><i class="fas fa-chart-line"></i></div>
                    <h3>戦績・スクリム管理</h3>
                    <p>試合結果を記録してチームの成長を可視化。対戦相手の管理も。</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon-container"><i class="fas fa-globe"></i></div>
                    <h3>チームサイト自動生成</h3>
                    <p>情報を入力するだけで、クランやチームの公式サイトが完成。</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon-container"><i class="fa-regular fa-calendar-check"></i></div>
                    <h3>活動カレンダー</h3>
                    <p>練習日程や大会スケジュールを共有し、参加可否を一括管理。</p>
                </div>
            </div>
        </section>
    </div>

    <footer class="footer-portal">
        <div class="footer-content">
            <div class="footer-section">
                <h4>GAME SYNDICATE</h4>
                <p style="color:#888; font-size:0.9rem; line-height:1.6;">
                    ゲーマーのための究極のチーム運営プラットフォーム。<br>
                    最強のチームを作り、eスポーツの世界へ飛び込もう。
                </p>
                <div class="social-links" style="margin-top:20px; display:flex; gap:15px;">
                    <a href="#" style="color:#fff;"><i class="fab fa-twitter"></i></a>
                    <a href="#" style="color:#fff;"><i class="fab fa-discord"></i></a>
                    <a href="#" style="color:#fff;"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h4>サービス</h4>
                <ul>
                    <li><a href="../team/team_create.php">チームを作成</a></li>
                    <li><a href="../team/team_search.php">チームを探す</a></li>
                    <li><a href="../team/fast_party.php">今すぐフルパ</a></li>
                    <li><a href="../scrim/scrim_search.php">スクリム募集</a></li>
                    <li><a href="../rank/leaderboard.php">ランキング</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>サポート & ガイド</h4>
                <ul>
                    <li><a href="#">初めての方へ</a></li>
                    <li><a href="#">よくある質問 (FAQ)</a></li>
                    <li><a href="#">お問い合わせ</a></li>
                    <li><a href="#">機能リクエスト</a></li>
                    <li><a href="#">運営ブログ</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>運営情報</h4>
                <ul>
                    <li><a href="#">運営会社</a></li>
                    <li><a href="#">利用規約</a></li>
                    <li><a href="#">プライバシーポリシー</a></li>
                    <li><a href="#">特定商取引法に基づく表記</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 GAME SYNDICATE. All rights reserved.</p>
        </div>
    </footer>

    <div id="loadingScreen" class="loading-screen">
        <div class="loading-container">
            <div class="loading-glow"></div>
            <div class="loading-box">
                <div class="loading-content">
                    <i class="fas fa-gamepad"></i>
                    <div class="spinner"></div>
                </div>
            </div>
            <p class="loading-text">GAME SYNDICATE</p>
        </div>
    </div>

    <script src="./js/top.js"></script>
</body>
</html>