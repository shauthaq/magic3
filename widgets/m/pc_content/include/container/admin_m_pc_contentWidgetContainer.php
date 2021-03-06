<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_m_pc_contentWidgetContainer.php 2568 2009-11-18 13:05:26Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/pc_contentDb.php');

class admin_m_pc_contentWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();		// 表示されているコンテンツシリアル番号
	private $_menuId = 'mobile_menu';				// メインメニューID
	private $isExistsContent;		// コンテンツ項目が存在するかどうか
	const INC_INDEX = 1;		// メニュー項目表示順の増加分
	const ADMIN_WIDGET_ID = 'admin_main';		// 管理ウィジェットのウィジェットID
	const CONTENT_TYPE = 'ct';		// 参照数カウント用
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new pc_contentDb();
	}
	/**
	 * テンプレートファイルを設定
	 *
	 * _assign()でデータを埋め込むテンプレートファイルのファイル名を返す。
	 * 読み込むディレクトリは、「自ウィジェットディレクトリ/include/template」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						テンプレートファイル名。テンプレートライブラリを使用しない場合は空文字列「''」を返す。
	 */
	function _setTemplate($request, &$param)
	{
		$task = $request->trimValueOf('task');
		if ($task == 'detail'){		// 詳細画面
			return 'admin_main_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_main.tmpl.html';
		}
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @param								なし
	 */
	function _assign($request, &$param)
	{
		$task = $request->trimValueOf('task');
		if ($task == 'detail'){	// 詳細画面
			return $this->createDetail($request);
		} else {			// 一覧画面
			return $this->createList($request);
		}
	}
	/**
	 * コンテンツ一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		// ユーザ情報、表示言語
		$userId = $this->gEnv->getCurrentUserId();
		$langId = $this->gEnv->getDefaultLanguage();
		
		$act = $request->trimValueOf('act');
		if ($act == 'addtomenu'){			// メニューに項目を追加
			$contentId = $request->trimValueOf('contentid');		// コンテンツID
			
			/*
			// このウィジェットがマップされているページサブIDを取得
			$subPageId = $this->gPage->getPageSubIdByWidget($this->gEnv->getDefaultPageId(), $this->gEnv->getCurrentWidgetId());
			$sub = '';
			if (!empty($subPageId)) $sub = 'sub=' . $subPageId . '&';
			
			// URLの作成
			$url = M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END . '/index.php?' . $sub . 'contentid=' . $contentId;*/
			// このウィジェットがマップされているページサブIDを取得
			$subPageId = $this->gPage->getPageSubIdByWidget($this->gEnv->getDefaultMobilePageId(), $this->gEnv->getCurrentWidgetId());
			$sub = '';
			if (!empty($subPageId)) $sub = 'sub=' . $subPageId . '&';
			
			// URLの作成
			$url = M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END . '/m/index.php?' . $sub . 'contentid=' . $contentId;

			// コンテンツ名を取得
			$menutItemName = '';
			$ret = $this->db->getContentByContentId($contentId, $langId, $row);
			if ($ret){
				// 取得値を設定
				$menutItemName = $row['cn_name'];		// 名前
			}
			
			// メニュー項目追加
			$ret = $this->db->addMenuItem($this->_menuId, $langId, $menutItemName, $url, self::INC_INDEX, $userId);
			if ($ret){
				$this->setGuidanceMsg('メインメニューに項目を追加しました');
			} else {
				$this->setAppErrorMsg('メインメニューの項目追加に失敗しました');
			}
		} else if ($act == 'delete'){		// 項目削除の場合
			$listedItem = explode(',', $request->trimValueOf('seriallist'));
			$delItems = array();
			for ($i = 0; $i < count($listedItem); $i++){
				// 項目がチェックされているかを取得
				$itemName = 'item' . $i . '_selected';
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				
				if ($itemValue){		// チェック項目
					$delItems[] = $listedItem[$i];
				}
			}
			if (count($delItems) > 0){
				$ret = $this->db->delContentItem($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		
		// コンテンツ項目リストをデフォルト言語で取得
		$this->db->getAllContentItems(array($this, 'itemListLoop'), $langId);
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		// コンテンツ項目がないときは、一覧を表示しない
		if (!$this->isExistsContent) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');		
	}
	/**
	 * コンテンツ詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		// ユーザ情報、表示言語
		$langId = $this->gEnv->getDefaultLanguage();
		
		// ウィンドウ表示状態
		$openby = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);
		
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$name = $request->trimValueOf('item_name');
		$showTitle = ($request->trimValueOf('show_title') == 'on') ? 1 : 0;		// タイトルの表示
		$titleBgColor = $request->trimValueOf('item_title_bgcolor');		// タイトルバックグランドカラー
		$html = $request->valueOf('item_html');		// HTMLタグを可能とする
		$key = $request->valueOf('item_key');		// 外部参照用キー
		$visible = ($request->trimValueOf('item_visible') == 'on') ? 1 : 0;		// チェックボックス
		$limited = ($request->trimValueOf('item_limited') == 'on') ? 1 : 0;		// チェックボックス
		$default = ($request->trimValueOf('item_default') == 'on') ? 1 : 0;		// チェックボックス
		
		$reloadData = false;		// データの再読み込み
		if ($act == 'add'){		// 項目追加の場合
			// 入力チェック
			$this->checkInput($name, '名前');
					
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				$ret = $this->db->addContentItem(0, $langId, $name, ''/*説明*/, $html, $visible, $default, $limited, $key, $newSerial);
				
				// 表示属性はコンテンツIDを定義IDにして、ウィジェットパラメータとして保存
				/*if ($ret){
					$paramObj = new stdClass;
					$paramObj->showTitle	= $showTitle;		// タイトルの表示
					$paramObj->titleBgColor	= $titleBgColor;		// タイトルバックグランドカラー
					$ret = $this->updateWidgetParamObjByConfigId($newContentId, $paramObj);
				}*/
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 項目更新の場合
			// 入力チェック
			$this->checkInput($name, '名前');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$ret = $this->db->updateContentItem($this->serialNo, $name, $html, $visible, $default, $limited, $key, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setAppErrorMsg('データ更新に失敗しました');
				}
			}				
		} else if ($act == 'delete'){		// 項目削除の場合
			if (empty($this->serialNo)){
				$this->setUserErrorMsg('削除項目が選択されていません');
			}
			// エラーなしの場合は、データを削除
			if ($this->getMsgCount() == 0){
				$ret = $this->db->delContentItem(array($this->serialNo));
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else {
			// コンテンツIDが設定されているとき(他ウィジェットからの表示)は、データを取得
			$contentId = $request->trimValueOf('contentid');		// コンテンツID
			if (empty($contentId)){
				if (empty($this->serialNo)){		// 新規項目追加のとき
					$visible = 1;		// 初期状態は表示
				} else {
					$reloadData = true;		// データの再読み込み
				}
			} else {
				// コンテンツを取得
				$ret = $this->db->getContentByContentId($contentId, $langId, $row);
				if ($ret){
					$this->serialNo = $row['cn_serial'];		// コンテンツシリアル番号
					$reloadData = true;		// データの再読み込み
				} else {
					$this->serialNo = 0;
				}
			}
		}
		if ($reloadData){		// データの再読み込み
			$ret = $this->db->getContentBySerial($this->serialNo, $row);
			if ($ret){
				$contentId = $row['cn_id'];		// コンテンツID
				$name = $row['cn_name'];		// コンテンツ名前
				$html = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $row['cn_html']);				// HTML
				$key = $row['cn_key'];					// 外部参照用キー
				$update_user = $this->convertToDispString($row['lu_name']);// 更新者
				$update_dt = $this->convertToDispDateTime($row['cn_create_dt']);
			
				// 項目表示、デフォルト値チェックボックス
				$visible = $row['cn_visible'];
				$default = $row['cn_default'];
				$limited = $row['cn_user_limited'];		// ユーザ制限
			} else {
				$this->serialNo = 0;
			}
		}
		
		// ### 入力値を再設定 ###
		$this->tmpl->addVar("_widget", "sel_item_name", $name);		// 名前
		$this->tmpl->addVar("_widget", "sel_item_html", $html);		// HTML
		$this->tmpl->addVar("_widget", "sel_item_key", $key);		// 外部参照用キー
		$this->tmpl->addVar("_widget", "update_user", $update_user);	// 更新者
		$this->tmpl->addVar("_widget", "update_dt", $update_dt);	// 更新日時
	
		// 項目表示、項目利用可否チェックボックス
		$visibleStr = '';
		if ($visible) $visibleStr = 'checked';
		$this->tmpl->addVar("_widget", "sel_item_visible", $visibleStr);
		$defaultStr = '';
		if ($default) $defaultStr = 'checked';
		$this->tmpl->addVar("_widget", "sel_item_default", $defaultStr);
		$limitedStr = '';
		if ($limited) $limitedStr = 'checked';
		$this->tmpl->addVar("_widget", "sel_item_limited", $limitedStr);
	
		// 選択中のシリアル番号を設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
			
		// パスの設定
		$this->tmpl->addVar('_widget', 'admin_url', $this->getUrl($this->gEnv->getDefaultAdminUrl()));// 管理者URL
		$this->tmpl->addVar('_widget', 'custom_value_task', 'usercustom');		// ユーザ定義値参照用
		$this->tmpl->addVar('_widget', 'admin_widget_id', self::ADMIN_WIDGET_ID);// ユーザ定義値参照用(管理ウィジェットのウィジェットID)
		
		// ボタンの表示制御
		if (empty($this->serialNo)){		// 新規追加項目を選択しているとき
			$this->tmpl->addVar("_widget", "sel_item_id", '新規');			// コンテンツID
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
		} else {
			$this->tmpl->addVar("_widget", "sel_item_id", $contentId);			// コンテンツID
			$this->tmpl->setAttribute('del_button', 'visibility', 'visible');// 「削除」ボタン
		}
		// 「戻る」ボタンの表示
		if ($openby == 'simple') $this->tmpl->setAttribute('cancel_button', 'visibility', 'hidden');		// 詳細画面のみの表示のときは戻るボタンを隠す
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function itemListLoop($index, $fetchedRow, $param)
	{
		$serial = $this->convertToDispString($fetchedRow['cn_serial']);
		
		// 表示状態
		$visible = '';
		if ($fetchedRow['cn_visible']) $visible = 'checked';

		// ユーザ制限
		$limited = '';
		if ($fetchedRow['cn_user_limited']) $limited = 'checked';

		// デフォルト時の項目かどうか
		$default = '';
		if ($fetchedRow['cn_default']) $default = 'checked';
		
		// 対応言語を取得
		$lang = '';
		$ret = $this->db->getLangByContentId($fetchedRow['cn_id'], $rows);
		if ($ret){
			$count = count($rows);
			for ($i = 0; $i < $count; $i++){
				if ($this->gEnv->getCurrentLanguage() == 'ja'){	// 日本語の場合
					$lang .= $rows[$i]['ln_name'];
					if ($i != $count -1) $lang .= ',';
				} else {
					$lang .= $rows[$i]['ln_name_en'];
					if ($i != $count -1) $lang .= ',';
				}
			}
		}
		// 総参照数
		$totalViewCount = $this->gInstance->getAnalyzeManager()->getTotalContentViewCount(self::CONTENT_TYPE, $serial);
		
		$row = array(
			'index' => $index,													// 項目番号
			'serial' => $serial,			// シリアル番号
			'id' => $this->convertToDispString($fetchedRow['cn_id']),			// ID
			'name' => $this->convertToDispString($fetchedRow['cn_name']),		// 名前
			'lang' => $lang,													// 対応言語
			'view_count' => $totalViewCount,									// 総参照数
			'update_user' => $this->convertToDispString($fetchedRow['lu_name']),	// 更新者
			'update_dt' => $this->convertToDispDateTime($fetchedRow['cn_create_dt']),	// 更新日時
			'visible' => $visible,											// メニュー項目表示制御
			'limited' => $limited,											// ユーザ制限
			'default' => $default											// デフォルト項目
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中のコンテンツIDを保存
		$this->serialArray[] = $fetchedRow['cn_serial'];
		
		$this->isExistsContent = true;		// コンテンツ項目が存在するかどうか
		return true;
	}
}
?>
