<?php
session_start();
require('dbconnect.php');

if(isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()){
    $_SESSION['time'] = time();
    $members = $db->prepare('SELECT name, picture FROM members WHERE id=?');
    $members->execute(array($_SESSION['id']));
    $member = $members->fetch();

    $posts = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id  AND m.id=? ORDER BY p.created DESC');
    $posts->execute(array($_SESSION['id']));
} else{
    header('Location: login.php');
    exit();
}


if (!empty($_POST)) {
    if ($_POST['name'] != '') {
        $change_name = $db->prepare('UPDATE members SET name=? WHERE id=?');
        $change_name->execute(array($_POST['name'],$_SESSION['id']));
    }
    
    $fileName = $_FILES['image']['name'];
    if (!empty($fileName)) {
        $ext = substr($fileName, -3);
        if ($ext != 'jpg' && $ext != 'gif' && $ext != 'png') {
            $error['image'] = 'type';
        } else {
            $image = date('YmdHis') . $_FILES['image']['name'];
            $_POST['image'] = $image;
            move_uploaded_file($_FILES['image']['tmp_name'], 'member_picture/' . $_POST['image']);
            $change_img = $db->prepare('UPDATE members SET picture=? WHERE id=?');
            $change_img->execute(array($_POST['image'],$_SESSION['id']));
        }
    }
    header('Location: mypage.php');
	exit();
}
    
  
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Mypage</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <dl>
        <div class="back_index"><a href="index.php">戻る</a></div>
        <dt class="mypage_img">
        <img src="member_picture/<?php print(htmlspecialchars($member['picture'], ENT_QUOTES)); ?>" width="500">
        </dt>

        <dt class="mypage_name">
            <?php print(htmlspecialchars($member['name'],ENT_QUOTES)); ?>
        </dt>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mypage_changes">
            <dt class="mypage_change">プロフィール画像を変更する</dt>
            <dt class="mypage_change">名前を変更する</dt>
            </div>
            <div class="mypage_changes_button">
            <input type="file" name="image" size="35" value="プロフィール画像を変更する" class="mypage_change" />
            <input type="text" name="name" size="35" maxlength="255" value="" class="mypage_change"/>
            </div>
            <div class="mypage_changes_submit"><input type="submit" value="変更する"></div>
        </form>
        <?php foreach ($posts as $post): ?>
            <section class="mypage_article">
            <p><?php print(htmlspecialchars($post['message'],ENT_QUOTES)); ?></p><br>
            <?php print(htmlspecialchars($post['created'],ENT_QUOTES)); ?>
            <form id="fm1">
                <input type="hidden" name="member_id" value="<?php print(htmlspecialchars($post['member_id']));?>">
                <input type="hidden" name="id" value="<?php print(htmlspecialchars($post['id']));?>">
                <a href="javascript:submitFnc();" style="color: #F33;">[削除]</a>
            </form>

            </section>
        <?php endforeach; ?>
    </dl>
    <script type="text/javascript">
    function submitFnc() {
        //formオブジェクトを取得する
        var fm = document.getElementById("fm1");
        //Submit形式指定する
        fm.method = "post"; 
        //action先を指定する
        fm.action = "delete.php";
        //Submit実行
        fm.submit();
    }
    </script>


</body>
</html>