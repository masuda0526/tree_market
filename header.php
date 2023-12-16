<body>
  <header id="header">
    <div class="logo">
      <h1><a href="index.php">WEB<br>木材取引所</h1></a>
    </div>
    <nav class="nav">
      <ul class="nav__ul">
        <li><a href="<?php echo(isset($_SESSION['user_id']))?'logout.php':'login.php';?>"><?php echo(isset($_SESSION['user_id']))?'ログアウト':'ログイン';?></a></li>
        <li><a href="mypage.php">マイページ</a></li>
        <li><a href="regist.php">木材募集</a></li>
      </ul>
    </nav>
  </header>