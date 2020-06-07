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
} else {
    // $_SESSION['settings']から.id,.name,.introductionを受取
    $member = $_SESSION['settings'];
};

// 確認画面から修正しに戻ったら既入力内容を復元
if ($_REQUEST['action'] === 'rewrite' && isset($_SESSION['edit'])) {
    $member = $_SESSION['edit'];
};

if (!empty($_POST)) {
    $member = $_POST;

    // 入力チェック
    // 名前
    if ($_POST['name'] === '') {
        $error['name'] .= "名前を入力してください。" . "\n";
    };
    if (strlen($_POST['name']) > 40) {
        $error['name'] .= "名前は全角20文字以内で記述してください。" . "\n";
    };
    // 新しいパスワード
    if ($_POST['password1'] === '') {
        $error['password1'] = "現在のパスワードを入力してください。" . "\n";
    };
    // 新しいパスワード
    if ($_POST['password2'] === '') {
        $error['password2'] .= "新しいパスワードを入力してください。" . "\n";
    };
    if (strlen($_POST['password2']) > 0 && strlen($_POST['password2']) < 6) {
        $error['password2'] .= "パスワードは6文字以上で入力してください。" . "\n";
    };
    // 自己紹介
    if (strlen($_POST['introduction']) > 300) {
        $error['introduction'] .= "自己紹介は全角150文字以内で記述してください。" . "\n";
    };

    // 名前重複チェック
    if (empty($error)) {
        $checkNm = $db->prepare('SELECT COUNT(*) AS cnt FROM members WHERE id<>? AND is_deleted=0 AND name=?');
        $checkNm->execute(array(
            $_SESSION['settings']['id'],
            $_POST['name']
        ));
        $recode = $checkNm->fetch();
        if ($recode['cnt'] > 0) {
            // 本人以外ですでに存在する名前はNG
            $error['duplicate'] = "その名前はすでに使われています。別の名前を入力してください。" . "\n";
        }
    }

    // 現在のパスワードチェック
    if ($_SESSION['settings']['id'] !== '' && $_POST['password1'] !== '') {
        $sql = 'SELECT * FROM members WHERE id=? AND password=?';
        $checkPw = $db->prepare($sql);
        $checkPw->execute(array(
            $_SESSION['settings']['id'],
            sha1($_POST['password1'])
        ));
        $isOkayPw = $checkPw->fetch();
        if (!$isOkayPw) {
            //失敗
            $error['password1'] = "現在のパスワードが正しくありません。入力内容を確認してください。" . "\n";
        };
    };

    // エラーがなければ確認画面へ
    if (empty($error)) {
        $_SESSION['edit'] = $_POST;
        header('Location: edit_check.php');
        exit();
    };
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
            <h2>プロフィール編集</h2>

            <form method="post" action="">
                <dl>
                    <dt>
                        <label for="edit_nm">名前</label><span class="required">*</span>
                    </dt>
                    <dd>
                        <div class="form-group">
                            <input id="edit_nm" class="form-control" type="text" name="name" value="<?php print(htmlspecialchars($member['name'], ENT_QUOTES)); ?>" size="35" maxlength="255" />
                            <div class="error"><?php print($error['name']); ?></div>
                            <div class="error"><?php print($error['duplicate']); ?></div>
                        </div>
                    </dd>
                    <dt>
                        ID（変更不可）<span class="required">*</span>
                    </dt>
                    <dd>
                        <?php print($_SESSION['id']); ?>
                    </dd>
                    <dt>
                        <label for="edit_pw1">現在のパスワード</label><span class="required">*</span>
                    </dt>
                    <dd>
                        <div class="form-group">
                            <input id="edit_pw1" class="form-control" type="password" name="password1" value="<?php print(htmlspecialchars($member['password1'], ENT_QUOTES)); ?>" size="35" maxlength="255" />
                            <div class="error"><?php print($error['password1']); ?></div>
                        </div>
                    </dd>
                    <dt>
                        <label for="edit_pw2">新しいパスワード（現在と同じパスワードも使用可）</label><span class="required">*</span>
                    </dt>
                    <dd>
                        <div class="form-group">
                            <input id="edit_pw2" class="form-control" type="password" name="password2" value="<?php print(htmlspecialchars($member['password2'], ENT_QUOTES)); ?>" size="35" maxlength="255" />
                            <div class="error"><?php print($error['password2']); ?></div>
                        </div>
                    </dd>
                    <dt>
                        <label for="edit_introd">自己紹介</label>
                    </dt>
                    <dd>
                        <div class="form-group">
                            <input id="edit_introd" class="form-control" type="text" name="introduction" value="<?php print(htmlspecialchars($member['introduction'], ENT_QUOTES)); ?>" size="100" maxlength="300" />
                            <div class="error"><?php print($error['introduction']); ?></div>
                        </div>
                    </dd>
                </dl>
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