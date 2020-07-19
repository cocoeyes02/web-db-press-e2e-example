# 『WEB+DB PRESS Vol.118』「PuPHPeteerでE2Eテストを作成しよう！」ソースコード集
このリポジトリでは『WEB+DB PRESS Vol.118』に掲載された記事「PuPHPeteerでE2Eテストを作成しよう！」に登場するソースコードを公開しています。

## ソースコードへのリンク
* [新規登録のE2Eテスト](./tests/MemberTest.php#L49)
* [ログインのE2Eテスト](./tests/MemberTest.php#L158)
* [退会のE2Eテスト](./tests/MemberTest.php#L302)
* [宿泊プラン一覧画面の確認テスト](./tests/PlanTest.php#L49)
* [宿泊プランと金額の確認テスト](./tests/PlanTest.php#L174)
* [宿泊予約入力のテスト](./tests/PlanTest.php#L319)
* [宿泊予約確認のテスト](./tests/PlanTest.php#L513)

## リポジトリ全体のディレクトリ構造
```
/
├── screenshots/       # E2Eテストがエラーになった時に撮ったスクリーンショット置き場
├── tests/             # E2Eテストスクリプト置き場
│   ├── MemberTest.php # 会員関連のE2Eテストスクリプトファイル
│   ├── PlanTest.php   # 宿泊プラン関連のE2Eテストスクリプトファイル
:
```

## 環境準備
以下のコマンドを実行すると、E2Eテストを実行する環境が用意されます。

```
$ composer install
$ npm install
```

## テスト実行方法
以下のコマンドを実行すると、E2Eテストが実行できます
```
$ ./vendor/phpunit/phpunit/phpunit tests/
```