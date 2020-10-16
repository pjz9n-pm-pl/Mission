# Mission

Select language:
[日本語](#日本語)
[English](#English)

## Select the plugin language

By default, the PM language (`pocketmine.yml` or `server.properties`) is used, but if it is not supported, it will be Japanese.

1. Open the `config.yml`
2. Change language setting: `language: <language code>`

A list of available languages can be found in `resources/locale/`

(Remove the .ini extension)

e.g. `resources/locale/eng.ini` => `eng`

## 日本語

### 動作例

![screenshot_20201014_043559](https://user-images.githubusercontent.com/38120936/95907721-f173e980-0dd6-11eb-8431-06a0604f94bb.png)

![screenshot_20201014_043615](https://user-images.githubusercontent.com/38120936/95907726-f2a51680-0dd6-11eb-919e-57c77d82a4e6.png)

![screenshot_20201014_081521](https://user-images.githubusercontent.com/38120936/95926075-54c14400-0df6-11eb-9dbb-2ef5206f958c.png)

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

#### レシピをステップトリガーとして使う

1. Mineflow側でアクション追加画面を開き「ミッション」、「ミッションのステップを増やす」を選択して追加する

##### 例

![screenshot_20201014_060403](https://user-images.githubusercontent.com/38120936/95916139-2ab25680-0de3-11eb-8ad8-ee7e03466a5a.png)

##### Tips

- ミッション側での設定は不要です
- レシピ側で条件式などを組み合わせることによって、かなり柔軟な設定が可能になります

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

```php
pjz9n\mission\mission\MissionList
```

#### Progressを操作

```php
`pjz9n\mission\mission\progress\ProgressList`
```

詳しくはソースコードを参照してください

※各操作のタイミングは問いません(基本的にはプラグイン有効化タイミングに行うのが望ましい)

## English

### Operation example

![screenshot_20201014_081341](https://user-images.githubusercontent.com/38120936/95926122-76223000-0df6-11eb-9ceb-df3c16f0e492.png)

![screenshot_20201014_081406](https://user-images.githubusercontent.com/38120936/95926124-77535d00-0df6-11eb-9035-ce567a7414a8.png)

![screenshot_20201014_081449](https://user-images.githubusercontent.com/38120936/95926126-77ebf380-0df6-11eb-908f-aee93834cd52.png)

### Command

| command name | description | permission | alias | player only |
| --- | --- | --- | --- | --- |
| mission | Open the mission list form | mission.command.mission | mi | Yes |

#### Sub command (/mission)

| sub command name | description | permission | alias | player only |
| --- | --- | --- | --- | --- |
| edit | Edit mission | mission.command.mission.edit | None | Yes |
| setting | Settings | mission.command.mission.setting | set, config | Yes |

### Permission

| permission name | default |
| --- | --- |
| mission.command.mission | true 
| mission.command.mission.edit | op |
| mission.command.mission.setting | op |

### Usage

#### Item description

- Maximum number of achievements: Number of times you can complete a mission
- Target step: Target number of steps
- Steptrigger: Trigger to increase mission steps

#### Mission creation example

- Achieved after breaking blocks 10 times
- The reward is 10 diamonds
- Can be achieved up to once

1. Execute command: `/mission edit`
2. Select "Add mission"
3. Input the 1 to "Maximum number of achievements" and 10 to "Target step"
4. Select the created mission
5. Select "Edit reward"
6. Select "Add reward"
7. Specify "Item reward" for "Reward type"
8. Input the 264(Diamond ID) to "ID" and 10 to "Amount"
9. Select "Edit steptrigger"
10. Select "Add steptrigger"
11. Specify "Event" for "Steptrigger type"
12. Specify "BlockBreakEvent" for "Event"

If it looks like this, it ’s a success.

![screenshot_20201014_081717](https://user-images.githubusercontent.com/38120936/95926177-95b95880-0df6-11eb-8d9f-9fba8f28c170.png)

### Cooperation with Mineflow plugin

#### Create reward with recipe

1. Add "Mineflow reward" by referring to [Mission creation example](#Mission creation example)
2. Select "MissionReward" with the trigger on the Mineflow plugin side and specify the target mission

##### Examples

- Recipe

![screenshot_20201014_081807](https://user-images.githubusercontent.com/38120936/95926212-b2ee2700-0df6-11eb-9eff-8cc81fb38c53.png)

- Mission

![screenshot_20201014_081952](https://user-images.githubusercontent.com/38120936/95926215-b41f5400-0df6-11eb-84c3-cc81d027e6a3.png)

![screenshot_20201014_082017](https://user-images.githubusercontent.com/38120936/95926217-b41f5400-0df6-11eb-95b5-b73d30d4dd7f.png)

![screenshot_20201014_082029](https://user-images.githubusercontent.com/38120936/95926219-b4b7ea80-0df6-11eb-9c9b-88fcab4d5f97.png)

##### Tips

- The variable "target" can be used in Mineflow recipes triggered by mission rewards
- Even if you want to create multiple recipe rewards, please limit the number of "Mineflow reward" to one
- If you want to create multiple rewards based on recipes and want to display the second and subsequent rewards, you can use "Nothing (text show only)"

#### Use the recipe as a Steptrigger

1. Open the action addition screen on the Mineflow plugin side and select "Mission", "Increase the mission step" to add

##### Examples

![screenshot_20201014_082129](https://user-images.githubusercontent.com/38120936/95926271-d1542280-0df6-11eb-9e26-8203aa272c0f.png)

##### Tips

- No setting is required on the mission side
- By combining conditional expressions on the recipe side, it is possible to make fairly flexible settings

### Cooperation with external plugins (for developers)

#### Prerequisites

- Add this plugin to depend in plugin.yml

#### Add reward type

1. Create a class that inherits Reward (see existing Reward for implementation example)
2. Add that class to Reward

```php
pjz9n\mission\reward\Rewards::add(ExampleReward::class);
```

#### Add Steptrigger type

1. Create a class that inherits Executor (see existing Executor for implementation example)
2. Add that class to Executor

```php
pjz9n\mission\mission\executor\Executors::add(ExampleExecutor::class);
```

#### Operate Mission

```php
pjz9n\mission\mission\MissionList
```

#### Operate Progress

```php
`pjz9n\mission\mission\progress\ProgressList`
```

See the source code for details

\* The timing of each operation does not matter (basically, it is desirable to perform it at the plug-in activation timing)
