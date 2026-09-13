<?php

session_start();

$message = '';
$error = '';

/*
 * セッション項目を編集
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $key = $_POST['key'] ?? '';

    if ($key === '') {
        $error = 'セッション項目が指定されていません。';
    } elseif (!array_key_exists($key, $_SESSION)) {
        $error = '指定されたセッション項目が存在しません。';
    } else {

        if ($action === 'delete') {

            unset($_SESSION[$key]);

            $message = 'セッション項目「' . $key . '」を削除しました。';

        } elseif ($action === 'edit') {

            $value_json = $_POST['value'] ?? '';

            /*
             * JSONとして値を受け取る
             *
             * 例：
             * 123       → integer
             * "abc"     → string
             * true      → boolean
             * null      → NULL
             * {"a":1}   → array
             */
            $value = json_decode($value_json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {

                $error =
                    '値が正しいJSONではありません。'
                    . ' エラー: '
                    . json_last_error_msg();

            } else {

                $_SESSION[$key] = $value;

                $message =
                    'セッション項目「'
                    . $key
                    . '」を更新しました。';
            }

        } else {
            $error = '不正な操作です。';
        }
    }
}


/*
 * セッション値を表示用JSONに変換
 */
function session_value_json($value)
{
    return json_encode(
        $value,
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );
}


/*
 * PHPの型を表示
 */
function session_value_type($value)
{
    if (is_array($value)) {
        return 'array';
    }

    if (is_object($value)) {
        return 'object';
    }

    if (is_null($value)) {
        return 'NULL';
    }

    if (is_bool($value)) {
        return 'boolean';
    }

    if (is_int($value)) {
        return 'integer';
    }

    if (is_float($value)) {
        return 'double';
    }

    return gettype($value);
}

?>

<!doctype html>

<html lang="ja">

<head>

<meta charset="UTF-8">

<meta name="viewport"
   content="width=device-width, initial-scale=1">

<title>セッション管理（DEBUG）</title>

<style>

body {
    margin: 0;
    padding: 20px;

    font-family: sans-serif;

    background: #f5f5f5;
    color: #222;
}

.container {
    max-width: 1100px;
    margin: 0 auto;
}

h1 {
    font-size: 1.5rem;
    margin-bottom: 20px;
}

.notice {
    padding: 12px 15px;
    margin-bottom: 15px;

    background: #e8f5e9;
    border: 1px solid #b7d8ba;

    border-radius: 6px;
}

.error {
    padding: 12px 15px;
    margin-bottom: 15px;

    background: #ffebee;
    border: 1px solid #e0a0a0;

    border-radius: 6px;
}

.info {
    margin-bottom: 15px;

    padding: 10px 15px;

    background: white;
    border-radius: 6px;
}

.session-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.session-item {
    background: white;

    border: 1px solid #ddd;
    border-radius: 8px;

    padding: 15px;
}

.session-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 15px;

    margin-bottom: 10px;
}

.session-key {
    font-weight: bold;
    font-size: 1.05rem;

    word-break: break-all;
}

.session-type {
    color: #666;
    font-size: 0.85rem;
}

textarea {
    width: 100%;
    min-height: 100px;

    box-sizing: border-box;

    padding: 10px;

    border: 1px solid #ccc;
    border-radius: 5px;

    font-family: monospace;
    font-size: 14px;

    resize: vertical;
}

.buttons {
    display: flex;
    gap: 8px;

    margin-top: 10px;
}

button {
    padding: 8px 14px;

    border: none;
    border-radius: 5px;

    cursor: pointer;

    font-size: 14px;
}

.edit-button {
    background: #333;
    color: white;
}

.delete-button {
    background: #c62828;
    color: white;
}

.refresh-button {
    background: #777;
    color: white;
}

.empty {
    padding: 30px;

    background: white;

    text-align: center;

    border-radius: 8px;

    color: #666;
}
.back-button {
    display: inline-block;
    padding: 10px 24px;
    background: #123a70;
    color: #ffffff;
    text-decoration: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: bold;
}

.back-button:hover {
    background: #0d2d58;
}
</style>

</head>

<body>

<div class="container">

<h1>セッション管理（DEBUG）</h1>

<?php if ($message !== ''): ?>

<div class="notice">
    <?php echo htmlspecialchars(
        $message,
        ENT_QUOTES,
        'UTF-8'
    ); ?>
</div>

<?php endif; ?>

<?php if ($error !== ''): ?>

<div class="error">
    <?php echo htmlspecialchars(
        $error,
        ENT_QUOTES,
        'UTF-8'
    ); ?>
</div>

<?php endif; ?>

<div class="info">

セッション項目数：
<strong><?php echo count($_SESSION); ?></strong>

<form method="get"
      style="display:inline; margin-left:15px;">

    <button type="submit"
            class="refresh-button">
        再読み込み
    </button>

</form>

</div>

<?php if (count($_SESSION) === 0): ?>

<div class="empty">
    セッションには何も保存されていません。
</div>

<?php else: ?>

<div class="session-list">

<?php foreach ($_SESSION as $key => $value): ?>

<div class="session-item">

<div class="session-header">

    <div>

        <div class="session-key">
            <?php echo htmlspecialchars(
                $key,
                ENT_QUOTES,
                'UTF-8'
            ); ?>
        </div>

        <div class="session-type">
            型：
            <?php echo htmlspecialchars(
                session_value_type($value),
                ENT_QUOTES,
                'UTF-8'
            ); ?>
        </div>

    </div>

</div>


<form method="post">

    <input type="hidden"
           name="action"
           value="edit">

    <input type="hidden"
           name="key"
           value="<?php echo htmlspecialchars(
               $key,
               ENT_QUOTES,
               'UTF-8'
           ); ?>">

    <textarea name="value"><?php
        echo htmlspecialchars(
            session_value_json($value),
            ENT_QUOTES,
            'UTF-8'
        );
    ?></textarea>


    <div class="buttons">

        <button type="submit"
                class="edit-button">
            編集
        </button>

    </div>

</form>


<form method="post"
      onsubmit="return confirm(
          'セッション項目「<?php
          echo htmlspecialchars(
              $key,
              ENT_QUOTES,
              'UTF-8'
          );
          ?>」を削除しますか？'
      );">

    <input type="hidden"
           name="action"
           value="delete">

    <input type="hidden"
           name="key"
           value="<?php echo htmlspecialchars(
               $key,
               ENT_QUOTES,
               'UTF-8'
           ); ?>">

    <div class="buttons">

        <button type="submit"
                class="delete-button">
            削除
        </button>

    </div>

</form>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

</div>

<a href="index.html" class="back-button">戻る</a>

</body>

</html>
