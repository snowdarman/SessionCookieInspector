<?php

session_start();

// Session のサンプルデータ
$_SESSION['sample_name'] = 'ぽちたま太郎';
$_SESSION['sample_email'] = 'taro@example.com';
$_SESSION['sample_count'] = 10;
$_SESSION['sample_status'] = 'active';
$_SESSION['sample_message'] = 'これはSessionのテストデータです。';

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Session Sample</title>
</head>
<body>

<h1>Session Sample</h1>

<p>
    Sessionにテストデータをセットしました。
</p>

<p>
    <a href="manage-session.php">Session Inspectorを開く</a>
</p>

</body>
</html>