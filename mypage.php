<?php

//共通関数・変数の読み込み
require('function.php');

debug('----------------------------------------------------------------------------------');
debug('-------        マイページ       ----------------------------------------------------');
debug('----------------------------------------------------------------------------------');

//ログイン認証
require('auth.php');

//変数格納
$user_id = $_SESSION['user_id'];
$mypageInfo = array();

$mypageInfo = getMypageInfo($user_id);
debug('$mypageInfo = '.print_r($mypageInfo, true));

?>
<?php
$title = 'マイページ';
require('head.php');
require('header.php');
?>

<div class="mypage">
  <h2 class="mypage__title">マイページ</h2>
  <div class="mypage__inner">

    <div class="mypage__accept">
      <h2>受入待ち一覧</h2>
      <?php if(empty($mypageInfo['accept'])){?>
      <p>現在、承認待ちの納品はありません。</p>
      <?php 
      }else{
        foreach($mypageInfo['accept'] as $key => $val){?>
      <div class="mypage__acceptList">
        <form action="" method="post">
          <a href="message.php?t_id=<?php echoStr($val['t_id']);?>">
            <p><?php echoStr($val['tree_name'].'　φ'.$val['d_min'].'-'.$val['d_max'].'cm　'.$val['length'].'m　'.$val['delivery_vol'].'㎥');?><br>
                <?php echoStr('納入日:'.$val['delivery_date']);?></p>
          </a>
          <input type="hidden" name="d_id" value="<?php echoStr($val['d_id']);?>">
          <input type="submit" value="受入">
        </form>
      </div>
      <?php }}?>
    </div>

    <div class="mypage__delivery">
      <h2>納入済み一覧</h2>
      <?php if(empty($mypageInfo['delivery'])){?>
        <p>現在、納入した木材はありません。</p>
      <?php
       }else{
        foreach($mypageInfo['delivery'] as $key => $val){
      ?>
      <div class="mypage__deliveryList">
        <a href="message.php?t_id=<?php echoStr($val['t_id']);?>">
          <p><?php echoStr($val['tree_name'].'　φ'.$val['d_min'].'-'.$val['d_max'].'cm　'.$val['length'].'m　'.$val['delivery_vol'].'㎥');?><br>
              <?php echoStr('納入日:'.$val['delivery_date']);?></p>
        </a>
        <span><?php echo ($val['accept_flg'])?'受入済':'受入待ち';?></span>
      </div>
      <?php }}?>
    </div>

  </div>

  <div class="mypage__inner">
    <div class="mypage__agree">
      <h2>応募承認待ち</h2>
      <?php if(empty($mypageInfo['agree'])){?>
      <p>現在、応募の承認待ちはありません。</p>
      <?php 
      }else{
        foreach($mypageInfo['agree'] as $key => $val){
      ?>
      <div class="mypage__agreeList">
        <form action="" method="post">
          <a href="transaction.php?t_id=<?php echoStr($val['t_id']);?>">
            <p><?php echoStr($val['tree_name'].'　φ'.$val['d_min'].'-'.$val['d_max'].'cm　'.$val['length'].'m　'.$val['request_vol'].'㎥');?><br>
                <?php echoStr('応募日:'.$val['a_apply_date']);?></p>
          </a>
          <input type="hidden" name="t_id" value="<?php $val['t_id'];?>">
          <input type="submit" name="agree" value="取引開始">
        </form>
      </div>
      <?php }} ?>
    </div>

    <div class="mypage__apply">
      <h2>応募した募集一覧</h2>
      <?php if(empty($mypageInfo['apply'])){?>
      <p>現在、応募していません。</p>
      <?php }else{
        foreach($mypageInfo['apply'] as $key => $val){
      ?>
      <div class="mypage__applyList">
        <a href="transaction.php?t_id=<?php echo $val['t_id'];?>">
          <p><?php echoStr($val['tree_name'].'　φ'.$val['d_min'].'-'.$val['d_max'].'cm'.$val['length'].'m　'.$val['request_vol'].'㎥');?><br>
              <?php echoStr('納入日:'.$val['a_apply_date']);?></p>
        </a>
        <span><?php echo ($val['r_agree_flg'])? '承認済':'承認待ち';?></span>
      </div>
      <?php }} ?>
    </div>
  </div>
  
  <div class="mypage__inner">
    
    <div class="mypage__transaction_r">
      <h2>募集者として取引中</h2>
      <?php if(empty($mypageInfo['transaction_r'])){?>
      <p>現在、募集者としての取引はありません。</p>
      <?php
      }else{
        foreach($mypageInfo['transaction_r'] as $key => $val){
      ?>
      <div class="mypage__transactionList_r">
        <a href="message.php?t_id=<?php echo $val['t_id'];?>">
          <p><?php echoStr($val['tree_name'].'　φ'.$val['d_min'].'-'.$val['d_max'].'cm　'.$val['length'].'m　'.$val['price'].'円/㎥');?><br>
              <?php echoStr('取引相手:'.$val['user_name']);?></p>
        </a>
      </div>
      <?php }} ?>
    </div>

    <div class="mypage__transaction_a">
      <h2>応募者として取引中</h2>
      <?php if(empty($mypageInfo['transaction_a'])){?>
      <p>現在、応募者としての取引はありません。</p>
      <?php
      }else{
        foreach($mypageInfo['transaction_a'] as $key => $val){
      ?>
      <div class="mypage__transactionList_a">
        <a href="message.php?t_id=<?php echo $val['t_id'];?>">
            <p><?php echoStr($val['tree_name'].'　φ'.$val['d_min'].'-'.$val['d_max'].'cm　'.$val['length'].'m　'.$val['price'].'円/㎥');?><br>
                <?php echoStr('取引相手:'.$val['user_name']);?></p>
        </a>
      </div>
      <?php }} ?>
    </div>

    </div>
  </div>
</div>