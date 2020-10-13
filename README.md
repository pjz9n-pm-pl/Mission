# Mission

Select language:
[日本語](#日本語)

## 日本語

### 動作例

![screenshot_20201014_043559](https://user-images.githubusercontent.com/38120936/95907721-f173e980-0dd6-11eb-8431-06a0604f94bb.png)

![screenshot_20201014_043615](https://user-images.githubusercontent.com/38120936/95907726-f2a51680-0dd6-11eb-919e-57c77d82a4e6.png)

![screenshot_20201014_043651](https://user-images.githubusercontent.com/38120936/95907729-f33dad00-0dd6-11eb-8fd1-20e1b6478ac7.png)

### コマンド

| コマンド名 | 説明 | 権限 | エイリアス | プレイヤーのみ |
| --- | --- | --- | --- | --- |
| mission | ミッション一覧フォームを開く | mission.command.mission | mi | はい |

#### サブコマンド (/mission)

| サブコマンド名 | 説明 | 権限 | エイリアス | プレイヤーのみ |
| --- | --- | --- | --- | --- |
| edit | ミッションを編集する | mission.command.mission.edit | なし | はい |
| setting | 設定 | mission.command.mission.setting | set, config | はい |

### 権限

| 権限名 | デフォルト |
| --- | --- |
| mission.command.mission | true 
| mission.command.mission.edit | op |
| mission.command.mission.setting | op |

### 使い方

#### 項目の説明

- 最大達成回数: ミッションを達成できる回数
- 目標ステップ数: 目標のステップ数
- ステップトリガー: ミッションのステップを増やすトリガー

#### ミッションの作成例

- ブロックを10回壊したら達成
- 報酬はダイヤモンド10個
- 1回まで達成できる

1. `/mission edit` コマンドを実行
2. 「ミッション追加」を選択
3. 「最大達成回数」に1を、「目標ステップ数」に10を入力
4. 作成したミッションを選択
5. 「報酬の編集」を選択
6. 「報酬追加」を選択
7. 「報酬の種類」に「アイテム報酬」を指定
8. 「id」に264(ダイヤモンドのID)を、「個数」に10を入力
9. 「ステップトリガー編集」を選択
10. 「ステップトリガー追加」を選択
11. 「ステップトリガーの種類」に「イベント」を指定
12. 「イベント」に「BlockBreakEvent」を指定

このようになっていれば成功です(フォントの乱れはMCBEの仕様です)

![screenshot_20201014_033307](https://user-images.githubusercontent.com/38120936/95901266-08faa480-0dce-11eb-99b2-f1febc53bcad.png)

### Mineflowとの連携

#### レシピによる報酬を作成

1. [ミッションの作成例](#ミッションの作成例) を参考に「Mineflow報酬」を追加する
2. Mineflow側のトリガーで「ミッション報酬」を選択、対象の実績を指定する

##### 例

- レシピ

![screenshot_20201014_042130](https://user-images.githubusercontent.com/38120936/95906249-df914700-0dd4-11eb-80c5-5f02f21d2ee6.png)

- ミッション

![screenshot_20201014_042154](https://user-images.githubusercontent.com/38120936/95906242-ddc78380-0dd4-11eb-8cff-6182f80a5c22.png)

![screenshot_20201014_042202](https://user-images.githubusercontent.com/38120936/95906246-de601a00-0dd4-11eb-8c1b-ba268bc1b94e.png)

![screenshot_20201014_042211](https://user-images.githubusercontent.com/38120936/95906247-def8b080-0dd4-11eb-9927-adb7a15d8201.png)

##### Tips

- ミッション報酬をトリガに指定したMineflowレシピでは、変数targetが使用できます
- レシピによる報酬を複数作成したい場合でも、「Mineflow報酬」は1つまでにしてください
- レシピによる報酬を複数作成する場合で、2つ目以降の報酬内容を表示したい場合「何もしない(文字表示のみ)」を使用することができます

### 外部プラグインとの連携(開発者向け)

#### 前提条件

- plugin.ymlのdependにこのプラグインを追加する

#### 報酬の種類を追加する

1. Rewardを継承したクラスを作成する(実装例は既存Rewardを参照)
2. そのクラスをRewardsに追加

```php
pjz9n\mission\reward\Rewards::add(ExampleReward::class);
```

#### ステップトリガーの種類を追加する

1. Executorを継承したクラスを作成する(実装例は既存Executorを参照)
2. そのクラスをExecutorsに追加

```php
pjz9n\mission\mission\executor\Executors::add(ExampleExecutor::class);
```

#### Missionを操作

`pjz9n\mission\mission\MissionList`

#### Progressを操作

`pjz9n\mission\mission\progress\ProgressList`

詳しくはソースコードを参照してください

※各操作のタイミングは問いません(基本的にはプラグイン有効化タイミングに行うのが望ましい)
