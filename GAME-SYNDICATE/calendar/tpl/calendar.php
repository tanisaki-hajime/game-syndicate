<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCHEDULE | <?php echo htmlspecialchars($team_name); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/calendar.css?v=<?php echo time(); ?>">
</head>
<body>
    <header class="app-header">
        <div class="team-brand">
            <a href="../team/team_page.php?id=<?php echo $target_team_id; ?>" class="back-link">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="team-logo">
                <?php echo strtoupper(substr($team_name, 0, 2)); ?>
            </div>
            <div class="team-info">
                <h1><?php echo htmlspecialchars($team_name); ?></h1>
                <div class="activity-time">
                    ACTIVITY TIME: <strong><?php echo $start_time; ?> - <?php echo $end_time; ?></strong>
                </div>
            </div>
        </div>

        <div class="controls">
            <div class="month-nav">
                <button class="nav-btn" onclick="calendarApp.changeMonth(-1)"><i class="fas fa-chevron-left"></i></button>
                <span id="monthLabel"><?php echo date('Y.m', strtotime($month)); ?></span>
                <button class="nav-btn" onclick="calendarApp.changeMonth(1)"><i class="fas fa-chevron-right"></i></button>
            </div>
            <button id="btnMultiSelect" class="btn-secondary" onclick="calendarApp.toggleMultiSelect()">
                <i class="fas fa-check-double"></i> MULTI SELECT
            </button>
        </div>
    </header>

    <div class="container">
        <div id="calendar-root" class="calendar-grid"></div>

        <div class="legend-area">
            <div class="l-item"><span class="dot ok"></span> 参加可能 (<?php echo $start_time; ?>〜)</div>
            <div class="l-item"><span class="dot tentative"></span> 要調整 / 遅刻</div>
            <div class="l-item"><span class="dot ng"></span> 参加不可</div>
        </div>

        <div id="bulkActionMenu" class="bulk-menu">
            <span id="selectedCount" style="color:#fff; font-weight:bold;">0 SELECTED</span>
            <div style="display:flex; gap:10px;">
                <button onclick="calendarApp.bulkSave('ok')" class="b-btn ok">◯</button>
                <button onclick="calendarApp.bulkSave('tentative')" class="b-btn tentative">△</button>
                <button onclick="calendarApp.bulkSave('ng')" class="b-btn ng">×</button>
            </div>
        </div>
    </div>

    <div id="modal" class="modal">
        <div class="modal-content">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h3 id="modalDate">DATE</h3>
                <i class="fas fa-times close-modal" onclick="calendarApp.closeModal()" style="cursor:pointer; color:#888;"></i>
            </div>
            
            <div class="form-group">
                <label>AGENT</label>
                <select id="userSelect" <?php if($user_role !== 'owner') echo 'disabled'; ?>>
                    <?php foreach($members as $m): ?>
                        <option value="<?php echo $m['id']; ?>" <?php if($m['id'] == $user_id) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($m['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if($user_role !== 'owner'): ?>
                    <p style="font-size:10px; color:#666; margin-top:4px;">※自分の予定のみ変更可能です</p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>STATUS</label>
                <div class="radio-group">
                    <label class="radio-label" style="color:#00ff9d;">
                        <input type="radio" name="status" value="ok" checked> ◯
                    </label>
                    <label class="radio-label" style="color:#ff9800;">
                        <input type="radio" name="status" value="tentative"> △
                    </label>
                    <label class="radio-label" style="color:#ff4655;">
                        <input type="radio" name="status" value="ng"> ×
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>MEMO <span style="font-weight:normal; color:#666;">(Optional)</span></label>
                <input type="text" id="commentInput" placeholder="22時から参加可能、など">
            </div>

            <button onclick="calendarApp.saveSchedule()" class="btn-save">
                <span>REGISTER</span>
            </button>
        </div>
    </div>

    <script>
        window.CalendarConfig = {
            scheduleData: <?php echo json_encode($schJson); ?>,
            currentUserId: <?php echo $user_id; ?>,
            targetTeamId: <?php echo $target_team_id; ?>,
            userRole: "<?php echo $user_role; ?>",
            membersMap: <?php echo json_encode($members_map); ?>,
            requiredMembers: <?php echo $required_members; ?>,
            initialMonth: "<?php echo $month; ?>"
        };
    </script>
    <script src="js/calendar.js?v=<?php echo time(); ?>"></script>
</body>
</html>