<?php
try{
    $db = new PDO('mysql:dbname=twitter_cl; host; charset=utf8', user , password);
}catch(PDOException $e){
    print('DB接続エラー:' . $e->getMessage());
}
