<?php
session_start();
require('dbconnect.php');

if(isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()){
  $_SESSION['time'] = time();

  $members = $db->prepare('SELECT * FROM members WHERE id=?');
  $members->execute(array($_SESSION['id']));
  $member = $members->fetch();
} else{
  header('Location: login.php');
  exit();
}

if(!empty($_POST)){
  if($_POST['message'] !== ''){
    $message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, reply_message_id=?, created=NOW()');
    $message->execute(array($member['id'], $_POST['message'],$_POST['reply_post_id']));
 
    header('Location: index.php');
    exit();
  }
}

$page = $_REQUEST['page'];
if($page == ''){
  $page = 1;
}
$page = max($page, 1);

$counts = $db->query('SELECT COUNT(*) AS cnt FROM posts');
$cnt = $counts->fetch();
$maxPage = ceil($cnt['cnt'] / 5);
$page = min($page, $maxPage);

$start = ($page - 1) * 5;

$posts = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC LIMIT ?,5');
$posts->bindParam(1,$start,PDO::PARAM_INT);
$posts->execute();

if(isset($_REQUEST['res'])){
  $response = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=?');
  $response->execute(array($_REQUEST['res']));

  $table = $response->fetch();
  $res_name = '「' . $table['name'] . '」さんの' ;
  $res_message =  $table['message'] ;

}



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
            <h1>Twiban</h1>
        </div>
        <div class="profile">
            <img src="member_picture/<?php print(htmlspecialchars($member['picture'], ENT_QUOTES)); ?>"  />
            <div><a href="mypage.php?id=<?php print(htmlspecialchars($member['id'],ENT_QUOTES)); ?>">プロフィール</a></div>
            <form action="" method="POST">
                <dl>
                    <?php if(isset($table['name'])):?>
                    <dt><?php print(htmlspecialchars($res_name,ENT_QUOTES)); ?><br><?php print(htmlspecialchars($res_message,ENT_QUOTES)); ?><br>へのコメント
                    </dt>
                    <input type="hidden" name="reply" value="<?php print(htmlspecialchars($res_name,ENT_QUOTES)); ?>">
                    <?php else: ?>
                    <dt class="profile_name"><?php print(htmlspecialchars($member['name'], ENT_QUOTES)); ?>のつぶやき</dt>
                    <?php endif; ?>
                    <dd>
                        <textarea class="profile_tweet" name="message" cols="50" rows="5"></textarea>
                        <input type="hidden" name="reply_post_id"
                            value="<?php print(htmlspecialchars($_REQUEST['res'],ENT_QUOTES)); ?>" />
                    </dd>
                </dl>
                <div>
                    <p>
                        <input class="profile_submit" type="submit" value="投稿する" />
                    </p>
                </div>
            </form>
        </div>
        <div id="content">
            <div style="text-align: right"><a href="logout.php">ログアウト</a></div>
            <?php foreach ($posts as $post): ?>
            <div class="main_msg">
                <img src="member_picture/<?php print(htmlspecialchars($post['picture'], ENT_QUOTES)); ?>" width="48"
                    height="48" alt="<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>" />
                <?php if($post['reply_message_id'] > 0): ?>
                    <?php
                        $rep_abouts = $db->prepare('select p2.member_id from posts as p1 left join posts as p2 on p1.reply_message_id = p2.id WHERE p1.reply_message_id=?');
                        $rep_abouts->execute(array($post['reply_message_id']));
                        $rep_about = $rep_abouts->fetch();
                        $rep_about_names = $db->prepare('SELECT name FROM members WHERE id=?');
                        $rep_about_names->execute(array($rep_about['member_id']));
                        $rep_about_name =  $rep_about_names->fetch();
                    ?>
                <p class="name">
                    <?php print(htmlspecialchars($post['name'],ENT_QUOTES)); ?><span>　-To　<?php print(htmlspecialchars($rep_about_name["name"],ENT_QUOTES)); ?></span>
                    <?php else: ?>
                    <p class="name"><?php print(htmlspecialchars($post['name'],ENT_QUOTES)); ?></p>
                    <?php endif; ?>

                </p>
                <p><?php print(htmlspecialchars($post['message'], ENT_QUOTES)); ?>[<a
                        href="index.php?res=<?php print(htmlspecialchars($post['id'],ENT_QUOTES)); ?>">Re</a>]</p>
                <p class="day"><a
                        href="view.php?id=<?php print(htmlspecialchars($post['id'])) ?>"><?php print(htmlspecialchars($post['created'], ENT_QUOTES)); ?></a>

                    <?php if($post['reply_message_id'] > 0) :?>
                    <a href="view.php?id=<?php print(htmlspecialchars($post['reply_message_id'])) ?>">
                        返信元のメッセージ</a>
                    <?php endif; ?>

                    <?php if($_SESSION['id'] === $post['member_id']): ?>

                    [<a href="delete.php?id=<?php print(htmlspecialchars($post['id'])); ?>" style="color: #F33;">削除</a>]
                    <?php endif; ?>
                </p>
            </div>
            <?php endforeach; ?>

            <ul class="paging">
                <?php if($page > 1): ?>
                <li><a href="index.php?page=<?php print(htmlspecialchars($page - 1)); ?>">前のページへ</a></li>
                <?php else: ?>
                <li>前のページへ</li>
                <?php endif; ?>

                <?php if($page < $maxPage): ?>
                <li><a href="index.php?page=<?php print(htmlspecialchars($page + 1)); ?>">次のページへ</a></li>
                <?php else: ?>
                <li>次のページへ</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>

</html>