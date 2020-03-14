<?php
session_start();
require('dbconnect.php');

if(empty($_REQUEST['id'])){
    header('Location: index.php');
    exit();
}
var_dump($_REQUEST['id']);
$posts = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=? UNION SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.reply_message_id=?');
$posts->execute(array($_REQUEST['id'],$_REQUEST['id']));
?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ひとこと掲示板</title>

    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <div id="wrap">
        <div id="head">
            <h1>ひとこと掲示板</h1>
        </div>
        <div id="content">
            <?php if($_REQUEST['thread_id'] > 0):?>
                <p>&laquo;<a href="thread.php">一覧にもどる</a></p>
            <?php else: ?>
                <p>&laquo;<a href="index.php">一覧にもどる</a></p>
           <?php endif ;?>
            <?php foreach($posts as $posted): ?>
            <div class="msg">
                <div class="message_img">
                    <img src="member_picture/<?php print(htmlspecialchars($posted['picture'])); ?>" width='300px'
                        height='auto'>
                </div>
                <div class="message_text">
                    <p><span class="name"><?php print(htmlspecialchars($posted['name'])); ?></span>　<span
                            class="day"><?php print(htmlspecialchars($posted['created'])); ?></span></p>
                    <p><?php print(htmlspecialchars($posted['message'])); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($posted)): ?>
            <p>その投稿は削除されたか、URLが間違えています</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>