const displayMaps = {
    // ヴァロラント全キャラ
    agent: {
      'jett': 'ジェット', 'raze': 'レイズ', 'phoenix': 'フェニックス', 'reyna': 'レイナ', 
      'yoru': 'ヨル', 'neon': 'ネオン', 'iso': 'アイソ',
      'sova': 'ソーヴァ', 'breach': 'ブリーチ', 'skye': 'スカイ', 'kayo': 'KAY/O', 
      'fade': 'フェイド', 'gekko': 'ゲッコー', 'tejo': 'テホ',
      'brimstone': 'ブリム', 'viper': 'ヴァイパー', 'omen': 'オーメン', 'astra': 'アストラ', 
      'harbor': 'ハーバー', 'clove': 'クローブ', 'waylay': 'ウェイレイ',
      'sage': 'セージ', 'cypher': 'サイファー', 'killjoy': 'キルジョイ', 'chamber': 'チェンバー', 
      'deadlock': 'デッドロック', 'vyse': 'ヴァイス', 'vito': 'ヴィトー'
    },
    role: {
      'duelist': 'デュエリスト', 'initiator': 'イニシエーター', 
      'controller': 'コントローラー', 'sentinel': 'センチネル', 
      'flex': 'フレックス', 'coach': 'コーチ'
    }
  };
  
  class TeamEditManager {
      constructor() {
          this.init();
      }
  
      init() {
          this.loadTeamData();
          this.setupDeleteButton();
          this.setupEditForm();
          
          // 入力監視 (プレビュー更新)
          document.querySelectorAll('input, select, textarea').forEach(el => {
              el.addEventListener('input', () => this.updatePreview());
              el.addEventListener('change', () => this.updatePreview());
          });
      }
  
      loadTeamData() {
          const urlParams = new URLSearchParams(window.location.search);
          const teamId = urlParams.get('id');
          
          fetch(`team_edit.php?api=get_team&id=${teamId}`)
          .then(r => r.json())
          .then(data => {
              this.teamData = data;
              this.fillForm();
              this.updatePreview();
          });
      }
  
      fillForm() {
          const d = this.teamData;
          const setVal = (id, val) => { 
              const el = document.getElementById(id); 
              if(el) el.value = val || ''; 
          };
  
          setVal('team_name', d.team_name);
          setVal('team_division', d.team_division);
          setVal('activity_time', d.activity_time);
          setVal('description', d.description);
          setVal('recruitment_text', d.recruitment_text);
  
          // ラジオボタン (Status)
          const status = d.team_status || 'existing';
          const r = document.querySelector(`input[name="team_status"][value="${status}"]`);
          if(r) r.checked = true;
  
          // チェックボックス (Wanted Roles)
          if(d.wanted_roles) {
              d.wanted_roles.split(',').forEach(v => {
                  const cb = document.querySelector(`input[name="wanted_roles"][value="${v}"]`);
                  if(cb) cb.checked = true;
              });
          }
          // チェックボックス (Wanted Agents)
          if(d.wanted_agents) {
              d.wanted_agents.split(',').forEach(v => {
                  const cb = document.querySelector(`input[name="wanted_agents"][value="${v}"]`);
                  if(cb) cb.checked = true;
              });
          }
      }
  
      updatePreview() {
          // 名前
          document.getElementById('previewName').innerText = document.getElementById('team_name').value || 'TEAM NAME';
          // ランク
          const div = document.getElementById('team_division').value;
          document.getElementById('previewDivision').innerText = 'AVG: ' + div.toUpperCase();
          // 時間
          document.getElementById('previewTime').innerText = document.getElementById('activity_time').value || '-';
          
          // ステータス
          const status = document.querySelector('input[name="team_status"]:checked')?.value;
          const statusEl = document.getElementById('previewStatus');
          if(status === 'recruiting') {
              statusEl.innerText = '募集中';
              statusEl.style.color = '#ff0078';
          } else {
              statusEl.innerText = '活動中';
              statusEl.style.color = '#e0e0e0';
          }
  
          // 募集ロールプレビュー
          const roles = Array.from(document.querySelectorAll('input[name="wanted_roles"]:checked'))
                             .map(c => displayMaps.role[c.value] || c.value);
          document.getElementById('previewRoles').innerText = roles.length ? roles.join(' / ') : '-';
      }
  
      setupDeleteButton() {
          const btn = document.getElementById('deleteTeamBtn');
          if(btn) btn.addEventListener('click', () => this.deleteTeam());
      }
  
      async deleteTeam() {
          if(!confirm('本当にチームを削除しますか？\nメンバーも全員脱退扱いになります。')) return;
          
          const fd = new FormData();
          fd.append('action', 'delete');
          const urlParams = new URLSearchParams(window.location.search);
          
          await this.sendRequest(fd, `team_edit.php?id=${urlParams.get('id')}`);
      }
  
      setupEditForm() {
          const form = document.getElementById('editTeamForm');
          if(form) {
              form.addEventListener('submit', (e) => {
                  e.preventDefault();
                  const fd = new FormData(form);
                  
                  // チェックボックスの値をカンマ区切り文字列に変換してセット
                  const roles = Array.from(document.querySelectorAll('input[name="wanted_roles"]:checked')).map(c => c.value);
                  fd.set('wanted_roles', roles.join(','));
                  
                  const agents = Array.from(document.querySelectorAll('input[name="wanted_agents"]:checked')).map(c => c.value);
                  fd.set('wanted_agents', agents.join(','));
  
                  const urlParams = new URLSearchParams(window.location.search);
                  this.sendRequest(fd, `team_edit.php?id=${urlParams.get('id')}`);
              });
          }
      }
  
      async sendRequest(fd, url) {
          try {
              const res = await fetch(url, { method:'POST', body:fd });
              const data = await res.json();
              
              if(data.success) {
                  alert(data.message || '完了しました');
                  if(data.redirect) window.location.href = data.redirect;
                  else location.reload(); // 変更反映のためリロード
              } else {
                  alert('エラー: ' + data.message);
              }
          } catch(e) {
              console.error(e);
              alert('通信エラーが発生しました');
          }
      }
  }
  
  document.addEventListener('DOMContentLoaded', () => {
      new TeamEditManager();
  });