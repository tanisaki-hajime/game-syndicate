<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン - Valorant Team Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/login.css">
    <style>
        /* 追加スタイル: 戻るボタン */
        .back-link {
            position: absolute;
            top: 30px;
            left: 30px;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            z-index: 100;
            background: rgba(0, 0, 0, 0.3);
            padding: 10px 20px;
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .back-link:hover {
            color: #fff;
            background: rgba(0, 0, 0, 0.5);
            border-color: rgba(255, 255, 255, 0.3);
        }
        @media (max-width: 480px) {
            .back-link { top: 15px; left: 15px; font-size: 0.9rem; padding: 8px 15px; }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <a href="../top/top.php" class="back-link">
            <i class="fas fa-arrow-left"></i> TOPへ戻る
        </a>

        <div class="login-background">
            <div class="bg-circle circle-1"></div>
            <div class="bg-circle circle-2"></div>
            <div class="bg-circle circle-3"></div>
        </div>

        <div class="login-wrapper">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-crosshairs"></i>
                    <span>VALORANT<br>TEAM PORTAL</span>
                </div>
            </div>

            <div id="loginForm" class="form-wrapper active">
                <h2><i class="fas fa-sign-in-alt"></i> ログイン</h2>
                <form onsubmit="handleLogin(event)">
                    <div class="form-group">
                        <label><i class="fas fa-at"></i> アカウントID</label>
                        <input 
                            type="text" 
                            id="login_account_id" 
                            class="input-alphanumeric"
                            placeholder="@から始まるID" 
                            inputmode="latin"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> パスワード</label>
                        <div class="password-input">
                            <input 
                                type="password" 
                                id="login_password" 
                                class="input-alphanumeric"
                                placeholder="パスワード" 
                                inputmode="latin"
                                required
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword('login_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> ログイン
                    </button>
                </form>

                <div class="form-footer">
                    <p>アカウントを持っていませんか？ 
                        <button type="button" class="link-btn" onclick="switchForm('register')">
                            新規登録
                        </button>
                    </p>
                </div>
                
                </div>

            <div id="registerForm" class="form-wrapper">
                <h2><i class="fas fa-user-plus"></i> 新規登録</h2>
                <form onsubmit="handleRegister(event)">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> プレイヤー名</label>
                        <input 
                            type="text" 
                            id="register_name" 
                            placeholder="プレイヤー名" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-at"></i> アカウントID</label>
                        <input 
                            type="text" 
                            id="register_account_id" 
                            class="input-alphanumeric"
                            placeholder="@で始まるID" 
                            inputmode="latin"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> メールアドレス</label>
                        <input 
                            type="email" 
                            id="register_mailadress" 
                            placeholder="example@email.com" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> パスワード</label>
                        <div class="password-input">
                            <input 
                                type="password" 
                                id="register_password" 
                                class="input-alphanumeric"
                                placeholder="8文字以上" 
                                inputmode="latin"
                                required
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword('register_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> パスワード確認</label>
                        <div class="password-input">
                            <input 
                                type="password" 
                                id="register_password_confirm" 
                                class="input-alphanumeric"
                                placeholder="パスワード確認" 
                                inputmode="latin"
                                required
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword('register_password_confirm')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> 登録
                    </button>
                </form>

                <div class="form-footer">
                    <p>既にアカウントを持っていますか？ 
                        <button type="button" class="link-btn" onclick="switchForm('login')">
                            ログイン
                        </button>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="js/login.js"></script>
</body>
</html>