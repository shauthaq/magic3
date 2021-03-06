<?php
/**
 * renameプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: rename.inc.php 1601 2009-03-21 05:51:06Z fishbone $
 * @link       http://www.magic3.org
 */
//
// Usage: http://path/to/pukiwikiphp?plugin=rename[&refer=page_name]

define('PLUGIN_RENAME_LOGPAGE', ':RenameLog');

function plugin_rename_action()
{
	//global $whatsnew;

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits this');

	$method = plugin_rename_getvar('method');
	if ($method == 'regex') {
		$src = plugin_rename_getvar('src');
		if ($src == '') return plugin_rename_phase1();

		$src_pattern = '/' . preg_quote($src, '/') . '/';
		$arr0 = preg_grep($src_pattern, get_existpages());
		if (! is_array($arr0) || empty($arr0))
			return plugin_rename_phase1('nomatch');

		$dst = plugin_rename_getvar('dst');
		$arr1 = preg_replace($src_pattern, $dst, $arr0);
		foreach ($arr1 as $page)
			if (! is_pagename($page))
				return plugin_rename_phase1('notvalid');

		return plugin_rename_regex($arr0, $arr1);

	} else {
		// $method == 'page'
		$page  = plugin_rename_getvar('page');
		$refer = plugin_rename_getvar('refer');		// 変更元のページ名

		if ($refer == '') {
			return plugin_rename_phase1();
		} else if (!is_page($refer)) {
			return plugin_rename_phase1('notpage', $refer);
		//} else if ($refer == $whatsnew) {
		} else if ($refer == WikiConfig::getWhatsnewPage()) {
			return plugin_rename_phase1('norename', $refer);
		} else if ($page == '' || $page == $refer) {	// 新規ページ名未入力、または、新旧ページ名が同じとき
			// 新規ページ名入力フィールド表示
			return plugin_rename_phase2();
		} else if (! is_pagename($page)) {
			return plugin_rename_phase2('notvalid');
		} else if (is_page($page)){				// 変更先のページが既に存在するとき add by magic3
			return plugin_rename_phase2('already', $page);
		} else {
			// ページ名変更処理
			//return plugin_rename_refer();
			return plugin_rename_refer($page, $refer);
		}
	}
}

// 変数を取得する
function plugin_rename_getvar($key)
{
	//global $vars;
	//return isset($vars[$key]) ? $vars[$key] : '';
	return WikiParam::getVar($key);
}

// エラーメッセージを作る
function plugin_rename_err($err, $page = '')
{
	global $_rename_messages;

	if ($err == '') return '';

	$body = $_rename_messages['err_' . $err];
	if (is_array($page)) {
		$tmp = '';
		foreach ($page as $_page) $tmp .= '<br />' . $_page;
		$page = $tmp;
	}
	if ($page != '') $body = sprintf($body, htmlspecialchars($page));

	$msg = sprintf($_rename_messages['err'], $body);
	return $msg;
}

//第一段階:ページ名または正規表現の入力
function plugin_rename_phase1($err = '', $page = '')
{
	global $script, $_rename_messages;

	$msg    = plugin_rename_err($err, $page);
	$refer  = plugin_rename_getvar('refer');
	$method = plugin_rename_getvar('method');

	$radio_regex = $radio_page = '';
	if ($method == 'regex') {
		$radio_regex = ' checked="checked"';
	} else {
		$radio_page  = ' checked="checked"';
	}
	$select_refer = plugin_rename_getselecttag($refer);

	$s_src = htmlspecialchars(plugin_rename_getvar('src'));
	$s_dst = htmlspecialchars(plugin_rename_getvar('dst'));

	$postScript = $script . WikiParam::convQuery("?");
	$ret = array();
	$ret['msg']  = $_rename_messages['msg_title'];

	$ret['body'] = <<<EOD
$msg
<form action="$postScript" method="post" class="form">
 <div>
  <input type="hidden" name="plugin" value="rename" />
  <input type="radio"  name="method" id="_p_rename_page" value="page"$radio_page />
  <label for="_p_rename_page">{$_rename_messages['msg_page']}:</label>$select_refer<br />
  <input type="radio"  name="method" id="_p_rename_regex" value="regex"$radio_regex />
  <label for="_p_rename_regex">{$_rename_messages['msg_regex']}:</label><br />
  <label for="_p_rename_from">From:</label><br />
  <input type="text" name="src" id="_p_rename_from" size="80" value="$s_src" /><br />
  <label for="_p_rename_to">To:</label><br />
  <input type="text" name="dst" id="_p_rename_to"   size="80" value="$s_dst" /><br />
  <input type="submit" class="button" value="{$_rename_messages['btn_next']}" /><br />
 </div>
</form>
EOD;
	return $ret;
}

//第二段階:新しい名前の入力
//function plugin_rename_phase2($err = '')
function plugin_rename_phase2($err = '', $page = '')
{
	global $script, $_rename_messages;

	//$msg   = plugin_rename_err($err);
	$msg   = plugin_rename_err($err, $page);
	$page  = plugin_rename_getvar('page');
	$refer = plugin_rename_getvar('refer');
	if ($page == '') $page = $refer;

	$msg_related = '';
	$related = plugin_rename_getrelated($refer);
	if (! empty($related))
		$msg_related = '<label for="_p_rename_related">' . $_rename_messages['msg_do_related'] . '</label>' .
		'<input type="checkbox" name="related" id="_p_rename_related" value="1" checked="checked" /><br />';

	$msg_rename = sprintf($_rename_messages['msg_rename'], make_pagelink($refer));
	$s_page  = htmlspecialchars($page);
	$s_refer = htmlspecialchars($refer);

	$postScript = $script . WikiParam::convQuery("?");
	$ret = array();
	$ret['msg']  = $_rename_messages['msg_title'];

	$ret['body'] = <<<EOD
$msg
<form action="$postScript" method="post" class="form">
 <div>
  <input type="hidden" name="plugin" value="rename" />
  <input type="hidden" name="refer"  value="$s_refer" />
  $msg_rename<br />
  <label for="_p_rename_newname">{$_rename_messages['msg_newname']}:</label>
  <input type="text" name="page" id="_p_rename_newname" size="80" value="$s_page" /><br />
  $msg_related
  <input type="submit" class="button" value="{$_rename_messages['btn_next']}" /><br />
 </div>
</form>
EOD;
	if (! empty($related)) {
		$ret['body'] .= '<hr /><p>' . $_rename_messages['msg_related'] . '</p><ul>';
		sort($related);
		foreach ($related as $name)
			$ret['body'] .= '<li>' . make_pagelink($name) . '</li>';
		$ret['body'] .= '</ul>';
	}
	return $ret;
}

//ページ名と関連するページを列挙し、phase3へ
//function plugin_rename_refer()
function plugin_rename_refer($page, $refer)
{
//	$page  = plugin_rename_getvar('page');
//	$refer = plugin_rename_getvar('refer');
	$pages = array();
	//$pages[encode($refer)] = encode($page);
	$pages[$refer] = $page;		// 名前を変更するページ自身
	if (plugin_rename_getvar('related') != '') {
		$from = strip_bracket($refer);		// 変更元ページ名
		$to   = strip_bracket($page);		// 変更先ページ名
		
		// 関連ファイルを取得し、関連ファイルの新規ページ名を作成
		foreach (plugin_rename_getrelated($refer) as $_page){
			//$pages[encode($_page)] = encode(str_replace($from, $to, $_page));
			$pages[$_page] = $to . substr($_page, strlen($from));
		}
	}
	// $pagesには、「$pages[旧ページ名]=[新規ページ名]」の形式で変更対象のページ名がすべて格納されている
	return plugin_rename_phase3($pages);
}

//正規表現でページを置換
function plugin_rename_regex($arr_from, $arr_to)
{
	$exists = array();
	foreach ($arr_to as $page)
		if (is_page($page))
			$exists[] = $page;

	if (! empty($exists)) {
		return plugin_rename_phase1('already', $exists);
	} else {
		$pages = array();
		foreach ($arr_from as $refer){
			//$pages[encode($refer)] = encode(array_shift($arr_to));
			$pages[$refer] = array_shift($arr_to);
		}
		return plugin_rename_phase3($pages);
	}
}

function plugin_rename_phase3($pages)
{
	// $pagesには、「$pages[旧ページ名]=[新規ページ名]」の形式で変更対象のページ名がすべて格納されている
	global $script, $_rename_messages;

	$msg = $input = '';
	
	// 名前を変更するファイルを取得(アップロードディレクトリ内)
	$files = plugin_rename_get_files($pages);

	// 変更先のファイルの存在をチェック。存在する場合は削除するため
	$exists = array();
	foreach ($files as $_page=>$arr){
		foreach ($arr as $old=>$new){
			if (file_exists($new)) $exists[$_page][$old] = $new;
		}
	}

	$pass = plugin_rename_getvar('pass');
	if ($pass != '' && pkwk_login($pass)) {		// パスワードの入力チェック
		return plugin_rename_proceed($pages, $files, $exists);
	} else if ($pass != '') {
		$msg = plugin_rename_err('adminpass');
	}

	$method = plugin_rename_getvar('method');
	if ($method == 'regex') {
		$s_src = htmlspecialchars(plugin_rename_getvar('src'));
		$s_dst = htmlspecialchars(plugin_rename_getvar('dst'));
		$msg   .= $_rename_messages['msg_regex'] . '<br />';
		$input .= '<input type="hidden" name="method" value="regex" />';
		$input .= '<input type="hidden" name="src"    value="' . $s_src . '" />';
		$input .= '<input type="hidden" name="dst"    value="' . $s_dst . '" />';
	} else {
		$s_refer   = htmlspecialchars(plugin_rename_getvar('refer'));
		$s_page    = htmlspecialchars(plugin_rename_getvar('page'));
		$s_related = htmlspecialchars(plugin_rename_getvar('related'));
		$msg   .= $_rename_messages['msg_page'] . '<br />';
		$input .= '<input type="hidden" name="method"  value="page" />';
		$input .= '<input type="hidden" name="refer"   value="' . $s_refer   . '" />';
		$input .= '<input type="hidden" name="page"    value="' . $s_page    . '" />';
		$input .= '<input type="hidden" name="related" value="' . $s_related . '" />';
	}

	if (! empty($exists)) {
		$msg .= $_rename_messages['err_already_below'] . '<ul>';
		foreach ($exists as $page=>$arr) {
			//$msg .= '<li>' . make_pagelink(decode($page));
			$msg .= '<li>' . make_pagelink($page);
			$msg .= $_rename_messages['msg_arrow'];
			//$msg .= htmlspecialchars(decode($pages[$page]));
			$msg .= htmlspecialchars($pages[$page]);
			if (! empty($arr)) {
				$msg .= '<ul>' . "\n";
				foreach ($arr as $ofile=>$nfile)
					$msg .= '<li>' . $ofile .
					$_rename_messages['msg_arrow'] . $nfile . '</li>' . "\n";
				$msg .= '</ul>';
			}
			$msg .= '</li>' . "\n";
		}
		$msg .= '</ul><hr />' . "\n";

		$input .= '<input type="radio" name="exist" value="0" checked="checked" />' .
			$_rename_messages['msg_exist_none'] . '<br />';
		$input .= '<input type="radio" name="exist" value="1" />' .
			$_rename_messages['msg_exist_overwrite'] . '<br />';
	}

	$postScript = $script . WikiParam::convQuery("?");
	$ret = array();
	$ret['msg'] = $_rename_messages['msg_title'];

	$ret['body'] = <<<EOD
<p>$msg</p>
<form action="$postScript" method="post" class="form">
 <div>
  <input type="hidden" name="plugin" value="rename" />
  <input type="hidden" name="pass" />
  $input
  <label for="_p_rename_adminpass">{$_rename_messages['msg_adminpass']}</label>
  <input type="password" name="password" id="_p_rename_adminpass" size="12" />
  <input type="submit" class="button" value="{$_rename_messages['btn_submit']}" onclick="this.form.pass.value = hex_md5(this.form.password.value);" />
 </div>
</form>
<p>{$_rename_messages['msg_confirm']}</p>
EOD;

	//ksort($pages);
	$ret['body'] .= '<ul>' . "\n";
	foreach ($pages as $old=>$new){
		//$ret['body'] .= '<li>' .  make_pagelink(decode($old)) . $_rename_messages['msg_arrow'] . htmlspecialchars(decode($new)) .  '</li>' . "\n";
		$ret['body'] .= '<li>' .  make_pagelink($old) . $_rename_messages['msg_arrow'] . htmlspecialchars($new) .  '</li>' . "\n";
	}
	$ret['body'] .= '</ul>' . "\n";
	return $ret;
}
function plugin_rename_get_files($pages)
{
	// $pagesには、「$pages[旧ページ名]=[新規ページ名]」の形式で変更対象のページ名がすべて格納されている
	$files = array();
	/*$dirs  = array(BACKUP_DIR, DIFF_DIR, DATA_DIR);
	if (exist_plugin_convert('attach'))  $dirs[] = UPLOAD_DIR;
	if (exist_plugin_convert('counter')) $dirs[] = COUNTER_DIR;
	*/
	// アップロードディレクトリを検索
	$dirs = array(UPLOAD_DIR);

	$matches = array();
	foreach ($dirs as $path) {
		$dir = opendir($path);
		if (! $dir) continue;

		while ($file = readdir($dir)) {
			if ($file == '.' || $file == '..') continue;

			foreach ($pages as $from => $to) {
				$encodeFrom = encode($from);
				$encodeTo = encode($to);
				//$pattern = '/^' . str_replace('/', '\/', $from) . '([._].+)$/';
				$pattern = '/^' . str_replace('/', '\/', $encodeFrom) . '([._].+)$/';
				if (! preg_match($pattern, $file, $matches))
					continue;

				//$newfile = $to . $matches[1];
				$newfile = $encodeTo . $matches[1];
				$files[$from][$path . $file] = $path . $newfile;
			}
		}
	}
	return $files;
}
// ページデータの実際の移行処理
function plugin_rename_proceed($pages, $files, $exists)
{
	global $now, $_rename_messages;

	if (plugin_rename_getvar('exist') == ''){
		foreach ($exists as $key=>$arr)
			unset($files[$key]);
	}

	// アップロードディレクトリ内のファイルの名前を更新
	set_time_limit(0);
	foreach ($files as $page => $arr) {
		// 添付ファイルの情報取得
		$newLines = array();
		$lines = WikiPage::getPageUpload($page);
				
		foreach ($arr as $old => $new) {
			// 変更先のファイルを削除
			$ret = true;
			if (isset($exists[$page][$old]) && $exists[$page][$old]) $ret = unlink($new);
			
			// ファイル名変更
			$ret = rename($old, $new);
			
			// ファイル名変更に成功した場合は、ファイル情報を更新
			$oldFilename = basename($old);
			$newFilename = basename($new);
			if ($ret){
				// ##### 添付ファイル情報を更新 #####
				// 1レコード(行)のパターンは、「ファイル名」「元のファイル名」「ファイルハッシュキー」「世代番号」「アクセスカウント数」「凍結状態」をタブ区切りで格納
				for ($i = 0; $i < count($lines); $i++){
					$lineData = rtrim($lines[$i]);
					if (empty($lineData)) continue;
	
					list($filename, $originalFilename, $md5, $age, $count, $freeze) = explode("\t", $lineData);
					if ($filename == $oldFilename){
						// 新規データ追加
						$newLine = '';
						$newLine .= $newFilename . "\t";		// 新規ファイル名
						$newLine .= $originalFilename . "\t";	// 元のファイル名
						$newLine .= $md5 . "\t";				// ファイルハッシュキー
						$newLine .= '' . "\t";	// age
						$newLine .= '0' . "\t";	// count
						$newLine .= '' . "\n";	// freeze
						$newLines[] = $newLine;
						break;
					}
				}
			}
			// linkデータベースを更新する BugTrack/327 arino
			//links_update($old);
			//links_update($new);
		}
		// 新規ページの添付ファイル情報を更新
		WikiPage::updatePageUpload($pages[$page], join('', $newLines));
	}

	// データの移行処理
	foreach ($pages as $oldPage => $newPage){
		// ページ名を変更
		WikiPage::renamePage($oldPage, $newPage);
	}
	
	// ##### データ移行完了後の処理 #####
	// リンク更新
	foreach ($pages as $oldPage => $newPage){
		links_update($newPage);
	}
		
	// 最終更新ファイルアップデート
	put_lastmodified();

	// 名前変更のログを残す
	$postdata = get_source(PLUGIN_RENAME_LOGPAGE);
	$postdata[] = '*' . $now . "\n";
	if (plugin_rename_getvar('method') == 'regex') {
		$postdata[] = '-' . $_rename_messages['msg_regex'] . "\n";
		$postdata[] = '--From:[[' . plugin_rename_getvar('src') . ']]' . "\n";
		$postdata[] = '--To:[['   . plugin_rename_getvar('dst') . ']]' . "\n";
	} else {
		$postdata[] = '-' . $_rename_messages['msg_page'] . "\n";
		$postdata[] = '--From:[[' . plugin_rename_getvar('refer') . ']]' . "\n";
		$postdata[] = '--To:[['   . plugin_rename_getvar('page')  . ']]' . "\n";
	}

	if (! empty($exists)) {
		$postdata[] = "\n" . $_rename_messages['msg_result'] . "\n";
		foreach ($exists as $page=>$arr) {
			//$postdata[] = '-' . decode($page) . $_rename_messages['msg_arrow'] . decode($pages[$page]) . "\n";
			$postdata[] = '-' . $page . $_rename_messages['msg_arrow'] . $pages[$page] . "\n";
			foreach ($arr as $ofile=>$nfile)
				$postdata[] = '--' . $ofile .
					$_rename_messages['msg_arrow'] . $nfile . "\n";
		}
		$postdata[] = '----' . "\n";
	}

	foreach ($pages as $old=>$new){
		//$postdata[] = '-' . decode($old) . $_rename_messages['msg_arrow'] . decode($new) . "\n";
		$postdata[] = '-' . $old . $_rename_messages['msg_arrow'] . $new . "\n";
	}

	// 更新の衝突はチェックしない。

	// ファイルの書き込み
	page_write(PLUGIN_RENAME_LOGPAGE, join('', $postdata));

	//リダイレクト
	$page = plugin_rename_getvar('page');
	if ($page == '') $page = PLUGIN_RENAME_LOGPAGE;

	pkwk_headers_sent();
	//header('Location: ' . get_script_uri() . '?' . rawurlencode($page));
	header('Location: ' . get_script_uri() . WikiParam::convQuery('?' . rawurlencode($page), false));
	exit;
}

function plugin_rename_getrelated($page)
{
	$related = array();
	$pages = get_existpages();
	$pattern = '/(?:^|\/)' . preg_quote(strip_bracket($page), '/') . '(?:\/|$)/';
	foreach ($pages as $name) {
		if ($name == $page) continue;
		if (preg_match($pattern, $name)) $related[] = $name;
	}
	return $related;
}

function plugin_rename_getselecttag($page)
{
//	global $whatsnew;

	$pages = array();
	foreach (get_existpages() as $_page) {
		//if ($_page == $whatsnew) continue;
		if ($_page == WikiConfig::getWhatsnewPage()) continue;

		$selected = ($_page == $page) ? ' selected' : '';
		$s_page = htmlspecialchars($_page);
		$pages[$_page] = '<option value="' . $s_page . '"' . $selected . '>' .
			$s_page . '</option>';
	}
	ksort($pages);
	$list = join("\n" . ' ', $pages);

	return <<<EOD
<select name="refer">
 <option value=""></option>
 $list
</select>
EOD;

}
?>
