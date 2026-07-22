# Alacarte

レシピ共有アプリ（クックパッド型）。

「今日何を作るか」を考える手間をなくすことをコンセプトに、他人のレシピをフィードで眺めたり、自分でレシピを投稿したりできるサービスです。

> **本リポジトリは転職活動用のポートフォリオとして開発しています。**

## 技術スタック

### バックエンド
- PHP 8.2
- Laravel 12
- Inertia.js 2（Laravel adapter）
- MySQL 8.0
- Pest（テスト）
- Laravel Pint（コード整形）

### フロントエンド
- Vue 3 / TypeScript
- Inertia.js 2（Vue3 adapter）
- Tailwind CSS 3
- Vite

Inertiaを介してLaravel側からVueのページコンポーネントに直接propsを渡すため、独立したREST APIは持たない構成です。

### 開発環境
- Docker Compose
  - `app`: PHP 8.2 + Apache
  - `db`: MySQL 8.0
  - `phpmyadmin`

## ローカル環境構築

### 前提
- Docker / Docker Compose
- Node.js（フロントエンドのビルド用。ホスト側で実行）

### 手順

```bash
# リポジトリを取得
git clone https://github.com/kaito5335/Alacarte.git
cd Alacarte

# .env を用意
cp .env.example .env

# コンテナを起動（app / db / phpmyadmin）
docker compose up -d

# PHP依存パッケージをインストール
docker compose exec app composer install

# アプリケーションキーを生成
docker compose exec app php artisan key:generate

# マイグレーション実行
docker compose exec app php artisan migrate

# フロントエンド依存パッケージをインストール（ホスト側）
npm install

# フロントエンド開発サーバー起動（ホスト側）
npm run dev
```

### アクセス先
- アプリ: http://localhost:8080
- phpMyAdmin: http://localhost:8081

### よく使うコマンド

PHP側は必ずコンテナ経由で実行し、Node/Viteはホスト側で実行します。

| 用途 | コマンド |
|---|---|
| コンテナ起動 | `docker compose up -d` |
| コンテナ停止 | `docker compose down` |
| コンテナに入る | `docker compose exec app bash` |
| artisanコマンド | `docker compose exec app php artisan <command>` |
| composerコマンド | `docker compose exec app composer <command>` |
| テスト実行 | `docker compose exec app php artisan test` |
| コード整形 | `docker compose exec app ./vendor/bin/pint` |
| マイグレーション | `docker compose exec app php artisan migrate` |
| フロント開発サーバー | `npm run dev` |
| フロントビルド | `npm run build` |

## DB設計

テーブル定義・ER図は [docs/db-design.md](docs/db-design.md) を参照してください。

## 実装予定の機能

DB設計（[docs/db-design.md](docs/db-design.md)）に基づく、実装予定の機能一覧です（未着手）。

### MVP
- ユーザー登録・ログイン
- レシピ投稿（材料・手順・手順ごとの画像）
- レシピ一覧（Topページ、2フィード切替）
  - みんなのレシピ（ランダム表示）
  - フォロー中ユーザーのレシピ
- レシピ詳細閲覧（閲覧数カウント）
- レシピのお気に入り登録
- レシピへのコメント・コメントへのいいね
- ユーザーのフォロー / フォロー解除

### 後回し（MVP後）
- レシピの下書き保存
- 材料のカート機能
- 管理者ユーザー機能

### 対象外
- レシピのカテゴリ分類（現状のDB設計ではカテゴリを表現できないため、MVPでは扱いません）
