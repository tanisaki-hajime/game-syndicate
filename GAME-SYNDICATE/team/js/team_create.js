// ========================================
// 画像プレビュー
// ========================================
function previewTeamIcon(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('iconPreviewImg');
    const previewContainer = document.getElementById('iconPreview');
  
    if (file) {
      const reader = new FileReader();
      reader.onload = (e) => {
        preview.style.backgroundImage = `url(${e.target.result})`;
        previewContainer.style.display = 'block';
      };
      reader.readAsDataURL(file);
    }
  }
  
  // ========================================
  // フォーム処理
  // ========================================
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('teamCreateForm');
    const descriptionTextarea = document.getElementById('description');
    const charCount = document.getElementById('charCount');
  
    // 文字数カウント
    if (descriptionTextarea && charCount) {
      descriptionTextarea.addEventListener('input', (e) => {
        charCount.textContent = e.target.value.length;
  
        if (e.target.value.length > 450) {
          charCount.parentElement.style.color = '#ff6464';
        } else {
          charCount.parentElement.style.color = '';
        }
      });
    }
  
    // フォーム送信
    if (form) {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
  
        const team_name = document.getElementById('team_name').value;
        const team_division = document.getElementById('team_division').value;
        const description = document.getElementById('description').value;
        const team_icon = document.getElementById('team_icon');
        const button = form.querySelector('button[type="submit"]');
  
        if (!team_name || !team_division) {
          showNotification('チーム名と平均ランクを入力してください', 'error');
          return;
        }
  
        button.classList.add('loading');
        button.disabled = true;
  
        const formData = new FormData();
        formData.append('team_name', team_name);
        formData.append('team_division', team_division);
        formData.append('description', description);
  
        if (team_icon.files.length > 0) {
          formData.append('team_icon', team_icon.files[0]);
        }
  
        fetch('team_create.php?api=create_team', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            button.classList.remove('loading');
            button.disabled = false;
  
            if (data.success) {
              showNotification(data.message, 'success');
              setTimeout(() => {
                window.location.href = 'team_search.php';
              }, 1500);
            } else {
              showNotification(data.message || 'エラーが発生しました', 'error');
            }
          })
          .catch(error => {
            button.classList.remove('loading');
            button.disabled = false;
            console.error('Error:', error);
            showNotification('チーム作成に失敗しました', 'error');
          });
      });
    }
  });
  
  // ========================================
  // 通知表示
  // ========================================
  function showNotification(message, type = 'info') {
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
      existingNotification.remove();
    }
  
    const notification = document.createElement('div');
    const iconClass = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
      <i class="fas fa-${iconClass}"></i>
      <span>${message}</span>
    `;
  
    document.body.appendChild(notification);
  
    setTimeout(() => {
      notification.classList.add('show');
    }, 10);
  
    setTimeout(() => {
      notification.classList.remove('show');
      setTimeout(() => {
        notification.remove();
      }, 300);
    }, 3000);
  }
  
  // 通知スタイル
  const notificationStyle = document.createElement('style');
  notificationStyle.textContent = `
    .notification {
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      padding: 1.2rem 1.5rem;
      border-radius: 8px;
      font-weight: bold;
      display: flex;
      align-items: center;
      gap: 0.8rem;
      z-index: 2000;
      animation: slideInRight 0.3s ease;
      transform: translateX(400px);
      transition: transform 0.3s ease;
    }
  
    .notification.show {
      transform: translateX(0);
    }
  
    .notification-success {
      background: linear-gradient(135deg, rgba(0, 200, 100, 0.2) 0%, rgba(0, 200, 100, 0.1) 100%);
      border: 1px solid rgba(0, 200, 100, 0.5);
      color: #00c864;
    }
  
    .notification-error {
      background: linear-gradient(135deg, rgba(255, 100, 100, 0.2) 0%, rgba(255, 100, 100, 0.1) 100%);
      border: 1px solid rgba(255, 100, 100, 0.5);
      color: #ff6464;
    }
  
    .notification-info {
      background: linear-gradient(135deg, rgba(100, 150, 255, 0.2) 0%, rgba(100, 150, 255, 0.1) 100%);
      border: 1px solid rgba(100, 150, 255, 0.5);
      color: #6496ff;
    }
  
    @keyframes slideInRight {
      from {
        transform: translateX(400px);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
  
    .btn.loading {
      opacity: 0.7;
      pointer-events: none;
    }
  
    @media (max-width: 768px) {
      .notification {
        bottom: 1rem;
        right: 1rem;
        left: 1rem;
      }
    }
  `;
  document.head.appendChild(notificationStyle);
  
  console.log('✅ Team Create Script Loaded');