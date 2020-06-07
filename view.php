<?php
session_start();
require('dbconnect.php');

//ログイン確認
if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
    $_SESSION['time'] = time();

    $sql = 'SELECT * FROM members WHERE id=?';
    $members = $db->prepare($sql);
    $members->execute(array($_SESSION['id']));
    $member = $members->fetch();

    $welcome = "ようこそ、" . "<b>" . htmlspecialchars($member['name'], ENT_QUOTES) . "</b>" . "さん";
} else {
    header('Location: index.php');
    exit();
};

if (empty($_REQUEST['id'])) {
    header('Location: index.php');
    exit();
}

//投稿を取得
$posts = $db->prepare('SELECT m.name, m.image, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=?');
$posts->execute(array($_REQUEST['id']));
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>つぶやきBBS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container">
        <nav class="navbar sticky-top navbar-expand-md navbar-dark" style="background-color: #00aced;">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
                <h1 class="navbar-brand">つぶやきBBS</h1>
                <ul class="navbar-nav mr-auto mt-2 mt-lg-0">
                    <li class="nav-item active" style="list-style-type: none">
                        <a class="nav-link" href="index.php">ホーム <span class="sr-only">(current)</span></a>
                    </li>
                    <?php if (!empty($members)) : ?>
                        <li class="nav-item" style="list-style-type: none">
                            <a class="nav-link" href="settings/">設定</a>
                        </li>
                        <li class="nav-item" style="list-style-type: none">
                            <a class="nav-link" href="logout.php">ログアウト</a>
                        </li>
                    <?php else : ?>
                        <li class="nav-item" style="list-style-type: none">
                            <a class="nav-link" href="signup/">アカウント作成</a>
                        </li>
                        <li class="nav-item" style="list-style-type: none">
                            <a class="nav-link" href="login.php">ログイン</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <span class="navbar-text" style="color: #fff;">
                    <?php print($welcome); ?>
                </span>
            </div>
        </nav>

        <header>
            <h1>メッセージ詳細</h1>
        </header>

        <main>
            <?php if ($post = $posts->fetch()) : ?>
                <div class="msg">
                    <?php if ($post['image'] == '') : ?>
                        <img src="default_user.png" width="48" height="48" alt="user" />
                    <?php else : ?>
                        <img src="settings/image/<?php print(htmlspecialchars($post['image'], ENT_QUOTES)); ?>" width="48" height="48" alt="user" />
                    <?php endif; ?>
                    <p>
                        <span class="message_id">[<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>]</span>
                        <span class="name"><?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?></span>
                        <span class="created"><?php print(htmlspecialchars($post['created'], ENT_QUOTES)); ?></span>
                    </p>
                    <p>
                        <?php print(nl2br(htmlspecialchars($post['message'], ENT_QUOTES))); ?>
                    </p>
                </div>
            <?php else : ?>
                <p>その投稿は削除されたか、URLが正しくありません。</p>
            <?php endif; ?>
        </main>
    </div>

    <div class="container">
        <footer>
            <a href="javascript:history.back();">戻る</a>
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
</body>

</html>