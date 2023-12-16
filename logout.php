<?php
//共通関数・変数を読み込み
require('function.php');

//ログイン認証
require('auth.php');

debug('----------------------------------------------------------------------------------');
debug('-------       ログアウトページ      -------------------------------------------------');
debug('----------------------------------------------------------------------------------');

if(!empty($_POST)){
  debug('ログアウトのためのPOST送信あり');

  session_destroy();

  header('Location:index.php');
}
?>

<?php
$title = 'ログインアウトページ';
require('head.php');
require('header.php');
?>

<div class="site-width">
  <div class="main">
    <h2 class="main__title" style="margin-bottom: 100px;">ログアウト</h2>
    <form method="post">
      <p style="font-size: 20px;margin-bottom:50px;">ログアウトしますか？</p>
      <div class="btn-box">
          <input type="submit" name="logout" value="ログアウトする" class="btn" style="width: 150px;">
      </div>
    </form>
  </div>
</div>