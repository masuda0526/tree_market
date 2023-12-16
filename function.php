<?php

//定数の準備
class ERROR_MSG{
  const REQURIRE = '入力必須です。';
  const EMAIL = 'メールアドレスではありません。';
  const MATCH = '再入力したパスワードと一致しません。';
  const FEW_CHAR = '文字数が少なすぎます。';
  const TEL = '電話番号を入力してください。';
  const MANY_CHAR = '文字数が多すぎます。';
  const COMMON = 'エラーが発生しました。時間をおいてからお試しください。';
}
//変数の準備
$err_msg = array();
$debugFlg = TRUE;

//エラーログの表示
ini_set('error_reporting', E_ALL);
ini_set('log_errors','on');
ini_set('error_log', 'php.log');

//sessionの有効期限を延長
session_save_path('/var/tmp/');//保存先を変更
ini_set('session.gc_maxlifetime',60*60*24*30);//ガーベージコレクションが削除する有効期限を伸ばす
ini_set('session.cookie_lifetime',60*60*24*30);//ブラウザを閉じても削除されないようにクッキーの有効期限を伸ばす
session_start();

//デバッグ用
function debug($str){
  global $debugFlg;
  if(!empty($debugFlg)){
    error_log($str);
  }
}


//バリデーション関数
class VALID{
  public static function REQUIRED($str, $key){
    global $err_msg;
    if(empty($str)){
      $err_msg[$key] = ERROR_MSG::REQURIRE;
    }
  }
  public static function PASS_MATCH($str1, $str2, $key){
    global $err_msg;
    if($str1 !== $str2){
      $err_msg[$key] = ERROR_MSG::MATCH; 
    }
  }
  public static function MIN_LENGTH($str, $key, $len=8){
    global $err_msg;
    if(mb_strlen($str) < $len){
      $err_msg[$key] = ERROR_MSG::FEW_CHAR;
    }
  }
  public static function TEL($str, $key){
      global $err_msg;
      if(mb_strlen($str) !== 10 && mb_strlen($str) !== 11){
        debug('TELーバリデーションエラー1'.mb_strlen($str).'文字です');
        $err_msg['tel'] = ERROR_MSG::TEL;
      }
      if((int)substr($str, 0, 1) !== 0){
        //debug('TELーバリデーションエラー2:先頭文字->'.substr($str, 0, 1));
        $err_msg['tel'] = ERROR_MSG::TEL;
      }
      if((int)substr($str, 0, 1) == 0 && (int)substr($str, 1, 1) == 0){
        //debug('TELーバリデーションエラー3:先頭の２文字->'.substr($str, 0, 2));
        $err_msg['tel'] = ERROR_MSG::TEL;
      }
  }
  public static function MAX_LENGTH($str, $key, $len=255){
    global $err_msg;
    if(mb_strlen($str) > $len){
      debug('文字数は'.mb_strlen($str).'文字です。');
      $err_msg[$key] = ERROR_MSG::MANY_CHAR;
    }
  }
  public static function EMAIL($str, $key){
    global $err_msg;
    if(!preg_match('/^[a-z0-9._+^~-]+@[a-z0-9.-]+$/i',$str)){
      $err_msg['email'] = ERROR_MSG::EMAIL;
    }
  }
}

//バリデーションエラー出力
function errOutput($key){
  global $err_msg;
  if(!empty($err_msg[$key])){
    echo $err_msg[$key];
  }
}

//フォームの保持
function holdForm($key){
  if(!empty($_POST[$key])){
    echo htmlspecialchars($_POST[$key], ENT_QUOTES, 'UTF-8');
  }
}

//サニタイズ
function sanitaiz($str){
  echo htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

//文字列を表示する関数
function echoStr($str){
  echo htmlspecialchars($str);
}

//DB関係
function dbConect(){
  $dsn = 'mysql:host=localhost;dbname=tree_market;charset=utf8';
  $db_user = 'root';
  $db_pass = 'root';
  $options = array(
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, //詳細なエラーを吐き出す
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, //取得したデータを連想配列で返す
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY=>true //取得したデータを格納する設定
  );

  $dbh = new PDO($dsn, $db_user, $db_pass, $options);
  return $dbh;
}

function queryPost($dbh, $sql, $data){
  global $err_msg;
  $stmt = $dbh->prepare($sql);
  if(!$stmt->execute($data)){
    debug('クエリ失敗。');
    $err_msg['common'] = ERROR_MSG::COMMON;
    return 0;
  }else{
    debug('クエリ成功');
    return $stmt;
  }
}

//樹種一覧を取得
function getTreeName(){

  debug('樹種一覧を取得します。');

  $dbh = dbConect();
  $sql = 'SELECT * FROM trees';
  $data = array();

  $stmt = queryPost($dbh, $sql, $data);
  
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  //debug('取得した樹種一覧：'.print_r($result, true));

  return $result; 
}
//樹種名を返す関数
function returnTreeName($tree_id){
  $tree_List = getTreeName();
  foreach($tree_List as $key => $val){
    if($val['tree_id'] === $tree_id){
      $returnName = $val['tree_name'];
    }
  }
  debug('返す樹種名：'.$returnName);
  return $returnName;
}

//募集情報を１つ取得する
function getRequestOne($request_id){

  debug('募集情報を取得します。');
  debug('取得する募集ID：　$request_id = '.$request_id);

  $dbh = dbConect();
  $sql = 'SELECT r.user_id, r.pref, r.city, r.d_min, r.d_max, r.length, r.price, r.request_vol, r.request_comment, t.tree_name FROM requests as r LEFT JOIN trees as t ON r.tree_id = t.tree_id WHERE r.request_id = :request_id';
  $data = array(':request_id' => $request_id);

  $stmt = queryPost($dbh, $sql, $data);

  $result = $stmt -> fetch(PDO::FETCH_ASSOC);

  debug('取得した募集情報：'.print_r($result, true));

  return $result;
}

//募集情報一覧を取得する関数
function getRequests($page, $showSpan=10, $pref, $tree_id, $sort){
  $result = array();
  $dbh = dbConect();
  $sql = 'SELECT r.request_id, r.pref, r.city, r.d_min, r.d_max, r.length, r.price, t.tree_name FROM requests as r LEFT JOIN trees as t ON r.tree_id = t.tree_id';
  $data = array();

  //検索用SQL文作成
  if(!empty($pref) && !empty($tree_id)){
    $sql .= ' WHERE pref = :pref AND tree_id = :tree_id';
    $data = array(':pref' => $pref, ':tree_id' => $tree_id);
  }elseif(!empty($pref)){
    $sql .= ' WHERE pref = :pref';
    $data = array(':pref' => $pref);
  }elseif(!empty($tree_id)){
    $sql .= ' WHERE tree_id = :tree_id';
    $data = array(':tree_id' => $tree_id);
  }
  debug('検索用SQL文：'.$sql);

  //一覧を取得
  $stmt = queryPost($dbh, $sql, $data);
  //debug('取得結果：'.print_r($stmt->fetchall(PDO::FETCH_ASSOC), true));
  $result['rowCount'] = $stmt->rowCount(); 

  //並び替えようにSQL追加
  if(!empty($sort)){
    switch($sort){
      case 1;
        $sql .= ' ORDER BY create_date ASC';
        break;
      case 2:
        $sql .= ' ORDER BY create_date DESC';
        break;
      case 3:
        $sql .= ' ORDER BY price DESC';
        break;
      case 4:
        $sql .= ' ORDER BY price ASC';
        break;
    }
  }

   //表示用にSQLにLIMIT・OFFSETを追加
   $sql .= ' LIMIT '.$showSpan;
   if($page > 1){
     $page = ($page - 1) * 10;
     $sql .= ' OFFSET '.$page;
   }

  debug('LIMIT, OFFSET付与後のSQL文：'.$sql);

  $stmt = queryPost($dbh, $sql, $data);
  $result['requestLists'] = $stmt -> fetchAll(PDO::FETCH_ASSOC);

  //debug('取得したデータ一覧：'.print_r($result, true));

  return $result;
  
}
//取引情報があるか確認する関数
function checkTransaction($request_id, $a_user_id){
  debug('-------       応募済みか確認      ----------------------------------------------------');
  debug('応募済みか確認。');
  debug('パラメータのチェック　$request_id = '.$request_id.'　　$a_user_id = '.$a_user_id);
  try{
    $dbh = dbConect();
    $sql = 'SELECT * FROM transactions WHERE request_id = :r_id AND a_user_id = :a_user_id';
    $data = array(':r_id'=>$request_id, ':a_user_id'=>$a_user_id);

    $stmt = queryPost($dbh, $sql, $data);

    if(!$stmt){
      debug('未応募の募集です。');
      debug('後続の処理へ進みます。');
      return 0;
    }else{
      debug('応募済みの募集です。');
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      debug('$result = '.print_r($result, true));
      return $result;
      //header('Location:transaction.php?t_id='.$result['t_id']);
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
//取引情報を取得
function getTransactionInfo($request_id, $user_id){
  //DB接続
  try{
    //変数準備
    $transactionLists = array();//情報を格納用
    $transactionInfo = array();//返却用配列
    $dbh = dbConect();
    $sql = 'SELECT * FROM transactions WHERE request_id = :request_id';
    $data = array(':request_id'=>$request_id);

    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      debug('クエリ成功。');
      $transactionLists = $stmt->fetchall(PDO::FETCH_ASSOC);
      debug('取得した取引情報一覧'.print_r($transactionLists, true));
      foreach($transactionLists as $key => $val){
        if($val['r_user_id'] == $user_id){
          debug('ユーザーは募集者です。');
          $transactionInfo = $val;
          $transactionInfo['RA_flg'] = 1;
        }elseif($val['a_user_id'] == $user_id){
          debug('ユーザーは応募者です。');
          $transactionInfo = $val;
          $transactionInfo['RA_flg'] = 0;
        }else{
          debug('一致する情報がありません。');
          //debug('マイページへ移動します。');
          //header('Location:mypage.php');
        }
      }

      debug('返却する取引情報：'.print_r($transactionInfo, true));
      return $transactionInfo;

    }else{
      debug('クエリ失敗。');
      debug('マイページへ移動します。');
      //header('Location:mypage.php');
    }
  }catch(Exception $e){
    error_log('エラー発生:'.$e->getMessage());
  }
}
//transaction_idによる検索
function getTransactionInfoUseTid($t_id){
  debug('-------       取引情報取得      ----------------------------------------------------');
  try{
    $dbh = dbConect();
    $sql = 'SELECT * FROM transactions WHERE t_id = :t_id';
    $data = array(':t_id'=>$t_id);

    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      debug('t_idでの取引情報取得成功。');
      return $stmt;
    }else{
      debug('t_idでの取引情報取得失敗。');
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
//ユーザー情報を取得
function getPartnerInfo($partner_id){

  debug('-------       取引相手の情報取得      ----------------------------------------------------');
  //DB接続
  try{
    $dbh = dbConect();
    $sql = 'SELECT user_id, user_name, address, profile FROM users WHERE user_id = :user_id';
    $data = array(':user_id'=>$partner_id);

    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      debug('パートナー情報取得成功。');
      $partnerInfo = $stmt->fetchall(PDO::FETCH_ASSOC);
      debug('パートナーの情報：'.print_r($partnerInfo, true));
      return $partnerInfo[0];
    }else{
      debug('パートナー情報取得失敗。');
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

function requestApproval($transaction_id){
  try{
    $dbh = dbConect();
    $sql = 'UPDATE transactions SET r_agree_flg = true, r_agree_date = :r_agree_date WHERE t_id = :transaction_id';
    $data = array(':r_agree_date'=>date('Y:m:d H:i:s'), 'transaction_id'=>$transaction_id);

    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      debug('承認処理成功。');
      return $stmt;
    }else{
      debug('承認処理失敗。');
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

//メッセージを取得
function getMessage($t_id){
  try{
    debug('-------       メッセージ取得      ----------------------------------------------------');

    //DB接続
    $dbh = dbConect();
    $sql = 'SELECT message, send_user, recieve_user, create_date FROM messages WHERE t_id = :t_id ORDER BY create_date DESC';
    $data = array(':t_id'=>$t_id);

    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      debug('メッセージ取得成功');
      $messageList = $stmt->fetchall(PDO::FETCH_ASSOC);
      debug('取得したメッセージ一覧：'.print_r($messageList, true));
      return $messageList;
    }else{
      debug('メッセージ取得失敗');
      return 0;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

//納入状況を取得
function getDelivery($t_id){
  debug('-------       納入状況取得      ----------------------------------------------------');

  try{
    $dbh = dbConect();
    $sql = 'SELECT d_id, delivery_vol, delivery_date, accept_flg, accept_date FROM deliveries WHERE t_id = :t_id ORDER BY delivery_date DESC';
    $data = array(':t_id'=>$t_id);

    $stmt = queryPost($dbh, $sql, $data);

    $result = $stmt->fetchall(PDO::FETCH_ASSOC);
    debug('取得した納入状況：'.print_r($result, true));

    return $result;

  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

//納品を受け入れる関数
function acceptDelivery($d_id){
  debug('-------       納品の承認      ----------------------------------------------------');

  try{
    //DB接続
    $dbh = dbConect();
    $sql = 'UPDATE deliveries SET accept_flg = 1, accept_date = :accept_date WHERE d_id = :d_id';
    $data = array(':accept_date'=>date('Y:m:d H:i:s'), 'd_id'=>$d_id);

    $stmt = queryPost($dbh, $sql, $data);

  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }

}
//マイページの情報を取得する
function getMypageInfo($user_id){
  $result = array();
  //受入待ち一覧を取得
  $dbh = dbConect();
  $sql_accept = 'SELECT d.d_id, d.t_id, d.delivery_vol, d.delivery_date, t.request_id, tree.tree_name, r.d_min, r.d_max, r.length FROM (((deliveries as d LEFT JOIN transactions as t ON d.t_id = t.t_id) LEFT JOIN requests as r ON t.request_id = r.request_id) LEFT JOIN trees as tree ON r.tree_id = tree.tree_id)  WHERE t.r_user_id = :user_id AND d.accept_flg = 0 ORDER BY d.delivery_date DESC';
  $data = array(':user_id'=>$user_id);

  $stmt_accept = queryPost($dbh, $sql_accept, $data);

  $result['accept'] = $stmt_accept->fetchall(PDO::FETCH_ASSOC);
  
  //納品済みの一覧を取得
  $sql_delivery = 'SELECT d.d_id, d.t_id, d.delivery_vol, d.delivery_date, d.accept_flg, t.request_id, tree.tree_name, r.d_min, r.d_max, r.length FROM (((deliveries as d LEFT JOIN transactions as t ON d.t_id = t.t_id) LEFT JOIN requests as r ON t.request_id = r.request_id) LEFT JOIN trees as tree ON r.tree_id = tree.tree_id)  WHERE t.a_user_id = :user_id ORDER BY d.delivery_date DESC LIMIT 5';
  $stmt_delivery = queryPost($dbh, $sql_delivery, $data);

  $result['delivery'] = $stmt_delivery->fetchall(PDO::FETCH_ASSOC);
  
  //応募承認待ち一覧を取得する
  $sql_agree = 'SELECT t.t_id, t.request_id, t.a_apply_date, r.request_vol, r.d_min, r.d_max, r.length, r.price, tree.tree_name FROM ((transactions as t LEFT JOIN requests as r ON t.request_id = r.request_id) LEFT JOIN trees as tree ON r.tree_id = tree.tree_id) WHERE t.r_user_id = :user_id AND t.r_agree_flg = 0 ORDER BY t.a_apply_date DESC';
  $stmt_agree = queryPost($dbh, $sql_agree, $data);

  $result['agree'] = $stmt_agree->fetchall(PDO::FETCH_ASSOC);
  
  //応募済みの一覧を取得する
  $sql_apply = 'SELECT t.t_id, t.request_id, t.r_agree_flg, t.a_apply_date, r.request_vol, r.d_min, r.d_max, r.length, r.price, tree.tree_name FROM ((transactions as t LEFT JOIN requests as r ON t.request_id = r.request_id) LEFT JOIN trees as tree ON r.tree_id = tree.tree_id) WHERE t.a_user_id = :user_id ORDER BY t.a_apply_date DESC LIMIT 5';
  $stmt_apply = queryPost($dbh, $sql_apply, $data);

  $result['apply'] = $stmt_apply->fetchall(PDO::FETCH_ASSOC);

  //募集者として取引中の一覧を取得する
  $sql_transaction_r = 
  'SELECT t.t_id, t.request_id, u.user_name, tree.tree_name, r.d_min, r.d_max, r.length, r.price, r.request_vol 
  FROM (((transactions as t LEFT JOIN requests as r ON t.request_id = r.request_id) LEFT JOIN trees as tree ON r.tree_id = tree.tree_id) LEFT JOIN users as u ON t.a_user_id = u.user_id) 
  WHERE t.r_user_id = :user_id AND t.r_agree_flg = 1';
  $stmt_transaction_r = queryPost($dbh, $sql_transaction_r, $data);

  $result['transaction_r'] = $stmt_transaction_r->fetchall(PDO::FETCH_ASSOC);

  //応募者として取引中の一覧を取得
  $sql_transaction_a = 
  'SELECT t.t_id, t.request_id, u.user_name, tree.tree_name, r.d_min, r.d_max, r.length, r.price, r.request_vol 
  FROM (((transactions as t LEFT JOIN requests as r ON t.request_id = r.request_id) LEFT JOIN trees as tree ON r.tree_id = tree.tree_id) LEFT JOIN users as u ON t.r_user_id = u.user_id) 
  WHERE t.a_user_id = :user_id AND t.r_agree_flg = 1';
  $stmt_transaction_a = queryPost($dbh, $sql_transaction_a, $data);

  $result['transaction_a'] = $stmt_transaction_a->fetchall(PDO::FETCH_ASSOC);

  return $result;

}

//ページネーション
function addPageNation($currentPage, $totalPage, $pageSpan=5, $link=''){
  $minPage = 0;
  $maxPage = 0;
  if(!empty($link)){
    $link = '&';
  }

  if($currentPage == $totalPage && $pageSpan <= $totalPage){
    $minPage = $currentPage - 4;
    $maxPage = $currentPage;
  }elseif($currentPage == $totalPage - 1 && $pageSpan <= $totalPage){
    $minPage = $currentPage - 3;
    $maxPage = $currentPage + 1;
  }elseif($currentPage == 1 && $pageSpan <= $totalPage){
    $minPage = $currentPage;
    $maxPage = $currentPage + 4;
  }elseif($currentPage == 2 && $pageSpan <= $totalPage){
    $minPage = $currentPage - 1;
    $maxPage = $currentPage + 3;
  }elseif($pageSpan >= $totalPage){
    $minPage = 1;
    $maxPage = $totalPage;
  }else{
    $minPage = $currentPage - 2;
    $maxPage = $currentPage + 2;
  }

  echo '<div class="pagenation">';
  echo '<div class="pagenation__inner">';
  echo '<ul>';
  if($currentPage != 1){
    echo '<li><a href="?page=1'.$link.'">&lt</a></li>';
  }
  for($i = $minPage; $i <= $maxPage; $i++){
    if($i == $currentPage){
      echo '<li class="currentPage"><a href="?page='.$i.$link.'">'.$i.'</a></li>';
    }else{
      echo '<li><a href="?page='.$i.$link.'">'.$i.'</a></li>';
    }
  }
  if($currentPage != $totalPage){
    echo '<li><a href="?page='.$totalPage.$link.'">&gt;</a></li>';
  }
  echo '</ul>';
  echo '</div>';
  echo '</div>';
}

//Linkを作成する関数
function appendGetparam($del_key_array=array()){
  if(!empty($_GET)){
    $str = '?';
    debug('$del_key_array = '.print_r($del_key_array, true));
    foreach($_GET as $key => $val){
      debug('$key = '.$key.'  $val = '.$val);
      if(!in_array($key, $del_key_array, true)){
        $str .= $key.'='.$val.'&';
      }
    }
    $str = mb_substr($str, 0, -1,"UTF-8");
    return $str;
  }
}


?>


