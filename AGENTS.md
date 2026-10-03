# AGENTS.md

## システム名
認証ONU

## システム概要
- 本システムはONU Familyで使用する認証機能を提供するアプリケーションである。
- 主に以下の機能を持つ。
  - ログイン・ログアウト
  - ユーザー管理
  - OTP認証
  - パスワードリセット
  - メールアドレス認証
  - セッション管理
- 認証情報および認証セッションは、家計簿ONU・作り置きONUなどのONU Family内の各アプリケーションから共通利用される。
- authデータベースのスキーマ管理は本システムが担当する。

## 技術スタック
- 言語：PHP 8.5
- フレームワーク：Laravel 13
- フロントエンド：Blade
- 依存関係ツール：Composer
- DB：MariaDB

## 命名、コーディング規則
- クラス名はパスカルケースとする。（例： `SampleItem`, `SampleClass`）
- 変数はキャメルケースとする。

```php
$sampleString = "hello";
```

- 定数はアッパースネークケースとする。

```php
CONSTANT_STR = "Apple";
```

- テーブル名は複数形のスネークケースとする。
- カラム名はスネークケースとする。

## ディレクトリ構成
主要なディレクトリ構成は以下の通り。

```text
.
├── app
│   ├── Http
│   │   ├── Controllers   # HTTPリクエストの受付・レスポンス生成
│   │   └── Requests      # バリデーション
│   ├── Models            # Eloquentモデル
│   ├── Services          # ビジネスロジック
│   ├── Notifications     # メール等の通知
│   └── Providers         # Service Provider
├── database
│   ├── migrations
│   ├── factories
│   └── seeders
├── resources
│   └── views             # Bladeテンプレート
├── routes                # ルーティング定義
└── tests
    ├── Feature
    └── Unit
```

## 開発環境
- Docker Composeを使用する。
- 本プロジェクトはONU Family共通のCompose環境上で動作する。
- PHPおよびComposerはローカルマシンにインストールしない。
- PHP、Composer、Artisan関連コマンドは必ずDockerコンテナ内で実行する。
- Docker環境上で動作することを前提とする。
- ローカル環境はWSL2 + Dockerを前提とする。
- 静的解析はVSCodeのPHP Intelephenseで行う。

## フロントエンド方針
- フロントエンドは原則としてBladeを使用する。
- JavaScriptは必要最小限の範囲で使用する。
- SPA化は行わない。
- 新しいフロントエンドライブラリ・フレームワークを追加しない。

## バックエンド方針

### コントローラー方針
- Fat Controllerを避ける。
- 業務ロジックはServiceクラスへ分離する。
- Controllerにはリクエスト受付とレスポンス返却のみを書く。
- ControllerにDBクエリを極力書かない。ただし、`User::find($id)`のような、きわめてシンプルなものは例外とする。

### Serviceクラス方針
- 業務ロジックはServiceクラスへ配置する。
- Serviceクラスは単一責務を意識する。
- 複雑な処理は小さなメソッドへ分割する。

### Request Validation方針
- バリデーションには必ずFormRequestを使用する。
- `request()->validate()` の使用禁止。

### Eloquent方針
- DB操作は原則としてEloquentまたはQuery Builderの通常メソッドを使用する。
- 必要に応じて eager loading を使用し、N+1問題を避ける。
- 複数のテーブルを更新する際は必ずtransactionを使用し、処理中にエラーが生じてもrollbackして整合性が崩れないようにする。

## データベース方針
- 本システムはauthデータベースを管理する。
- authデータベースに対するmigrationは本プロジェクトで管理する。
- 他のONU Familyアプリケーション固有のデータベースおよびテーブルを本プロジェクトから変更しない。
- 新規migrationでは、特別な理由がない限りデフォルトDB接続を使用する。

## 認証方針
- ONU Family共通の認証処理は本システムに集約する。
- Laravelが提供する認証・セッション・パスワードリセット・メール認証等の標準機能を可能な限り利用する。
- 認証・セッションに関する変更では、認証情報を共有する他のONU Familyアプリケーションへの影響を考慮する。
- 認証情報、OTP、セッション情報等の機密情報をログへ出力しない。
- 認証に関するセキュリティ機構を独自判断で無効化・緩和しない。

## テスト方針
- Laravelのテスト作成・修正・レビュー時は`laravel-testing` skillの方針に従うこと。
- プロジェクト固有のテスト方針が本ファイルに記載されている場合は、本ファイルの方針を優先する。
- テストはDockerコンテナ内で実行する。

```bash
# 全テスト実行
docker compose exec --user sail auth php artisan test

# 特定のテストファイルのみ実行
docker compose exec --user sail auth php artisan test tests/Feature/ExampleTest.php
```

## 禁止事項
- `.env` の編集およびコミットの禁止。どうしても編集が必要な場合は理由を提示して許可を取ること。
- Raw SQL（`DB::raw`, `selectRaw`, `whereRaw`, `orderByRaw` 等）の使用禁止。どうしても必要な場合は理由を提示して許可を取ること。
- Controller内でのValidator、`request()->validate()`、`$request->validate()` を使用したバリデーションは禁止。FormRequestを使用する。
- ローカルマシンへのPHP、Composerのインストール禁止。
- `/node_modules`、`/vendor` 等の自動生成ファイルの変更は行わない。必要な場合は必ず理由を提示して許可を取ること。
- 依存関係変更時以外の `composer.json` と `composer.lock` の編集禁止。
- 既存migrationの編集は禁止。
- rollback不可能なmigrationの作成は禁止。必ず `up()` と逆操作を `down()` に実装する。
- 他のONU Familyアプリケーション固有のDBスキーマを変更しない。
- Laravel標準のセキュリティ機構を理由なく無効化しない。

## Git運用方針
- 機能単位でコミットを分ける。
- 無関係な変更を同一コミットへ含めない。

## コミットメッセージ指針
- コミットメッセージにはコミット内容に応じて以下を文頭に書くこと。
  - feature : 新機能実装
  - fix : 不具合修正
  - refact : 機能実装、変更を伴わない修正
  - docs : ドキュメント修正
  - test : テスト追加・修正
  - chore : 設定・依存関係など

## AIエージェント向け指示
- 既存コードの設計方針を優先すること。
- 不明な仕様を推測しないこと。
- 大規模なリファクタリングを勝手に行わないこと。
- 新規ライブラリ導入時は理由を明示すること。
- 既存命名規則を優先すること。
- コメントは必要最小限にすること。
- 認証・セッション関連の変更では他のONU Familyアプリケーションへの影響を考慮すること。
