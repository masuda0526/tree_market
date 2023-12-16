<?php
//共通関数・変数の読み込み
require('function.php');

//ログイン認証
require('auth.php');

debug('----------------------------------------------------------------------------------');
debug('-------       木材募集画面      ----------------------------------------------------');
debug('----------------------------------------------------------------------------------');

//樹種リスト取得
$treeList = getTreeName();

if(!empty($_POST)){
  debug('POST送信あり。');
  debug('POST送信の内容：'.print_r($_POST, true));

  //変数を格納
  $pref = $_POST['pref'];
  $city = $_POST['city'];
  $tree_id = $_POST['tree_id'];
  $d_min = $_POST['d_min'];
  $d_max = $_POST['d_max'];
  $length = $_POST['length'];
  $price = $_POST['price'];
  $request_vol = $_POST['request_vol'];
  $request_comment = $_POST['request_comment'];

  //バリデーションチェック
  VALID::MAX_LENGTH($request_comment, 'request_comment');
  VALID::MAX_LENGTH($pref, 'pref');
  VALID::MAX_LENGTH($city, 'city');

  VALID::REQUIRED($pref, 'pref');
  VALID::REQUIRED($city, 'city');
  VALID::REQUIRED($tree_id, 'tree_id');
  VALID::REQUIRED($d_min, 'd_min');
  VALID::REQUIRED($d_max, 'd_max');
  VALID::REQUIRED($length, 'length');
  VALID::REQUIRED($price, 'price');
  VALID::REQUIRED($request_vol, 'request_vol');
  VALID::REQUIRED($request_comment, 'request_comment');

  

  if(empty($err_msg)){
    debug('バリデーションOK。');

    try{
      //DB接続
      $dbh = dbConect();
      $sql = 'INSERT INTO requests (user_id, request_vol, tree_id, d_min, d_max, length, price, request_comment, create_date) VALUES (:user_id, :request_vol, :tree_id, :d_min, :d_max, :length, :price, :request_comment, :create_date)';
      $data = array(':user_id'=>$_SESSION['user_id'], ':request_vol'=>$request_vol, ':tree_id'=>$tree_id, ':d_min'=>$d_min, ':d_max'=>$d_max, ':length'=>$length, ':price'=>$price, ':request_comment'=>$request_comment, ':create_date'=>date('Y:m:d H:i:s'));

      $stmt = queryPost($dbh, $sql, $data);

      header('Location:mypage.php');

    }catch(Exception $e){
      debug('エラー発生'.$e->getMessage());
      $err_msg['common'] = ERROR_MSG::COMMON;
    }

  }
}

?>

<?php
$title = '木材を募集する';
require('head.php');
require('header.php');
?>

<div class="site-width">
  <h2 class="main__title">木材を募集する</h2>
  <form method="post">
    <div class="err-msg"></div>
    <label for="">
      募集する都道府県
      <input type="text" name="pref" value=<?php holdForm('pref');?>>
    </label>
    <div class="err-msg"><?php errOutput('pref');?></div>
    <label for="">
      募集する市町村
      <input type="text" name="city" value=<?php holdForm('city');?>>
    </label>
    <div class="err-msg"><?php errOutput('city');?></div>
    <label for="">
      樹種
      <select name="tree_id" id="" class="tree-name">
        <option value="0">樹種を選択してください</option>
        <?php
        foreach($treeList as $key => $val){?>
          <option value="<?php echo $val['tree_id'];?>"<?php  if(!empty($_POST['tree_id']) && $_POST['tree_id']==$val['tree_id'])echo 'selected';?>><?php echo $val['tree_name'];?></option>
        <?php } ?>
      </select>
    </label>
    <div class="err-msg"><?php errOutput('tree_id');?></div>
    <label for="">
      直径
      <div class="diameter">
        <input type="text" name="d_min" class="diameter__min" value=<?php holdForm('d_min');?>><span>㎝　〜　</span><input type="text" name="d_max" class="diameter__max" value=<?php holdForm('d_max');?>><span>㎝</span>
      </div>
    </label>
    <div class="err-msg"><?php errOutput('d_min');?></div>
    <div class="request-sep">
      <div class="request-sep__box1">
        <label for="">
          長さ
          <div class="length">
            <input type="text" name="length" class="length__input" value=<?php holdForm('length');?>><span>メートル</span>
          </div>
        </label>
        <div class="err-msg"><?php errOutput('length');?></div>
        <label for="">
          買取単価
          <div class="tanka">
            <input type="text" name="price" class="tanka__input" value=<?php holdForm('price');?>><span>円/㎥</span>
          </div>
        </label>
        <div class="err-msg"><?php errOutput('price');?></div>
        <label for="">
          必要量
          <div class="requestVol">
            <input type="text" name="request_vol" class="requestVol__input" value=<?php holdForm('request_vol');?>><span>㎥</span>
          </div>
        </label>
        <div class="err-msg"><?php errOutput('request_vol');?></div>
      </div>
      <div class="request-sep__box2">
        <label for="">
          募集コメント
          <textarea name="request_comment" id="" cols="30" rows="8"><?php holdForm('request_comment');?></textarea>
        </label>
        <div class="err-msg"><?php errOutput('request_comment');?></div>
      </div>
    </div>
    <div class="btn-box">
      <input type="submit" value="募集する" class="btn">
    </div>
  </form>
</div>