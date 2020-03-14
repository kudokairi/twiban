<?php
require('db_date.php');
session_start();
try{
    $db = new PDO($dsn, $user , $password);
}catch(PDOException $e){
    print('DB接続エラー:' . $e->getMessage());
}
