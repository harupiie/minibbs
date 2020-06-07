<?php
session_start();
require('../dbconnect.php');

//ログイン確認
if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
    $_SESSION['time'] = time();

    $sql = 'SELECT * FROM members WHERE id=?';
    $members = $db->prepare($sql);
    $members->execute(array($_SESSION['id']));
    $member = $members->fetch();

    $welcome = "ようこそ、" . "<b>" . htmlspecialchars($member['name'], ENT_QUOTES) . "</b>" . "さん";
} else {
    header('Location: ../index.php');
    exit();
};

// 設定画面から遷移してきているか
if (!isset($_SESSION['settings'])) {
    //確認失敗
    header('Location: ../index.php');
    exit();
}

if (!empty($_POST)) {

    $fileName = $_FILES['image']['name'];
    if (empty($fileName)) {
        $error['image'] = "イメージを指定してください。";
    } else {
        // 拡張子チェック
        $ext = pathinfo($fileName, PATHINFO_EXTENSION); // 拡張子取得
        $ext = strtolower($ext);    // 小文字変換
        if ($ext != 'jpg' && $ext != 'jpeg' && $ext != 'gif' && $ext != 'png') {
            $error['image'] = "イメージは [.gif][.jpg][.jpeg][.png] いずれかの形式で指定してください。";
        };
    };
    // エラーがなければ確認画面へ
    if (empty($error)) {
        $fileName = date('YmdHis') . $_FILES['image']['name'];    // ファイル名作成
        move_uploaded_file($_FILES['image']['tmp_name'], 'image/' . $fileName); // ファイルアップロード

        $_SESSION['edit_image'] = $fileName;        // DBに格納するファイル名
        header('Location: edit_image_check.php');
        exit();
    };
};

// 確認画面から修正しに戻ったら既入力内容を復元
if ($_REQUEST['action'] === 'rewrite' && isset($_SESSION['edit_image'])) {
    $_POST = $_SESSION['edit_image'];
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>設定</title>
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
                        <a class="nav-link" href="../index.php">ホーム</a>
                    </li>
                    <?php if (!empty($members)) : ?>
                        <li class="nav-item" style="list-style-type: none">
                            <a class="nav-link active" href="index.php"">設定</a>
                        </li>
                        <li class=" nav-item" style="list-style-type: none">
                                <a class="nav-link" href="../logout.php">ログアウト</a>
                        </li>
                    <?php else : ?>
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
            <h1>設定</h1>
        </header>

        <main>
            <h2>イメージ編集</h2>
            <form method="post" action="" enctype="multipart/form-data">
                <dl>
                    <dt>
                        <label for="edit_img">イメージ</label>
                    </dt>
                    <dd>
                        <?php if ($_SESSION['settings']['image'] == '') : ?>
                            <img src="../default_user.png" width="48" height="48" alt="" />
                        <?php else : ?>
                            <img src="image/<?php print(htmlspecialchars($_SESSION['settings']['image'], ENT_QUOTES)); ?>" width="150" height="150" alt="" />
                        <?php endif; ?>
                    </dd>
                </dl>
                <div class="form-group">
                    <input id="edit_img" class="form-control-file" type="file" name="image" size="35" value="test" />
                    <input type="hidden" name="hidden" value="image_on" />
                    <div class="error"><?php print($error['image']); ?></div>
                </div>
                <div class="form-group">
                    <button class="btn btn-primary mb-2" type="submit">登録する</button>
                </div>
            </form>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
</body>

</html>


<script src="https://kit.fontawesome.com/12d4831439.js" crossorigin="anonymous"></script>