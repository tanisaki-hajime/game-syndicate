// ===== MODAL FUNCTIONS =====
function openModal(modalName) {
  const modal = document.getElementById(`${modalName}Modal`);
  if (modal) {
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    playModalAnimation();
  }
}

function closeModal(modalName) {
  const modal = document.getElementById(`${modalName}Modal`);
  if (modal) {
    modal.classList.remove('show');
    document.body.style.overflow = 'auto';
  }
}

function playModalAnimation() {
  const modalContent = document.querySelector('.modal.show .modal-content');
  if (modalContent) {
    modalContent.style.animation = 'none';
    setTimeout(() => {
      modalContent.style.animation = 'modalSlide 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
    }, 10);
  }
}

// ESCキーでモーダルを閉じる
document.addEventListener('keydown', function (event) {
  if (event.key === 'Escape') {
    const loginModal = document.getElementById('loginModal');
    if (loginModal && loginModal.classList.contains('show')) {
      closeModal('login');
    }
  }
});


// ===== NOTIFICATION SYSTEM =====
function showNotification(message, type = 'info') {
  const existingNotification = document.querySelector('.notification');
  if (existingNotification) {
    existingNotification.remove();
  }

  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;

  const icon = getNotificationIcon(type);
  notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${icon}"></i>
            <span>${message}</span>
        </div>
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

function getNotificationIcon(type) {
  const icons = {
    'success': 'check-circle',
    'error': 'exclamation-circle',
    'info': 'info-circle'
  };
  return icons[type] || 'info-circle';
}

// 通知のスタイル
const notificationStyle = document.createElement('style');
notificationStyle.textContent = `
    .notification {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        padding: 1.2rem 1.5rem;
        border-radius: 12px;
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 0.8rem;
        z-index: 2000;
        animation: slideInRight 0.3s ease;
        transform: translateX(400px);
        transition: transform 0.3s ease;
        backdrop-filter: blur(10px);
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
    
    @media (max-width: 768px) {
        .notification {
            bottom: 1rem;
            right: 1rem;
            left: 1rem;
        }
    }
`;
document.head.appendChild(notificationStyle);

// ===== GAME NAVIGATION =====
// 以前のロジックは削除されました（カードからのリンク廃止のため）
function goToGame(gameName) {
    // 機能なし
    return;
}

// ===== LOADING SCREEN =====
window.addEventListener('load', function () {
  const loadingScreen = document.getElementById('loadingScreen');
  if (loadingScreen) {
    setTimeout(() => {
      loadingScreen.classList.add('fade-out');
      setTimeout(() => {
        loadingScreen.style.display = 'none';
      }, 600);
    }, 1200);
  }
});

// ===== SCROLL ANIMATIONS =====
document.addEventListener('DOMContentLoaded', function () {
  initializeScrollAnimations();
  initializeInteractions();
});

function initializeScrollAnimations() {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, observerOptions);

  const animateElements = document.querySelectorAll('.game-card, .feature-card');
  animateElements.forEach(el => {
    observer.observe(el);
  });
}

function initializeInteractions() {
  // ゲームカードのホバーエフェクト
  const gameCards = document.querySelectorAll('.game-card');
  gameCards.forEach(card => {
    card.addEventListener('mouseenter', function () {
      this.style.transform = 'translateY(-10px) scale(1.02)';
    });

    card.addEventListener('mouseleave', function () {
      this.style.transform = 'translateY(0) scale(1)';
    });
  });

  // フィーチャーカードのホバーエフェクト
  const featureCards = document.querySelectorAll('.feature-card');
  featureCards.forEach(card => {
    card.addEventListener('mouseenter', function () {
      this.style.transform = 'translateY(-10px)';
    });

    card.addEventListener('mouseleave', function () {
      this.style.transform = 'translateY(0)';
    });
  });

  // ボタンのリップルエフェクト
  document.querySelectorAll('.btn').forEach(button => {
    button.addEventListener('click', function (e) {
      const ripple = document.createElement('span');
      const rect = this.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      const x = e.clientX - rect.left - size / 2;
      const y = e.clientY - rect.top - size / 2;

      ripple.style.width = ripple.style.height = size + 'px';
      ripple.style.left = x + 'px';
      ripple.style.top = y + 'px';
      ripple.style.position = 'absolute';
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
}

// リップルアニメーション
const rippleStyle = document.createElement('style');
rippleStyle.textContent = `
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
document.head.appendChild(rippleStyle);

// ===== PARALLAX EFFECT ON SCROLL =====
window.addEventListener('scroll', function () {
  const scrolled = window.pageYOffset;
  const heroBg = document.querySelector('.bg-animated');

  if (heroBg) {
    heroBg.style.transform = `translateY(${scrolled * 0.5}px)`;
  }
});

// ===== MOUSE MOVE EFFECT =====
document.addEventListener('mousemove', function (e) {
  const glowingCircle = document.querySelector('.glowing-circle');

  if (glowingCircle) {
    const x = (window.innerWidth - e.clientX * 1) / 100;
    const y = (window.innerHeight - e.clientY * 1) / 100;

    glowingCircle.style.transform = `translate(calc(-50% + ${x * 0.5}px), calc(-50% + ${y * 0.5}px)) translateY(var(--float-offset, 0px))`;
  }
});

console.log('Game Syndicate Portal - Ready! 🎮');