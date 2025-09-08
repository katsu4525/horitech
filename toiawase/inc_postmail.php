<?php
//ヘッダ出力
header("X-XSS-Protection: 1; mode=block");
header('X-Frame-Options: SAMEORIGIN');	// クリックジャッキング対策

//PHP設定
ini_set('session.name',"toiawase");
ini_set('session.cookie_httponly',1);
ini_set('session.hash_function',1);
//session.nameはuse_strict_modeの前でなければ、セッションが切れる
ini_set('session.use_strict_mode',1);	//5.5以上

date_default_timezone_set('Asia/Tokyo');

require_once "inc_set.php";

/*-------------------------------------------------------
	tokenの作成
-------------------------------------------------------*/
function make_token() {

  //トークンの生成
  $str = get_csrf_token();
  myses_start();

  $_SESSION["token"] = $str;

  return $str;

}


/*------------------------------------------------------
	セッション　スタート
------------------------------------------------------*/
function myses_start() {

	if (!isset($_SESSION)) {
	  session_start();
	}
  
 }
  
/*-------------------------------------------------------
	トークンの生成　32桁以上が安全といわれる
-------------------------------------------------------*/
function get_csrf_token() {

  $TOKEN_LENGTH = 16;//16*2=32バイト
  $bytes = openssl_random_pseudo_bytes($TOKEN_LENGTH);

  return bin2hex($bytes);

}


/*-------------------------------------------------------
  recapcha用スクリプト
-------------------------------------------------------*/
function recapcha_info($value) {

  if (!RECAPTCHAV3 && (!defined('FILE_NUM') || FILE_NUM==0) ) {
    $submit=<<<"HTML"
    <input type="submit" value="{$value}">
HTML;
    return [$submit,null];
  }

  $script="";
  $script=<<<"HTML"
  <script src="https://www.google.com/recaptcha/api.js"></script>
  <script>
  <!-- recapcha用 -->
  function handleSubmit(form) {
    // Validator.submit(this)がtrueの場合のみフォームを送信
    if (Validator.submit(form)) {
      grecaptcha.execute();
      return false; // reCAPTCHAの実行を待つためにfalseを返す
    }
    return false;
  }

  function onSubmit(token) {
    if (Validator.submit(document.getElementById("myForm"))) {
      document.getElementById("myForm").submit();
    } else {
      // 入力エラーが発生した場合、reCAPTCHAトークンをリセット
      grecaptcha.reset();
    }
  }

  // 確実にValidatorオブジェクトがロードされるように確認
  document.addEventListener("DOMContentLoaded", function() {
    if (typeof Validator !== 'undefined') {
      document.getElementById("myForm").onsubmit = function() {
        return handleSubmit(this);
      };
    }
  });
  </script>
HTML;

  $site_key = RECAPTCHA_SITE_KEY;
  $submit=<<<"HTML"
    <input type="submit" class="g-recaptcha" value="{$value}"
      data-sitekey="{$site_key}" 
      data-callback='onSubmit' 
      data-action='submit'>
HTML;

  return [$submit,$script];

}

/*-------------------------------------------------------
  添付ファイルチェック用
-------------------------------------------------------*/
function file_check() {

  //添付ファイルがある場合
  if (defined('FILE_NUM') && FILE_NUM > 0) {
    global $Ext_array;
    $size = TOTAL_SIZE;
    $json_ext = json_encode($Ext_array);

    $script=<<<"HTML"
<script>
  const maxFileSize = {$size} * 1024 * 1024; // 最大ファイルサイズを指定（例：10MB）
  const allowedExtensions = {$json_ext}; // 許可する拡張子
  const fileInputs = document.querySelectorAll('input[type="file"]');
  const message = document.getElementById('message');
  const submitButton = document.querySelector('#image-btn input[type="submit"]');

  fileInputs.forEach(input => {
    input.addEventListener('change', function () {
      checkFiles();
    });
  });

  function checkFiles() {
    let totalSize = 0;
    let validExtensions = true;

    fileInputs.forEach(input => {
      if (input.files.length > 0) {
        const file = input.files[0];
        const fileSize = file.size;
        const fileName = file.name;
        const fileExtension = fileName.split('.').pop().toLowerCase();

        // 拡張子を確認
        if (!allowedExtensions.includes(fileExtension)) {
            validExtensions = false;
        }

        totalSize += fileSize;
      }
    });

    if (!validExtensions) {
      message.innerHTML = '<p style="color:red; background-color:#ffffe0; padding:20px">許可されていないファイル拡張子があります！</p>';
      fileInputs.forEach(input => input.value = ''); // ファイル選択をリセット
      submitButton.disabled = true; // submitボタンを無効化
    } else if (totalSize > maxFileSize) {
      message.innerHTML = '<p style="color:red; background-color:#ffffe0; padding:20px">総ファイルサイズが指定のサイズを超えています！</p>';
      fileInputs.forEach(input => input.value = ''); // ファイル選択をリセット
      submitButton.disabled = true; // submitボタンを無効化
    } else {
      message.innerHTML = '<p style="color:red; background-color:#ffffe0; padding:20px">ファイルは正常にチェックされました。</p>';
      submitButton.disabled = false; // submitボタンを有効化
    }

  }
</script>
HTML;
  }

  return $script;
  
}
