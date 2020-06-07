<?php
session_start();
require('dbconnect.php');

//ログイン確認
if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
    //ログイン中
    $_SESSION['time'] = time();
    header('Location: index.php');
    exit();
};

if ($_COOKIE['id'] !== '') {
    $id = $_COOKIE['id'];
};

if (!empty($_POST)) {
    $id = $_POST['id'];

    // 入力チェック
    // ID
    if ($_POST['id'] === '') {
        $error['id'] .= "IDを入力してください。" . "\n";
    };
    // パスワード
    if ($_POST['password'] === '') {
        $error['password'] .= "パスワードを入力してください。" . "\n";
    };

    if ($_POST['id'] !== '' && $_POST['password'] !== '') {
        $sql = 'SELECT * FROM members WHERE id=? AND password=? AND is_deleted=0';
        $login = $db->prepare($sql);
        $login->execute(array(
            $_POST['id'],
            sha1($_POST['password'])
        ));
        $member = $login->fetch();

        //ログイン結果
        if ($member) {
            //成功
            $_SESSION['id'] = $member['id'];
            $_SESSION['time'] = time();

            if ($_POST['save'] === 'on') {
                // IDを14日間Cookieに保存
                setcookie('id', $_POST['id'], time() + 60 * 60 * 24 * 14);
            };

            header('Location: index.php');
            exit();
        } else {
            //失敗
            $error['login'] .= "ログインに失敗しました。IDとパスワードが正しく入力されているか確認してください。" . "\n";
        };
    };
};
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>ログイン</title>
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
                    <li class="nav-item" style="list-style-type: none">
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
                        <li class="nav-item active" style="list-style-type: none">
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
            <h1>ログイン</h1>
        </header>

        <main>
            <form action="" method="post">
                <div class="form-group row">
                    <label for="form_id" class="col-sm-2 col-form-label">ID</label>
                    <div class="col-sm-10">
                        <input id="form_id" class="form-control" type="text" name="id" value="<?php print(htmlspecialchars($id)); ?>" size="35" maxlength="255" />
                        <div class="error"><?php print($error['id']); ?></div>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="form_pw" class="col-sm-2 col-form-label">パスワード</label>
                    <div class="col-sm-10">
                        <input id="form_pw" class="form-control" type="password" name="password" value="<?php print(htmlspecialchars($_POST['password'])); ?>" size="35" maxlength="255" />
                        <div class="error"><?php print($error['password']); ?></div>
                    </div>
                </div>
                <div class="form-group">
                    <input id="form_save" type="checkbox" name="save" value="on">
                    <label for="form_save" class="col-form-label">次回から自動的にIDを入力する</label>
                </div>
                <div class="form-group">
                    <button class="btn btn-primary mb-2" type="submit">ログインする</button>
                </div>
            </form>
            <div class="error"><?php print($error['login']); ?></div>
            <p><a href="signup/">アカウントを作成</a></p>
        </main>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
</body>

</html>