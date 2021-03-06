/**
 * Magic3管理機能用JavaScriptライブラリ
 *
 * JavaScript 1.5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: m3admin1.6.2.js 6010 2013-05-19 11:38:31Z fishbone $
 * @link       http://www.magic3.org
 */
// 親ウィンドウを更新
function m3UpdateParentWindow()
{
	var href = 	window.opener.location.href.split('#');
	window.opener.location.href = href[0];
	//window.opener.location.href = window.opener.location.href;

}
// 設定ウィンドウから親ウィンドウを更新
function m3UpdateParentWindowByConfig(serial)
{
	if (window.opener){
		if (window.opener.m3UpdateByConfig) window.opener.m3UpdateByConfig(serial);
		if (window.opener.m3UpdateByChildWindow) window.opener.m3UpdateByChildWindow(serial);
	}
	if (window.parent != window.self){	// 呼び出し元がiframeの場合のみ実行
		// iframeから親フレームを更新
		if (window.parent.m3UpdateByChildWindow) window.parent.m3UpdateByChildWindow(serial);
		
		// iframeから起動元ウィンドウを更新
		if (window.parent.opener && window.parent.opener.m3UpdateByConfig) window.parent.opener.m3UpdateByConfig(serial);
	}
}
// 親ウィンドウサイズを調整
function m3AdjustParentWindow()
{
	if (window.parent){
		if (window.parent.m3AdjustWindow) window.parent.m3AdjustWindow();
	}
}
// ウィンドウサイズ調整
function m3AdjustHeight(obj, min)
{
	var name = obj.name;
	
	if (!name) name = 0;

	var app = navigator.appName.charAt(0);
	var height = min;
	if(navigator.userAgent.indexOf('Safari') != -1){
		height = parent.frames[name].document.body.scrollHeight + 80;
	}else if (app == "N"){
		height = parent.frames[name].document.height +80;
	} else {
		try {
			height = parent.frames[name].document.body.scrollHeight + 80;
		} catch (e){}
	}
	if (height > min){
		obj.height = height;
	} else {
		obj.height = min;
	}
}
// 一般ウィンドウ表示
function m3ShowStandardWindow(url)
{
	if (M3_CONFIG_WINDOW_OPEN_TYPE == 0){
		window.open(url, "", "toolbar=no,menubar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=1050,height=900");
	} else {
		window.open(url);
	}
}
// 設定用ウィンドウ表示
function m3ShowConfigWindow(widgetId, configId, serial)
{
	if (M3_CONFIG_WINDOW_OPEN_TYPE == 0){
		window.open(M3_DEFAULT_ADMIN_URL + "?cmd=configwidget&openby=other&widget=" + widgetId + 
					"&_defconfig=" + configId + "&_defserial=" + serial, "",
					"toolbar=no,menubar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=1050,height=900");
	} else {
		window.open(M3_DEFAULT_ADMIN_URL + "?cmd=configwidget&openby=other&widget=" + widgetId + 
					"&_defconfig=" + configId + "&_defserial=" + serial);
	}
}
// ウィジェット表示微調整用ウィンドウ表示
function m3ShowAdjustWindow(configId, serial, pageId, pageSubId)
{
	if (M3_CONFIG_WINDOW_OPEN_TYPE == 0){
		window.open(M3_DEFAULT_ADMIN_URL + "?task=adjustwidget&openby=simple" + 
					"&_defconfig=" + configId + "&_defserial=" + serial + "&_page=" + pageId + "&_sub=" + pageSubId, "",
					"toolbar=no,menubar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=1050,height=900");
	} else {
		window.open(M3_DEFAULT_ADMIN_URL + "?task=adjustwidget&openby=simple" + 
					"&_defconfig=" + configId + "&_defserial=" + serial + "&_page=" + pageId + "&_sub=" + pageSubId);
	}
}
// 各種端末用プレビューウィンドウ表示
function m3ShowPreviewWindow(type, url)
{
	var width, height;
	
	switch (type){
		case 0:		// PC用
		default:
			width = 1000;
			height = 800;
			break;
		case 1:		// 携帯用
			width = 240;
			height = 320;
			break;
		case 2:		// スマートフォン用
			width = 320;
			height = 480;
			break;
	}
	window.open(url, "", "toolbar=no,menubar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=" + width + ",height=" + height);
}
/**
 * 画像ファイルブラウザを表示
 *
 * @return なし
 */
function m3_openImageFileBrowser()
{
	var imageConnector = 'connectors/php/connector.php';
	var imageBrowser = M3_ROOT_URL + '/scripts/fckeditor2.6.6/editor/plugins/FileBrowser_Thumbnail/browser.html?Type=Image&Lang=ja&Connector=' + imageConnector;
	var sOptions = "toolbar=no,status=no,resizable=yes,dependent=yes,scrollbars=yes";
	var screenWidth, screenHeight;
	var width, height;
	try
	{
		screenWidth	= screen.width;
		screenHeight	= screen.height;
	} catch (e){
		screenWidth		= 800;
		screenHeight	= 600;
	}
	width	= screenWidth * 0.7;
	height	= screenHeight * 0.7;
	window.open(imageBrowser, 'FCKBrowseWindow', sOptions + ",width=" + width + ",height=" + height);
}
/**
 * Flashファイルブラウザを表示
 *
 * @return なし
 */
function m3_openFlashFileBrowser()
{
	var imageConnector = 'connectors/php/connector.php';
	var imageBrowser = M3_ROOT_URL + '/scripts/fckeditor2.6.6/editor/plugins/FileBrowser_Thumbnail/browser.html?Type=Flash&Lang=ja&Connector=' + imageConnector;
	var sOptions = "toolbar=no,status=no,resizable=yes,dependent=yes,scrollbars=yes";
	var screenWidth, screenHeight;
	var width, height;
	try
	{
		screenWidth	= screen.width;
		screenHeight	= screen.height;
	} catch (e){
		screenWidth		= 800;
		screenHeight	= 600;
	}
	width	= screenWidth * 0.7;
	height	= screenHeight * 0.7;
	window.open(imageBrowser, 'FCKBrowseWindow', sOptions + ",width=" + width + ",height=" + height);
}
/**
 * TextAreaをHTMLエディターに変更
 *
 * @param string id			TextAreaタグのIDまたはname
 * @param bool	isMobile	携帯用のツールバー表示
 * @return なし
 */
function m3_setHtmlEditor(id, isMobile)
{
	var oFCKeditor		= new FCKeditor(id);
	oFCKeditor.BasePath	= M3_ROOT_URL + '/scripts/fckeditor2.6.6/';
	oFCKeditor.Config['CustomConfigurationsPath'] = M3_ROOT_URL + '/scripts/m3/fckconfig.js';
	if (isMobile == null || isMobile == false){
		oFCKeditor.ToolbarSet	= "M3Default";			// ツールバーリソース名
	} else {
		oFCKeditor.ToolbarSet	= "M3MobileDefault";	// ツールバーリソース名
	}
	oFCKeditor.Width	= "100%";
	oFCKeditor.Height	= "100%";
	oFCKeditor.Value	= 'This is some <strong>sample text<\/strong>. You are using <a href="http://www.fckeditor.net/">FCKeditor<\/a>.';
	oFCKeditor.ReplaceTextarea();
}
/**
 * TextAreaをWYSIWYGエディターに変更
 *
 * @param string id			TextAreaタグのIDまたはname
 * @param int height		エディター領域の高さ
 * @param bool toolbarVisible	ツールバーを表示するかどうか
 * @param string barType		ツールバータイプ(full=全項目,layout=レイアウト用)
 * @return なし
 */
function m3SetWysiwygEditor(id, height, toolbarVisible, barType)
{
	if (M3_WYSIWYG_EDITOR == 'ckeditor'){
		var config = {};
		config['customConfig'] = M3_ROOT_URL + '/scripts/m3/ckconfig.js';
		if (height) config['height'] = height;
		if (toolbarVisible != null && !toolbarVisible) config['toolbarStartupExpanded'] = false;
		if (barType){
			switch (barType){
			case 'full':
			default:
				config['toolbar'] = 'Full';
				break;
			case 'layout':
				config['toolbar'] = 'Layout';
				break;
			}
		} else {
			config['toolbar'] = 'Full';
		}
		CKEDITOR.replace(id, config);
	} else {
		var oFCKeditor		= new FCKeditor(id);
		oFCKeditor.BasePath	= M3_ROOT_URL + '/scripts/fckeditor2.6.6/';
		oFCKeditor.Config['CustomConfigurationsPath'] = M3_ROOT_URL + '/scripts/m3/fckconfig.js';
		oFCKeditor.ToolbarSet	= "M3Default";			// ツールバーリソース名
		if (height) oFCKeditor.Height = String(height) + 'px';
		oFCKeditor.Value	= 'This is some <strong>sample text<\/strong>. You are using <a href="http://www.fckeditor.net/">FCKeditor<\/a>.';
		oFCKeditor.ReplaceTextarea();
	}
}
/**
 * TextAreaをスクリプト編集エディターに変更
 *
 * @param string id			TextAreaタグのIDまたはname
 * @param int height		エディター領域の高さ
 * @return なし
 */
function m3SetScriptEditor(id, height)
{
	if (typeof CodeMirror == 'undefined') return;
	
	var obj;
	obj = document.getElementById(id);
	if (!obj) obj = document.getElementsByName(id);
	if (!obj) alert(id + "not found.");
	var editor = CodeMirror.fromTextArea(obj, {
		mode: "javascript",
		lineNumbers: true,
		matchBrackets: true,
		extraKeys: {"Enter": "newlineAndIndentContinueComment"}
	});
	if (height) editor.setSize('100%', height);
/*	if (M3_WYSIWYG_EDITOR == 'ckeditor'){
		var config = {};
		config['customConfig'] = M3_ROOT_URL + '/scripts/m3/ckconfig_script.js';
		if (height) config['height'] = height;
		CKEDITOR.replace(id, config);
	} else {
	}*/
}
/**
 * 画面操作用スライド開閉メニューバー
 */
(function($){
	$.fn.m3SlideMenubar = function(givenOpts){
		opts = $.extend({
			position: 'top',			// panel position 'top' or 'bottom'
			height: '80px',				// set the height of the panel
			speed: 'normal',			// 'slow', 'normal', 'fast', or number in milliseconds
			touchPanel: true,			// mouse click operation than mouse hover
			openBtn: '.m3open',			// open button id or class inside 'slidetrigger' div
			closeBtn: '.m3close',			// close button id or class inside 'slidetrigger' div
			slideTrigger: '#slidetrigger'	// trigger id or class
		}, givenOpts);

		// refers to the selector 'm3SlideMenubar'
		var $this = $(this);

		// vars needed to pass args to animate method
		var containerpadding;
		var aniOpenArgs = {};
		var aniCloseArgs = {};

		// add appropriate class names based on position
		if (opts.position == 'top') {
			$this.addClass('top');
			$(opts.slideTrigger).addClass('top');
			containerpadding = 'top';
		} else {
			$this.addClass('bottom');
			$(opts.slideTrigger).addClass('bottom');
			containerpadding = 'padding-bottom';
		};

		// set panel's height
		$this.css('height', opts.height);

		// remove the 'px' from the height string so we can calculate the container padding
		var newpadding = opts.height.replace("px","");
		newpadding = parseInt(opts.height) + 21;

		aniOpenArgs[containerpadding] = newpadding;
		aniCloseArgs[containerpadding] = 0;

		if (opts.touchPanel){
			// slide panel in and container down
			$(opts.openBtn).click(function(){
				$this.slideDown(opts.speed);
				$(opts.slideTrigger).animate(aniOpenArgs, opts.speed);
				$(opts.openBtn).css({'display':'none'});
				$(opts.closeBtn).css({'display':'inline'});
				return false;
			});
			// slide panel out and container up
			$(opts.closeBtn).click(function(){
				$this.slideUp(opts.speed);
				$(opts.slideTrigger).animate(aniCloseArgs, opts.speed);
				$(opts.openBtn).css({'display':'inline'});
				$(opts.closeBtn).css({'display':'none'});
				return false;
			});
		} else {
			// slide panel in and container down
			$(opts.openBtn).mouseover(function(){
				$this.slideDown(opts.speed);
				$(opts.slideTrigger).slideUp(opts.speed);
				return false;
			});
			// slide panel out and container up
			$this.hover(function(){},function(){
				$this.slideUp(opts.speed);
				$(opts.slideTrigger).slideDown(opts.speed);
				return false;
			});
		}
	};
})(jQuery);
