# Alacarte

レシピ共有アプリ（クックパッド型）。転職ポートフォリオ。
コンセプト: 「今日何を作るか」の意思決定コストをなくす。他人のレシピをフィードで眺め、自分でも投稿できる。

回答は日本語で。

## Tech Stack
- PHP 8.2 / Laravel 12
- Inertia 2 + Vue 3 + TypeScript（Inertia経由でSPA的体験を実現。独立したREST APIは作らない）
- Tailwind CSS / Vite
- MySQL 8.0
- 環境: Docker Compose（app: PHP+Apache / db: MySQL / phpmyadmin）
- テスト: Pest（Feature中心）
- 整形: Laravel Pint

## Commands
PHP側は必ずコンテナ経由で実行する。Node/Viteはホスト側で実行する。

- 起動: `docker compose up -d`
- 停止: `docker compose down`
- コンテナに入る: `docker compose exec app bash`
- artisan: `docker compose exec app php artisan <command>`
- composer: `docker compose exec app composer <command>`
- テスト: `docker compose exec app php artisan test`
- 整形: `docker compose exec app ./vendor/bin/pint`
- マイグレーション: `docker compose exec app php artisan migrate`
- DB作り直し: `docker compose exec app php artisan migrate:fresh --seed`（破壊的。実行前に必ず確認を取ること）
- フロント開発: `npm run dev`（ホスト側）
- フロントビルド: `npm run build`（ホスト側）

アクセス先: アプリ http://localhost:8080 / phpMyAdmin http://localhost:8081

## Conventions
### Laravel側
- テーブルは snake_case 複数形（users, recipes, ingredients, steps, favorites, follows …）
- 全テーブルのPKは `bigint unsigned`（= `$table->id()`）。FKは `$table->foreignId()` で型を揃える
- バリデーションは FormRequest に置く。Controller には書かない
- 更新系のドメインロジックは Action クラス（app/Actions/）に切り出し、Controller は薄く保つ
- データ取得は Eloquent リレーション経由。生SQLは書かない
- 一覧取得は必ず N+1 を潰す（`with()` / `withCount()` で eager load）
- 認可は Policy（app/Policies/）で表現する。「自分のレシピしか編集不可」等はPolicyに寄せる
- Vueに渡すデータは必ず Eloquent API Resource を通す。モデルをそのまま渡さない
  （password / email 等の意図しないカラム露出を防ぐため）
- 画像はファイル本体を storage に置き、DBにはパスのみ保存。Storage ファサード経由でアクセス

### Inertia / Vue側
- ページコンポーネントは resources/js/pages/ に配置（例: pages/recipes/Index.vue）
- 再利用UIは resources/js/components/ に配置。共通レイアウトは resources/js/layouts/
- 画面遷移は Inertia の `<Link>` / `router.visit()` を使う。素の `<a href>` 遷移はしない
- フォームは Inertia の `useForm()` を使い、バリデーションエラーは `errors` から表示する
- モーダルは共通の Modal コンポーネントを1つ作り、全画面でそれを再利用する（個別実装を増やさない）
- Composition API + `<script setup lang="ts">` で書く。Options API は使わない
- 型定義は resources/js/types/ に置く。props・APIレスポンスには必ず型を付ける。`any` は使わない
- 一覧の追加読み込みは Inertia の partial reload（`only:`）を使い、全データ再取得しない

## Testing
- Feature テスト（HTTPレベル）を基本にする。`RefreshDatabase` を使う
- Inertia のレスポンスは `assertInertia()` でコンポーネント名とpropsを検証する
- 新しいルート・Actionを追加したら、必ず対応するテストも追加する
- テストが緑になるまでタスク完了としない

## Workflow
- 大きめの変更は必ず Plan Mode で計画を出し、合意してから実装する
- 1タスク=1コミット。ステップごとにこまめにコミットする（巻き戻しやすさのため）
- コミットメッセージは日本語可。prefix は feat / fix / refactor / test / chore
- UIは一発で仕上げようとせず、実装 → 確認 → 修正の反復を前提にする

## Do NOT
- vendor/ · node_modules/ を編集しない
- .env / storage 内の認証情報をコミットしない。生トークン・APIキーをコードに埋め込まない
- 既存の migration を書き換えない。変更は必ず新しい migration を追加する
- `migrate:fresh` 等の破壊的コマンドを勝手に実行しない。必ず確認を取る
- docker-compose.yml / Dockerfile を勝手に変更しない。必要なら提案してから
- 外部パッケージを勝手に追加しない。必要なら理由を添えて提案してから
- 指示と無関係なコードには触らない。一度に広範囲を書き換えない
- 過剰な抽象化をしない。今必要な分だけ作る（YAGNI）

## Review Policy
コードレビューを依頼された時は、正しさ・要件・セキュリティに影響する問題だけを報告すること。
好みの問題や些細なスタイル指摘は「任意」と明示するか、報告しない。
指摘を増やすことが目的ではなく、過剰な防御コードや不要な抽象化を招かないことを優先する。

## Domain Notes
DB設計の確定版は docs/db-design.md を参照すること。

- Topページは2フィードの切替: 「みんなのレシピ（ランダム表示）」と「フォロー中ユーザーのレシピ」
- follows: `follower_id`（する側）/ `followed_id`（される側）。`(follower_id, followed_id)` は複合ユニーク。自己フォロー禁止
- favorites: `(user_id, recipe_id)` は複合ユニーク（二重お気に入り防止）
- recipe_comment_goods: FKは `recipe_comments.id`（原典の recipes.id は誤り）。`(user_id, comment_id)` 複合ユニーク
- carts: FKは `ingredients.id`（原典の ingredient.id は誤記）
- recipe には ingredients / steps が1対多。step には step_images が1対多
- view_count はレシピ詳細閲覧時にインクリメント
- recipe_categories は現状カテゴリを表現できていないため、MVPでは扱わない
