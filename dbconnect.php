<?php
require('db_date.php');
session_start();
try{
<<<<<<< HEAD
    $db = new PDO($dsn, $user , $password);
=======

>>>>>>> master
}catch(PDOException $e){
    print('DB接続エラー:' . $e->getMessage());
}
