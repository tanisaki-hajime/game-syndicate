/**
 * TEAM CRYPTIC STYLE CALENDAR LOGIC
 */
const CalendarApp = function() {
    const config = window.CalendarConfig;
    const { scheduleData, currentUserId, targetTeamId, userRole, membersMap, requiredMembers, initialMonth } = config;
    
    let currentDate = new Date(initialMonth + "-01");
    let isMultiSelectMode = false;
    let selectedDates = [];
    let selectedDate = ''; // モーダル用

    // DOM Elements
    const root = document.getElementById('calendar-root');
    const modal = document.getElementById('modal');
    const modalDateLabel = document.getElementById('modalDate');
    const btnMultiSelect = document.getElementById('btnMultiSelect');
    const bulkMenu = document.getElementById('bulkActionMenu');
    const selectedCountLabel = document.getElementById('selectedCount');

    // 初期化
    const init = () => {
        renderCalendar();
    };

    // カレンダー描画
    const renderCalendar = () => {
        root.innerHTML = '';
        const y = currentDate.getFullYear();
        const m = currentDate.getMonth();
        const firstDay = new Date(y, m, 1).getDay();
        const lastDate = new Date(y, m + 1, 0).getDate();

        // 曜日ヘッダー
        ['SUN','MON','TUE','WED','THU','FRI','SAT'].forEach(d => {
            const div = document.createElement('div');
            div.className = 'day-header';
            div.innerText = d;
            root.appendChild(div);
        });

        // 空白セル
        for (let i = 0; i < firstDay; i++) {
            const div = document.createElement('div');
            div.style.background = 'var(--bg-secondary)'; 
            root.appendChild(div);
        }

        // 日付セル
        for (let d = 1; d <= lastDate; d++) {
            const dateStr = `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            const cell = document.createElement('div');
            cell.className = 'day-cell';
            
            // データ集計
            let okCount = 0;
            let memberListHTML = '';
            
            if (scheduleData[dateStr]) {
                Object.keys(scheduleData[dateStr]).forEach(uid => {
                    const s = scheduleData[dateStr][uid];
                    const name = membersMap[uid] ? membersMap[uid].substring(0, 6) : 'User';
                    
                    let className = 'ng';
                    if (s.status === '◯') { className = 'ok'; okCount++; }
                    if (s.status === '△') className = 'tentative';
                    
                    memberListHTML += `<div class="sch-item ${className}">${name}</div>`;
                });
            }

            // 規定人数以上で光らせる
            if (okCount >= requiredMembers) cell.classList.add('ready');

            cell.innerHTML = `<div class="date-num">${d}</div><div class="sch-list">${memberListHTML}</div>`;
            
            // クリックイベント
            cell.onclick = () => {
                if (isMultiSelectMode) {
                    toggleDateSelection(cell, dateStr);
                } else {
                    openModal(dateStr);
                }
            };
            
            // 選択状態の復元（一括モード時）
            if (selectedDates.includes(dateStr)) cell.classList.add('selected');
            if (isMultiSelectMode) cell.classList.add('select-mode');

            root.appendChild(cell);
        }
    };

    // 一括選択モード切替
    const toggleMultiSelect = () => {
        isMultiSelectMode = !isMultiSelectMode;
        btnMultiSelect.classList.toggle('active');
        selectedDates = []; // リセット
        updateBulkMenu();
        renderCalendar();
    };

    // 日付選択のトグル
    const toggleDateSelection = (cell, dateStr) => {
        if (selectedDates.includes(dateStr)) {
            selectedDates = selectedDates.filter(d => d !== dateStr);
            cell.classList.remove('selected');
        } else {
            selectedDates.push(dateStr);
            cell.classList.add('selected');
        }
        updateBulkMenu();
    };

    // 一括メニュー更新
    const updateBulkMenu = () => {
        selectedCountLabel.innerText = `${selectedDates.length} SELECTED`;
        if (selectedDates.length > 0) bulkMenu.classList.add('show');
        else bulkMenu.classList.remove('show');
    };

    // モーダル表示
    const openModal = (date) => {
        selectedDate = date;
        modalDateLabel.innerText = date.replace(/-/g, '.');
        modal.classList.add('show');
        
        const targetUid = document.getElementById('userSelect').value;
        const commentInput = document.getElementById('commentInput');
        
        // 既存データの反映
        if (scheduleData[date] && scheduleData[date][targetUid]) {
            const s = scheduleData[date][targetUid];
            const val = s.status === '◯' ? 'ok' : (s.status === '△' ? 'tentative' : 'ng');
            document.querySelector(`input[name="status"][value="${val}"]`).checked = true;
            commentInput.value = s.comment || '';
        } else {
            document.querySelector(`input[name="status"][value="ok"]`).checked = true;
            commentInput.value = '';
        }
    };

    const closeModal = () => {
        modal.classList.remove('show');
    };

    // 月変更
    const changeMonth = (diff) => {
        currentDate.setMonth(currentDate.getMonth() + diff);
        const y = currentDate.getFullYear();
        const m = String(currentDate.getMonth() + 1).padStart(2, '0');
        location.href = `calendar.php?team_id=${targetTeamId}&m=${y}-${m}`;
    };

    // 保存処理 (単体)
    const saveSchedule = async () => {
        const uid = document.getElementById('userSelect').value;
        const status = document.querySelector('input[name="status"]:checked').value;
        const comment = document.getElementById('commentInput').value;

        if (userRole !== 'owner' && uid != currentUserId) {
            alert('ACCESS DENIED: 自分の予定以外は編集できません');
            return;
        }

        const fd = new FormData();
        fd.append('team_id', targetTeamId);
        fd.append('date', selectedDate);
        fd.append('user_id', uid);
        fd.append('status', status);
        fd.append('comment', comment);

        await sendAjax(fd);
    };

    // 保存処理 (一括)
    const bulkSave = async (status) => {
        if (!confirm(`選択した ${selectedDates.length} 日分を更新しますか？`)) return;

        const fd = new FormData();
        fd.append('team_id', targetTeamId);
        fd.append('user_id', currentUserId); // 一括は自分のみ
        fd.append('dates', JSON.stringify(selectedDates));
        fd.append('status', status);
        
        await sendAjax(fd);
    };

    // Ajax送信
    const sendAjax = async (fd) => {
        try {
            const res = await fetch('ajax_schedule.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert('ERROR: ' + data.error);
            }
        } catch (e) {
            console.error(e);
            alert('通信エラーが発生しました');
        }
    };

    return {
        init,
        changeMonth,
        toggleMultiSelect,
        openModal,
        closeModal,
        saveSchedule,
        bulkSave
    };
};

// アプリ起動
const calendarApp = CalendarApp();
calendarApp.init();