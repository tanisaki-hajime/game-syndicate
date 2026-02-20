// Team Select Page JavaScript

document.addEventListener('DOMContentLoaded', () => {
    // カード選択時のアニメーション
    const selectCards = document.querySelectorAll('.select-card');
    
    selectCards.forEach((card, index) => {
        // 遅延表示アニメーション
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 300 + (index * 150));
        
        // クリック時の効果
        card.addEventListener('click', function(e) {
            // ローディング効果
            const icon = this.querySelector('.card-icon i');
            if (icon) {
                icon.style.animation = 'spin 1s linear';
            }
        });
    });

    // 背景アニメーション
    createFloatingElements();
});

// 浮遊する装飾要素を作成
function createFloatingElements() {
    const bg = document.querySelector('.select-bg');
    if (!bg) return;

    for (let i = 0; i < 20; i++) {
        const element = document.createElement('div');
        element.style.cssText = `
            position: absolute;
            width: ${Math.random() * 4 + 2}px;
            height: ${Math.random() * 4 + 2}px;
            background: rgba(255, 0, 120, ${Math.random() * 0.5 + 0.2});
            border-radius: 50%;
            top: ${Math.random() * 100}%;
            left: ${Math.random() * 100}%;
            animation: float ${Math.random() * 10 + 10}s ease-in-out infinite;
            animation-delay: ${Math.random() * 5}s;
        `;
        bg.appendChild(element);
    }

    // アニメーション定義
    const style = document.createElement('style');
    style.textContent = `
        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }
            50% {
                transform: translateY(-100px) translateX(50px);
                opacity: 1;
            }
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
}

// スムーススクロール
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});