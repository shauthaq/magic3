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
 * @version    SVN: $Id: m_quizkWidgetContainer.php 2936 2010-03-17 14:20:08Z fishbone $
 * @link       http://www.magic3.org
 */
//require_once($gEnvManager->getContainerPath() . '/baseMobileWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/m_quizkBaseWidgetContainer.php');

class m_quizkWidgetContainer extends m_quizkBaseWidgetContainer
{
	const DEFAULT_TASK = 'top';		// デフォルトの画面

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
	}
	/**
	 * ディスパッチ処理(メインコンテナのみ実行)
	 *
     * HTTPリクエストの内容を見て処理をコンテナに振り分ける
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return bool 						このクラスの_setTemplate(), _assign()へ処理を継続するかどうかを返す。
	 *                                      true=処理を継続、false=処理を終了
	 */
	function _dispatch($request, &$param)
	{
		// 実行処理を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TASK;

		// ログインが必要な処理の場合は、ログイン状況をチェックする
		switch ($task){
			case 'member':			// 会員情報変更
				// ログイン状態を取得
				if (!$this->gEnv->isCurrentUserLogined()){		// ログインされていない場合
					$this->SetMsg(self::MSG_APP_ERR, "ログインが必要です");
					return true;
				}
				break;
			default:
				break;
		}
		// アクセス権をチェック
		switch ($task){
			case 'progress':			// クイズ質問回答画面
			case 'complete':			// 受付完了画面
				// 携帯以外からのアクセスの場合はエラー
				if (empty($this->mobileId)) return true;
				break;
		}
		
		// ##### コンテナを起動 #####
		switch ($task){
			case 'top':			// トップ画面
			case 'profile':			// プロフィール入力画面
			case 'progress':			// クイズ質問回答画面
			case 'complete':			// 受付完了画面
				$this->gLaunch->goSubWidget($task);
				return false;
			default:
				$this->SetMsg(self::MSG_APP_ERR, "画面が見つかりません");
				return true;
		}
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
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);

		// 要求画面によってテンプレートを変更
		switch ($task){
			case 'member':			// 会員情報変更
				// ログインが必要であるメッセージを表示
				return 'message_login.tmpl.html';
			default:
				return 'message.tmpl.html';
		}
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _assign($request, &$param)
	{
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);

		// 要求画面によってテンプレートを変更
		switch ($task){
			case 'member':			// 会員情報変更
				// ログインが必要であるメッセージを表示
				$this->tmpl->addVar("_widget", "login_url", $this->gEnv->createCurrentPageUrl() . '&task=login');		// ログイン用URL
				break;
			case 'progress':			// クイズ質問回答画面
			case 'complete':			// 受付完了画面
				$message = sprintf(self::ERR_MESSAGE_FORMAT, '携帯電話以外からは実行できません');
				$this->tmpl->addVar("_widget", "message", $message);
				$this->tmpl->addVar('_widget', 'top_url', $this->gEnv->createCurrentPageUrlForMobile(''));
				break;
			default:
				break;
		}
	}
}
?>
