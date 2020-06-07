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
};

// 投稿処理
if (!empty($_POST)) {
    if ($_POST['message'] !== '') {
        $message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, parent_message_id=?, created=NOW()');
        $message->execute(array(
            $member['id'],
            $_POST['message'],
            $_POST['parent_message_id']
        ));

        header('Location: index.php');
        exit();
    }
}

// 投稿一覧取得処理
$page = $_REQUEST['page'];

// ページ指定なし又は1以下の場合は1ページ目に飛ばす
if ($page == '') {
    $page = 1;
}
$page = max($page, 1);

// 最大ページ以上の指定は最大ページに飛ばす
$counts = $db->query('SELECT COUNT(*) AS cnt FROM posts');
$cnt = $counts->fetch();
$maxPage = ceil($cnt['cnt'] / 10);
$page = min($page, $maxPage);

// 最新10件を表示
$start = ($page - 1) * 10;
$posts = $db->prepare('SELECT m.name, m.image, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC LIMIT ?,10');
$posts->bindParam(1, $start, PDO::PARAM_INT);
$posts->execute();

if (isset($_REQUEST['res'])) {
    // 返信処理
    $response = $db->prepare('SELECT m.name, m.image, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=?');
    $response->execute(array($_REQUEST['res']));
    $parent = $response->fetch();
    $message = '>>' . $parent['id'] . ' ' . '@' . $parent['name'];
}
?>

<!DOCTYPE html>
<html lang="ja" class="custom">

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
            <?php if (!empty($members)) : ?>
                <label for="message">つぶやきを投稿しましょう！</label>
                <form action="" method="post">
                    <div class="form-row">
                        <div class="form-group col-sm-10">
                            <textarea id="message" class="form-control" name="message"><?php print(htmlspecialchars($message, ENT_QUOTES)); ?></textarea>
                            <input class="form-control" type="hidden" name="parent_message_id" value="<?php print(htmlspecialchars($_REQUEST['res'], ENT_QUOTES)); ?>" />
                        </div>
                        <div class="form-group col-sm-2">
                            <button class="btn btn-primary mb-2" type="submit">投稿する</button>
                        </div>
                    </div>
                </form>
            <?php else : ?>
                <label for="voidform">つぶやきを投稿するには、ログインが必要です。</label>
                <div class="form-row">
                    <div class="form-group col-sm-10">
                        <textarea id="voidform" class="form-control" disabled="disabled"></textarea>
                    </div>
                    <div class="form-group col-sm-2">
                        <button class="btn btn-secondary mb-2" type="button" disabled="disabled">投稿する</button>
                    </div>
                </div>
            <?php endif; ?>
        </header>

        <main>
            <?php foreach ($posts as $post) : ?>
                <div class="msg">
                    <p>
                        <?php if ($post['image'] == '') : ?>
                            <img src="default_user.png" width="48" height="48" alt="" />
                        <?php else : ?>
                            <img src="settings/image/<?php print(htmlspecialchars($post['image'], ENT_QUOTES)); ?>" width="48" height="48" alt="" />
                        <?php endif; ?>
                        <span class="message_id">[<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>]</span>
                        <span class="name"><?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?></span>
                        <span class="created"><?php print(htmlspecialchars($post['created'], ENT_QUOTES)); ?></span>
                        [<a href="index.php?res=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>">Re</a>]
                        <?php if ($_SESSION['id'] === $post['member_id']) : ?>
                            [<a href="delete.php?id=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>">削除</a>]
                        <?php endif; ?>
                    </p>
                    <p class="message">
                        <?php print(nl2br(htmlspecialchars($post['message']), ENT_QUOTES)); ?>
                    </p>
                    <p class="day">
                        <a href="view.php?id=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>">詳細</a>
                        <?php if ($post['parent_message_id'] > 0) : ?>
                            <a href="view.php?id=<?php print(htmlspecialchars($post['parent_message_id'], ENT_QUOTES)); ?>">返信元のメッセージ</a>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </main>

        <footer>
            <ul class="paging">
                <?php if ($page > 1) : ?>
                    <li><a href="index.php?page=<?php print($page - 1); ?>">&laquo;&nbsp;前の10件</a></li>
                <?php else : ?>
                    <li>&laquo;&nbsp;前の10件</li>
                <?php endif; ?>
                <?php if ($page < $maxPage) : ?>
                    <li><a href="index.php?page=<?php print($page + 1); ?>">次の10件&nbsp;&raquo;</a></li>
                <?php else : ?>
                    <li>次の10件&nbsp;&raquo;</li>
                <?php endif; ?>

            </ul>
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>

</body>

</html>