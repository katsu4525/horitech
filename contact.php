<?php
require "toiawase/inc_postmail.php";
$str = make_token();
?>

<!doctype html>
<html><!-- InstanceBegin template="/Templates/temp.dwt" codeOutsideHTMLIsLocked="false" -->
<head prefix="og: https://ogp.me/ns#">
<meta charset="utf-8">
<!-- InstanceBeginEditable name="doctitle" -->
<title>お問い合わせ｜人と社会をつなぐ通信インフラの総合サポートと照明から消防設備まで電気工事をトータル対応 【株式会社 HoriTech】</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="descdata" -->
<meta name="description" content="お問い合わせページです" />
<!-- InstanceEndEditable -->
<meta name="viewport" content="width=device-width,user-scalable=no,maximum-scale=1" />
<meta name="format-detection" content="telephone=no">
<!-- InstanceBeginEditable name="head" -->
<meta property="og:url" content="https://HoriTech.com/contact.php" />
<meta property="og:type" content="article" />
<meta property="og:title" content="お問い合わせ｜人と社会をつなぐ通信インフラの総合サポートと照明から消防設備まで電気工事をトータル対応 【株式会社 HoriTech】" />
<meta property="og:description" content="お問い合わせページです" />
<meta property="og:site_name" content="株式会社 HoriTech" />
<meta property="og:image" content="https://HoriTech.com/images/ogp.jpg" />
<link rel="stylesheet" href="./css/contact.css">
<script type="text/javascript" src="./form/util.js"></script>
<script type="text/javascript" src="./form/validator.js"></script>
<script type="text/javascript" src="./form/yubinbango.js" charset="UTF-8"></script>
<link rel="stylesheet" href="./form/default.css" type="text/css">
<!-- InstanceEndEditable -->
<!--CSS-->
<link href="css/reset.css" rel="stylesheet" type="text/css">
<link href="css/base.css" rel="stylesheet" type="text/css">
<link href="css/header.css" rel="stylesheet" type="text/css">
<link href="css/footer.css" rel="stylesheet" type="text/css">
<link href="css/nav.css" rel="stylesheet" type="text/css">
<link href="css/style.css" rel="stylesheet" type="text/css">
<!--googlefont-->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&family=Zen+Kaku+Gothic+New&display=swap" rel="stylesheet">
</head>

<body>
<!--ヘッドナビ-->
<header id="header">
  <div id="h-box">
    <h1><a href="../"><img src="images/h-logo.svg" width="271" height="53" alt="株式会社 HoriTech"/></a></h1>
    <div class="global-nav">
      <div class="global-nav-button">
        <div class="global-nav-button-icon"></div>
      </div>
      <div class="global-nav-item-list"> 
      <!-- SP用ロゴ -->
        <div class="global-nav-logo spOnly">
          <a href="../">
            <img src="images/h-logo.svg" alt="株式会社 HoriTech">
          </a>
        </div>
        <div class="global-nav-item"><a href="company.html">企業情報</a></div>
        <div class="global-nav-item"><a href="communication-service.html">通信工事部</a></div>
        <div class="global-nav-item"><a href="electrical-service.html">電気工事部</a></div>
        <div class="global-nav-item"><a href="works.html">実績一覧</a></div>
        <div class="global-nav-item"><a href="qualification.html">保有資格</a></div>
        <div class="global-nav-item"><a href="contact.php">お問い合わせ</a></div>
      </div>
    </div>
    <!-- .global-nav --> 
  </div>
</header>
<main> <!-- InstanceBeginEditable name="mainArea" -->
  <section class="title-area">
    <h2>お問い合わせ</h2>
  </section>
  <div class="pan">
    <a href="./"><img src="images/pan-home.svg" alt="HOME"/></a>
    <p class="current-page">お問い合わせ</p>
  </div>
  <section id="contact">
<!--
    <div class="inner">
      <p>株式会社 HoriTechをご利用いただき、誠にありがとうございます。<br>
        お問い合わせの際は、下記に必要事項をご記入の上、「入力内容を確認する」ボタンをクリックしてください。<br>
        個人情報の重要性を認識しその保護の徹底を図るため、各種法令を順守しこれに従うことを宣言します。<br>
      なお当サイトは、個人情報のみならず、法人その他団体のお客様に関する情報についても個人情報と同様に適正に取り扱ってまいります。</p>
      <p class="tel"><a href="tel:"></a></p>
        <p class="s-font">受付時間 / 10：00-18：00（土日、祝お休み）</p>
    </div>
-->
		<section id="form">
			<form action="toiawase/design_toiawase.php" method="post" onsubmit="return Validator.submit(this)">
				<!-- 添付がある場合は下記のタグ
			<form action="toiawase/design_toiawase.php" method="post" onsubmit="return Validator.submit(this)" enctype="multipart/form-data"> -->

				<input name="himitsu" type="hidden" id="himitsu" value="<?= $str ?>" />

				<!-- 必須項目 -->
				<input name="need" type="hidden" id="need" value="お問い合わせ内容 お名前 email email2 電話番号 メッセージ本文" />

				<!-- 修正画面を表示用設置 -->
        <input type="hidden" name="disp[お問い合わせ内容]" value="select サービスについて 資料請求 パソコン教室 その他">
				<input type="hidden" name="disp[お名前]" value="text 50">
				<input type="hidden" name="disp[貴社名]" value="text 50">
				<input type="hidden" name="disp[email]" value="text 60">
				<input type="hidden" name="disp[email2]" value="text 60">
				<input type="hidden" name="disp[電話番号]" value="text 60">
				<input type="hidden" name="disp[メッセージ本文]" value="textarea 5 55">
				<input type="hidden" name="disp[個人情報について]" value="check 同意する">
				<!-- 入力部分 -->
				<dl class="clearfix">
          <dt>お問い合わせ内容<span class="must">必須</span></dt>
          <dd class="selectbox">
            <select name="内容" id="内容">
              <option value="サービスについて" onblur="Validator.check(this)">サービスについて</option>
              <option value="資料請求">資料請求</option>
              <option value="パソコン教室">パソコン教室</option>
              <option value="その他">その他</option>
            </select>
          </dd>
					<dt>お名前<span class="must">必須</span></dt>
					<dd class="boxW100">
						<input name="お名前" type="text" id="お名前" onblur="Validator.check(this)" />
					</dd>
					<dt>貴社名</dt>
					<dd class="boxW100">
						<input name="貴社名" type="text" id="貴社名" />
					</dd>
<!--
					 <dt>住所</dt>
				<dd class="boxW100">
					<div class="h-adr">
						<span class="p-country-name" style="display:none;">Japan</span>
						<div class="boxW40">〒<input type="tel" name="郵便番号" class="p-postal-code" maxlength="8" placeholder="例：5420081"></div>
						<span>※ハイフン(－)なしでご入力ください</span><br>
						<input name="ご住所" type="text" class="p-region p-locality p-street-address p-extended-address" value="" />
						<br>
						<span>※英数字は半角でご入力ください</span>
					</div> 
					</dd>
-->
					<dt>メールアドレス<span class="must">必須</span></dt>
					<dd class="boxW100">
						<input name="email" type="email" id="email" onblur="Validator.check(this, 'equal mail', 'email2')" />
						<br>
						※半角英数字でご入力ください<br>
						<input name="email2" type="email" id="email2" placeholder="確認のためもう一度ご入力ください" onblur="Validator.check(this, 'equal mail', 'email')" />
					</dd>
					<dt>電話番号<span class="must">必須</span></dt>
					<dd class="boxW50">
						<input name="電話番号" type="tel" id="電話番号" onblur="Validator.check(this)" />
					</dd>
					<dt>メッセージ本文<span class="must">必須</span></dt>
					<dd class="boxW100">
						<textarea name="メッセージ本文" rows="6" id="メッセージ本文" onblur="Validator.check(this)"></textarea>
					</dd>
					<dt>個人情報について<span class="must">必須</span></dt>
					<dd>
						<label>
							<input name="個人情報について" type="checkbox" id="個人情報について" value="同意する" onblur="Validator.check(this)">
							<input name="個人情報について" type="hidden" id="個人情報について" value="同意する">同意する　《<a href="privacy.html">個人情報に関する表記</a>》
						</label>
					</dd>
				</dl>
				<p id="image-btn">
					<input type="submit" value="入力内容を確認する" />
				</p>
			</form>
		</section>
  </section>
<!-- InstanceEndEditable --> </main>
<!--フッター-->
<footer>
  <section class="f-contact">
    <div class="inner">
      <div class="logo-nav">
        <figure class="f-log"><img src="images/f-logo.svg" width="271" height="53" alt="株式会社 HoriTech"/></figure>
        <ul>
          <li class="f-nav"><a href="company.html"><span class="circle-icon">
          <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
            <path d="M3 2L7 5L3 8" stroke="#333" stroke-width="1" fill="none"/>
          </svg>
          </span>企業情報</a></li>
          <li class="f-nav"><a href="communication-service.html"><span class="circle-icon">
          <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
            <path d="M3 2L7 5L3 8" stroke="#333" stroke-width="1" fill="none"/>
          </svg>
          </span>通信工事部</a></li>
          <li class="f-nav"><a href="electrical-service.html"><span class="circle-icon">
          <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
            <path d="M3 2L7 5L3 8" stroke="#333" stroke-width="1" fill="none"/>
          </svg>
          </span>電気工事部</a></li>
          <li class="f-nav"><a href="works.html"><span class="circle-icon">
          <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
            <path d="M3 2L7 5L3 8" stroke="#333" stroke-width="1" fill="none"/>
          </svg>
          </span>実績一覧</a></li>
          <li class="f-nav"><a href="qualification.html"><span class="circle-icon">
          <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
            <path d="M3 2L7 5L3 8" stroke="#333" stroke-width="1" fill="none"/>
          </svg>
          </span>保有資格</a></li>
          <li class="f-pv"><a href="privacy.html">プライバシーポリシー</a></li>
        </ul>
      </div>
      <div class="tel-form">
        <p class="f-form"><a href="contact.php">お問い合わせ</a></p>
        <figure class="f-tel"><a href="tel:0773-22-1120"><img src="images/tel1.svg" alt="通信工事部電話番号"/></a></figure>
        <figure class="f-tel"><a href="tel:0773-25-3748"><img src="images/tel2.svg" alt="電気工事部電話番号"/></a></figure>
      </div>
    </div>
    <div class="bnr-area">
      <p>グループ会社</p>
      <figure><a href="" target="_blank"><img src="images/group-1.svg" width="560" height="160" alt="HRホールディングス"/></a></figure>
      <figure><a href="http://www.horinet.co.jp/" target="_blank"><img src="images/group-2.svg" width="560" height="160" alt="株式会社堀通信"/></a></figure>
    </div>
  </section>
  <p class="copy-r text-center">&copy; 2017 Hori Communication Corp All Rights Reserved.</p>
</footer>
<!-- jQuery --> 
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script> 
<!-- js --> 
<script>
  jQuery(function($){
    // ハンバーガーボタン
    $(document).on('click', '.global-nav-button', function(e){
      const $t = $(e.currentTarget);
      $t.toggleClass('open');
      $t.closest('.global-nav').toggleClass('open');
    });

    // サブメニュー開閉
    $(document).on('click', '.global-nav-item > a', function(e){
      const $t = $(e.currentTarget);
      const $next = $t.next('.global-nav-sub-item-list');
      if ($next.length > 0) {
        e.preventDefault();
        $t.toggleClass('open');
        $next.toggleClass('open');
      }
    });
  });
</script>
<!-- InstanceBeginEditable name="foot" -->
<!-- InstanceEndEditable -->
</body>
<!-- InstanceEnd --></html>
