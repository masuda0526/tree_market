<?php

//共通関数・変数読み込み
require('function.php');
//ログイン認証
require('auth.php');


debug('----------------------------------------------------------------------------------');
debug('-------       募集詳細画面      ----------------------------------------------------');
debug('----------------------------------------------------------------------------------');

if(!empty($_GET)){
  
  debug('GET送信あり。');
  debug('GET送信内容：'.print_r($_GET, true));
  
  $request_id = $_GET['r_id'];

  $request_info = getRequestOne($request_id);

}else{
  //GET送信が不正な場合
  debug('不正な値が入力されました。');
  debug('マイページへ遷移します。');
  header('Location:mypage.php');
}

if(!empty($_POST)){
  debug('POST送信あり（応募されました）。');
  $transactionInfo = checkTransaction($request_id, $_SESSION['user_id']);

  if(empty($transactionInfo)){
    try{
      $dbh = dbConect();
      $sql = 'INSERT INTO transactions (request_id, r_user_id, a_user_id, a_apply_flg, a_apply_date, create_date) VALUES (:request_id, :r_user_id, :a_user_id, :a_apply_flg, :a_apply_date, :create_date)';
      $data = array(':request_id'=>$request_id, ':r_user_id'=>$request_info['user_id'], ':a_user_id'=>$_SESSION['user_id'], ':a_apply_flg'=>true, ':a_apply_date'=>date('Y:m:d H:i:s'), ':create_date'=>date('Y:m:d H:i:s'));
  
      $stmt = queryPost($dbh, $sql, $data);
  
      if($stmt){
        debug('クエリ成功。（transactionsへの新規登録完了）');
        $t_id = $dbh->lastInsertId();
        debug('取引ページへ遷移します.　取引ページURL：transaction.php?t_id='.$t_id);
        header('Location:transaction.php?t_id='.$t_id);
      }else{
        debug('クエリ失敗。');
        debug('マイページへ遷移します。');
        header('Location:mypage.php');
      }
    }catch(Exception $e){
      error_log('エラー発生。'.$e->getMessage());
      debug('マイページへ遷移します。');
      header('Location:mypage.php');
    }
  }else{
    header('Location:transaction.php?t_id='.$transactionInfo['t_id']);
  }
  
  
  
}

?>
<?php

$title = '募集詳細';
require('head.php');
require('header.php');

?>

<div class="site-width">
  <div class="main">
    <h2 class="main__title">募集詳細</h2>
    <div class="detail">
      <table class="detail__table">
        <tr>
          <td class="head">地域</td>
          <td class="content"><?php echoStr($request_info['pref'].$request_info['city']);?></td>
        </tr>
        <tr>
          <td class="head">樹種</td>
          <td class="content"><?php echoStr($request_info['tree_name']);?></td>
        </tr>
        <tr>
          <td class="head">直径</td>
          <td class="content"><?php echoStr($request_info['d_min'].'cm　〜　'.$request_info['d_max'].'cm')?></td>
        </tr>
        <tr>
          <td class="head">長さ</td>
          <td class="content"><?php echoStr($request_info['length'].'メートル')?></td>
        </tr>
        <tr>
          <td class="head">買取単価</td>
          <td class="content"><?php echoStr($request_info['price']);?> 円/㎥</td>
        </tr>
        <tr>
          <td class="head">必要量</td>
          <td class="content"><?php echoStr($request_info['request_vol'])?>㎥</td>
        </tr>
      </table>
      <div class="detail__comment">
        <h3>コメント</h3>
        <div class="detail__comment__content">
          <?php echoStr($request_info['request_comment'])?>
        </div>
      </div>
      <?php
        if($_SESSION['user_id'] !== $request_info['user_id']){?>
        <form method="post">
          <div class="btn-box">
              <input type="submit" name="apply" value="応募する" class="btn">
          </div>
        </form>
      <?php } ?>
    </div>
    <div class="detail__return">
      <a href="<?php echo 'index.php'.appendGetparam(array('r_id'));?>">&lt; 一覧へ戻る</a>
    </div>
  </div>
</div>