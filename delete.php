<?php
session_start();
require('dbconnect.php');

if(isset($_SESSION['id'])){
    $id = $_REQUEST['id'];
    $member_id = $_REQUEST['member_id'];
    $thread_id = $_REQUEST['thread_id'];


    $messages = $db->prepare('SELECT * FROM posts WHERE id=?');
    $messages->execute(array($id));
    $message = $messages->fetch();
    
    if($message['member_id']) {
        $del = $db->prepare('DELETE FROM posts WHERE id=?');
        $del->execute(array($id));
    }
}

if(isset($member_id)){
    header('Location: mypage.php');
}else if($thread_id > 0){
    header('Location: thread.php');
}else{
    header('Location: index.php');
}

exit();

?>