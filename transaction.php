<?php
//共通関数・変数読み込み
require('function.php');

//ログイン認証
require('auth.php');

debug('----------------------------------------------------------------------------------');
debug('-------       　取引画面　      ----------------------------------------------------');
debug('----------------------------------------------------------------------------------');

if(!empty($_GET)){
  debug('GET送信あり。');
  debug('GET送信の内容：'.print_r($_GET, true));

  if($_GET['t_id']){
    $stmt = getTransactionInfoUseTid($_GET['t_id']);
    $transactionInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    debug('取得した取引情報'.print_r($transactionInfo, true));
    $requestInfo = getRequestOne($transactionInfo['request_id']);
    $user_id = $_SESSION['user_id'];
  }else{
    debug('GETパラメータの値が不適切です。');
    debug('TOPページへ遷移します。');
    header('Location:index.php');
  }

  if(empty($transactionInfo['r_agree_flg'])){

    debug('募集者が取引を未承認です。');
  
    if($transactionInfo['r_user_id'] == $user_id){
      $partnerInfo = getPartnerInfo($transactionInfo['a_user_id']);
    }elseif($transactionInfo['a_user_id'] == $user_id){
      $partnerInfo = getPartnerInfo($transactionInfo['r_user_id']);
    }else{
      debug('予期せぬ値が入りました。');
      header('Location:index.php');
    }
  }else{
    debug('募集者による取引の承認済みです。');
    debug('メッセージ画面へ遷移します。');
    header('Location:message.php?t_id='.$transactionInfo['t_id']);
  }
}else{
  debug('予期せぬ値が入りました。');
  header('Location:index.php');
}

if(!empty($_POST)){
  debug('承認のためのPOST送信あり。');
  debug('POSTの内容： $_POST ='.print_r($_POST, true));
  
  $stmt = requestApproval($transactionInfo['t_id']);

  if($stmt){
    header('Location:message.php?t_id='.$transactionInfo['t_id']);
  }else{
    header('Location:index.php');
  }
}

?>

<?php
$title = '取引画面';
require('head.php');
require('header.php');
?>
<div class="two-colum">
  <div class="main">
    <h2 class="main__title">取引承認前画面</h2>
    <div class="transaction">
      <h2>募集内容</h2>
      <table class="transaction__table">
        <tr>
          <td class="head">地域</td>
          <td class="content"><?php echoStr($requestInfo['pref'].$requestInfo['city']);?></td>
        </tr>
        <tr>
          <td class="head">樹種</td>
          <td class="content"><?php echoStr($requestInfo['tree_name']);?></td>
        </tr>
        <tr>
          <td class="head">直径</td>
          <td class="content"><?php echoStr($requestInfo['d_min'].'cm　〜　'.$requestInfo['d_max'].'cm')?></td>
        </tr>
        <tr>
          <td class="head">長さ</td>
          <td class="content"><?php echoStr($requestInfo['length'].'メートル')?></td>
        </tr>
        <tr>
          <td class="head">買取単価</td>
          <td class="content"><?php echoStr($requestInfo['price']);?> 円/㎥</td>
        </tr>
      </table>
    </div>
    <div class="partner">
      <h2 class="partner__title">⚪︎取引相手の情報</h2>
      <table class="partner__table">
        <tr>
          <td class="head">氏名：</td>
          <td class="content"><?php isset($partnerInfo)?echoStr($partnerInfo['user_name']):'';?></td>
        </tr>
        <tr>
          <td class="head">住所：</td>
          <td class="content"><?php isset($partnerInfo)?echoStr($partnerInfo['address']):'';?></td>
        </tr>
      </table>
      <div class="partner__comment">
        <h2>コメント</h2>
        <p><?php if(!isset($partnerInfo['comment'])){echo 'コメントはありません。';}else{echoStr($partnerInfo['comment']);};?></p>
      </div>
    </div>
    <div class="state">
      <?php if($transactionInfo['r_user_id'] == $user_id){?>
        <form action="" method="post">
          <div class="btn-box">
            <input type="submit" name="approval" value="承認する" class="btn">
          </div>
        </form>
      <?php }else{?>
      <p>相手の承認を待っています<br>承認をお待ちください</p>
      <?php } ?>
    </div>
  </div>
</div>
