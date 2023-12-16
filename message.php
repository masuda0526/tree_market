<?php

//共通関数・変数の読み込み
require('function.php');

debug('----------------------------------------------------------------------------------');
debug('-------       　連絡画面　      ----------------------------------------------------');
debug('----------------------------------------------------------------------------------');

//ログイン認証
require('auth.php');


if($_GET['t_id']){
  //変数に格納
  $t_id = $_GET['t_id'];

  //取引情報取得
  $transactionInfo = getTransactionInfoUseTid($t_id)->fetch(PDO::FETCH_ASSOC);
  debug('$transaction = '.print_r($transactionInfo, true));
  //募集情報を取得
  $requestInfo = getRequestOne($transactionInfo['request_id']);


  if(!empty($transactionInfo['r_agree_flg']) && ($transactionInfo['r_user_id'] == $_SESSION['user_id'] || $transactionInfo['a_user_id'] == $_SESSION['user_id'])){

    debug('取引ユーザ・取引の承認を確認');
    
    
    if($transactionInfo['r_user_id'] == $_SESSION['user_id']){
      debug('ユーザーは募集者です。');
      $RA_flg = 1;
      $partnerInfo = getPartnerInfo($transactionInfo['a_user_id']);
    }elseif($transactionInfo['a_user_id'] == $_SESSION['user_id']){
      debug('ユーザーは応募者です。');
      $RA_flg = 0;
      $partnerInfo = getPartnerInfo($transactionInfo['r_user_id']);
    }else{
      debug('エラー発生。トップページへ遷移します。');
      header('Location:index.php');
    }
  }else{
    debug('未承認またはユーザーに関係のない取引です。');
    header('Location:index.php');
  }

}else{
  debug('不正な値が入りました。：t_id');
  header('Location:index.php');
}

if(!empty($_POST)){
  debug('POST送信あり');
  debug('POST送信の内容：'.print_r($_POST, true));

  if(!empty($_POST['send_message'])){
    debug('メッセージ送信あり。');
    
    $message = ($_POST['message'])?$_POST['message']:'';

    VALID::MAX_LENGTH($message, 'message');
    VALID::REQUIRED($message, 'message');

    if(empty($err_msg)){
      debug('メッセージ送信のバリデーションOK。');

      try{
        //DB登録
        $dbh = dbConect();
        $sql = 'INSERT INTO messages (t_id, message, send_user, recieve_user, create_date) VALUES (:t_id, :message, :send_user, :recieve_user, :create_date)';
        $date = array(':t_id'=>$transactionInfo['t_id'], ':message'=>$message, ':send_user'=>$_SESSION['user_id'], ':recieve_user'=>$partnerInfo['user_id'], ':create_date'=>date('Y:m:d H:i:s'));

        $stmt = queryPost($dbh, $sql, $date);

        if($stmt){
          debug('メッセージ送信成功');
          header('Location:message.php?t_id='.$t_id);
          exit;
        }else{
          debug('メッセージ送信失敗');
          $err_msg['message'] = ERROR_MSG::COMMON;
        }
      }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
      }
    }
  }elseif(!empty($_POST['delivery'])){
    debug('納入あり。');

    $delivery_vol = ($_POST['delivery_vol'])?$_POST['delivery_vol']:'';

    VALID::REQUIRED($delivery_vol, 'delivery_vol');

    if(empty($err_msg)){
      debug('納入のバリデーションOK');

      try{
        //DB接続
        $dbh = dbConect();
        //納入登録用
        $str = <<<EOT
{$delivery_vol}㎥の納品があります。
確認お願いいたします。
EOT;
        $sql_delivery = 'INSERT INTO deliveries (t_id, delivery_vol, delivery_date, create_date) VALUES (:t_id, :delivery_vol, :delivery_date, :create_date)';
        $data_delivery = array(':t_id'=>$t_id, ':delivery_vol'=>$delivery_vol, ':delivery_date'=>date('Y:m:d H:i:s'), ':create_date'=>date('Y:m:d H:i:s'));
        $stmt_delivery = queryPost($dbh, $sql_delivery, $data_delivery);

        $sql_message = 'INSERT INTO messages (t_id, message, send_user, recieve_user, create_date) VALUES (:t_id, :message, :send_user, :recieve_user, :create_date)';
        $data_message = array(':t_id'=>$t_id, ':message'=>$str, ':send_user'=>$_SESSION['user_id'], ':recieve_user'=>$partnerInfo['user_id'], ':create_date'=>date('Y:m:d H:i:s'));
        $stmt_message = queryPost($dbh, $sql_message, $data_message);
        if($stmt_delivery && $stmt_message){
          debug('納入登録成功。');
          header('Location:message.php?t_id='.$t_id);
          exit;
        }else{
          debug('納入登録失敗。');
          $err_msg['delivery_vol'] = ERROR_MSG::COMMON; 
        }
      }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
      }

    }
  }elseif(!empty($_POST['accept'])){
    debug('受入あり。');
    //変数格納
    $d_id = $_POST['d_id'];
    debug('POST送信の内容： d_id = '.$d_id);

    acceptDelivery($d_id);
    
  }
  //header('Location:message.php?t_id='.$t_id);
  //exit;
}

//やりとりメッセージ取得
$messageList = getMessage($t_id);
//納入状況取得
$deliveryList = getDelivery($t_id);

?>

<?php
$title = '取引・連絡画面';
require('head.php');
require('header.php');

?>
<div class="two-colum">
  <div class="message">
    <h3>メッセージボード</h3>
    <div class="message__board">
      <div class="message__board__info">
        <p>応募日時　<?php echoStr($transactionInfo['a_apply_date']); ?></p>
      </div>
      <div class="message__board__info">
        <p>承認日時　<?php echoStr($transactionInfo['r_agree_date']); ?></p>
      </div>
      <?php
      foreach($messageList as $key => $val){?>
      <div class="message-box">
        <div class="<?php echo ($val['send_user']==$_SESSION['user_id'])?'send-message':'recieve-message';?>">
          <p class="<?php echo ($val['send_user']==$_SESSION['user_id'])?'send-content':'recieve-content';?>"><?php echoStr($val['message'])?></p>
          <p class="message-date"><?php echoStr($val['create_date'])?></p>
        </div>
      </div>
      <?php }?>
    </div>
  </div>

  <div class="info">
    <div class="info__request">
      <h3>募集情報</h3>
      <p>地域：<?php echoStr($requestInfo['pref'].$requestInfo['city']); ?></p>
      <p>樹種：<?php echoStr($requestInfo['tree_name']); ?></p>
      <p>直径：<?php echoStr($requestInfo['d_min'].'cm 〜 '.$requestInfo['d_max'].'cm'); ?></p>
      <p>長さ：<?php echoStr($requestInfo['length'].'m'); ?></p>
      <p>単価：<?php echoStr($requestInfo['price'].'円/㎥'); ?></p>
      <p>必要量：<?php echoStr($requestInfo['request_vol'].'㎥'); ?></p>
      <p>コメント：<?php echoStr($requestInfo['request_comment']); ?></p>
    </div>
    <div class="info__partner">
      <h3>取引相手</h3>
      <p>氏名：<?php echoStr($partnerInfo['user_name']); ?></p>
      <p>住所：<?php echoStr($partnerInfo['address']); ?></p>
      <p>プロフィール：<?php echoStr($partnerInfo['profile']); ?></p>
    </div>
  </div>

  <div class="send">
    <form action="" method="post">
      <label for="">
        メッセージ
        <textarea name="message" id="" cols="30" rows="6"></textarea>
      </label>
      <div class="err-msg"><?php errOutput('message');?></div>
      <div class="btn-box">
        <input type="submit" name="send_message" class="btn" value="送信">
      </div>
    </form>
  </div>

  <?php if(!$RA_flg){ //応募者なら納入ボタンを表示?>
  <div class="delivery">
    <h3>納入する</h3>
    <form action="" method="post">
      <div class="delivery__inner">
        <label for="">
        <input type="text" name="delivery_vol" placeholder="100">㎥
        </label>
        <input type="submit" name="delivery" class="btn" value="納入">
      </div>
      <div class="err-msg"><?php errOutput('delivery_vol');?></div>
    </form>
  </div>
  <?php } ?>
  
  <?php if(!empty($deliveryList)){?>
  <div class="accept">
    <h3>納入状況</h3>
    <?php foreach($deliveryList as $key => $val){?>
    <form action="" method="post">
      <div class="accept__list">
        <p class="accept__vol"><?php echoStr($val['delivery_vol'].'㎥');?><span class="accept_date">(<?php echoStr($val['delivery_date']);?>)</span></p>
        <?php if($RA_flg && !$val['accept_flg']){ //募集者で未受入なら受入ボタンを表示?>
        <input type="hidden" name="d_id" value="<?php echoStr($val['d_id']);?>">
        <input type="submit" name="accept" class="btn" value="受入">
        <?php }elseif(!$RA_flg && !$val['accept_flg']){ //募集者で未受入なら承認まちと表示 ?>
        <p class="accept__flg wait">受入待ち</p>
        <?php }else{ //受入済なら承認済みと表示?>
        <p class="accept__flg">受入済み</p>
        <?php } ?>
      </div>
    </form>
    <?php }?> 
  </div>
  <?php }?>
  

</div>