<?php

debug('-------       ログイン認証      ----------------------------------------------------');

if(isset($_SESSION['user_id'])){

  //ログインあり
  debug('ログイン履歴あり');
  debug('ユーザー情報（セッション情報）：'.print_r($_SESSION, true));
  debug('現在の時刻（UNIX TIMESTAMP）：'.time());
  $debug_login_limit = $_SESSION['login_date'] + $_SESSION['login_limit'];
  debug('ログイン期限と現在時刻の比較：'.$debug_login_limit.' vs '.time());


  if($_SESSION['login_date'] + $_SESSION['login_limit'] > time()){
    
    debug('ログイン期限内');

    if(basename($_SERVER['PHP_SELF']) === 'login.php'){
      
      //ログイン日時の更新
      $_SESSION['login_date']  = time();

      debug('マイページへ遷移します。');
      header('Location:mypage.php');
    }
  }else{
    
    debug('ログイン期限切れ');

    //セッション情報の削除
    session_destroy();

    //ログインページへ遷移
    debug('ログインページへ遷移します。');
    header('Location:login.php');
  }

}else{
  if(basename($_SERVER['PHP_SELF']) !== 'login.php'){
    
    debug('ログイン履歴なし');

    //ログインページへ遷移
    debug('ログインページへ遷移します。');
    header('Location:login.php');

  }
}