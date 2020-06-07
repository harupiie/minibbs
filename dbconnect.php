<?php 
try {
    require_once('/var/www/html/hidden/dbinfo.php');
    $db = new PDO($dsn, $user, $pw);

} catch(PDOException $e){
    print('DB接続エラー：' . $e->getMessage());

}
?>