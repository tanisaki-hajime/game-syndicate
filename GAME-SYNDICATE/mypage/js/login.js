// ========================================
// 画面切り替え (ログイン <-> 新規登録)
// ========================================
function switchForm(formType) {
  const loginForm = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');

  if (formType === 'register') {
      loginForm.classList.remove('active');
      registerForm.classList.add('active');
  } else {
      registerForm.classList.remove('active');
      loginForm.classList.add('active');
  }
}

// ========================================
// ★追加：強制的に半角英数字にする処理
// ========================================
function enforceAlphanumeric(event) {
    const input = event.target;
    // 全角文字や日本語を空文字に置換し、半角英数字記号のみ許可
    // @はログインID用、その他記号はパスワード用に許可
    input.value = input.value.replace(/[^a-zA-Z0-9@\-_!#$%&()*+,./:;<=>?[\]^`{|}~]/g, '');
}

// ページ読み込み時にイベントを設定
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const mode = urlParams.get('mode');
    
    if (mode === 'register') {
        switchForm('register');
    }

    // クラス 'input-alphanumeric' がついている要素全てに適用
    const inputs = document.querySelectorAll('.input-alphanumeric');
    inputs.forEach(input => {
        input.addEventListener('input', enforceAlphanumeric);
        input.addEventListener('change', enforceAlphanumeric);
        // IME(日本語入力)を無効化するスタイルを適用
        input.style.imeMode = 'disabled';
    });
});

// ========================================
// パスワードの表示/非表示切り替え
// ========================================
function togglePassword(inputId) {
  const input = document.getElementById(inputId);
  const icon = input.parentElement.querySelector('.toggle-password i');

  if (input.type === 'password') {
      input.type = 'text';
      if(icon) {
          icon.classList.remove('fa-eye');
          icon.classList.add('fa-eye-slash');
      }
  } else {
      input.type = 'password';
      if(icon) {
          icon.classList.remove('fa-eye-slash');
          icon.classList.add('fa-eye');
      }
  }
}

// ========================================
// ログイン処理
// ========================================
function handleLogin(event) {
  event.preventDefault();

  const accountId = document.getElementById('login_account_id').value;
  const password = document.getElementById('login_password').value;
  const button = event.target.querySelector('button[type="submit"]');

  if (!accountId || !password) {
    showNotification('アカウントIDとパスワードを入力してください', 'error');
    return;
  }

  button.classList.add('loading');
  button.disabled = true;

  const formData = new FormData();
  formData.append('account_id', accountId);
  formData.append('password', password);

  const urlParams = new URLSearchParams(window.location.search);
  const nextUrl = urlParams.get('next');
  if (nextUrl) {
      formData.append('next', nextUrl);
  }

  fetch('login.php?api=login', {
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
          window.location.href = data.redirect;
        }, 1500);
      } else {
        showNotification(data.message, 'error');
      }
    })
    .catch(error => {
      button.classList.remove('loading');
      button.disabled = false;
      console.error('Error:', error);
      showNotification('ログイン処理に失敗しました', 'error');
    });
}

// ========================================
// 登録処理
// ========================================
function handleRegister(event) {
  event.preventDefault();

  const name = document.getElementById('register_name').value;
  const accountId = document.getElementById('register_account_id').value;
  const mailadress = document.getElementById('register_mailadress').value;
  const password = document.getElementById('register_password').value;
  const passwordConfirm = document.getElementById('register_password_confirm').value;
  const button = event.target.querySelector('button[type="submit"]');

  if (!name || !accountId || !mailadress || !password || !passwordConfirm) {
    showNotification('全ての項目を入力してください', 'error');
    return;
  }
  if (password !== passwordConfirm) {
      showNotification('パスワードが一致しません', 'error');
      return;
  }
  if (password.length < 8) {
      showNotification('パスワードは8文字以上で設定してください', 'error');
      return;
  }
  if (!isValidEmail(mailadress)) {
      showNotification('メールアドレスの形式が正しくありません', 'error');
      return;
  }

  button.classList.add('loading');
  button.disabled = true;

  const formData = new FormData();
  formData.append('name', name);
  formData.append('account_id', accountId);
  formData.append('mailadress', mailadress);
  formData.append('password', password);
  formData.append('password_confirm', passwordConfirm);

  const urlParams = new URLSearchParams(window.location.search);
  const nextUrl = urlParams.get('next');
  if (nextUrl) {
      formData.append('next', nextUrl);
  }

  fetch('login.php?api=register', {
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
          window.location.href = data.redirect;
        }, 1500);
      } else {
        showNotification(data.message, 'error');
      }
    })
    .catch(error => {
      button.classList.remove('loading');
      button.disabled = false;
      console.error('Error:', error);
      showNotification('登録処理に失敗しました', 'error');
    });
}

// ========================================
// ユーティリティ関数
// ========================================
function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

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