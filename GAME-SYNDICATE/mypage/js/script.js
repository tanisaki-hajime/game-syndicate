// MyPage JavaScript

class MyPageManager {
  constructor() {
      this.init();
  }

  init() {
      this.setupAnimations();
      this.setupInteractions();
      this.setupTooltips();
      this.loadProfileData();
  }

  // 初期アニメーション
  setupAnimations() {
      // プロフィールカードのスタッガーアニメーション
      const cards = document.querySelectorAll('.info-card');
      cards.forEach((card, index) => {
          card.style.opacity = '0';
          card.style.transform = 'translateY(30px)';
          
          setTimeout(() => {
              card.style.transition = 'all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
              card.style.opacity = '1';
              card.style.transform = 'translateY(0)';
          }, 100 * index);
      });

      // アバターのフロート効果
      const avatar = document.querySelector('.profile-avatar-large');
      if (avatar) {
          this.setupAvatarFloat(avatar);
      }
  }

  setupAvatarFloat(avatar) {
      let mouseX = 0;
      let mouseY = 0;
      let avatarX = 0;
      let avatarY = 0;

      document.addEventListener('mousemove', (e) => {
          mouseX = (e.clientX - window.innerWidth / 2) / 50;
          mouseY = (e.clientY - window.innerHeight / 2) / 50;
      });

      const animate = () => {
          avatarX += (mouseX - avatarX) * 0.1;
          avatarY += (mouseY - avatarY) * 0.1;
          
          avatar.style.transform = `translate(${avatarX}px, ${avatarY}px)`;
          requestAnimationFrame(animate);
      };

      animate();
  }

  // インタラクション設定
  setupInteractions() {
      // エージェントタグのクリック効果
      document.querySelectorAll('.agent-tag').forEach(tag => {
          tag.addEventListener('click', function() {
              this.style.animation = 'pop 0.3s ease';
              setTimeout(() => {
                  this.style.animation = '';
              }, 300);
          });
      });

      // ソーシャルリンクのホバー効果
      document.querySelectorAll('.social-link').forEach(link => {
          link.addEventListener('mouseenter', function() {
              this.style.transform = 'translateX(5px) scale(1.1)';
          });

          link.addEventListener('mouseleave', function() {
              this.style.transform = 'translateX(0) scale(1)';
          });
      });

      // 編集ボタンのクリック効果
      const editBtn = document.querySelector('a[href="edit_profile.php"]');
      if (editBtn) {
          editBtn.addEventListener('click', (e) => {
              this.showLoadingOverlay();
          });
      }

      // チームカードのクリック効果
      const teamBadge = document.querySelector('.team-badge');
      if (teamBadge) {
          teamBadge.addEventListener('click', function(e) {
              if (!e.target.closest('a')) return;
              
              const card = this;
              card.style.transform = 'scale(0.98)';
              setTimeout(() => {
                  card.style.transform = '';
              }, 150);
          });
      }
  }

  // ツールチップ設定
  setupTooltips() {
      // ランク表示にツールチップを追加
      const rankElements = document.querySelectorAll('.rank-text');
      rankElements.forEach(rank => {
          const rankValue = rank.textContent.trim();
          if (rankValue && rankValue !== '未設定') {
              rank.setAttribute('data-tooltip', `ランク: ${rankValue}`);
          }
      });

      // IGL表示にツールチップを追加
      const iglElements = document.querySelectorAll('.game-stat:has(label:contains("IGL"))');
      iglElements.forEach(el => {
          const label = el.querySelector('label');
          if (label && label.textContent.includes('IGL')) {
              el.setAttribute('data-tooltip', 'In-Game Leader');
          }
      });
  }

  // プロフィールデータの読み込み
  loadProfileData() {
      // 将来的にAJAXでデータを動的に読み込む場合の準備
      const profileData = this.getProfileDataFromPage();
      
      if (profileData.hasTeam) {
          this.highlightTeamSection();
      }

      this.updateStatistics(profileData);
  }

  getProfileDataFromPage() {
      return {
          name: document.querySelector('.profile-hero h1')?.textContent || '',
          hasTeam: !!document.querySelector('.team-badge'),
          agentCount: document.querySelectorAll('.agent-tag').length,
          hasSocialLinks: document.querySelectorAll('.social-link').length > 0
      };
  }

  highlightTeamSection() {
      const teamCard = document.querySelector('.team-card');
      if (teamCard) {
          teamCard.style.borderColor = 'rgba(74, 158, 255, 0.3)';
          teamCard.style.boxShadow = '0 5px 20px rgba(74, 158, 255, 0.1)';
      }
  }

  updateStatistics(data) {
      console.log('Profile Statistics:', {
          name: data.name,
          hasTeam: data.hasTeam,
          agentCount: data.agentCount,
          hasSocialLinks: data.hasSocialLinks
      });
  }

  // ローディングオーバーレイ
  showLoadingOverlay() {
      let overlay = document.querySelector('.loading-overlay');
      
      if (!overlay) {
          overlay = document.createElement('div');
          overlay.className = 'loading-overlay';
          overlay.innerHTML = '<div class="loading-spinner"></div>';
          document.body.appendChild(overlay);
      }

      setTimeout(() => {
          overlay.classList.add('active');
      }, 10);
  }

  hideLoadingOverlay() {
      const overlay = document.querySelector('.loading-overlay');
      if (overlay) {
          overlay.classList.remove('active');
      }
  }

  // トースト通知
  showToast(message, type = 'info') {
      const toast = document.createElement('div');
      toast.className = 'toast-notification';
      
      const colors = {
          success: 'linear-gradient(135deg, #2ed573, #26d07c)',
          error: 'linear-gradient(135deg, #ee5253, #ff6b6b)',
          info: 'linear-gradient(135deg, #4a9eff, #6bb6ff)',
          warning: 'linear-gradient(135deg, #ffb800, #ffa000)'
      };

      toast.style.cssText = `
          position: fixed;
          bottom: 30px;
          right: 30px;
          background: ${colors[type] || colors.info};
          color: white;
          padding: 15px 25px;
          border-radius: 10px;
          box-shadow: 0 8px 20px rgba(0,0,0,0.3);
          z-index: 10000;
          animation: slideUp 0.3s ease;
          font-weight: 500;
      `;
      
      toast.textContent = message;
      document.body.appendChild(toast);
      
      setTimeout(() => {
          toast.style.animation = 'slideDown 0.3s ease';
          setTimeout(() => toast.remove(), 300);
      }, 3000);
  }
}

// アニメーション定義
const style = document.createElement('style');
style.textContent = `
  @keyframes pop {
      0% { transform: scale(1); }
      50% { transform: scale(1.1); }
      100% { transform: scale(1); }
  }

  @keyframes slideUp {
      from {
          transform: translateY(100px);
          opacity: 0;
      }
      to {
          transform: translateY(0);
          opacity: 1;
      }
  }

  @keyframes slideDown {
      from {
          transform: translateY(0);
          opacity: 1;
      }
      to {
          transform: translateY(100px);
          opacity: 0;
      }
  }
`;
document.head.appendChild(style);

// 初期化
let myPageManager;
document.addEventListener('DOMContentLoaded', () => {
  myPageManager = new MyPageManager();
});

// ページ離脱時の確認（編集中の場合）
window.addEventListener('beforeunload', (e) => {
  const editForm = document.querySelector('form[action*="edit"]');
  if (editForm && editForm.classList.contains('dirty')) {
      e.preventDefault();
      e.returnValue = '';
  }
});

// スクロール時のヘッダー固定強化
let lastScroll = 0;
window.addEventListener('scroll', () => {
  const header = document.querySelector('.mypage-header');
  const currentScroll = window.pageYOffset;

  if (currentScroll > 100) {
      header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.3)';
      header.style.background = 'rgba(10, 14, 39, 0.98)';
  } else {
      header.style.boxShadow = 'none';
      header.style.background = 'rgba(10, 14, 39, 0.95)';
  }

  lastScroll = currentScroll;
});

// レスポンシブメニュー（モバイル対応）
const createMobileMenu = () => {
  if (window.innerWidth <= 768) {
      const nav = document.querySelector('.header-nav');
      if (nav && !nav.classList.contains('mobile-ready')) {
          nav.classList.add('mobile-ready');
          
          const hamburger = document.createElement('div');
          hamburger.className = 'hamburger';
          hamburger.innerHTML = '<span></span><span></span><span></span>';
          
          hamburger.addEventListener('click', () => {
              nav.classList.toggle('active');
              hamburger.classList.toggle('active');
          });
          
          document.querySelector('.header-content').prepend(hamburger);
      }
  }
};

window.addEventListener('resize', createMobileMenu);
createMobileMenu();