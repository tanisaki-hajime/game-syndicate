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
      'info': 'info-circle'
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
        background: linear-gradient(135deg, rgba(100, 200, 255, 0.2) 0%, rgba(100, 200, 255, 0.1) 100%);
        border: 1px solid rgba(100, 200, 255, 0.5);
        color: #64c8ff;
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
    const form = document.getElementById('scrimForm');
    
    if (form) {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
  
        const team = document.getElementById('team').value;
        const date = document.getElementById('date').value;
        const message = document.getElementById('message').value;
  
        if (!team || !date) {
          showNotification('チームと日時を選択してください', 'error');
          return;
        }
  
        // フォームデータを送信
        const formData = {
          team_id: team,
          requested_datetime: date,
          message: message || null
        };
  
        console.log('Scrim request:', formData);
  
        // デモ用の処理
        showNotification('スクリム申請を送信中...', 'info');
  
        setTimeout(() => {
          showNotification('スクリム申請を送信しました！', 'success');
          form.reset();
          
          // リアルタイム更新の表現
          addRequestCard(document.getElementById('team').options[team].text, date, message);
        }, 1000);
      });
    }
  
    // アニメーション効果を追加
    addScrollAnimations();
  });
  
  // ===== REQUEST HANDLING =====
  window.acceptRequest = (requestId) => {
    console.log('Accept request:', requestId);
    showNotification('申請を承認しました', 'success');
    
    // UIを更新
    setTimeout(() => {
      location.reload();
    }, 1500);
  };
  
  window.rejectRequest = (requestId) => {
    console.log('Reject request:', requestId);
    showNotification('申請を拒否しました', 'error');
    
    // UIを更新
    setTimeout(() => {
      location.reload();
    }, 1500);
  };
  
  // ===== REQUEST CARD ADDITION =====
  const addRequestCard = (teamName, date, message) => {
    const requestsList = document.getElementById('sentRequests');
    
    const dateObj = new Date(date);
    const dateStr = dateObj.toLocaleDateString('ja-JP', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    });
  
    const cardHTML = `
      <div class="request-card request-pending">
        <div class="request-header">
          <h4>${teamName}</h4>
          <span class="status-badge pending">待機中</span>
        </div>
        <p class="request-time">
          <i class="fas fa-calendar"></i>
          ${dateStr}
        </p>
        ${message ? `<p class="request-message">${message}</p>` : ''}
      </div>
    `;
  
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = cardHTML;
    const card = tempDiv.firstElementChild;
    
    requestsList.insertBefore(card, requestsList.firstChild);
    
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
  
    document.querySelectorAll('.request-card').forEach(el => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(20px)';
      el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
      observer.observe(el);
    });
  };
  
  // ===== INTERACTIONS =====
  document.addEventListener('DOMContentLoaded', () => {
    // フォーム要素のインタラクション
    const inputs = document.querySelectorAll('.scrim-form input, .scrim-form select, .scrim-form textarea');
    
    inputs.forEach(input => {
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
  
  console.log('🎮 Scrim Page Loaded');