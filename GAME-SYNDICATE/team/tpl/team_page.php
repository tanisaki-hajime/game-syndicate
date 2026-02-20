<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($team['team_name']); ?> | GAME SYNDICATE</title>
    <script src="https://kit.fontawesome.com/659df936c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <style>
        .intro-bg { background-image: url('<?php echo $bg_image; ?>'); }
        /* タグ用のスタイル */
        .team-tags { margin-top: 15px; display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; }
        .tag-role { background: rgba(255,0,120,0.2); color: #ff0078; border: 1px solid #ff0078; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; }
        .tag-agent { background: rgba(255,255,255,0.1); color: #fff; border: 1px solid #444; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-left">
            <a href="../top/top.php" class="logo-link"><i class="fas fa-rocket"></i> GAME SYNDICATE</a>
        </div>
        <div class="header-right">
            <ul class="nav">
                <li class="nav-item"><a href="#roster">ROSTER</a></li>
                <li class="nav-item"><a href="#contact">CONTACT</a></li>
                <li class="nav-item"><a href="../team/team_search.php">SEARCH</a></li>
                <li class="nav-item"><a href="../mypage/mypage.php">MY PAGE</a></li>
                <?php if($is_owner): ?>
                    <li class="nav-item"><a href="team_edit.php?id=<?php echo $team_id; ?>" style="color:#ff0078">EDIT</a></li>
                <?php endif; ?>
            </ul>
            <div class="header-icons">
                <?php if($is_logged_in): ?>
                    <a href="../mypage/notice.php" class="header-icon"><i class="fas fa-bell"></i></a>
                    <a href="../mypage/mypage.php" class="header-user">
                        <img src="<?php echo htmlspecialchars($user_icon); ?>" alt="icon">
                    </a>
                <?php else: ?>
                    <a href="../mypage/login.php" class="btn-login">LOGIN</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <section class="team-intro-section">
            <div class="intro-bg"></div>
            <div class="teamText">
                <img src="<?php echo $icon_image; ?>" class="intro-logo" alt="Team Logo">
                <h2 class="intro-title"><?php echo htmlspecialchars($team['team_name']); ?></h2>
                <div class="intro-meta">
                    <span><?php echo strtoupper(htmlspecialchars($team['game_title'])); ?></span>
                    <span>/</span>
                    <span>AVG: <?php echo htmlspecialchars(strtoupper($team['team_division'])); ?></span>
                </div>
                
                <?php if($team['team_status'] === 'recruiting'): ?>
                    <div class="recruiting-text" style="margin-top:10px;"><i class="fas fa-circle-notch fa-spin"></i> RECRUITING</div>
                    <div class="team-tags">
                        <?php 
                        $w_roles = array_filter(array_map('trim', explode(',', $team['wanted_roles'] ?? '')));
                        $w_agents = array_filter(array_map('trim', explode(',', $team['wanted_agents'] ?? '')));
                        
                        foreach($w_roles as $r) { echo "<span class='tag-role'>".htmlspecialchars($r)."</span>"; }
                        foreach($w_agents as $a) { echo "<span class='tag-agent'>".htmlspecialchars($a)."</span>"; }
                        
                        if(empty($w_roles) && empty($w_agents)) {
                            echo "<span style='color:#888; border:1px dashed #666; padding:5px 12px; border-radius:20px; font-size:0.85rem;'>募集条件の指定なし</span>";
                        }
                        ?>
                    </div>
                <?php else: ?>
                    <div class="recruiting-text" style="margin-top:10px; color:#aaa;"><i class="fas fa-users"></i> 活動中</div>
                <?php endif; ?>

                <?php if(!empty($team['description'])): ?>
                    <div class="intro-desc" style="margin-top:20px;"><?php echo nl2br(htmlspecialchars($team['description'])); ?></div>
                <?php endif; ?>
            </div>
        </section>

        <section class="members-section" id="roster">
            <h2 class="section-title">ROSTER</h2>
            <div class="members-container">
                <?php 
                    $roles_order = [
                        'owner' => 'LEADER', 'manager' => 'MANAGER', 'coach' => 'COACH',
                        'analyst' => 'ANALYST', 'main' => 'MAIN ROSTER', 'sub' => 'SUB ROSTER'
                    ];

                    foreach ($roles_order as $roleKey => $roleLabel):
                        $members = $groups[$roleKey] ?? [];
                        
                        if ($roleKey == 'main' && count($members) < 5) {
                            for($i=count($members); $i<5; $i++) $members[] = ['is_demo'=>true, 'name'=>'RECRUITING', 'team_role'=>'main'];
                        }
                        if ($roleKey == 'sub' && count($members) < 2) {
                            for($i=count($members); $i<2; $i++) $members[] = ['is_demo'=>true, 'name'=>'RECRUITING', 'team_role'=>'sub'];
                        }

                        foreach ($members as $m):
                            $isDemo = isset($m['is_demo']);
                            $uIcon = $isDemo ? $default_icon_url : getImg($m['user_icon'], $default_icon_url);
                            
                            $roleStr = '-';
                            if (!$isDemo) {
                                $roles = [];
                                if(!empty($m['main_role'])) $roles[] = $m['main_role'];
                                if(!empty($m['sub_role'])) {
                                    $subs = explode(',', $m['sub_role']);
                                    foreach($subs as $s) $roles[] = trim($s);
                                }
                                if(!empty($roles)) {
                                    $roles = array_unique($roles);
                                    $roleStr = implode(', ', $roles);
                                }
                            }
                ?>
                    <div class="player-card <?php echo $isDemo ? 'demo' : ''; ?>">
                        <div class="card-visual">
                            <img src="<?php echo $uIcon; ?>" class="player-icon" alt="icon">
                        </div>
                        <h3 class="player-name">
                            <?php echo htmlspecialchars($m['name']); ?>
                            <?php if(!$isDemo && isset($m['igl']) && $m['igl'] === 'yes'): ?>
                                <span class="igl-mark">★IGL</span>
                            <?php endif; ?>
                        </h3>
                        <div class="player-title"><?php echo $roleLabel; ?></div>

                        <div class="card-details">
                            <ul class="player-info">
                                <li>
                                    <span class="label">RANK</span>
                                    <span class="value">
                                        <?php echo htmlspecialchars($m['current_rank'] ?? '-'); ?>
                                        <?php if(!$isDemo && !empty($m['highest_rank'])): ?>
                                            <span class="sub-val">(Max: <?php echo htmlspecialchars($m['highest_rank']); ?>)</span>
                                        <?php endif; ?>
                                    </span>
                                </li>
                                <li>
                                    <span class="label">ROLE</span>
                                    <span class="value"><?php echo htmlspecialchars($roleStr); ?></span>
                                </li>
                            </ul>
                            <?php if(!$isDemo): ?>
                                <a href="../calendar/calendar.php?team_id=<?php echo $team_id; ?>" class="calendar-btn">
                                    <i class="far fa-calendar-alt"></i> TEAM SCHEDULE
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; endforeach; ?>
            </div>
        </section>

        <section class="contact-section" id="contact">
            <h2 class="section-title">CONTACT</h2>
            <div class="contact-buttons-container">
            <div class="contact-card">
                    <i class="fas fa-handshake contact-icon"></i>
                    <h3>Scrim</h3>
                    <a href="../scrim/scrim_match.php?opponent_id=<?php echo $team_id; ?>&game=<?php echo htmlspecialchars($team['game_title']); ?>" class="contact-btn">APPLY</a>
                </div>
                <div class="contact-card">
                    <i class="fas fa-flag contact-icon" style="color:#9146ff"></i>
                    <h3>Report</h3>
                    <a href="../report/report.php?team_id=<?php echo $team_id; ?>" class="contact-btn" style="background:#444">REPORT</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>