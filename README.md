# 勤怠管理アプリ

## アプリケーション概要

一般ユーザーの出勤・退勤・休憩登録や勤怠確認、勤怠修正申請ができる勤怠管理アプリです。

管理者は、スタッフの勤怠確認、勤怠情報の修正、修正申請の承認を行えます。

## 機能一覧

### 一般ユーザー

* 会員登録
* ログイン / ログアウト
* メール認証
* 認証メール再送
* 出勤登録
* 退勤登録
* 休憩開始
* 休憩終了
* 月次勤怠一覧表示
* 勤怠詳細表示
* 勤怠修正申請
* 修正申請一覧表示
* 承認待ち / 承認済み申請の切り替え

### 管理者

* 管理者ログイン / ログアウト
* 日次勤怠一覧表示
* 表示日の切り替え
* 勤怠詳細表示
* 勤怠情報の修正
* スタッフ一覧表示
* スタッフ別月次勤怠一覧表示
* 修正申請一覧表示
* 修正申請詳細表示
* 修正申請の承認

## 環境構築

### Dockerビルド

1. リポジトリをクローンする

```bash
git clone git@github.com:misaki-m11111/laravel-attendance-app.git
```

2. プロジェクトディレクトリへ移動する

```bash
cd laravel-attendance-app
```

3. Docker Desktopを起動する

4. Dockerコンテナを起動する

```bash
docker compose up -d --build
```

※ MySQLが起動しない場合は、使用している環境に合わせて`docker-compose.yml`を編集してください。

## Laravel環境構築

1. PHPコンテナに入る

```bash
docker compose exec php bash
```

2. Composerパッケージをインストールする

```bash
composer install
```

3. `.env`ファイルを作成する

```bash
cp .env.example .env
```

4. `.env`のデータベース設定を確認する

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=attendance_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

5. `.env`のメール設定を確認する

```env
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

6. アプリケーションキーを作成する

```bash
php artisan key:generate
```

7. マイグレーションを実行する

```bash
php artisan migrate
```

8. シーディングを実行する

```bash
php artisan db:seed
```

9. キャッシュを削除する

```bash
php artisan optimize:clear
```

## メール認証

本アプリでは、一般ユーザーの新規会員登録時にMailHogを使用してメール認証を行います。

MailHog：

```text
http://localhost:8026
```

### 確認手順

1. `/register`から新規会員登録を行う
2. MailHogを開く
3. 受信した認証メールを開く
4. メール内の認証リンクをクリックする
5. 認証後、勤怠登録画面へ移動できることを確認する

認証メールが届かない場合は、MailHogコンテナの起動状態を確認してください。

```bash
docker compose ps
```

## 使用技術（実行環境）

| 技術              | バージョン          |
| --------------- | -------------- |
| PHP             | 8.1            |
| Laravel         | 8.83.29        |
| Laravel Fortify | 1.19.1         |
| MySQL           | 8.0.26         |
| Nginx           | 1.21.1         |
| Docker          | Docker Compose |
| MailHog         | メール認証確認用       |


## ER図

```html
<img width="1200" alt="attendance-app-er" src="docs/er/attendance-app.er.png">
```

## テーブル設計

### usersテーブル

一般ユーザーの認証情報を管理します。

| カラム名              | 型         | 制約                | 説明          |
| ----------------- | --------- | ----------------- | ----------- |
| id                | bigint    | primary key       | ユーザーID      |
| name              | string    | not null          | ユーザー名       |
| email             | string    | not null / unique | メールアドレス     |
| email_verified_at | timestamp | nullable          | メール認証日時     |
| password          | string    | not null          | パスワード       |
| created_at        | timestamp | nullable          | 作成日時        |
| updated_at        | timestamp | nullable          | 更新日時        |

---

### adminsテーブル

管理者の認証情報を管理します。

| カラム名              | 型         | 制約                | 説明          |
| ----------------- | --------- | ----------------- | ----------- |
| id                | bigint    | primary key       | 管理者ID       |
| name              | string    | not null          | 管理者名        |
| email             | string    | not null / unique | メールアドレス     |
| email_verified_at | timestamp | nullable          | メール認証日時     |
| password          | string    | not null          | パスワード       |
| admin_status      | boolean   | default true      | 管理者を表す値     |
| created_at        | timestamp | nullable          | 作成日時        |
| updated_at        | timestamp | nullable          | 更新日時        |

---

### attendancesテーブル

一般ユーザーの日ごとの勤怠情報を管理します。

| カラム名            | 型         | 制約          | 説明        |
| --------------- | --------- | ----------- | --------- |
| id              | bigint    | primary key | 勤怠ID      |
| user_id         | bigint    | foreign key | ユーザーID    |
| attendance_date | date      | not null    | 勤怠日       |
| clock_in        | time      | not null    | 出勤時刻      |
| clock_out       | time      | nullable    | 退勤時刻      |
| status          | string    | not null    | 現在の勤務状態   |
| remarks         | text      | nullable    | 確定した勤怠の備考 |
| created_at      | timestamp | nullable    | 作成日時      |
| updated_at      | timestamp | nullable    | 更新日時      |

`status`には、勤務状態に応じて以下の値を保存します。

```text
勤務外
出勤中
休憩中
退勤済
```

---

### break_timesテーブル

勤怠に紐づく休憩時刻を管理します。

| カラム名          | 型         | 制約          | 説明     |
| ------------- | --------- | ----------- | ------ |
| id            | bigint    | primary key | 休憩ID   |
| attendance_id | bigint    | foreign key | 勤怠ID   |
| break_start   | time      | not null    | 休憩開始時刻 |
| break_end     | time      | nullable    | 休憩終了時刻 |
| created_at    | timestamp | nullable    | 作成日時   |
| updated_at    | timestamp | nullable    | 更新日時   |

1件の勤怠に対して、複数の休憩を登録できます。

---

### attendance_requestsテーブル

一般ユーザーから送信された勤怠修正申請を管理します。

| カラム名                | 型           | 制約          | 説明         |
| ------------------- | ----------- | ----------- | ---------- |
| id                  | bigint      | primary key | 修正申請ID     |
| user_id             | bigint      | foreign key | 申請したユーザーID |
| attendance_id       | bigint      | foreign key | 修正対象の勤怠ID  |
| requested_clock_in  | time        | not null    | 申請後の出勤時刻   |
| requested_clock_out | time        | not null    | 申請後の退勤時刻   |
| reason              | text        | not null    | 修正申請理由     |
| status              | tinyint     | default 0   | 承認状態       |
| created_at          | timestamp   | nullable    | 申請日時       |
| updated_at          | timestamp   | nullable    | 更新日時       |

`status`には以下の値を使用します。

|  値 | 状態   |
| -: | ---- |
|  0 | 承認待ち |
|  1 | 承認済み |

---

### attendance_request_breaksテーブル

勤怠修正申請に含まれる休憩時刻を管理します。

| カラム名                  | 型         | 制約          | 説明         |
| --------------------- | --------- | ----------- | ---------- |
| id                    | bigint    | primary key | 申請休憩ID     |
| attendance_request_id | bigint    | foreign key | 修正申請ID     |
| requested_break_start | time      | not null    | 申請後の休憩開始時刻 |
| requested_break_end   | time      | not null    | 申請後の休憩終了時刻 |
| created_at            | timestamp | nullable    | 作成日時       |
| updated_at            | timestamp | nullable    | 更新日時       |

## リレーション

| 親テーブル               | 子テーブル                     | 関係  |
| ------------------- | ------------------------- | --- |
| users               | attendances               | 1対多 |
| users               | attendance_requests       | 1対多 |
| attendances         | break_times               | 1対多 |
| attendances         | attendance_requests       | 1対多 |
| attendance_requests | attendance_request_breaks | 1対多 |

## URL

### 一般ユーザー

| ページ       | URL                              |
| --------- | -------------------------------- |
| 会員登録ページ   | `/register`                      |
| ログインページ   | `/login`                         |
| メール認証ページ  | `/email/verify`                  |
| 勤怠登録ページ   | `/attendance`                    |
| 月次勤怠一覧ページ | `/attendance/list`               |
| 勤怠詳細ページ   | `/attendance/detail/{id}`        |
| 修正申請一覧ページ | `/stamp_correction_request/list` |

### 管理者

| ページ            | URL                                      |
| -------------- | ---------------------------------------- |
| 管理者ログインページ     | `/admin/login`                           |
| 日次勤怠一覧ページ      | `/admin/attendance/list`                 |
| 勤怠詳細ページ        | `/admin/attendance/{id}`                 |
| スタッフ一覧ページ      | `/admin/staff/list`                      |
| スタッフ別月次勤怠一覧ページ | `/admin/attendance/staff/{id}`           |
| 修正申請一覧ページ      | `/stamp_correction_request/list`         |
| 修正申請詳細・承認ページ   | `/stamp_correction_request/approve/{id}` |

### 開発用URL

| サービス       | URL                     |
| ---------- | ----------------------- |
| アプリケーション   | `http://localhost`      |
| phpMyAdmin | `http://localhost:8080` |
| MailHog    | `http://localhost:8026` |

## テスト用アカウント

### 一般ユーザー1

```text
email: user1@example.com
password: password
```

### 一般ユーザー2

```text
email: user2@example.com
password: password
```

### 管理者

```text
email: user3@example.com
password: password
```

Seederで作成されるユーザーと管理者は、メール認証済みです。

## 管理者認証について

一般ユーザーと管理者では、認証に使用するテーブルとGuard（ガード）を分けています。

```text
一般ユーザー
usersテーブル
web Guard
```

```text
管理者
adminsテーブル
admin Guard
```

要件の管理者ダミーデータに合わせるため、`admins`テーブルに`admin_status`カラムを追加しています。

現在の管理者認証は、`admin_status`ではなく、`admin` Guardと`admins`テーブルを使用して判定しています。

## 補足説明

### ファイル権限について

以下のエラーが発生した場合は、`storage`と`bootstrap/cache`の権限を確認してください。

```text
Failed to open stream: Permission denied
```

修正コマンド：

```bash
docker compose exec php bash -lc "cd /var/www && chown -R www-data:www-data storage bootstrap/cache && chmod -R ug+rwX storage bootstrap/cache"
```

Bladeキャッシュを再作成する場合：

```bash
docker compose exec -u www-data php bash -lc "cd /var/www && php artisan view:clear && php artisan view:cache"
```

## PHPUnitテスト

