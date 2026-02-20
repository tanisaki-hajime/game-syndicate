<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>チーム検索 - GAME SYNDICATE</title>
    <script src="https://kit.fontawesome.com/659df936c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../mypage/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/search.css?v=<?php echo time(); ?>">
    <style>
        /* ★修正: モーダルが他の要素より確実に手前に来るようにz-indexを強化 */
        .modal {
            z-index: 9999 !important;
        }
        .modal-content {
            z-index: 10000 !important;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-left">
            <a href="../top/top.php" class="logo-link"><i class="fas fa-rocket"></i> GAME SYNDICATE</a>
            <nav class="nav">
                <a href="team_create.php" class="nav-item">チーム作成</a>
                <a href="team_search.php" class="nav-item">ゲーム選択へ</a>
                <a href="../mypage/mypage.php" class="nav-item">マイページ</a>
            </nav>
        </div>
        <div class="header-right">
            <?php if(isset($user_id) && $user_id > 0): ?>
                <a href="../mypage/notice.php" class="header-icon" title="お知らせ"><i class="fas fa-bell"></i></a>
                <a href="../mypage/mypage.php" class="header-user">
                    <img src="<?php echo htmlspecialchars($user_icon); ?>" alt="icon">
                </a>
            <?php else: ?>
                <a href="../mypage/login.php" class="btn-login">LOGIN</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="main-wrapper">
        <?php if ($view_mode === 'select_mode'): ?>
        <section class="game-select-screen fade-in">
            <h1 class="page-title-large">SELECT YOUR BATTLEFIELD</h1>
            <p class="page-subtitle">チームを探すゲームタイトルを選択してください</p>
            <div class="game-cards-large">
                <a href="?game=valorant" class="game-card-large valorant">
                    <div class="card-bg"></div><div class="card-overlay"></div>
                    <div class="card-content"><span>VALORANT</span></div>
                </a>
                <a href="?game=apex" class="game-card-large apex">
                    <div class="card-bg"></div><div class="card-overlay"></div>
                    <div class="card-content"><span>APEX LEGENDS</span></div>
                </a>
                <a href="?game=lol" class="game-card-large lol">
                    <div class="card-bg"></div><div class="card-overlay"></div>
                    <div class="card-content"><span>LEAGUE OF LEGENDS</span></div>
                </a>
                <a href="?game=ow2" class="game-card-large ow2">
                    <div class="card-bg"></div><div class="card-overlay"></div>
                    <div class="card-content"><span>OVERWATCH 2</span></div>
                </a>
                <a href="?game=all" class="game-card-large other">
                    <div class="card-bg"></div><div class="card-overlay"></div>
                    <div class="card-content"><span>ALL TITLES</span></div>
                </a>
            </div>
        </section>

        <?php else: ?>
        <div class="container fade-in">
            <div class="search-header">
                <h2><i class="fas fa-search"></i> TEAM SEARCH: <span style="color:#ff0078"><?php echo strtoupper($selected_game); ?></span></h2>
                <a href="team_search.php" class="btn-back"><i class="fas fa-undo"></i> 戻る</a>
            </div>

            <div class="search-panel">
                <input type="hidden" id="s_game" value="<?php echo htmlspecialchars($selected_game); ?>">
                
                <div class="search-row">
                    <div class="form-group search-bar-group">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="s_keyword" placeholder="チーム名で検索...">
                    </div>
                    <button class="btn-search" onclick="searchTeams(1)">検索</button>
                </div>
                
                <div class="search-row filters">
                    <div class="form-group">
                        <label>ランク帯</label>
                        <select id="s_division"><option value="">指定なし</option></select>
                    </div>
                    <div class="form-group">
                        <label>ロール</label>
                        <select id="s_role"><option value="">指定なし</option></select>
                    </div>
                    <div class="form-group">
                        <label>キャラクター</label>
                        <select id="s_character"><option value="">指定なし</option></select>
                    </div>
                </div>
            </div>

            <div id="teamList" class="team-grid"></div>
            
            <div id="paginationArea" class="pagination-wrapper"></div>
        </div>
        <?php endif; ?>
    </div>

    <div id="applyModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2><i class="fas fa-paper-plane"></i> チームへ加入申請</h2>
            <div id="applyTeamName" class="modal-team-name"></div>
            <input type="hidden" id="applyTeamId">
            <div class="modal-info-box">
                <span class="modal-info-label">このチームが募集しているロール・キャラ</span>
                <div id="wantedInfo" class="tag-container"></div>
            </div>
            <div class="form-group">
                <label>希望ロール</label>
                <select id="applyRole" style="width:100%; padding:10px; background:#000; color:#fff; border:1px solid #444; border-radius:6px;">
                    <option value="member">メンバー</option>
                    <option value="sub">サブ</option>
                    <option value="coach">コーチ</option>
                    <option value="analyst">アナリスト</option>
                </select>
            </div>
            <div class="form-group" style="margin-top:10px;">
                <label>メッセージ</label>
                <textarea id="applyMessage" rows="4" style="width:100%; padding:10px; background:#000; color:#fff; border:1px solid #444; border-radius:6px;" placeholder="自己紹介や意気込みを入力してください"></textarea>
            </div>
            <button class="btn-primary" onclick="confirmAction('join')" style="margin-top:15px; width:100%; padding:12px; background:#ff0078; color:#fff; border:none; border-radius:6px; cursor:pointer;">申請を送る</button>
        </div>
    </div>

    <div id="scrimModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2><i class="fas fa-handshake"></i> スクリム申し込み</h2>
            <div id="scrimTeamName" class="modal-team-name"></div>
            <input type="hidden" id="scrimTeamId">
            <div class="modal-info-box">
                <span class="modal-info-label">相手チームの構成（ヒット）</span>
                <div id="compositionInfo" class="tag-container"></div>
            </div>
            <div class="form-group" style="margin-top:10px;">
                <label>メッセージ</label>
                <textarea id="scrimMessage" rows="4" style="width:100%; padding:10px; background:#000; color:#fff; border:1px solid #444; border-radius:6px;" placeholder="希望日時、ランク帯、マップなどを入力してください"></textarea>
            </div>
            <button class="btn-primary" onclick="confirmAction('scrim')" style="margin-top:15px; width:100%; padding:12px; background:#00d26a; color:#000; border:none; border-radius:6px; cursor:pointer;">送信確認</button>
        </div>
    </div>

    <script src="js/team_search.js?v=<?php echo time(); ?>"></script>
</body>
</html>