<?php
//共通変数・関数の読み込み
require('function.php');

require('auth.php');

if(!empty($_POST)){
  debug('POST送信あり');
  debug('POST送信の内容：'.print_r($_POST, true));
  
  //変数に格納
  $email = $_POST['email'];
  $pass = $_POST['pass'];

  //バリデーション
  VALID::EMAIL($email, 'email');
  VALID::REQUIRED($email, 'email');

  VALID::REQUIRED($pass, 'pass');

  if(empty($err_msg)){
    debug('バリデーションOK');

    //DBからメールアドレスでユーザーIDとパスワードを取得
    $dbh = dbConect();
    $sql = 'SELECT password,user_id FROM users WHERE email = :email';
    $data = array(':email'=>$email);

    $stmt = queryPost($dbh, $sql, $data);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    debug('取得したデータ：'.print_r($result, true));

    if(password_verify($pass, array_shift($result))){
      debug('パスワードが一致しました');
      debug('array_shift()後の$resultの中身：'.print_r($result, true));

      //セッションの有効期限の設定
      if(isset($_POST['loginhold'])){
        $sesLimit = 60 * 60 * 24 * 30;//３０日
      }else{
        $sesLimit = 60 * 60;
      }

      //セッションに記録
      $_SESSION['user_id'] = $result['user_id'];
      $_SESSION['login_limit'] = $sesLimit;
      $_SESSION['login_date'] = time();

      //マイページへ遷移
      header('Location:mypage.php');
    }else{
      debug('パスワードが一致しません。');
      $err_msg['common'] = 'パスワードまたはメールアドレスが間違っています。';
    }
  }
}
?>

<?php
$title = 'ログインページ';
require('head.php');
require('header.php');
?>

<div class="site-width">
  <div class="main">
    <h2 class="main__title">ログイン</h2>
    <div class="err-msg"><?php errOutput('common');?></div>
    <form method="post">
      <label for="">
        メールアドレス
        <input type="text" name="email">
      </label>
      <div class="err-msg"><?php errOutput('email');?></div>
      <label for="">
        パスワード
        <input type="password" name="pass">
      </label>
      <div class="err-msg"><?php errOutput('pass');?></div>
      <label for="">
        ログイン状態を保持する
        <input type="checkbox" name="loginhold" style="width: 20px;">
      </label>
      <div class="btn-box">
          <input type="submit" name="submit" value="ログイン" class="btn">
      </div>
    </form>
    <a href="signup.php">&lt 新規登録はこちら</a>
  </div>
</div>