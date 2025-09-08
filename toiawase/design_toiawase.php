<?php
// 公式通り
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require(__DIR__ . '/inc_set.php');
require(__DIR__ . '/inc_postmail.php');

/*********************************************************************
	Ver7.0 English Version 区切り#
	25-08-12

	添付資料可能標準英語版お問合せ　SMTPおよびmb_send_mail双方可能
	phpmailer 6.9対応　PHP 7.4～8.4
	
	recapcha対応
	拒否アドレス対応
	レスポンシブル対応
	Notice対応
	メール配信をサーバのfromはnoreply@として、
	迷惑メール対策としてreturn-pathの機能を追加
	SMTP用のアドレスをreturn-pathとして使用する

 **********************************************************************/

/*--------------------------------------------------------------
更新履歴
	SMTP_MAILの指定がないときはFROM_MAILを利用

	セッションチェックのかっこの位置ずれを修正
	
	顧客からの返信のアドレスをオーナーメールではなく、noreply@ドメインとする（迷惑メール対策）
	return-pathはSMTPのアドレスを利用
	（mb_send_mailはnoreply@を使用）

	mb_send_mailのheaderが配列で対応できるのは、8.0以上なので
	PHPのバージョンで8.0未満は文字列連結にしてセットするように修正

	phpmailer 6.9対応
	gmailのSPFがPASSするようにreturn-pathはオーナーメールとサーバのドメインが一致しない
	場合、return-pathは設定しない
	docomoメーラーで文字化けをしないよう添付の有無で処理を分岐させた
	修正画面でjsによるチェックを有効化
	email2を必須にする必要が出た
	date型の値が取得できないバグを修正
	radio,checkboxともにlabelタグで囲うように変更
	ユーザへのメールにフッターを付与
	メールアドレスの抜けを修正
	レスポンシブ対応
	ユーザへは添付は送らない
	{}の配列がPHP7.4より非推奨のため[]に置換
	Notice対応
	メール配信をオーナーからのみとする
	
	迷惑メール対策としてreturn-pathおよびreply-toの機能を追加
	「確認する」ボタンをpタグで囲い、pにid image-btnを付与
	inc_postmail.phpでrequireしていたinc_setfileをこのファイルで呼ぶ
	（複数問い合わせ対応版）
	RECAPCHA対応版
	phpmailerバージョン6.9.1　SMTP対応
	
		if (strpos($reply_to, "@" . $domain)) {	//ドメインと異なるときは設定しない

	
--------------------------------------------------------------*/

/*------------------------------------------
	設置に関連する変更項目
-------------------------------------------*/
//ひな形
define("HINAGATA", "../../Templates/h_contact_eng.html");

//-----確認画面に関する内容-----
$I_bg_color = "#F0F0F0";	//テーブルの項目名の背景色
$V_bg_color = "#FFFFFF";	//テーブルの入力値の背景色
$E_bg_color = "#EDBBD4";	//テーブルの入力値がエラーの背景色
$I_char_color = "#000000";	//項目名の文字色
$V_char_color = "#000000";	//値の文字色
$E_mail = "Email";	//e-mailの項目名をどのように表示するか


/*----------------------------------------
	メイン
----------------------------------------*/
if (FILE_NUM > 0) {	//添付あり
	//フォルダの作成
	if (!file_exists(UPDIR)) {
		mkdir(UPDIR);
	}
}

del_tmp_file();	//不要ファイルがあれば消す
if (empty($_POST) && empty($_GET)) {	//不正アクセス
	$msg = 'There was a problem with the operation.<a href="../contact.php">Return to inquiry</a>';
	disp_contents($msg);
	exit;
} elseif (!empty($_POST)) {
	if (!chk_token()) {	//不正アクセス
		$msg = 'There was a problem with the operation.<a href="../contact.php">Return to inquiry2</a>';
		disp_contents($msg);
		exit;
	}
	if (!empty($_POST["mode"]) && $_POST["mode"] === "end") {
		$array = cnv_html($_POST);
		//最終頁へのクエリ
		$str = "?o=1";
		// recaptchaV3
		if (RECAPTCHAV3) {
			$verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . SECRET_KEY . "&response=" . $_POST["g-recaptcha-response"]);
			$reCAPTCHA = json_decode($verifyResponse);
			if ($reCAPTCHA->success) {
				// echo "認証成功";
			} else {
				$msg = 'An error has occurred.<a href="../contact.php">Return to inquiry</a>';
				disp_contents($msg);
				exit;
			}
		}
		//メール処理
		$nitiji = mail_proc($array);
		for ($i = 1; $i <= FILE_NUM; $i++) {
			if (!empty($array["tmpfile" . $i])) {
				//ファイルの作成された日時
				$ftime = filectime(UPDIR . $array["tmpfile" . $i]) * 3 + 1;
				//ファイル情報
				$f_array = explode(".", $array["tmpfile" . $i]);
				$str .= "&f" . $i . "=";
				$str .= rawurlencode($f_array[0]) . "&t" . $i . "=" . rawurlencode($ftime) .
					"&e" . $i . "=" . rawurlencode($f_array[1]);
			}
		}
		header("Location: " . $str);
		exit;
	} else {	//確認またはエラー画面
		$list = next_disp();
		echo $list;
	}
} elseif (!empty($_GET)) {
	last_proc();	//最終処理
	$list = end_disp();

	echo $list;
}


/*---------------------------------------------------
	確認またはエラー画面
---------------------------------------------------*/
function next_disp()
{

	global $I_bg_color;
	global $V_bg_color;
	global $E_bg_color;
	global $I_char_color;
	global $V_char_color;

	$array = cnv_html($_POST);
	$data_array = array();
	$notice_array = array();
	$list = "";

	//データ用
	$data_array = $array;
	unset($data_array["disp"]);
	//表示用
	$disp_array = $array["disp"];
	//注意書き
	$notice_array = (!empty($array["notice"])) ? $array["notice"] : array();
	//必須項目
	if (!empty($array["need"])) {
		$n_array = explode("#", $array["need"]);
	}

	//添付資料の表示があれば
	for ($i = 1; $i <= FILE_NUM; $i++) {
		if (!empty($disp_array[F_ATTACH . $i])) {
			list($filename, $tmpfile) = file_up($i);
			// extract($f_array);
			if (!empty($filename)) {	//資料のアップロードがあった
				$data_array[F_ATTACH . $i] = $filename;	//添付ファイル情報を追加
				$data_array["tmpfile" . $i] = (!empty($tmpfile)) ? $tmpfile : '';
			} else {
				//修正画面
				if (!empty($data_array["tmpfile" . $i])) {
					$data_array[F_ATTACH . $i] = F_ATTACH . 'があります';
				} elseif (!empty($data_array["filename"]) && strpos($data_array["filename" . $i], 'err')) {	//エラーの解除
					$data_array[F_ATTACH . $i] = F_ATTACH . 'はありません';
					$data_array["tmpfile" . $i] = '';
				} elseif (empty($data_array["filename"])) {
					$data_array[F_ATTACH . $i] = F_ATTACH . 'はありません';
				} else {
					$data_array[F_ATTACH . $i] = $filename;
				}
			}
		}
	}

	$r_array = array();
	foreach ($data_array as $key => $val) {
		$tmp = str_replace("_", " ", $key);
		$r_array[$tmp] = $val;
	}

	//エラーチェック
	$bg_array = err_chk($r_array, $n_array, $disp_array);
	if (
		!empty($bg_array["err_msg"]) ||
		((array_key_exists("mode", $array) !== FALSE) && ($array["mode"] === "edit"))
	) {	//エラーもしくは編集画面
		$list .= form_disp($r_array, $disp_array, $notice_array, $n_array, $bg_array);
	} else {
		$list .= confirm_disp($r_array, $disp_array, $notice_array, $n_array);
	}

	disp_contents($list);
}


/*---------------------------------------------------
	エラーおよび修正画面 必須はチェックを設ける
---------------------------------------------------*/
function form_disp($data_array, $disp_array, $notice_array, $n_array, $bg_array)
{

	global $I_bg_color;
	global $V_bg_color;
	global $E_bg_color;
	global $I_char_color;
	global $V_char_color;

	global $siteKey;

	//hiddenデータ
	$h_data = hidden_data(null, $disp_array, $n_array);
	//recapcha対応
	$script = '';
	$submit = '<input type="submit" value="Confirm">';
	$onsubmit =  'return Validator.submit(this)';
	$recapcha_str = '';
	if (RECAPTCHAV3) {
		$script = '<script src="https://www.google.com/recaptcha/api.js"></script>';
		$recapcha_str = '<input type="hidden" name="g-recaptcha-response" value="' . $_POST["g-recaptcha-response"] . '">';
	}
	if (defined('FILE_NUM') && FILE_NUM > 0) {
		$script .= file_check();
	}

	$list = <<<HTML
<p style="inline:block;backgroud-color:yellow; color:red; font-weight:bolder">
{$bg_array["err_msg"]}</p>
<form method="POST" action="{$_SERVER["PHP_SELF"]}" enctype="multipart/form-data" id="myForm"  onsubmit="{$onsubmit}">
{$h_data}
<dl class="clearfix">
HTML;

	$dl_flg = FALSE;
	foreach ($disp_array as $key => $val) {
		$item = $key;
		$need = (in_array($key, $n_array)) ? "<span class=\"must\">*</span>" : "";
		$notice = (empty($notice_array[$key])) ? "" : $notice_array[$key];

		if ($key === 'email') {
			$item = "E-mail";
		} elseif ($key === 'email2') {
			$item = "Repeated E-mail";
			$need = "<span class=\"must\">*</span>";
		}

		$array = explode("#", $val);
		$data_str = (empty($data_array[$key])) ? "" : $data_array[$key];
		$str = form_making($array, $data_str, $key, $n_array);
		//TAGの処理
		if (strpos($val, 'tag') !== FALSE) {
			$tmp = explode("#", $val);
			if ($dl_flg) {
				$list .= "</dl>";
			} else {
				$dl_flg = TRUE;
			}
			$list .= <<<HTML
<{$tmp[1]}>{$key}</{$tmp[1]}>
<dl class="clearfix">
HTML;
			continue;
		}
		$list .= <<<HTML
<dt>
{$item}{$need}
</dt>
<dd>
{$str}{$notice}
</dd>
HTML;
	}

	$list .= <<<HTML
</dl>
<center>
<p class="formText"> </p>
<label>
<input name="himitsu" type="hidden" id="himitsu" value="{$_SESSION["token"]}" />
{$recapcha_str}
<div id="message"></div>
<p id="image-btn">{$submit}</p>
</label>
</center>
</form>
{$script}
HTML;

	return $list;
}


/*---------------------------------------------------
	formの部品を作成する
---------------------------------------------------*/
function form_making($array, $data, $key, $n_array)
{

	//dispの中の各要素、値、キー

	$str = "";
	$style = "";
	$last = "";

	$tmp = cnv_html($_POST);

	if (in_array($key, $n_array) !== FALSE || $key === 'email2') {
		$last = ' onblur="Validator.check(this)"';
	}
	switch ($array[0]) {
		case "text":
			if ($key === 'zip') {
				$last .= "maxlength=\"8\" onKeyUp=\"AjaxZip2.zip2addr(this,'pref','addr');\"";
			} elseif ($key === 'email') {
				$last = " onblur=\"Validator.check(this, 'equal mail', 'email2')\"";
			} elseif ($key === 'email2') {
				$last = " onblur=\"Validator.check(this, 'equal mail', 'email')\"";
			}
			$array[2] = (!empty($array[2])) ? $array[2] : "";
			$str = "<input type=\"text\" name=\"" . $key . "\" style=\"width:" . $array[1] .
				"%\" value=\"" . $data . "\"" . $style . $last . "> " . $array[2];
			break;
		case "radio":
			for ($i = 1; $i < count($array); $i++) {
				$state = ($data == $array[$i]) ? " checked" : "";
				$last = ($i > 1) ? '' : $last;
				$str .= "<label><input type=\"radio\" name=\"" . $key . "\" value=\"" . $array[$i] . "\"" .
					$state . $last . ">" . $array[$i] . "</label><br />";
			}
			break;
		case "check":	//dataはチェックされたものが配列でくる＆必須の場合は先頭のひとつのみ$lastをつける
			for ($i = 1; $i < count($array); $i++) {
				if (is_array($data)) {
					$last = ($i > 1) ? '' : $last;
					$state = (in_array($array[$i], $data, true)) ? " checked" : "";
					$str .= "<label><input type=\"checkbox\" name=\"" . $key . "[]\" value=\"" . $array[$i] . "\"" .
						$state . $last . ">" . $array[$i] . "</label><br />";
				} else {	//非配列
					$state = ($array[$i] == $data) ? " checked" : "";
					$str .= "<label><input type=\"checkbox\" name=\"" . $key . "\" value=\"" . $array[$i] . "\"" . $state . $last . ">" .
						"<input type=\"hidden\" name=\"" . $key . "\" value=\"" . $array[$i] . "\">" . $array[$i] . "</label>";
					break;
				}
			}
			break;
		case "textarea":
			$data = (isset($data)) ? $data : "";
			$array[1] = (isset($array[1])) ? $array[1] : "";
			$array[2] = (isset($array[2])) ? $array[2] : "";
			$str .= "<textarea name=\"" . $key . "\" rows=\"" . $array[1] . "\" cols=\"" . $array[2] . "\"" .
				$last . ">" . $data . "</textarea> ";
			break;
		case "select":
			$str .= <<<HTML
				<select name="{$key}" id="{$key}"{$last}>
HTML;
			foreach ($array as $key => $val) {
				if ($key > 0) {
					$state = ($val === $data) ? " selected" : "";
					$str .= <<<HTML
						<option value="{$val}"{$state}>{$val}</option>
HTML;
				}
			}
			$str .= "</select>";
			break;
		case "file":		//添付ファイルの処理
			if ($data === 'size_err') {
				$res = 'サイズが大きすぎます。2M以下にしてください。';
			} elseif ($data === 'ext_err') {
				$res = 'ファイルの拡張子は、jpg,jpeg,JPG,JPEGのいずれかにしてください';
			} else {
				if (mb_strpos($data, 'あります')) {
					$res = $data . "。再度アップロードしてください";
				} else {
					$res = $data;
				}
			}
			$str .= <<<HTML
				{$res}<br>
				<input name="{$key}" type="file" id="添付資料" />
HTML;
			break;
		case "tag":		//今回新規　tag入り
			$str .= <<<HTML
				<{$array[1]}>{$key}</{$array[1]}>
HTML;
			break;
		case "date":		//今回新規　日付入り
			$str = "<input type=\"date\" name=\"" . $key . "\" value=\"" . $data . "\"" . $last . ">";
	}

	return $str;
}


/*---------------------------------------------------
	確認画面(エラーなし)
---------------------------------------------------*/
function confirm_disp($d_array, $disp_array, $notice_array, $n_array)
{

	global $I_bg_color;
	global $V_bg_color;
	global $I_char_color;
	global $V_char_color;
	global $Submit;	//submitボタンのID

	global $siteKey;

	foreach ($d_array as $key => $val) {
	  $tmp = str_replace("_", " ", $key);
		$data_array[$tmp] = $val;
	}

	$list = <<<HTML
<form method="POST" action="{$_SERVER["PHP_SELF"]}" enctype="multipart/form-data">
<dl class="clearfix">
HTML;
	$dl_flg = FALSE;
	foreach ($disp_array as $key => $val) {
		$var = '';
		$item = $key;
		$var = '';
		if ($key === 'email2') {
			continue;
		} elseif ($key === 'email') {
			$item = 'メールアドレス';
		} elseif ($key === 'zip') {
			$item = '郵便番号';
		} elseif ($key === 'pref') {
			$item = '都道府県';
		} elseif ($key === 'addr') {
			$item = '住所1';
		}

		$need = (in_array($key, $n_array)) ? "<span class=\"must\">*</span>" : "";

		if (!empty($data_array[$key]) && is_string($data_array[$key])) {
			$var .= nl2br($data_array[$key]);
		} elseif (!empty($data_array[$key]) && is_array($data_array[$key])) {
			foreach ($data_array[$key] as $v) {
				$var .= $v . '<br>';
			}
		}

		//TAGの処理
		if (@strpos($val, 'tag') !== FALSE) {
			$tmp = @explode("#", $val);
			if ($dl_flg) {
				$list .= "</dl>";
			} else {
				$dl_flg = TRUE;
			}
			$list .= <<<HTML
<{$tmp[1]}>{$key}</{$tmp[1]}>
<dl class="clearfix">
HTML;
			continue;
		}
		$list .= <<<HTML
<dt>{$item}{$need}</dt>
<dd>
{$var}
</dd>
HTML;
	}

	//hiddenデータ再度入力するので、データ部分は空で
	$h_data = hidden_data($data_array, $disp_array, $n_array);

	$script = '';
	if (RECAPTCHAV3) {
		$script = '<script src="https://www.google.com/recaptcha/api.js"></script>';
	}

	$list .= <<<HTML
</dl>
<center>
<p class="formText">If the above information is correct, please click "Submit."<br>
If you wish to make corrections, please click "Edit."</p>
<div class="formBtn">
<form method="POST" action="{$_SERVER["PHP_SELF"]}">
<input type="submit" value="Edit" class="submitBtn">
<input type="hidden" name="mode" value="edit">
{$h_data}
</form>

<form method="POST" action="{$_SERVER["PHP_SELF"]}" id="myForm">
<input type="submit" value="Submit" id="{$Submit}" class="submitBtn">
{$h_data}
<input type="hidden" name="mode" value="end">
</form>
</div>
</center>
HTML;

	return $list;
}


/*---------------------------------------------------
	メール送信
---------------------------------------------------*/
function mail_proc($array)
{
	//拒否メールはここではじく
	if (defined('IYAN_MAIL')) {
		$tmp = explode(",", IYAN_MAIL);
		if (in_array($array["email"], $tmp)) {
			//処理せずにお問い合わせへ
			header(("Location: ../"));
			exit;
		}
	}

	global $Mail_owner_title;
	global $Mail_user_title;
	global $Mail_header;
	global $Mail_fooder;

	$br = "\r\n";
	$nitiji = date("Y-m-d H:i:s");

	foreach ($array as $key => $val) {
		$tmp = str_replace("_", " ", $key);
		unset($array[$key]);
		$array[$tmp] = $val;
	}

	//オーナー、利用者兼用の本体部分
	$body = "Inquiry date and time：" . $nitiji . $br;

	foreach ($array["disp"] as $key => $val) {
		if (
			$key !== "mode" && $key !== "tmpfile" && $key !== 'need' && $key !== 'zen'
			&& $key !== 'filename' && $key !== 'tmpfile1' && $key !== 'tmpfile2' && $key !== 'tmpfile3'
			&& $key !== 'han' && $key !== 'email2' && $key !== 'preview_sys' && (mb_strpos($key, 'F_ATTACH') === FALSE)
		) {
			$key2 = $key;
			if ($key === 'email') {
				$key2 = 'E-mail';
			}
			if (!empty($array[$key]) && !is_array($array[$key])) {
				$body .= $key2 . "：" . str_replace("<br>", ",", $array[$key]) . $br;
			} elseif (!empty($array[$key]) && is_array($array[$key])) {
				$body .= $key2 . "：";
				foreach ($array[$key] as $k => $v) {
					$body .= $v . "　";
				}
				$body.= $br;
			} else {
				$body .= $key2 . "：" . $br;
			}
		}
	}

	//オーナーあて
	$attach = array();
	//資料
	for ($i = 1; $i <= FILE_NUM; $i++) {
		if (!empty($array["tmpfile" . $i])) {
			$attach[] = array(UPDIR . $array["tmpfile" . $i], F_ATTACH . $i);
		}
	}
	$list = "お客様から以下の内容でメールがきています。" . $br . $br . $br;
	$list .= $body . $br . "このメールはシステムより自動で送信されています。";
	$list .= $Mail_fooder;

	//オーナーあて、すべてのメールは、サーバと同一のドメインからの配信とする
	//gmailにも届くように処理
	$owner_mail = OWNER_MAIL;
	$bcc = BCC;
	/*
	$domain = str_replace("www.", "", $_SERVER['HTTP_HOST']);
	if (strpos(OWNER_MAIL, "@".$domain)===FALSE) {	//ドメインと異なるときはBccに追加
		$owner_mail = SMTP_MAIL;
		$bcc .= (empty($bcc)) ? OWNER_MAIL : ','.OWNER_MAIL;
	}
*/
	if (!defined('SMTP_MAIL')) {
		define('SMTP_MAIL', FROM_MAIL);
	}

	attach_send_mail($owner_mail, $bcc, CC, $Mail_owner_title, $list, FROM_MAIL, "お客さまより", SMTP_MAIL, FROM_MAIL, $attach);

	//ユーザあては添付なし
	$list = $Mail_header . $body . $br . $Mail_fooder;
	attach_send_mail($array["email"], null, null, $Mail_user_title, $list, FROM_MAIL, FROM_NAME, SMTP_MAIL, FROM_MAIL, array());

	return $nitiji;

}


/*---------------------------------------------------
	メール送信　SMTPかmb_send_mail
---------------------------------------------------*/
function attach_send_mail($to, $bcc, $cc, $subject, $body, $from, $from_name, $return_path, $reply_to, $attach)
{

	global $Method;

	//フラグでSMTPかどうか判断
	if ($Method == 1) {	//別ファイルのSMTPへ
		smtp_mail($to, $bcc, $cc, $subject, $body, $from, $from_name, $return_path, $reply_to, $attach);
	} else {
		$return_path = $from;
		$reply = $from;
		mysend_mail($to, $bcc, $cc, $subject, $body, $from, $from_name, $return_path, $reply_to, $attach);
	}

	//複数のメール送信に備えてタイマーを挿入
	usleep(200000);
}


/*---------------------------------------------------
	SMTP版
---------------------------------------------------*/
function smtp_mail($to, $bcc, $cc, $subject, $body, $from, $from_name, $return_path, $reply_to, $attach)
{
	mb_language("en");
	// mb_language("ja");
	mb_internal_encoding(CODE);

	//ソースを全部読み込ませる
	require_once(__DIR__ . "/mailsend/PHPMailer-master/src/PHPMailer.php");
	require_once(__DIR__ . "/mailsend/PHPMailer-master/src/SMTP.php");
	require_once(__DIR__ . "/mailsend/PHPMailer-master/src/Exception.php");
	require_once(__DIR__ . "/mailsend/PHPMailer-master/language/phpmailer.lang-ja.php");
	/*
	require_once(__DIR__ . "/mailsend/PHPMailer-master/src/POP3.php");
	require_once(__DIR__ . "/mailsend/PHPMailer-master/src/OAuth.php");
*/
	$mailer = new PHPMailer(true); //インスタンス生成
	try {
		// $mailer->SMTPDebug = 2; //2は詳細デバッグ1は簡易デバッグ本番はコメントアウトして
		$mailer->IsSMTP(); //SMTPを作成
		//メールサーバ設定部分
		$mailer->Host = HOST; //mailを使うのでメールの環境に合わせてね
		$mailer->SMTPAuth = true; //SMTP認証を有効にする
		$mailer->Username = USER; // mailのユーザー名
		$mailer->Password = PWD; // mailのパスワード
		$mailer->SMTPSecure = SECURE; //SSLも使えると公式で言ってます
		$mailer->Port = PORT;
		$mailer->CharSet = CHARSET; //文字セットこれでOK

		//送信内容
		//差出人アドレス, 差出人名
		if (!empty($from_name)) {
			$mailer->setFrom($from, mb_encode_mimeheader($from_name));
		} else {
			$mailer->setFrom($from);
		}
		//受信者
		$mailer->addAddress($to);
		//Return-Pathの設定
		$mailer->Sender = $return_path;

		// $mailer->addReplyTo($reply_to);	//迷惑メールにしないため、返信はさせない
		//メール表題（タイトル）
		$mailer->Subject = mb_encode_mimeheader($subject);
		//テキストメッセージ本体
		$mailer->Body = mb_convert_encoding($body, "UTF-8", "AUTO");

		// 宛先設定(cc)
		if (!empty($cc)) {
			$cc_array = explode(",", $cc);
			foreach ($cc_array as $val) {
				$mailer->addCC($val);
			}
		}
		// 宛先設定(bcc)
		if (!empty($bcc)) {
			$bcc_array = explode(",", $bcc);
			foreach ($bcc_array as $val) {
				$mailer->addBCC($val);
			}
		}
		// 添付ファイル
		if (!empty($attach)) {
			foreach ($attach as $key => $value) {
				$mailer->addAttachment($value[0]); // 添付
			}
		}
		$mailer->Send();
		return;
	} catch (Exception $e) {
		echo "Message could not be sent. Mailer Error: {$mailer->ErrorInfo}";
	}
}


/*---------------------------------------------------
	mb_send_mail版　添付なしはmb_send_mailで
---------------------------------------------------*/
function mysend_mail($to, $bcc, $cc, $subject, $body, $from, $from_name, $return_path, $reply_to, $attach)
{

	//	mb_language("uni");	docomoメール文字化け
	if (empty($attach)) {	//添付がなければjaを指定
		mb_language("ja");
	} else {
		mb_language("uni");
	}

	mb_internal_encoding("UTF-8");

	//添付有無の共通部分
	$common["Return-Path"] = $return_path;	//noreply@を利用
	if (!empty($from_name)) {
		$common["From"] = mb_encode_mimeheader($from_name) . "<{$from}>";
	} else {
		$common["From"] = $from;
	}
	$common["Reply-To"] = $reply_to;

	/*　下記は迷惑メール対策として返信させない方向で変更
	//gmailにも届くように処理
	$domain = str_replace("www.", "", $_SERVER['HTTP_HOST']);
	if (strpos($reply_to, "@" . $domain)) {	//ドメインと異なるときは設定しない
		$common["Reply-To"] = $reply_to;
	}
*/
	//送信先設定(bcc)
	if (!empty($bcc)) {
		$common["Bcc"] = $bcc;
	}
	//送信先設定(cc)
	if (!empty($cc)) {
		$common["Cc"] = $cc;
	}

	if (!empty($attach)) {	//添付資料があれば
		$header["Content-Type"] = "multipart/mixed;boundary=\"__BOUNDARY__\"";
		$header += $common;
		//タイトルが一部のメーラーで文字化けする
		$subject = base64_encode($subject);
		$subject = "=?UTF-8?B?{$subject}?=";

		$mailbody = "--__BOUNDARY__\n";
		// テキストメッセージを記述
		$mailbody .= "Content-Type: text/plain; charset=\"UTF-8\";Content-Transfer-Encoding: 8bit\n\n";
		$mailbody .= $body . "\n\n";
		foreach ($attach as $key => $value) {
			$c = $key + 1;
			$tmp = explode(".", $value[0]);
			$mailbody .= "--__BOUNDARY__\n";
			$mailbody .= "Content-Type: application/octet-stream; name=\"data[$c].{$tmp[2]}\"\n";
			$mailbody .= "Content-Disposition: attachment; filename=\"data[$c].{$tmp[2]}\"\n";
			$mailbody .= "Content-Transfer-Encoding: base64\n";
			$mailbody .= "\n";
			$mailbody .= chunk_split(base64_encode(file_get_contents($value[0])));
			$mailbody .= "\n\n";
		}
		$mailbody .= "--__BOUNDARY__--\n";
		//メール送信
		$ret = mail($to, $subject, $mailbody, $header);
		// $ret = mail($to, $subject, $mailbody, $header, "-f " . $return_path);
	} else {
		$header["Content-Type"] = "text/plain;  charset=UTF-8";
		$header["Content-Transfer-Encoding"] = '8bit';
		$header += $common;
		$spf_param = '-f ' . $from; // 送信元メールアドレスを指定

		//PHP8.0以上でないとヘッダは配列で扱えない
		if (phpversion() < 8) {
			$headers = null;
			foreach ($header as $key => $val) {
				$headers .= $key . ':' . $val . "\n";
			}
			$header = $headers;
		}
		mb_send_mail($to, $subject, $body, $header, $spf_param);
	}
}

/*---------------------------------------------------
	画面表示
---------------------------------------------------*/
function disp_contents($list)
{
	$script = null;

	$str = file_get_contents(HINAGATA);
	$str = str_replace("\$list", $list, $str);

	echo $str;
}


/*--------------------------------------------------
	ファイルのアップ　ファイルサイズのチェックなど
--------------------------------------------------*/
function file_up($i)
{

	//ファイルのアップロードが許可されたサーバかどうか
	if (ini_get('file_uploads') < 1) {
		exit('ファイルのアップロードが許可されていないサーバです');
	}
	global $Ext_array;

	$filename = "";
	$tmpfile = '';

	if (!empty($_FILES[F_ATTACH . $i]["tmp_name"]) && is_uploaded_file($_FILES[F_ATTACH . $i]["tmp_name"])) {
		//拡張子のチェック
		$f_array = pathinfo($_FILES[F_ATTACH . $i]["name"]);
		if ((in_array($f_array["extension"], $Ext_array)) === FALSE) {
			$filename = "許可されてない拡張子です";
		} else {
			//保存用　重複がないように作成
			do {
				$tmpfile = rand_str() . "." . $f_array["extension"];
			} while (file_exists($tmpfile));
			copy($_FILES[F_ATTACH . $i]["tmp_name"], UPDIR . $tmpfile);
			$filename = F_ATTACH . $i;
			if (defined('FILE_NAME')) {
				$filename .= '(' . FILE_NAME . $i . ')があります';
			}
		}
	}

	return [$filename, $tmpfile];
}


/*---------------------------------------------------
	ランダムな文字列を生成する。
	@param int $nLengthRequired 必要な文字列長。省略すると 8 文字
	@return String ランダムな文字列
---------------------------------------------------*/
function rand_str($nLengthRequired = 8)
{

	$sCharList = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-";
	mt_srand();
	$sRes = "";
	for ($i = 0; $i < $nLengthRequired; $i++) {
		$sRes .= $sCharList[mt_rand(0, strlen($sCharList) - 1)];
	}

	return $sRes;
}

/*---------------------------------------------------
	必須項目のエラーチェック
	戻り値：エラーのクラスの入った配列およびメッセージ
---------------------------------------------------*/
function err_chk($r_array, $n_array, $v_array)
{

	global $E_bg_color;
	global $V_bg_color;

	$err_msg = FALSE;
	$bg_array = array();

	//初期化
	foreach ($v_array as $key => $val) {
		$key = str_replace("_", " ", $key);
		$bg_array[$key] = $V_bg_color;
	}
	foreach ($n_array as $key => $val) {
		if (@array_key_exists($val, $r_array) === FALSE || empty($r_array[$val])) {
			//必須のチェック　ラジオボタン対応
			$bg_array[$val] = $E_bg_color;
			$err_msg = 1;
		}
		if ($val == "email2") {	//確認用メールアドレスがあれば
			if ($r_array["email"] !== $r_array["email2"]) {
				$bg_array["email2"] = $E_bg_color;
				$err_msg = 2;
			}
		}
	}

	//資料サイズと拡張子のチェック
	for ($i = 1; $i <= FILE_NUM; $i++) {
		if (!empty($r_array[F_ATTACH . $i]) && strpos($r_array[F_ATTACH . $i], 'err') !== FALSE) {
			$bg_array[F_ATTACH . $i] = $E_bg_color;
			$err_msg = 3;
		}
	}

	$bg_array["err_msg"] = (!empty($err_msg)) ? "There is an error in the input." : "";

	return $bg_array;
}


/*-----------------------------------------------
	セッションの解除＆アップされたファイルの削除など
------------------------------------------------*/
function last_proc()
{

	//セッションの破棄
	myses_start();
	$_SESSION = array();
	session_destroy();

	$array = cnv_html($_GET);
	for ($i = 1; $i <= FILE_NUM; $i++) {
		if (!empty($array["f" . $i]) && !empty($array["e" . $i])) {
			$file = UPDIR . $array["f" . $i] . "." . $array["e" . $i];
			$ftime = ($array["t" . $i] - 1) / 3;
			if (file_exists($file) && filectime($file) == ($ftime)) {
				@chmod($file, 0666);
				@unlink($file);
			}
		}
	}
}


/*---------------------------------------------------
	最終画面
---------------------------------------------------*/
function end_disp()
{

	$list = <<<HTML
<center>
Thank you for contacting us.<br>
Please check the automated confirmation email you will receive from the system.<br>
We will reply to you shortly, so please wait for a while.</p>
<form>
		<p id="image-btn" class="topBtn">
<input type="button" value="Return to TOP page" onclick="document.location = '../';" />
		</p>
</form>
</center>
HTML;

	disp_contents($list);
}


/*---------------------------------------------------
	メール用にhiddenでデータを飛ばす
---------------------------------------------------*/
function hidden_data($data_array, $disp_array, $n_array)
{

	global $Area;
	$str = '';

	$array = cnv_html($_POST);

	if (is_array($data_array)) {
		foreach ($data_array as $key => $val) {
			if ($key === 'mode') {
				continue;
			}
			if (is_string($val)) {
				$str .= <<<HTML
<input type="hidden" name="{$key}" value="{$val}">
HTML;
			} elseif (is_array($val)) {	//checkbox
				foreach ($val as $key2 => $val2) {
					$str .= <<<"HTML"
<input type="hidden" name="{$key}[]" value="{$val2}">
HTML;
				}
			}
		}
	}




	foreach ($disp_array as $key => $val) {
		$str .= <<<HTML
			<input type="hidden" name="disp[$key]" value="{$val}">
HTML;
	}

	if (!empty($n_array)) {
		$tmp = @implode('#', $n_array);
		$str .= <<<HTML
		<input type="hidden" name="need" value="{$tmp}">
HTML;
	}
	/*
	//セッションチェック用
	$str .= <<<HTML
<input type="hidden" name="himitsu" value="{$_SESSION["token"]}">
HTML;
*/
	if (!empty($array["han"])) {
		$str .= <<<HTML
<input type="hidden" name="han" value="{$array["han"]}">
HTML;
	}
	if (!empty($array["zen"])) {
		$str .= <<<HTML
		<input type="hidden" name="zen" value="{$array["zen"]}">
HTML;
	}

	return $str;
}


/*-------------------------------------------
	エンコーディングチェック
-------------------------------------------*/
function html_escape($array)
{

	foreach ((array)$array as $key => $val) {
		if (is_array($val)) {
			return array_map('html_escape', $val);
		}
		$array[$key] = htmlspecialchars($val, ENT_QUOTES, "UTF-8");
	}

	return $array;
}

/*-------------------------------------------
	POST GETをhtmlで表示(サニタイズ)
-------------------------------------------*/
function cnv_html($array)
{

	return filter_input_args_array($array);
}


/*------------------------------------------
	GETやPOSTの値をフィルター　2次元が基本
------------------------------------------*/
function filter_input_args_array($array)
{

	$args = array();

	try {
		//例外を投げる
		if (!$_POST && !$_GET) {
			//throw new Exception();
		}
	} catch (Exception $e) {
		echo "時間が過ぎました。もう一度始めからやり直してください。";
		return;
	}

	$input = ($_SERVER['REQUEST_METHOD'] === 'POST') ? INPUT_POST : INPUT_GET;

	$option = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
	foreach ((array)$array as $key => $val) {
		if (is_array($val)) {
			foreach ($val as $key2 => $val2) {
				$args[$key]['filter'] = $option;
				$args[$key]['flags']  = FILTER_REQUIRE_ARRAY | FILTER_FLAG_STRIP_LOW;
			}
		} else {
			//$args[$key] = $option;
			$args[$key] = $option . "," . FILTER_FLAG_STRIP_LOW;
		}
	}

	$res = filter_input_array($input, $args);

	return $res;
}


/*-------------------------------------------
	エンコーディングチェック
-------------------------------------------*/
function check_encoding($array)
{

	if (is_array($array)) {
		return array_map('check_encoding', $array);
	}
	if (!mb_check_encoding($array, CODE)) {
		die('適切な値ではありません');
	}
}


/*---------------------------------------------------
	データのみの配列の作成 checkboxはつなげる
---------------------------------------------------*/
function data_search($type)
{

	if ($type === 'html') {
		$array = cnv_html($_POST);
	} else {
		$array = $_POST;
	}
	foreach ($array as $key => $val) {
		if ($key === 'disp') {
			continue;
		} elseif ($key === 'need') {
			continue;
		} elseif ($key === 'han') {
			continue;
		} else {
			if (is_array($val)) {
				$tmp[$key] = implode(",", $array[$key]);
			} else {
				$tmp[$key] = $val;
			}
		}
	}

	return $tmp;
}



/*-------------------------------------------
	送信されたトークンとセッションの値を比較
-------------------------------------------*/
function chk_token()
{

	myses_start();

	$array = $_POST;
	if ((!empty($array["himitsu"]) && !empty($_SESSION["token"])) && ($array["himitsu"] === $_SESSION["token"])) {
		return TRUE;
	}

	return FALSE;
}


/*-------------------------------------------------------------
	添付ファイルを自動で消去
-------------------------------------------------------------*/
function del_tmp_file()
{

	$dir_name = dir(UPDIR);

	$now = time();
	while ($file_name = $dir_name->read()) {
		if (!($file_name == "." || $file_name == "..")) {
			if (($now - filemtime(UPDIR . $file_name)) > 30 * 60) {
				unlink(UPDIR . $file_name);
			}
		}
	}

	$dir_name->close();
}


/*---------------------------------------------------
	半角、全角に変換
---------------------------------------------------*/
function convert_char($array, $han, $zen)
{

	$han_array = explode("#", $han);
	$zen_array = explode("#", $zen);

	if (is_array($array) === FALSE) {
		return;
	}
	foreach ($array as $key => $val) {
		//半角
		if (array_search($key, $han_array) !== FALSE) {
			$array[$key] = @mb_convert_kana($val, 'as', 'UTF-8');
		}	//全角
		else {
			$array[$key] = @mb_convert_kana($val, 'K', 'UTF-8');
		}
	}

	return $array;
}
