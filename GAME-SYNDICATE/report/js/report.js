// ===== NOTIFICATION =====
const showNotification = (message, type = 'info') => {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
      <div class="notification-content">
        <i class="fas fa-${getIcon(type)}"></i>
        <span>${message}</span>
      </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
      notification.classList.add('show');
    }, 10);
  
    setTimeout(() => {
      notification.classList.remove('show');
      setTimeout(() => notification.remove(), 300);
    }, 3000);
  };
  
  const getIcon = (type) => {
    const icons = {
      'success': 'check-circle',
      'error': 'exclamation-circle',
      'info': 'info-circle',
      'warning': 'exclamation-triangle'
    };
    return icons[type] || 'info-circle';
  };
  
  // ===== NOTIFICATION STYLES =====
  (() => {
    const style = document.createElement('style');
    style.textContent = `
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
  
      .notification-content {
        display: flex;
        align-items: center;
        gap: 0.8rem;
      }
  
      .notification-success {
        background: linear-gradient(135deg, rgba(0, 210, 106, 0.2) 0%, rgba(0, 210, 106, 0.1) 100%);
        border: 1px solid rgba(0, 210, 106, 0.5);
        color: #00d26a;
      }
  
      .notification-error {
        background: linear-gradient(135deg, rgba(255, 68, 68, 0.2) 0%, rgba(255, 68, 68, 0.1) 100%);
        border: 1px solid rgba(255, 68, 68, 0.5);
        color: #ff6464;
      }
  
      .notification-info {
        background: linear-gradient(135deg, rgba(100, 181, 246, 0.2) 0%, rgba(100, 181, 246, 0.1) 100%);
        border: 1px solid rgba(100, 181, 246, 0.5);
        color: #64b5f6;
      }
  
      .notification-warning {
        background: linear-gradient(135deg, rgba(255, 165, 0, 0.2) 0%, rgba(255, 165, 0, 0.1) 100%);
        border: 1px solid rgba(255, 165, 0, 0.5);
        color: #ffa500;
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
  
      @media (max-width: 768px) {
        .notification {
          bottom: 1rem;
          right: 1rem;
          left: 1rem;
        }
      }
    `;
    document.head.appendChild(style);
  })();
  
  // ===== FORM HANDLING =====
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('reportForm');
    const detailsTextarea = document.getElementById('details');
    const charCount = document.getElementById('charCount');
  
    // 文字数カウント
    if (detailsTextarea && charCount) {
      detailsTextarea.addEventListener('input', (e) => {
        charCount.textContent = e.target.value.length;
        
        if (e.target.value.length > 1800) {
          charCount.parentElement.style.color = '#ff6464';
        } else {
          charCount.parentElement.style.color = '';
        }
      });
    }
  
    // ファイル入力
    const fileInput = document.getElementById('evidence');
    if (fileInput) {
      fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
          if (file.size > 5 * 1024 * 1024) {
            showNotification('ファイルサイズは5MB以下にしてください', 'error');
            fileInput.value = '';
            return;
          }
  
          if (!file.type.startsWith('image/')) {
            showNotification('画像ファイルを選択してください', 'error');
            fileInput.value = '';
            return;
          }
  
          const fileName = fileInput.parentElement.querySelector('.file-name');
          if (fileName) {
            fileName.textContent = file.name;
          }
        }
      });
    }
  
    // フォーム送信
    if (form) {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
  
        const type = document.getElementById('type').value;
        const reportedPlayer = document.getElementById('reportedPlayer').value;
        const incident = document.getElementById('incident').value;
        const details = document.getElementById('details').value;
        const agree = document.getElementById('agree').checked;
  
        // バリデーション
        if (!type || !reportedPlayer || !incident || !details) {
          showNotification('すべての必須項目を入力してください', 'error');
          return;
        }
  
        if (details.length < 50) {
          showNotification('詳細説明は50文字以上で入力してください', 'warning');
          return;
        }
  
        if (!agree) {
          showNotification('虚偽報告でないことを確認してください', 'error');
          return;
        }
  
        // フォーム送信
        const formData = {
          type,
          reportedPlayer,
          incident,
          details,
          file: fileInput.files[0] || null
        };
  
        console.log('Report data:', formData);
  
        // デモ用の処理
        showNotification('通報を送信中...', 'info');
  
        setTimeout(() => {
          showNotification('通報を送信しました。ご報告ありがとうございます。', 'success');
          form.reset();
          charCount.textContent = '0';
          
          // リアルタイムで表示
          addReportCard(type, reportedPlayer, details);
        }, 1500);
      });
    }
  
    // アニメーション効果
    addScrollAnimations();
  });
  
  // ===== REPORT CARD ADDITION =====
  const addReportCard = (type, playerName, description) => {
    const myReports = document.getElementById('myReports');
    if (!myReports) return;
  
    const typeLabels = {
      'harassment': 'ハラスメント',
      'cheating': 'チーティング疑い',
      'inappropriate': '不適切な言動',
      'spam': 'スパム',
      'bug': 'バグ報告',
      'other': 'その他'
    };
  
    const typeClasses = {
      'harassment': 'type-harassment',
      'cheating': 'type-cheating',
      'inappropriate': 'type-inappropriate',
      'spam': 'type-spam',
      'bug': 'type-bug',
      'other': 'type-other'
    };
  
    const now = new Date();
    const dateStr = now.toLocaleDateString('ja-JP', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    });
  
    const cardHTML = `
      <div class="report-card report-status-reviewing">
        <div class="report-status-indicator"></div>
        <div class="report-info">
          <div class="report-type-badge ${typeClasses[type]}">${typeLabels[type]}</div>
          <h4>${playerName}</h4>
          <p class="report-description">${description.substring(0, 50)}...</p>
          <p class="report-date">
            <i class="fas fa-calendar"></i>
            ${dateStr}
          </p>
        </div>
        <div class="report-status reviewing">
          <i class="fas fa-hourglass-half"></i>
          <span>審査中</span>
        </div>
      </div>
    `;
  
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = cardHTML;
    const card = tempDiv.firstElementChild;
    
    myReports.insertBefore(card, myReports.firstChild);
    
    // アニメーション効果
    card.style.opacity = '0';
    card.style.transform = 'translateX(-20px)';
    
    setTimeout(() => {
      card.style.transition = 'all 0.4s ease-out';
      card.style.opacity = '1';
      card.style.transform = 'translateX(0)';
    }, 10);
  };
  
  // ===== SCROLL ANIMATIONS =====
  const addScrollAnimations = () => {
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };
  
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
          observer.unobserve(entry.target);
        }
      });
    }, observerOptions);
  
    document.querySelectorAll('.report-card, .guideline-item').forEach(el => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(20px)';
      el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
      observer.observe(el);
    });
  };
  
  // ===== INTERACTIONS =====
  document.addEventListener('DOMContentLoaded', () => {
    // フォーム要素のインタラクション
    const inputs = document.querySelectorAll('.report-form input, .report-form select, .report-form textarea');
    
    inputs.forEach(input => {
      if (input.type === 'checkbox') return;
  
      input.addEventListener('focus', function() {
        this.parentElement.style.transform = 'scale(1.02)';
      });
  
      input.addEventListener('blur', function() {
        this.parentElement.style.transform = 'scale(1)';
      });
    });
  
    // ボタンのリップル効果
    document.querySelectorAll('.btn').forEach(btn => {
      btn.addEventListener('click', function(e) {
        if (e.target.type === 'submit' && !this.form.checkValidity()) {
          return;
        }
  
        const ripple = document.createElement('span');
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
  
        ripple.style.position = 'absolute';
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.style.background = 'rgba(255, 255, 255, 0.5)';
        ripple.style.borderRadius = '50%';
        ripple.style.animation = 'rippleEffect 0.6s ease-out';
        ripple.style.pointerEvents = 'none';
  
        this.style.position = 'relative';
        this.style.overflow = 'hidden';
        this.appendChild(ripple);
  
        setTimeout(() => ripple.remove(), 600);
      });
    });
  });
  
  // リップルエフェクトのアニメーション
  (() => {
    const style = document.createElement('style');
    style.textContent = `
      @keyframes rippleEffect {
        0% {
          transform: scale(0);
          opacity: 1;
        }
        100% {
          transform: scale(1);
          opacity: 0;
        }
      }
    `;
    document.head.appendChild(style);
  })();
  
  console.log('🎮 Report Page Loaded');