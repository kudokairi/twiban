<?php
session_start();
require('dbconnect.php');

if($_COOKIE['email'] !== ''){
  $email = $_COOKIE['email'];
}

if($_POST['email'] === ''){
	$error['email'] = 'blank';
}

if($_POST['password'] === ''){
	$error['password'] = 'blank';
}



if(!empty($_POST)){
  $email = $_POST['email'];
  if($_POST['email'] !== '' && $_POST['password'] !== ''){
    $login = $db->prepare('SELECT * FROM members WHERE email=? AND password=?');
    $login->execute(array($_POST['email'],sha1($_POST['password'])));
    $member = $login->fetch();

    if($member) {
      $_SESSION['id'] = $member['id'];
      $_SESSION['time'] = time();
      
      if($_POST['save'] === 'on'){
        setcookie('email' , $_POST['email'], time()+60*60*24*14);
      }


      header('Location: index.php');
      exit();
    }else{
      $error['login'] = 'failed';
    }
  }
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="style.css" />
<title>ログインする</title>
</head>

<body>
<div id="wrap">
  <div id="head">
    <h1>ログインする</h1>
  </div>
  <div id="login_content">
    <div id="lead">
      <p>メールアドレスとパスワードを記入してログインしてください。</p>
      <p>入会手続きがまだの方はこちらからどうぞ。</p>
      <p>&raquo;<a href="join/">入会手続きをする</a></p>
    </div>
    <form action="" method="post">
      <dl>
        <dt>メールアドレス</dt>
        <dd>
          <input type="text" name="email" size="35" maxlength="255" value="<?php echo htmlspecialchars($email); ?>" />
          <?php if ($error['email'] === 'blank'): ?>
    			<p class="error">＊メールアドレスを入力してください</p>
          <?php endif; ?> 
        </dd>
        <dt>パスワード</dt>
        <dd>
          <input type="password" name="password" size="35" maxlength="255" value="<?php echo htmlspecialchars($_POST['password']); ?>" />
          <?php if ($error['password'] === 'blank'): ?>
    			<p class="error">＊パスワードを入力してください</p>
	    		<?php endif; ?> 
        </dd>
        <dd>
          <input id="save" type="checkbox" name="save" value="on">
          <label for="save">次回からは自動的にログインする</label>
        </dd>
      </dl>
      <div>
        <input type="submit" value="ログインする" />
      </div><br>
      <p>【テストユーザーアカウント】</p>
      <p>メールアドレス：d.com</p>
      <p>パスワード：1111</p>
      <?php if ($error['login'] === 'failed'): ?>
    			<p class="error">ログインに失敗しました。正しいメールアドレスとパスワードを入力してください。</p>
	    		<?php endif; ?> 
    </form>
  </div>
</div>
</body>
</html>
