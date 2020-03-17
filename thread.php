<?php

session_start();
require('dbconnect.php');

if(isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()){
  $_SESSION['time'] = time();

  $members = $db->prepare('SELECT * FROM members WHERE id=?');
  $members->execute(array($_SESSION['id']));
  $member = $members->fetch();

}else{
    header('Location: login.php');
    exit();
}

//新規スレッド登録
if ($_SESSION['thread_name'] !== '' && $_SESSION['thread_name'] !== null && $_SESSION['registered'] !== $_SESSION['thread_name']) {
    $thread = $db->prepare('INSERT INTO thread SET member_id=?, title=?, created=NOW()');
    $thread->execute(array($_SESSION['id'],$_SESSION['thread_name']));
    $_SESSION['registered'] = $_SESSION['thread_name'];
}


  //スレッドネームをDB登録
  if (!empty($_POST)) {

      //スレコメントをDB登録
      if ($_POST['message'] !== '') {
          if ($_POST['reply_post_id'] === null) {
              $_POST['reply_post_id'] = 0;
          }
          $message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, reply_message_id=?, thread_id=?, created=NOW()');
          $message->execute(array($member['id'], $_POST['message'],$_POST['reply_post_id'], $_SESSION['thread_id']));
      }
  }
  if ($_REQUEST['id'] === '' or $_REQUEST['id'] === NULL) {
      $thread_titles = $db->prepare('SELECT * FROM thread WHERE title=?');
      $thread_titles->execute(array($_SESSION['thread_name']));
      $thread_title = $thread_titles->fetch();
      $_SESSION['thread_id'] = $thread_title['id'];
   }

   if($_REQUEST['id'] !== '' && $_REQUEST['id'] !== NULL){
      $thread_titles = $db->prepare('SELECT * FROM thread WHERE id=?');
      $thread_titles->execute(array($_REQUEST['id']));     
      $thread_title = $thread_titles->fetch();
      $_SESSION['thread_id'] = $thread_title['id'];

  }


  $page = $_REQUEST['page'];
if($page == ''){
  $page = 1;
}
$page = max($page, 1);

$counts = $db->prepare('SELECT COUNT(*) AS cnt FROM posts WHERE thread_id=?');
$counts->execute(array($_SESSION['thread_id']));
$cnt = $counts->fetch();
$maxPage = ceil($cnt['cnt'] / 5);
$page = min($page, $maxPage);

$start = ($page - 1) * 5;
$pp = $_SESSION['thread_id'];

$posts = $db->prepare('SELECT m.name, m.picture, p.* FROM members AS m JOIN posts AS p ON m.id=p.member_id WHERE p.thread_id=? ORDER BY p.created DESC LIMIT ?,5');
$posts->bindParam(2,$start,PDO::PARAM_INT);
$posts->bindParam(1,$pp,PDO::PARAM_STR);
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
    <title>thread</title>
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <div id="wrap" >
        <div id="head">
            <h1>Twiban</h1>
        </div>
        <div class="thread_profile">
            <img src="member_picture/<?php print(htmlspecialchars($member['picture'], ENT_QUOTES)); ?>" />
            <div><a href="mypage.php?id=<?php print(htmlspecialchars($member['id'],ENT_QUOTES)); ?>">プロフィール</a></div>
            <form action="" method="POST">
                <?php if(isset($table['name'])):?>
                <dt><?php print(htmlspecialchars($res_name,ENT_QUOTES)); ?><br><?php print(htmlspecialchars($res_message,ENT_QUOTES)); ?><br>へのコメント
                </dt>
                <input type="hidden" name="reply" value="<?php print(htmlspecialchars($res_name,ENT_QUOTES)); ?>">
                <?php else: ?>
                <dt class="profile_name"><?php print(htmlspecialchars($member['name'], ENT_QUOTES)); ?>のつぶやき</dt>
                <?php endif; ?>
                <div>
                    <textarea class="profile_tweet" name="message" cols="50" rows="5"></textarea>
                    <input type="hidden" name="reply_post_id"
                        value="<?php print(htmlspecialchars($_REQUEST['res'],ENT_QUOTES)); ?>" />
                    <p>
                        <input class="profile_submit" type="submit" value="投稿する" />
                    </p>

            </form>
            </div>
        </div>
        <div id="thread_content">
            <div class="thread_title">
                <p><?php print(htmlspecialchars($thread_title['title'],ENT_QUOTES)); ?>　について</p>
            </div>
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
                <p><?php print(htmlspecialchars($post['message'], ENT_QUOTES)); ?><br>[<a
                        href="thread.php?res=<?php print(htmlspecialchars($post['id'],ENT_QUOTES)); ?>">Re</a>]</p>
            <div class="detail_comments">            
                <form id="<?php print(htmlspecialchars($post['id']));?>">
                    <input type="hidden" name="thread_id" value="<?php print(htmlspecialchars($post['thread_id']));?>">
                    <input type="hidden" name="id" value="<?php print(htmlspecialchars($post['id']));?>">
                    <p class="day">
                        <a
                            href="javascript:submitFnc(<?php print(htmlspecialchars($post['id']));?>);"><?php print(htmlspecialchars($post['created'], ENT_QUOTES)); ?></a>
                </form>
                <?php if($post['reply_message_id'] > 0) :?>
                    <form id="<?php print(htmlspecialchars($post['reply_message_id']));?>">
                    <input type="hidden" name="thread_id" value="<?php print(htmlspecialchars($post['thread_id']));?>">
                    <input type="hidden" name="id" value="<?php print(htmlspecialchars($post['reply_message_id']));?>">
                    <p class="day">
                    <a href="javascript:submitFnc2(<?php print(htmlspecialchars($post['reply_message_id'])) ?>)">
                    返信元のメッセージ</a>
                </form>
                <?php endif; ?>
                <?php if($_SESSION['id'] === $post['member_id']): ?>
                <form id="fm1">
                    <input type="hidden" name="thread_id" value="<?php print(htmlspecialchars($post['thread_id']));?>">
                    <input type="hidden" name="id" value="<?php print(htmlspecialchars($post['id']));?>">
                    <a href="javascript:submitFnc1(<?php print(htmlspecialchars($post['id']));?>);"
                        style="color: #F33;">delete</a>
                </form>
                <?php endif; ?>
                </p>
                </div>
            </div>

            <?php endforeach; ?>
            <ul class="paging">
                <?php if($page > 1): ?>
                <li><a href="thread.php?page=<?php print(htmlspecialchars($page - 1)); ?>">前のページへ</a></li>
                <?php else: ?>
                <li>前のページへ</li>
                <?php endif; ?>

                <?php if($page < $maxPage): ?>
                <li><a href="thread.php?page=<?php print(htmlspecialchars($page + 1)); ?>">次のページへ</a></li>
                <?php else: ?>
                <li>次のページへ</li>
                <?php endif; ?>
            </ul>
            <div class="to_top"><a href="index.php">トップへ戻る</a></div>
            
    </div>
    <script type="text/javascript">
            function submitFnc(id) {
                //formオブジェクトを取得する
                var fm = document.getElementById(id);
                //Submit形式指定する
                fm.method = "post";
                //action先を指定する
                fm.action = "view.php";
                //Submit実行
                fm.submit();
            }

            function submitFnc1(id) {
                //formオブジェクトを取得する
                var fm1 = document.getElementById(id);
                //Submit形式指定する
                fm1.method = "post";
                //action先を指定する
                fm1.action = "delete.php";
                //Submit実行
                fm1.submit();
            }

            function submitFnc2(id){
                var fm2 = document.getElementById(id);
                fm2.method = "post";
                fm2.action = "view.php";
                fm2.submit();
            }
            </script>






</body>

</html>