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

    if(isset($_POST['search_thread_name']) && $_POST['search_thread_name'] !== ""){
        $search = 'SELECT * FROM thread WHERE title like :title';
        $search_result = $db->prepare($search);
    
        $search_name = $_POST['search_thread_name'];
        $search_name = '%'.$search_name.'%';
        $search_result->bindParam(':title', $search_name, PDO::PARAM_STR);
        $search_result->execute();
    }

    if(isset($_POST['thread_name'])){
        if($_POST['thread_name'] === ""){
            $error['thread_name'] = 'empty';
        }else{
            $thread = $db->prepare('SELECT * FROM thread WHERE title=?');
            $thread->execute(array($_POST['thread_name']));
            $threads =  $thread->fetch();
            if ($threads === false) {
                $_SESSION['thread_name'] = $_POST['thread_name'];
                header('Location: thread.php');
                exit();
            } else {
                $error['thread_name'] = 'same';
            }
        }
    }
    


  }
}

$page = $_REQUEST['page'];
if($page == ''){
  $page = 1;
}
$page = max($page, 1);

//投稿一覧のページネーション
$counts = $db->query('SELECT COUNT(*) AS cnt FROM posts');
$cnt = $counts->fetch();
$maxPage = ceil($cnt['cnt'] / 5);
$page = min($page, $maxPage);

$start = ($page - 1) * 5;

$posts = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC LIMIT ?,5');
$posts->bindParam(1,$start,PDO::PARAM_INT);
$posts->execute();


//スレッド一覧のページネーション
$thread_page = $_REQUEST['thread_page'];
if($thread_page == ''){
  $thread_page = 1;
}
$thread_page = max($thread_page, 1);


$thread_counts = $db->query('SELECT COUNT(*) AS thread_cnt FROM thread');
$thread_cnt = $thread_counts->fetch();
$thread_maxPage = ceil($thread_cnt['thread_cnt'] / 15);
$thread_page = min($thread_page, $thread_maxPage);


$thread_start = ($thread_page - 1) * 15;
$thread_id = $_SESSION['id'];

$thread = $db->prepare('SELECT * FROM thread WHERE member_id=? ORDER BY created DESC LIMIT ?,15');
$thread->bindParam(2,$thread_start,PDO::PARAM_INT);
$thread->bindParam(1,$thread_id,PDO::PARAM_INT);
$thread->execute();

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
    <title>twiban</title>
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
                        <input class="profile_submit" type="submit" value="post" />
                    </p>
            </form>
            
        </div>
                    </div>
        <div id="content">
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
                        href="index.php?res=<?php print(htmlspecialchars($post['id'],ENT_QUOTES)); ?>">Re</a>]</p>
                <p class="day"><a
                        href="view.php?id=<?php print(htmlspecialchars($post['id'])) ?>"><?php print(htmlspecialchars($post['created'], ENT_QUOTES)); ?></a>

                    <?php if($post['reply_message_id'] > 0) :?>
                    <a href="view.php?id=<?php print(htmlspecialchars($post['reply_message_id'])) ?>">
                        返信元のメッセージ</a>
                    <?php endif; ?>

                    <?php if($_SESSION['id'] === $post['member_id']): ?>

                    [<a href="delete.php?id=<?php print(htmlspecialchars($post['id'])); ?>" style="color: #F33;">delete</a>]
                    <?php endif; ?>
                </p><br>
            </div>
            <?php endforeach; ?>

            <ul class="paging">
                <?php if($page > 1): ?>
                <li><a href="index.php?page=<?php print(htmlspecialchars($page - 1)); ?>">back</a></li>
                <?php else: ?>
                <li>first</li>
                <?php endif; ?>

                <?php if($page < $maxPage): ?>
                <li><a href="index.php?page=<?php print(htmlspecialchars($page + 1)); ?>">next</a></li>
                <?php else: ?>
                <li>last</li>
                <?php endif; ?>
            </ul>
                </div>


                <div class="thread_wrap">
        <form action="" method="POST">
                    <p class="thread_plus">スレッド検索</p>
                        <input type="text" name="search_thread_name" class="thread_name" value="<?php print(htmlspecialchars($_POST['search_thread_name'], ENT_QUOTES));?>">
                        <input type="submit" value="search" class="thread_search">
            </form><br>
            <?php if(isset($search_result)) :?>
                <?php foreach( $search_result as $search_results) :?>
                    <a class="thread_link" href="thread.php?id=<?php print(htmlspecialchars($search_results['id'], ENT_QUOTES)); ?>"><?php print(htmlspecialchars($search_results['title'],ENT_QUOTES)); ?></a>
                <?php endforeach ;?>
                
            <?php elseif(empty($search_result) && $_POST["search_thread_name"] ==="") :?>    　
               <div class="empty"><?php print("入力欄が空です") ; ?></div>  
            <?php endif ;?>





            </div>


        <div class="thread_wrap">
        <form action="" method="POST">
                    <p class="thread_plus">スレッド作成</p>
                        <input type="text" name="thread_name" class="thread_name">
                        <input type="submit" value="make" class="thread_make">
            </form>

            <?php if ($error['thread_name'] === 'empty'): ?>
    			<p class="empty">新規スレッドネームを入力してください</p>
            <?php endif; ?> 

            <?php if ($error['thread_name'] === 'same'): ?>
    			<p class="empty">そのスレッドは既に存在しています</p>
            <?php endif; ?> 


            <?php foreach($thread as $threads) :?>
                <a class="thread_link" href="thread.php?id=<?php print(htmlspecialchars($threads['id'], ENT_QUOTES)); ?>"><?php print(htmlspecialchars($threads['title'], ENT_QUOTES)); ?></a><br>
            <?php endforeach; ?>
            <ul class="paging">
                <?php if($thread_page > 1): ?>
                <li><a href="index.php?thread_page=<?php print(htmlspecialchars($thread_page - 1)); ?>">back</a></li>
                <?php else: ?>
                <li>first</li>
                <?php endif; ?>

                <?php if($thread_page < $thread_maxPage): ?>
                <li><a href="index.php?thread_page=<?php print(htmlspecialchars($thread_page + 1)); ?>">next</a></li>
                <?php else: ?>
                <li>last</li>
                <?php endif; ?>
            </ul>


            <div style="text-align: right; font-size:16px; margin-top:60px"><a href="logout.php">logout</a></div>
        </div>
        
    </div>
</body>

</html>