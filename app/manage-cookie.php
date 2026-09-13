<?php

$message = '';
$error = '';

/*
 * Cookieを削除
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $name = $_POST['name'] ?? '';

    if ($name === '') {
        $error = 'Cookie名が指定されていません。';

    } elseif ($action === 'delete') {

        /*
         * Cookieを削除するには、過去のCookieと同じ
         * path / domain で期限切れを送る必要がある。
         *
         * PochTamaStampでは path=/ を基本とする。
         */
        setcookie(
            $name,
            '',
            [
                'expires'  => time() - 3600,
                'path'     => '/',
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );

        /*
         * このリクエスト中の $_COOKIE からも削除
         */
        unset($_COOKIE[$name]);

        $message =
            'Cookie「'
            . $name
            . '」を削除しました。';

    } elseif ($action === 'edit') {

        $value = $_POST['value'] ?? '';

        $secure = isset($_POST['secure']);
        $httponly = isset($_POST['httponly']);

        $samesite = $_POST['samesite'] ?? 'Lax';

        if (
            $samesite !== 'Lax' &&
            $samesite !== 'Strict' &&
            $samesite !== 'None'
        ) {
            $samesite = 'Lax';
        }

        /*
         * 有効期限
         *
         * 0 = セッションCookie
         * 正の値 = 指定秒数後
         */
        $expires_days = trim($_POST['expires_days'] ?? '');

        if ($expires_days === '') {

            $expires = 0;

        } elseif (
            !is_numeric($expires_days) ||
            (float)$expires_days < 0
        ) {

            $error = '有効期限は0以上の数値で指定してください。';

        } else {

            $expires =
                time() +
                (int)((float)$expires_days * 24 * 60 * 60);
        }

        if ($error === '') {

            /*
             * SameSite=None は Secure 必須
             */
            if ($samesite === 'None') {
                $secure = true;
            }

            setcookie(
                $name,
                $value,
                [
                    'expires'  => $expires,
                    'path'     => '/',
                    'secure'   => $secure,
                    'httponly' => $httponly,
                    'samesite' => $samesite
                ]
            );

            /*
             * このリクエスト中の表示も更新
             */
            $_COOKIE[$name] = $value;

            $message =
                'Cookie「'
                . $name
                . '」を更新しました。';
        }

    } elseif ($action === 'add') {

        $name = trim($_POST['new_name'] ?? '');
        $value = $_POST['new_value'] ?? '';

        if ($name === '') {

            $error = 'Cookie名を入力してください。';

        } else {

            $secure = isset($_POST['new_secure']);
            $httponly = isset($_POST['new_httponly']);

            $samesite =
                $_POST['new_samesite'] ?? 'Lax';

            if (
                $samesite !== 'Lax' &&
                $samesite !== 'Strict' &&
                $samesite !== 'None'
            ) {
                $samesite = 'Lax';
            }

            if ($samesite === 'None') {
                $secure = true;
            }

            $expires_days =
                trim($_POST['new_expires_days'] ?? '');

            if ($expires_days === '') {

                $expires = 0;

            } elseif (
                !is_numeric($expires_days) ||
                (float)$expires_days < 0
            ) {

                $error =
                    '有効期限は0以上の数値で指定してください。';

            } else {

                $expires =
                    time() +
                    (int)((float)$expires_days * 24 * 60 * 60);
            }

            if ($error === '') {

                setcookie(
                    $name,
                    $value,
                    [
                        'expires'  => $expires,
                        'path'     => '/',
                        'secure'   => $secure,
                        'httponly' => $httponly,
                        'samesite' => $samesite
                    ]
                );

                $_COOKIE[$name] = $value;

                $message =
                    'Cookie「'
                    . $name
                    . '」を追加しました。';
            }
        }
    }
}

?>

<!doctype html>

<html lang="ja">

<head>

<meta charset="UTF-8">

<meta name="viewport"
   content="width=device-width, initial-scale=1">

<title>Cookie管理（DEBUG）</title>

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
    margin-bottom: 10px;
}

.description {
    margin-bottom: 20px;

    padding: 12px 15px;

    background: #fff8e1;
    border: 1px solid #e0c97f;

    border-radius: 6px;

    line-height: 1.6;
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

.cookie-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.cookie-item {
    background: white;

    border: 1px solid #ddd;
    border-radius: 8px;

    padding: 15px;
}

.cookie-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 15px;

    margin-bottom: 10px;
}

.cookie-name {
    font-weight: bold;
    font-size: 1.05rem;

    word-break: break-all;
}

.cookie-value {
    margin-bottom: 10px;
}

textarea,
input[type="text"],
input[type="number"] {
    width: 100%;

    box-sizing: border-box;

    padding: 9px;

    border: 1px solid #ccc;
    border-radius: 5px;

    font-family: monospace;
    font-size: 14px;
}

textarea {
    min-height: 80px;
    resize: vertical;
}

.option-area {
    margin-top: 10px;

    display: flex;
    flex-wrap: wrap;

    gap: 15px;
}

.option-area label {
    white-space: nowrap;
}

.buttons {
    display: flex;
    gap: 8px;

    margin-top: 12px;
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

.add-button {
    background: #1565c0;
    color: white;
}

.refresh-button {
    background: #777;
    color: white;
}

.add-box {
    margin-top: 25px;

    padding: 20px;

    background: white;

    border: 1px solid #ddd;
    border-radius: 8px;
}

.add-box h2 {
    margin-top: 0;
    font-size: 1.2rem;
}

.form-row {
    margin-bottom: 12px;
}

.form-row label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
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

<h1>Cookie管理（DEBUG）</h1>

<div class="description">
    この画面では、現在このページに送信されているCookieを確認・編集できます。<br>
    HttpOnlyのCookieもPHPからは確認できます。
</div>

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

Cookie項目数：
<strong><?php echo count($_COOKIE); ?></strong>

<form method="get"
      style="display:inline; margin-left:15px;">

    <button type="submit"
            class="refresh-button">
        再読み込み
    </button>

</form>

</div>

<?php if (count($_COOKIE) === 0): ?>

<div class="empty">
    このページに送信されたCookieはありません。
</div>

<?php else: ?>

<div class="cookie-list">

<?php foreach ($_COOKIE as $name => $value): ?>

<div class="cookie-item">

<div class="cookie-header">

    <div class="cookie-name">
        <?php echo htmlspecialchars(
            $name,
            ENT_QUOTES,
            'UTF-8'
        ); ?>
    </div>

</div>


<form method="post">

    <input type="hidden"
           name="action"
           value="edit">

    <input type="hidden"
           name="name"
           value="<?php echo htmlspecialchars(
               $name,
               ENT_QUOTES,
               'UTF-8'
           ); ?>">


    <div class="cookie-value">

        <label>
            値
        </label>

        <textarea name="value"><?php
            echo htmlspecialchars(
                $value,
                ENT_QUOTES,
                'UTF-8'
            );
        ?></textarea>

    </div>


    <div class="option-area">

        <label>
            <input type="checkbox"
                   name="secure"
                   value="1"
                   checked>
            Secure
        </label>

        <label>
            <input type="checkbox"
                   name="httponly"
                   value="1"
                   checked>
            HttpOnly
        </label>

        <label>
            SameSite
            <select name="samesite">

                <option value="Lax"
                        selected>
                    Lax
                </option>

                <option value="Strict">
                    Strict
                </option>

                <option value="None">
                    None
                </option>

            </select>
        </label>

    </div>


    <div class="form-row"
         style="margin-top:12px;">

        <label>
            有効期限（日）
        </label>

        <input type="number"
               name="expires_days"
               value="7"
               min="0"
               step="0.01">

        <small>
            空欄または0の場合はセッションCookie
        </small>

    </div>


    <div class="buttons">

        <button type="submit"
                class="edit-button">
            編集
        </button>

    </div>

</form>


<form method="post"
      onsubmit="return confirm(
          'Cookie「<?php
          echo htmlspecialchars(
              $name,
              ENT_QUOTES,
              'UTF-8'
          );
          ?>」を削除しますか？'
      );">

    <input type="hidden"
           name="action"
           value="delete">

    <input type="hidden"
           name="name"
           value="<?php echo htmlspecialchars(
               $name,
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

<div class="add-box">

<h2>Cookieを追加</h2>

<form method="post">

<input type="hidden"
       name="action"
       value="add">


<div class="form-row">

    <label>
        Cookie名
    </label>

    <input type="text"
           name="new_name"
           required>

</div>


<div class="form-row">

    <label>
        値
    </label>

    <textarea name="new_value"></textarea>

</div>


<div class="option-area">

    <label>
        <input type="checkbox"
               name="new_secure"
               value="1"
               checked>
        Secure
    </label>

    <label>
        <input type="checkbox"
               name="new_httponly"
               value="1"
               checked>
        HttpOnly
    </label>

    <label>
        SameSite

        <select name="new_samesite">

            <option value="Lax"
                    selected>
                Lax
            </option>

            <option value="Strict">
                Strict
            </option>

            <option value="None">
                None
            </option>

        </select>

    </label>

</div>


<div class="form-row"
     style="margin-top:12px;">

    <label>
        有効期限（日）
    </label>

    <input type="number"
           name="new_expires_days"
           value="7"
           min="0"
           step="0.01">

    <small>
        空欄または0の場合はセッションCookie
    </small>

</div>


<div class="buttons">

    <button type="submit"
            class="add-button">
        Cookieを追加
    </button>

</div>

</form>

</div>
<div class="info">
    <a href="index.html" class="back-button">戻る</a>
</div>
</div>

</body>

</html>
