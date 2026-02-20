-- 1. データベース設定
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

-- 2. 既存テーブル削除
DROP TABLE IF EXISTS notification_messages; -- 新規
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS user_agents;
DROP TABLE IF EXISTS user_game_profiles;
DROP TABLE IF EXISTS team_members;
DROP TABLE IF EXISTS scrim;
DROP TABLE IF EXISTS report;
DROP TABLE IF EXISTS user;
DROP TABLE IF EXISTS team;

-- 3. テーブル作成

-- 【ユーザー】
CREATE TABLE user (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    account_id VARCHAR(100) UNIQUE NOT NULL,
    mailadress VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    birthday DATE,
    age INT,
    user_icon VARCHAR(255),
    agent VARCHAR(50), role VARCHAR(255), igl VARCHAR(10) DEFAULT 'no', playstyle VARCHAR(20) DEFAULT 'casual', job VARCHAR(20), nowrank VARCHAR(50), peekrank VARCHAR(50),
    team_id INT,
    x_link VARCHAR(255), twitch_link VARCHAR(255), tracker_link VARCHAR(255), youtube_link VARCHAR(255), line_link VARCHAR(255),
    show_mail TINYINT(1) DEFAULT 0,
    show_age TINYINT(1) DEFAULT 0,
    show_line TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES team(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 【チーム】
CREATE TABLE team (
    id INT PRIMARY KEY AUTO_INCREMENT,
    team_name VARCHAR(255) NOT NULL,
    game_title VARCHAR(50) NOT NULL DEFAULT 'valorant',
    team_status ENUM('existing', 'recruiting') NOT NULL DEFAULT 'existing',
    team_icon VARCHAR(255),
    header_image VARCHAR(255),
    team_division VARCHAR(50) DEFAULT 'unrated',
    description TEXT,
    activity_time VARCHAR(255),
    recruitment_text TEXT,
    team_count_member INT DEFAULT 1,
    wanted_roles VARCHAR(255),
    wanted_agents TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 【メンバー】
CREATE TABLE team_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    team_id INT NOT NULL,
    user_id INT NOT NULL,
    role VARCHAR(50) DEFAULT 'member',
    status ENUM('pending', 'approved', 'joined', 'rejected') DEFAULT 'pending',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES team(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participation (team_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 【ゲームプロフィール】
CREATE TABLE user_game_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    game_title VARCHAR(50) NOT NULL,
    ingame_name VARCHAR(100),
    current_rank VARCHAR(50),
    highest_rank VARCHAR(50),
    role VARCHAR(255),
    main_character VARCHAR(255),
    playstyle VARCHAR(50) DEFAULT 'casual',
    igl VARCHAR(10) DEFAULT 'no',
    note TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_game (user_id, game_title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 【通知】
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    target_user_id INT,
    sender_user_id INT,
    sender_team_id INT,
    type VARCHAR(50) NOT NULL DEFAULT 'system',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link_id INT COMMENT '関連ID',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (target_user_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_user_id) REFERENCES user(id) ON DELETE SET NULL,
    FOREIGN KEY (sender_team_id) REFERENCES team(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 【通知メッセージ (チャット用) ★新規】
CREATE TABLE notification_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    user_id INT NOT NULL COMMENT '発言者',
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 【スクリム】
CREATE TABLE scrim (
    id INT PRIMARY KEY AUTO_INCREMENT,
    applicant_team_id INT NOT NULL,
    target_team_id INT NOT NULL,
    scrim_date DATETIME,
    status ENUM('pending', 'accepted', 'rejected', 'completed', 'cancelled') DEFAULT 'pending',
    memo TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (applicant_team_id) REFERENCES team(id) ON DELETE CASCADE,
    FOREIGN KEY (target_team_id) REFERENCES team(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 【通報】
CREATE TABLE report (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reported_user_id INT,
    reporter_user_id INT NOT NULL,
    report_type VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 【エージェント(互換)】
CREATE TABLE user_agents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    agent_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- チャットメッセージ用テーブル
CREATE TABLE IF NOT EXISTS notification_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL COMMENT 'どの通知(スレッド)に対する会話か',
    user_id INT NOT NULL COMMENT '発言者ID',
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notification_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL COMMENT '通知ID',
    user_id INT NOT NULL COMMENT '発言者ID',
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notification_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL COMMENT '通知ID',
    user_id INT NOT NULL COMMENT '発言者ID',
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ゲーム別プロフィールテーブルの再構築
DROP TABLE IF EXISTS user_game_profiles;

CREATE TABLE user_game_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    game_title VARCHAR(50) NOT NULL COMMENT 'valorant, apex, lol',
    ingame_name VARCHAR(100) COMMENT 'ゲーム内ID',
    
    -- ランク情報
    current_rank VARCHAR(50) COMMENT '現在のランク',
    highest_rank VARCHAR(50) COMMENT '最高ランク',
    
    -- ロール情報 (メイン1つ、サブ複数)
    main_role VARCHAR(50) COMMENT 'メインロール',
    sub_role VARCHAR(255) COMMENT 'サブロール(カンマ区切り)',
    
    -- キャラクター (複数)
    main_character VARCHAR(255) COMMENT 'キャラ(カンマ区切り)',
    
    -- スタイル・IGL
    playstyle VARCHAR(50) DEFAULT 'casual' COMMENT 'casual(エンジョイ) or competitive(ガチ)',
    igl VARCHAR(10) DEFAULT 'no' COMMENT 'yes or no',
    
    note TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_game (user_id, game_title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ゲーム別プロフィールテーブルの再構築
DROP TABLE IF EXISTS user_game_profiles;

CREATE TABLE user_game_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    game_title VARCHAR(50) NOT NULL COMMENT 'valorant, apex, lol',
    ingame_name VARCHAR(100) COMMENT 'ゲーム内ID',
    
    -- ランク情報
    current_rank VARCHAR(50) COMMENT '現在のランク',
    highest_rank VARCHAR(50) COMMENT '最高ランク',
    
    -- ロール情報 (メイン1つ、サブ複数)
    main_role VARCHAR(50) COMMENT 'メインロール',
    sub_role VARCHAR(255) COMMENT 'サブロール(カンマ区切り)',
    
    -- キャラクター (複数)
    main_character VARCHAR(255) COMMENT 'キャラ(カンマ区切り)',
    
    -- スタイル・IGL
    playstyle VARCHAR(50) DEFAULT 'casual' COMMENT 'casual(エンジョイ) or competitive(ガチ)',
    igl VARCHAR(10) DEFAULT 'no' COMMENT 'yes or no',
    
    note TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_game (user_id, game_title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- チームメンバーテーブルの役割カラムを拡張
-- 既存データを保持したままカラム定義を変更
ALTER TABLE team_members MODIFY COLUMN role VARCHAR(50) DEFAULT 'member' COMMENT 'owner, member, main, sub, coach, analyst, manager';

-- (念のため) ゲームプロフィールテーブルも確認
CREATE TABLE IF NOT EXISTS user_game_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    game_title VARCHAR(50) NOT NULL,
    ingame_name VARCHAR(100),
    current_rank VARCHAR(50),
    highest_rank VARCHAR(50),
    main_role VARCHAR(50),
    sub_role VARCHAR(255),
    main_character VARCHAR(255),
    playstyle VARCHAR(50) DEFAULT 'casual',
    igl VARCHAR(10) DEFAULT 'no',
    note TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_game (user_id, game_title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notification_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL COMMENT '通知ID',
    user_id INT NOT NULL COMMENT '発言者ID',
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- チームテーブルに設定カラムを追加
ALTER TABLE team ADD COLUMN discord_webhook VARCHAR(255) COMMENT 'DiscordウェブフックURL';
ALTER TABLE team ADD COLUMN required_members INT DEFAULT 5 COMMENT '活動に必要な人数';
ALTER TABLE team ADD COLUMN activity_start_time TIME DEFAULT '21:00:00';
ALTER TABLE team ADD COLUMN activity_end_time TIME DEFAULT '24:00:00';

-- スケジュールテーブル (Firebaseの代わり)
CREATE TABLE team_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    team_id INT NOT NULL,
    user_id INT NOT NULL,
    schedule_date DATE NOT NULL,
    status ENUM('ok', 'ng', 'tentative') NOT NULL DEFAULT 'ng',
    comment VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES team(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    UNIQUE KEY unique_schedule (team_id, user_id, schedule_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- チーム設定に「通知時間」を追加
ALTER TABLE team ADD COLUMN notification_time TIME DEFAULT '20:00:00' COMMENT 'Discord通知を行う時間';

-- (まだ実行していなければ) 活動時間のカラムも確認
-- ALTER TABLE team ADD COLUMN activity_start_time TIME DEFAULT '21:00:00';
-- ALTER TABLE team ADD COLUMN activity_end_time TIME DEFAULT '24:00:00';
-- ALTER TABLE team ADD COLUMN required_members INT DEFAULT 5;
-- ALTER TABLE team ADD COLUMN discord_webhook VARCHAR(255);

-- 3. スケジュール管理テーブル (なければ作成)
CREATE TABLE IF NOT EXISTS team_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    team_id INT NOT NULL,
    user_id INT NOT NULL,
    schedule_date DATE NOT NULL,
    status ENUM('ok', 'ng', 'tentative') NOT NULL DEFAULT 'ng',
    comment VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES team(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    UNIQUE KEY unique_schedule (team_id, user_id, schedule_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 招待状にメッセージを含めるためのカラム追加
-- notificationsテーブルがまだない場合は作成されますが、既存の場合はALTERのみ実行されます
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    target_user_id INT NOT NULL,
    sender_user_id INT,
    sender_team_id INT,
    type VARCHAR(50),
    title VARCHAR(255),
    message TEXT,
    link_id INT,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 既存テーブルへの変更（招待メッセージ用）
-- 既に存在する場合はエラーになりますが無視してOKです
-- ALTER TABLE notifications ADD COLUMN invite_message TEXT; 
-- ※今回はmessageカラムに統合して保存するため、追加カラムは必須ではありません。

-- 1. 待機列 (マッチング待ちのユーザー)
CREATE TABLE IF NOT EXISTS fast_party_queue (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    game_title VARCHAR(50) NOT NULL,
    target_rank VARCHAR(50) DEFAULT 'any', -- 希望ランク (any:指定なし)
    status ENUM('waiting', 'matched') DEFAULT 'waiting',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_waiting (user_id) -- 1人1つまで
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. 成立したパーティルーム
CREATE TABLE IF NOT EXISTS fast_party_rooms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    game_title VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. ルームメンバー (誰がどの部屋にいるか)
CREATE TABLE IF NOT EXISTS fast_party_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES fast_party_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);

-- 4. パーティ専用チャット
CREATE TABLE IF NOT EXISTS fast_party_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES fast_party_rooms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 既存のフルパ関連テーブルをリセット
DROP TABLE IF EXISTS fast_party_queue;
DROP TABLE IF EXISTS fast_party_messages;
DROP TABLE IF EXISTS fast_party_members;
DROP TABLE IF EXISTS fast_party_rooms;

-- 1. パーティルーム (掲示板)
CREATE TABLE fast_party_rooms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    host_user_id INT NOT NULL, -- ホスト（部屋主）
    game_title VARCHAR(50) NOT NULL,
    target_ranks VARCHAR(255) DEFAULT 'any', -- 募集ランク (カンマ区切り: "gold,platinum")
    status ENUM('recruiting', 'full', 'closed') DEFAULT 'recruiting',
    current_members INT DEFAULT 1,
    max_members INT DEFAULT 5,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (host_user_id) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. ルームメンバー
CREATE TABLE fast_party_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES fast_party_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    UNIQUE KEY unique_join (room_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. チャットメッセージ
CREATE TABLE fast_party_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES fast_party_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 1. ユーザーに通報数カウントを追加
ALTER TABLE user ADD COLUMN report_count INT DEFAULT 0;

-- 2. 通報履歴テーブル
CREATE TABLE IF NOT EXISTS reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    target_user_id INT NOT NULL,
    reporter_user_id INT NOT NULL,
    type VARCHAR(50),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (target_user_id) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. スクリムマッチング用 (募集掲示板のようなもの)
-- 以前のscrimテーブルがあればそれを活用しますが、なければ作成
CREATE TABLE IF NOT EXISTS scrim_recruits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    team_id INT NOT NULL,
    game_title VARCHAR(50) NOT NULL,
    recruit_date DATE NOT NULL,
    start_time TIME,
    end_time TIME,
    description TEXT,
    status ENUM('open', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES team(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

