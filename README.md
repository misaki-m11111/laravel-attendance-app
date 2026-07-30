# 勤怠管理アプリ

## アプリケーション概要

一般ユーザーの出勤・退勤・休憩登録や勤怠確認、勤怠修正申請ができる勤怠管理アプリです。

管理者は、スタッフの勤怠確認、勤怠情報の修正、修正申請の承認、月次勤怠のCSV出力を行えます。

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
* スタッフ別月次勤怠のCSV出力
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

データベースを初期化し、すべてのダミーデータを入れ直す場合は、次を実行してください。

```bash
php artisan migrate:fresh --seed
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

<img width="1000" alt="attendance-app er" src="https://github.com/user-attachments/assets/8b68b44d-9dea-4abb-8112-91dd768e7565" />

## テーブル設計

### usersテーブル

一般ユーザーの認証情報を管理します。

| カラム名              | 型         | 制約                | 説明      |
| ----------------- | --------- | ----------------- | ------- |
| id                | bigint    | primary key       | ユーザーID  |
| name              | string    | not null          | ユーザー名   |
| email             | string    | not null / unique | メールアドレス |
| email_verified_at | timestamp | nullable          | メール認証日時 |
| password          | string    | not null          | パスワード   |
| created_at        | timestamp | nullable          | 作成日時    |
| updated_at        | timestamp | nullable          | 更新日時    |

---

### adminsテーブル

管理者の認証情報を管理します。

| カラム名              | 型         | 制約                | 説明      |
| ----------------- | --------- | ----------------- | ------- |
| id                | bigint    | primary key       | 管理者ID   |
| name              | string    | not null          | 管理者名    |
| email             | string    | not null / unique | メールアドレス |
| email_verified_at | timestamp | nullable          | メール認証日時 |
| password          | string    | not null          | パスワード   |
| admin_status      | boolean   | default true      | 管理者を表す値 |
| created_at        | timestamp | nullable          | 作成日時    |
| updated_at        | timestamp | nullable          | 更新日時    |

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

`user_id`と`attendance_date`の組み合わせには、複合UNIQUE制約を設定しています。

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

| カラム名                | 型         | 制約          | 説明         |
| ------------------- | --------- | ----------- | ---------- |
| id                  | bigint    | primary key | 修正申請ID     |
| user_id             | bigint    | foreign key | 申請したユーザーID |
| attendance_id       | bigint    | foreign key | 修正対象の勤怠ID  |
| requested_clock_in  | time      | not null    | 申請後の出勤時刻   |
| requested_clock_out | time      | not null    | 申請後の退勤時刻   |
| reason              | text      | not null    | 修正申請理由     |
| status              | tinyint   | default 0   | 承認状態       |
| created_at          | timestamp | nullable    | 申請日時       |
| updated_at          | timestamp | nullable    | 更新日時       |

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

`admins`テーブルは、他のテーブルへの外部キーを持たない独立したテーブルです。

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
| スタッフ別月次勤怠CSV出力 | `/admin/attendance/staff/{id}/csv`       |
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

## 意図的データの投入

応用要件の確認用として、`user1@example.com`へ2026年2月から2026年7月までの勤怠データを登録しています。

対象Seeder：

```text
database/seeders/User1IntentionalAttendanceSeeder.php
```

### 登録件数

| 対象月     |  件数 |
| ------- | --: |
| 2026年2月 | 15件 |
| 2026年3月 | 15件 |
| 2026年4月 | 15件 |
| 2026年5月 | 15件 |
| 2026年6月 | 15件 |
| 2026年7月 | 17件 |
| 合計      | 92件 |

2026年2月から6月までは、各月の平日から15日分を登録しています。

```text
出勤：09:00
退勤：18:00
休憩：12:00〜13:00
状態：退勤済
```

2026年7月は、以下の勤務パターンを登録しています。

| 勤務パターン |    出勤 |    退勤 |  件数 |
| ------ | ----: | ----: | --: |
| 通常勤務   | 09:00 | 18:00 | 10件 |
| 残業     | 09:00 | 20:00 |  3件 |
| 遅刻     | 09:30 | 18:00 |  2件 |
| 早退     | 09:00 | 17:00 |  1件 |
| 長時間勤務  | 08:00 | 21:00 |  1件 |
| 合計     |       |       | 17件 |

すべての勤怠に、次の休憩を1件登録しています。

```text
12:00〜13:00
```

意図的データを含めて初期化する場合は、次を実行してください。

```bash
php artisan migrate:fresh --seed
```

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

## 応用要件への対応

本アプリでは、以下の応用要件に対応しています。

### CSV出力

管理者用のスタッフ別月次勤怠一覧から、表示中のスタッフ・対象月の勤怠情報をCSV形式で出力できます。

CSVには以下の項目を出力します。

- 日付
- 出勤
- 退勤
- 休憩
- 合計勤務時間

### 型宣言

ControllerおよびModelの主要なメソッドに、引数と戻り値の型を宣言しています。

### PHPDoc

Controllerの公開メソッドとModelのリレーションメソッドを中心に、処理内容を説明するPHPDocを記述しています。

### Collectionメソッド

勤怠データや休憩時間の整理・集計に、LaravelのCollectionメソッドを使用しています。

主な使用例：

- `keyBy()`：勤怠データを日付ごとに整理
- `filter()`：開始・終了が登録された休憩だけを抽出
- `sum()`：休憩時間を合計
- `isNotEmpty()`：申請された休憩データの有無を判定

### N+1問題対策

一覧画面や詳細画面では、`with()`によるEager Loading（イーガーローディング）を使用しています。

勤怠とユーザー情報、休憩情報、修正申請情報などをまとめて取得し、不要なSQLの繰り返しを防止しています。

## 未実装の応用要件

以下の応用要件は、提出後の学習課題としているため、本アプリには実装していません。

- マイ勤怠レポート
- 公開API

## PHPUnitテスト

本アプリでは、PHPUnitを使用してFeatureテストを実装しています。

### テスト環境

テスト用の環境設定として、以下のファイルを使用します。

```text
src/.env.testing
```

主な設定：

```env
APP_ENV=testing

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=attendance_test
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

テストでは`RefreshDatabase`を使用しています。

`RefreshDatabase`は、各テストの実行前後にデータベースを初期状態へ戻し、他のテストデータの影響を受けにくくする仕組みです。

テスト内では必要なデータを個別に作成するため、通常のSeederは使用しません。

### テスト用データベースの作成

プロジェクトのルートディレクトリで、MySQLコンテナへ入ります。

```bash
docker compose exec mysql bash
```

MySQLへログインします。

```bash
mysql -u root -p
```

テスト用データベースを作成します。

```sql
CREATE DATABASE attendance_test;
exit;
```

### テスト環境の準備

PHPコンテナ内で、テスト環境用のアプリケーションキーを作成します。

```bash
docker compose exec php bash
php artisan key:generate --env=testing
php artisan config:clear
```

必要に応じて、テスト用データベースへマイグレーションを実行します。

```bash
php artisan migrate --env=testing
```

### テスト実行方法

PHPコンテナ内で実行する場合：

```bash
php artisan test
```

ホスト側から実行する場合：

```bash
docker compose exec php php artisan test
```

### テスト内容

#### 認証機能

対象：

```text
tests/Feature/Auth/UserRegistrationTest.php
tests/Feature/Auth/UserLoginTest.php
tests/Feature/Auth/AdminLoginTest.php
tests/Feature/Auth/EmailVerificationTest.php
```

主な確認項目：

* 会員登録のバリデーション
* 正常な会員登録
* 一般ユーザーのログイン
* 管理者のログイン
* 認証メールの送信
* メール認証リンクの表示
* メール認証後の画面遷移

#### 一般ユーザー勤怠機能

対象：

```text
tests/Feature/Attendance/AttendanceDateTest.php
tests/Feature/Attendance/AttendanceStatusTest.php
tests/Feature/Attendance/ClockInTest.php
tests/Feature/Attendance/ClockOutTest.php
tests/Feature/Attendance/BreakTest.php
tests/Feature/Attendance/UserAttendanceListTest.php
tests/Feature/Attendance/UserAttendanceDetailTest.php
tests/Feature/Attendance/UserAttendanceRequestTest.php
```

主な確認項目：

* 現在日時の表示
* 勤務状態の表示
* 出勤処理
* 退勤処理
* 休憩開始・休憩終了
* 1日に複数回の休憩
* 休憩合計時間
* 月次勤怠一覧
* 前月・翌月への移動
* 勤怠詳細表示
* 修正申請のバリデーション
* 修正申請の保存
* 承認待ち・承認済み申請一覧
* 承認待ち中の編集制限

#### 管理者機能

対象：

```text
tests/Feature/Admin/AdminAttendanceListTest.php
tests/Feature/Admin/AdminAttendanceDetailTest.php
tests/Feature/Admin/AdminStaffTest.php
tests/Feature/Admin/AdminAttendanceRequestTest.php
```

主な確認項目：

* 指定日の全ユーザー勤怠一覧
* 前日・翌日の表示
* 管理者用勤怠詳細
* 管理者による勤怠修正
* 管理者用バリデーション
* スタッフ一覧
* スタッフ別月次勤怠
* 勤怠詳細への遷移
* 承認待ち申請一覧
* 承認済み申請一覧
* 修正申請の承認
* 承認後の勤怠更新
* 休憩情報の更新
* 申請状態の更新

### テスト結果

全テストが成功することを確認しています。

```text
Tests: 65 passed
Time: 5.79s
```

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
