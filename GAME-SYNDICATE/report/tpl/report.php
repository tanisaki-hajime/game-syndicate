<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>通報 - GAME SYNDICATE</title>
  <script src="https://kit.fontawesome.com/659df936c7.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="./css/report.css">
  <style>
      .report-tabs { display: flex; gap: 10px; margin-bottom: 20px; }
      .tab-btn { flex: 1; padding: 10px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #888; cursor: pointer; border-radius: 6px; text-align: center; }
      .tab-btn.active { background: rgba(255, 68, 68, 0.2); color: #fff; border-color: #ff4444; font-weight: bold; }
      
      .tab-content { display: none; }
      .tab-content.active { display: block; }

      .member-select-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; }
      .member-radio { display: none; }
      .member-card { 
          background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 10px; 
          text-align: center; cursor: pointer; transition: 0.2s; position: relative;
      }
      .member-card img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-bottom: 5px; }
      .member-radio:checked + .member-card { border-color: #ff4444; background: rgba(255, 68, 68, 0.2); }
      .member-name { display: block; font-size: 0.8rem; color: #ccc; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

      .user-search-box { display: flex; gap: 10px; margin-bottom: 15px; }
      .user-search-box input { flex: 1; padding: 10px; background: #000; border: 1px solid #444; color: #fff; border-radius: 6px; }
      .search-btn { padding: 0 15px; background: #444; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
      
      .search-results { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; max-height: 200px; overflow-y: auto; }
      
      /* ★戻るボタン用追加スタイル */
      .btn-back-page { 
          display: inline-flex; align-items: center; gap: 8px; color: #aaa; text-decoration: none; 
          font-weight: bold; transition: 0.3s; padding: 10px 20px; background: rgba(255,255,255,0.05); 
          border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px;
      }
      .btn-back-page:hover { color: #fff; background: rgba(255,255,255,0.1); transform: translateX(-5px); }
  </style>
</head>
<body>
  <div class="bg-animated"></div>

  <header class="report-header">
    <div class="header-content">
      <div class="header-left">
        <a href="../top/top.php" class="logo"><i class="fas fa-shield-alt"></i> <span>GAME SYNDICATE</span></a>
      </div>
      <nav class="header-nav">
        <a href="#" onclick="history.back(); return false;" class="nav-link"><i class="fas fa-arrow-left"></i> 前のページに戻る</a>
      </nav>
    </div>
  </header>

  <div class="container">
    <a href="#" onclick="history.back(); return false;" class="btn-back-page"><i class="fas fa-arrow-left"></i> 戻る</a>

    <section class="report-hero">
      <div class="warning-icon"><i class="fas fa-exclamation-triangle"></i></div>
      <h1 class="hero-title">問題を報告する</h1>
      <p class="hero-subtitle">適切な環境維持のため、問題のあるユーザーをご報告ください。</p>
    </section>

    <div class="report-main">
      <section class="report-form-section">
        <h2 class="section-title"><i class="fas fa-clipboard"></i> 通報フォーム</h2>

        <form id="reportForm" onsubmit="submitReport(event)">
          <div class="form-group">
            <label>通報対象 <span class="required">*</span></label>
            
            <div class="report-tabs">
                <div class="tab-btn active" onclick="switchTab('team')">チームメンバーから</div>
                <div class="tab-btn" onclick="switchTab('search')">ユーザー検索</div>
            </div>

            <div id="tab-team" class="tab-content active">
                <div class="member-select-grid">
                    <?php if(empty($members)): ?>
                        <p style="color:#aaa; text-align:center; grid-column:1/-1;">チームIDが指定されていないか、メンバーがいません。</p>
                    <?php else: ?>
                        <?php foreach($members as $m): ?>
                        <label>
                            <input type="radio" name="target_user_id" value="<?php echo $m['id']; ?>" class="member-radio">
                            <div class="member-card">
                                <img src="<?php echo !empty($m['user_icon']) ? '../mypage/'.$m['user_icon'] : '../img/default_user.png'; ?>">
                                <span class="member-name"><?php echo htmlspecialchars($m['name']); ?></span>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div id="tab-search" class="tab-content">
                <div class="user-search-box">
                    <input type="text" id="searchQuery" placeholder="ユーザー名またはIDで検索...">
                    <button type="button" class="search-btn" onclick="searchUsers()"><i class="fas fa-search"></i></button>
                </div>
                <div id="searchResults" class="search-results">
                    <p style="color:#666; font-size:0.9rem;">検索してください</p>
                </div>
            </div>
          </div>

          <div class="form-group">
            <label>通報の種類 <span class="required">*</span></label>
            <select name="type" required>
              <option value="">選択してください</option>
              <option value="harassment">暴言・ハラスメント</option>
              <option value="cheating">チート・不正行為</option>
              <option value="troll">トロール・利敵行為</option>
              <option value="spam">スパム・宣伝</option>
              <option value="other">その他</option>
            </select>
          </div>

          <div class="form-group">
            <label>詳細説明 <span class="required">*</span></label>
            <textarea name="details" rows="5" placeholder="詳細を入力してください..." required></textarea>
          </div>

          <button type="submit" class="btn btn-danger">通報を送信</button>
        </form>
      </section>
    </div>
  </div>

  <script>
      function switchTab(tab) {
          document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
          document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
          
          if(tab === 'team') {
              document.querySelector('.tab-btn:nth-child(1)').classList.add('active');
              document.getElementById('tab-team').classList.add('active');
          } else {
              document.querySelector('.tab-btn:nth-child(2)').classList.add('active');
              document.getElementById('tab-search').classList.add('active');
          }
          
          document.querySelectorAll('input[name="target_user_id"]').forEach(r => r.checked = false);
      }

      async function searchUsers() {
          const q = document.getElementById('searchQuery').value;
          if(!q) return;
          
          const res = await fetch(`report.php?api=search_user&q=${q}`);
          const users = await res.json();
          
          const container = document.getElementById('searchResults');
          container.innerHTML = '';
          
          if(users.length === 0) {
              container.innerHTML = '<p style="color:#aaa;">見つかりませんでした</p>';
              return;
          }

          users.forEach(u => {
              const icon = u.user_icon ? `../mypage/${u.user_icon}` : '../img/default_user.png';
              const html = `
                <label>
                    <input type="radio" name="target_user_id" value="${u.id}" class="member-radio" required>
                    <div class="member-card">
                        <img src="${icon}">
                        <span class="member-name">${u.name}</span>
                        <span style="font-size:0.7rem; color:#666;">@${u.account_id}</span>
                    </div>
                </label>
              `;
              container.innerHTML += html;
          });
      }

      async function submitReport(e) {
          e.preventDefault();
          
          const selected = document.querySelector('input[name="target_user_id"]:checked');
          if(!selected) {
              alert('通報するユーザーを選択してください');
              return;
          }

          if(!confirm('本当にこのユーザーを通報しますか？\n(通報数は相手のアカウントに蓄積されます)')) return;

          const form = e.target;
          const fd = new FormData(form);
          fd.append('api', 'submit_report');

          try {
              const res = await fetch('report.php', { method:'POST', body:fd });
              const data = await res.json();
              if(data.success) {
                  alert('通報を受け付けました。');
                  window.history.back(); // 完了後も前のページへ戻る
              } else {
                  alert(data.message || 'エラーが発生しました');
              }
          } catch(err) {
              alert('通信エラー');
          }
      }
  </script>
</body>
</html>