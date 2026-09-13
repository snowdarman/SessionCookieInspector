<?php

// Cookie のサンプルデータ
setcookie('sample_name', 'ぽちたま太郎', time() + 86400, '/');
setcookie('sample_email', 'taro@example.com', time() + 86400, '/');
setcookie('sample_count', '25', time() + 86400, '/');
setcookie('sample_status', 'active', time() + 86400, '/');
setcookie('sample_message', 'これはCookieのテストデータです。', time() + 86400, '/');

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Cookie Sample</title>
</head>
<body>

<h1>Cookie Sample</h1>

<p>
    Cookieにテストデータをセットしました。
</p>

<p>
    <a href="manage-cookie.php">Cookie Inspectorを開く</a>
</p>

</body>
</html>