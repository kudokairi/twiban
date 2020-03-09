<?php
try{
    $db = new PDO('mysql:dbname=twitter_cl; host=127.0.0.1; charset=utf8', 'root' , 'kudodesu');
}catch(PDOException $e){
    print('DB接続エラー:' . $e->getMessage());
}