<?php

//変数・関数の読み込み
require('function.php');

debug('----------------------------------------------------------------------------------');
debug('-------    ユーザー登録ページ    ----------------------------------------------------');
debug('----------------------------------------------------------------------------------');


if(!empty($_POST)){
  debug('POST送信あり。');
  debug('POST送信の内容：'.print_r($_POST, true));
  //変数に格納
  $user_name = $_POST['user_name'];
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];
  $address = $_POST['address'];
  $tel = $_POST['tel'];
  $profile = $_POST['profile'];

  //バリデーション
  //名前
  Valid::REQUIRED($user_name, 'user_name');

  //メールアドレス
  VALID::EMAIL($email, 'email');
  VALID::REQUIRED($email, 'email');

  //パスワード
  VALID::PASS_MATCH($pass, $pass_re, 'pass');
  VALID::MIN_LENGTH($pass, 'pass');
  VALID::REQUIRED($pass, 'pass');
  VALID::REQUIRED($pass_re, 'pass_re');
  

  //住所
  VALID::REQUIRED($address, 'address');

  //電話番号
  VALID::TEL($tel, 'tel');
  VALID::REQUIRED($tel, 'tel');

  //プロフィール
  VALID::MAX_LENGTH($profile, 'profile');
  VALID::REQUIRED($profile, 'profile');

  if(empty($err_msg)){
    debug('バリデーションOK。');
    try{
      $dbh = dbConect();
      $sql = 'INSERT INTO users (user_name, password, email, address, tel, profile, create_date) 
              VALUE (:user_name, :password, :email, :address, :tel, :profile, :create_date)';
      $data = array(
        ':user_name'=>$user_name,
        ':password' => password_hash($pass, PASSWORD_DEFAULT),
        ':email' => $email,
        ':address' => $address,
        ':tel' => $tel,
        ':profile' => $profile,
        ':create_date' => date('Y-m-d H:i:s')
      );

      $stmt = queryPost($dbh, $sql, $data);
    
      if($stmt){
        debug('クエリ成功。');
        //クッキーの処理
        $sesLimit = 60 * 60 ;
        $_SESSION['login_date'] = time();
        $_SESSION['login_limit'] = $sesLimit;
        $_SESSION['user_id'] = $dbh->lastInsertId();

        //マイページへ遷移
        header('Location:mypage.php');
      }
    }catch(Exception $e){
      debug('クエリ失敗'.$e->getMessage());
      $err_msg['common'] = ERROR_MSG::COMMON;
    }
  }
}

?>
<?php
$title = '新規登録画面';
require('head.php');
require('header.php');
?>
  <div class="site-width">
    <div class="main">
      <h2 class="main__title">新規登録</h2>
      <form method="post">
        <label for="">
          名前
          <input type="text" name="user_name" value="<?php holdForm('user_name');?>">
        </label>
        <div class="err-msg"><?php errOutput('user_name');?></div>
        <label for="">
          メールアドレス
          <input type="text" name="email" value="<?php holdForm('email');?>">
        </label>
        <div class="err-msg"><?php errOutput('email');?></div>
        <label for="">
          パスワード
          <input type="text" name="pass" value="<?php holdForm('pass');?>">
        </label>
        <div class="err-msg"><?php errOutput('pass');?></div>
        <label for="">
          パスワード
          <input type="text" name="pass_re" value="<?php holdForm('pass_re');?>">
        </label>
        <div class="err-msg"><?php errOutput('pass_re');?></div>
        <label for="">
          住所
          <input type="text" name="address" value="<?php holdForm('address');?>">
        </label>
        <div class="err-msg"><?php errOutput('address');?></div>
        <label for="">
          電話番号
          <input type="text" name="tel" value="<?php holdForm('tel');?>">
        </label>
        <div class="err-msg"><?php errOutput('tel');?></div>
        <label for="">
          プロフィール
          <textarea name="profile" id="" cols="30" rows="10"><?php holdForm('profile');?></textarea>
        </label>
        <div class="err-msg"><?php errOutput('profile');?></div>
        <div class="btn-box">
          <input type="submit" name="submit" value="登録する" class="btn">
        </div>
      </form>
    </div>
  </div>
<?php
require('footer.php');
?>