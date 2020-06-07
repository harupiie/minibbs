<?php
session_start();
require('../dbconnect.php');

if (!isset($_SESSION['signup'])) {
    //入力画面を正しく経由しなかった場合
    header('Location: index.php');
    exit();
}

if (!empty($_POST)) {
    // DB更新（アカウント作成）
    $sql = 'INSERT INTO members SET name=?, password=?, introduction=?, created=NOW()';
    $statement = $db->prepare($sql);
    $statement->execute(
        array(
            $_SESSION['signup']['name'],
            sha1($_SESSION['signup']['password']),
            $_SESSION['signup']['introduction']
        )
    );
    // アカウントIDを取得
    $member_id = $db->lastInsertId();
    $_SESSION['lastInsId'] = htmlspecialchars(strval($member_id));

    header('Location: done.php');
    exit();
};
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>アカウント作成</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/style.css">
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
                <li class="nav-item" style="list-style-type: none">
                        <a class="nav-link" href="../index.php">ホーム <span class="sr-only">(current)</span></a>
                    </li>
                    <?php if (!empty($members)) : ?>
                        <li class="nav-item" style="list-style-type: none">
                            <a class="nav-link" href="../settings/">設定</a>
                        </li>
                        <li class="nav-item" style="list-style-type: none">
                            <a class="nav-link" href="../logout.php">ログアウト</a>
                        </li>
                    <?php else : ?>
                        <li class="nav-item active" style="list-style-type: none">
                            <a class="nav-link" href="index.php">アカウント作成</a>
                        </li>
                        <li class="nav-item" style="list-style-type: none">
                            <a class="nav-link" href="../login.php">ログイン</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <span class="navbar-text" style="color: #fff;">
                    <?php print($welcome); ?>
                </span>
            </div>
        </nav>

        <header>
            <h1>アカウント作成</h1>
        </header>

        <main>
            <p>入力内容を確認して、「登録する」ボタンをクリックしてください。</p>
            <form action="" method="post">
                <input type="hidden" name="action" value="submit" />
                <dl>
                    <dt>名前</dt>
                    <dd>
                        <?php print(htmlspecialchars($_SESSION['signup']['name'], ENT_QUOTES)); ?>
                    </dd>
                    <dt>ID</dt>
                    <dd>
                        （登録時に自動取得されます）
                    </dd>
                    <dt>パスワード</dt>
                    <dd>
                        （表示されません）
                    </dd>
                    <dt>自己紹介</dt>
                    <dd>
                        <?php print(htmlspecialchars($_SESSION['signup']['introduction'], ENT_QUOTES)); ?>
                    </dd>
                </dl>
                <div class="form-group">
                    <button class="btn btn-primary mb-2" type="submit">登録する</button>
                </div>
                <div>
                    <a href="index.php?action=rewrite">&laquo;&nbsp;修正する</a>
                </div>
            </form>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
</body>

</html>