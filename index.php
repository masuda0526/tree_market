<?php
//共通関数・変数読み込み
require('function.php');

debug('----------------------------------------------------------------------------------');
debug('-------       募集一覧画面      ----------------------------------------------------');
debug('----------------------------------------------------------------------------------');

//変数の準備
$page = (!empty($_GET['page']))?$_GET['page']:1;
$showSpan = 10;
$treeList = getTreeName();

//GET送信の確認
debug('GET送信内容：'.print_r($_GET, true));
$pref = (!empty($_GET['pref'])) ? $_GET['pref'] : '';
$tree_id = (!empty($_GET['tree_id'])) ? $_GET['tree_id'] : '';
$sort = (!empty($_GET['sort'])) ? $_GET['sort']: '';

//募集情報の取得
$result = getRequests($page, $showSpan, $pref, $tree_id, $sort);
$totalRequests = $result['rowCount'];
$totalPages = ceil($totalRequests/$showSpan);
debug('総ページ数：'.$totalPages.'ページ');

?>
<?php
$title = 'WEB木材取引場';
require('head.php');
require('header.php');
?>
<div class="two-colum">
  <div class="search">
    条件を指定して検索
    <div class="search__inner">
      <form action="" method="get" class="search__form">
        <label for="">
          地域
          <select name="pref">
            <option value="" selected>都道府県</option>
            <option value="北海道">北海道</option>
            <option value="青森県">青森県</option>
            <option value="岩手県">岩手県</option>
            <option value="宮城県">宮城県</option>
            <option value="秋田県">秋田県</option>
            <option value="山形県">山形県</option>
            <option value="福島県">福島県</option>
            <option value="茨城県">茨城県</option>
            <option value="栃木県">栃木県</option>
            <option value="群馬県">群馬県</option>
            <option value="埼玉県">埼玉県</option>
            <option value="千葉県">千葉県</option>
            <option value="東京都">東京都</option>
            <option value="神奈川県">神奈川県</option>
            <option value="新潟県">新潟県</option>
            <option value="富山県">富山県</option>
            <option value="石川県">石川県</option>
            <option value="福井県">福井県</option>
            <option value="山梨県">山梨県</option>
            <option value="長野県">長野県</option>
            <option value="岐阜県">岐阜県</option>
            <option value="静岡県">静岡県</option>
            <option value="愛知県">愛知県</option>
            <option value="三重県">三重県</option>
            <option value="滋賀県">滋賀県</option>
            <option value="京都府">京都府</option>
            <option value="大阪府">大阪府</option>
            <option value="兵庫県">兵庫県</option>
            <option value="奈良県">奈良県</option>
            <option value="和歌山県">和歌山県</option>
            <option value="鳥取県">鳥取県</option>
            <option value="島根県">島根県</option>
            <option value="岡山県">岡山県</option>
            <option value="広島県">広島県</option>
            <option value="山口県">山口県</option>
            <option value="徳島県">徳島県</option>
            <option value="香川県">香川県</option>
            <option value="愛媛県">愛媛県</option>
            <option value="高知県">高知県</option>
            <option value="福岡県">福岡県</option>
            <option value="佐賀県">佐賀県</option>
            <option value="長崎県">長崎県</option>
            <option value="熊本県">熊本県</option>
            <option value="大分県">大分県</option>
            <option value="宮崎県">宮崎県</option>
            <option value="鹿児島県">鹿児島県</option>
            <option value="沖縄県">沖縄県</option>
          </select>
        </label>
        <label for="">
          樹種
          <select name="tree_id" id="">
            <option value="0">選択してください</option>
            <?php foreach($treeList as $key => $val){?>
              <option value="<?php echo $val['tree_id'];?>"><?php echo $val['tree_name'];?></option>
            <?php } ?>
          </select>
        </label>
        <label for="">
          並び順
          <select name="sort" id="">
            <option value="1">募集の古い順</option>
            <option value="2">募集の新しい順</option>
            <option value="3">価格の高い順</option>
            <option value="4">価格の安い順</option>
          </select>
        </label>
        <div class="btn-box" style="margin-top: 20px;">
         <input type="submit" value="検索する" class="btn" style="background-color: rgb(200, 230, 255); width: 60%;">
        </div>
      </form>
    </div>
    <?php 
    if($page * 10 <= $result['rowCount']){
      echo '<p>'.(($page-1)*10+1).'〜'.($page*10).'/'.$result['rowCount'].'件中</p>';
    }else{
      echo '<p>'.(($page-1)*10+1).'〜'.$result['rowCount'].'/'.$result['rowCount'].'件中</p>';
    }
    ?>
  </div>
  <div class="request-list">
    <div class="request-list__inner">
      <?php foreach($result['requestLists'] as $key => $val){?>
        <a href="request_detail.php<?php echo (!empty(appendGetparam()))?appendGetparam().'&r_id='.$val['request_id']:'?r_id='.$val['request_id'];?>">
        <div class="request-list__card <?php echo ($key % 2 == 0) ?' left-card' : ' right-card';?>">
          <div class="request-list__cardinner">
            <table>
              <tr>
                <td class="head">地域</td>
                <td class="content"><?php sanitaiz($val['pref'].'　'.$val['city']);?></td>
              </tr>
              <tr>
                <td class="head">樹種</td>
                <td class="content"><?php sanitaiz($val['tree_name']);?></td>
              </tr>
              <tr>
                <td class="head">直径</td>
                <td class="content"><?php sanitaiz($val['d_min'].'cm　〜　'.$val['d_max'].'cm');?></td>
              </tr>
              <tr>
                <td class="head">長さ</td>
                <td class="content"><?php sanitaiz($val['length'].'m');?></td>
              </tr>
              <tr>
                <td class="head">単価</td>
                <td class="content"><?php sanitaiz($val['price'].'円/㎥');?></td>
              </tr>
            </table>
          </div>
        </div>
      </a>
      <?php } ?>
    </div>
  </div>
</div>
<?php
addPageNation($page, $totalPages,5);

require('footer.php');

?>