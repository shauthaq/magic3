/**
 * Magic3管理機能用JavaScriptライブラリ
 *
 * JavaScript 1.5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: m3admin_widget1.5.3.js 5718 2013-02-21 01:09:52Z fishbone $
 * @link       http://www.magic3.org
 */
(function($){
	window.m3 = {};
	var m3InsertIndex = -1;
	var m3UpdatePos;
	
	var m3UpdateByConfig = function(serial){
		var widgets = $('.m3_widget_sortable');
		for (var i = 0; i < widgets.length; i++){
			var widget = $(widgets[i]);
			var attrs = m3_splitAttr(widget.attr('m3'));
			
			if (attrs['serial'] == serial){
				m3TaskWidget('get', widget);
				break;
			}
		}
	};
	window.m3.m3UpdateByConfig = m3UpdateByConfig;
	
	var widgetClose = function(){
		var widget = $(this).parents('.m3_widget_sortable');
		widget.fadeOut('slow', function(){
			m3TaskWidget('delete', $(this));
			$(this).remove();
		});
	};
	var m3TaskWidget = function(task, obj, widgetType, index, pos){
		var parent = obj.parents('.m3_widgetpos_box');
		var attrs = m3_splitAttr(obj.attr('m3'));
		var pattrs = m3_splitAttr(parent.attr('m3'));
		var param;
		
		// 変更先のウィジェット
		var insertIndex = -1;
		var widgets = '';
		var children = parent.children('.m3_widget_sortable');
		for (var i = 0; i < children.length; i++){
			var child = $(children[i]);
			var wattr = m3_splitAttr(child.attr('m3'));
			
			if (task == 'move'){
				if (wattr['serial'] == attrs['serial']){
					insertIndex = i;
					if (!pos) widgets += wattr['serial'] + ',';
				} else {
					widgets += wattr['serial'] + ',';
				}
			} else {
				if (wattr['serial']) widgets += wattr['serial'] + ',';
			}
		}
		widgets = widgets.substr(0, widgets.length -1);
		
		// 更新するポジションID
		var updatepos = '';
		for (var i = 0; i < M3_POSITIONS.length; i++){
			var posObj = $(M3_POSITIONS[i]);
			var battrs = m3_splitAttr(posObj.attr('m3'));
			if (task == 'move'){
				if (battrs['pos'] == pattrs['pos'] || battrs['pos'] == pos) updatepos += posObj.attr('id') + ',';
			} else {
				if (battrs['pos'] == pattrs['pos']) updatepos += posObj.attr('id') + ',';
			}
		}
		updatepos = updatepos.substr(0, updatepos.length -1);

		// 送信パラメータ
		if (task == 'get'){
			param = '&task=wget' + '&serial=' + attrs['serial'] + '&shared=' + attrs['shared'] +
					'&pos=' + pattrs['pos'] + '&widgets=' + widgets + '&updatepos=' + updatepos + '&rev=' + M3_REVISION;
		} else if (task == 'toggle'){
			param = '&task=wtoggle' + '&serial=' + attrs['serial'] + '&shared=' + attrs['shared'] +
					'&pos=' + pattrs['pos'] + '&widgets=' + widgets + '&updatepos=' + updatepos + '&rev=' + M3_REVISION;
		} else if (task == 'delete'){
			param = '&task=wdelete' + '&serial=' + attrs['serial'] + '&shared=' + attrs['shared'] +
					'&pos=' + pattrs['pos'] + '&widgets=' + widgets + '&updatepos=' + updatepos + '&rev=' + M3_REVISION;
		} else if (task == 'add'){
			var idHead = 'm3widgettype_';
			var widgetId = widgetType.substr(idHead.length);
			param = '&task=wadd' + '&widget=' + widgetId + '&index=' + index + 
					'&pos=' + pattrs['pos'] + '&widgets=' + widgets + '&updatepos=' + updatepos + '&rev=' + M3_REVISION;
		} else if (task == 'move'){
			var position = pattrs['pos'];
			if (pos) position += ',' + pos;
			param = '&task=wmove' + '&serial=' + attrs['serial'] + '&index=' + insertIndex + 
					'&pos=' + position + '&widgets=' + widgets + '&updatepos=' + updatepos + '&rev=' + M3_REVISION;
		}
		//alert(param);
		$.ajax({	url: createUrl() + param,
					type:		'get',
					success:	function(data, textStatus){
									var children = jQuery(data).children('.m3_widgetpos_box');
									for (var i = 0; i < children.length; i++){
										var child = $(children[i]);
										if (i == 0){
											// リビジョン番号更新
											var attrs = m3_splitAttr(child.attr('m3'));
											M3_REVISION = attrs['rev'];
										}
										$('#' + child.attr('id')).replaceWith(child);
									}
									setupSortable();
				
									// 関連画面を更新
									if (window.parent.m3UpdateByChildWindow) window.parent.m3UpdateByChildWindow();
								},
					error:		function(request, textStatus, errorThrown){
									$("#m3_message").html('<font color="red">通信エラー</font>');
								}
		});
	};
	var setupSortable = function(){
		var els = M3_POSITIONS;
		var $els = $(els.toString());
		var urlParams = m3_getUrlParam(document.URL);
		var page = urlParams['_page'];
		var sub = urlParams['_sub'];
		
		// チェックボックス追加
		$('dt.m3_widget_with_check_box', $els).append('<span class="options"><a class="m3_widget_close">close</a></span>');
		$('a.m3_widget_close').bind('click', widgetClose);
		
		//$els.sortable('destroy');		// 一旦削除
		$els.sortable({
			items: '> dl',
			handle: 'dt',
			cursor: 'move',
		/*	cursorAt: { top: 0, left: 0 },		// selection bug? */
			opacity: 0.5,
			helper: 'clone',
			appendTo: 'body',
			placeholder: 'm3_spacer',
			connectWith: els,
			start: function(e,ui) {
				ui.helper.css("width", ui.item.width());
				
				$('#m3_widgetlist_inner').data('scroll', $('#m3_widgetlist_inner').scrollTop());
				$('#m3_widget_window').hide(1000);
			},
			stop: function(e,ui) {
				$('#m3_widget_window').show(500, function(){
					$('#m3_widgetlist_inner').scrollTop($('#m3_widgetlist_inner').data('scroll'));
				});
			},
//			change: changeSortable,
			update: updateSortable
		});
		
		// コンテキストメニューを作成
		$('.m3_widget_sortable').contextMenu('m3_widgetmenu', {
			menuStyle: {
				// border : "2px solid green",
				backgroundColor: '#FFFFFF',
				width: "150px",
				textAlign: 'left',
				font: '12px/1.5 Arial, sans-serif'
			},
			itemStyle: {
				padding: '3px 3px'
			},
			bindings: {
				'm3_wconfig': function(t){
					var attrs = m3_splitAttr($('#' + t.id).attr('m3'));
				    if (attrs['useconfig'] == '0'){
				        alert("このウィジェットには設定画面がありません");
						return;
				    }
					m3ShowConfigWindow(attrs['widgetid'], attrs['configid'], attrs['serial']);
				},
				'm3_wadjust': function(t){
					var attrs = m3_splitAttr($('#' + t.id).attr('m3'));
					m3ShowAdjustWindow(attrs['configid'], attrs['serial'], page, sub);
				},
				'm3_wshared': function(t){
					m3TaskWidget('toggle', $('#' + t.id));
				},
				'm3_wdelete': function(t){
					var widget = $('#' + t.id);
					widget.fadeOut('slow', function(){
						m3TaskWidget('delete', $(this));
						$(this).remove();
					});
				}
			},
			onContextMenu: function(e) {
				var attrs = m3_splitAttr($(e.target).parents('dl').attr('m3'));
				if (attrs['shared'] == '0'){	// 共通ウィジェットでない
					$('#m3_wshared span').text('ページ共通属性を設定');
				} else {
					$('#m3_wshared span').text('ページ共通属性を解除');
				}
				return true;
			},
			onShowMenu: function(e, menu) {
				// メニュー項目の変更
				var attrs = m3_splitAttr($(e.target).parents('dl').attr('m3'));
				if (attrs['useconfig'] == '0'){
					$('#m3_wconfig', menu).remove();
				}
				return menu;
			}
		});
	};
	
/*	var changeSortable = function(e, ui){
		if (ui.sender){
			var w = ui.item.width();
			ui.placeholder.width(w);
			ui.helper.css("width", w);
		}
	};*/
	
	var updateSortable = function(e, ui){
		var obj = $('#' + ui.item[0].id);
		var recvPosObj = obj.parents('.m3_widgetpos_box');	// 移動先ポジション
		var pos = '';
		if (recvPosObj.attr('id') == $(this).attr('id')){
			if (ui.sender){
				var senderObj = $('#' + ui.sender[0].id);
				var attrs = m3_splitAttr(senderObj.attr('m3'));
				pos = attrs['pos'];
			}
			m3TaskWidget('move', obj, '', -1, pos);
		}
	};
	
/*	var dragChange = function(e, ui){
		if (ui.sender){
			var w = ui.item.width();
			ui.placeholder.width(w);
			ui.helper.css("width",ui.item.children().width());
		}
		$('dl.m3_widget_sortable').css("margin-top", '100px');
	};*/
	var updateWidgetList = function(){
		$('dl.m3_widgetlist_item').draggable({
			helper: 'clone',
			cursor: 'move',
/*			cursorAt: { top: 0, left: 0 },	// selection bug? */
			opacity: 0.5,
			appendTo: 'body',
			drag: function(e, ui){
				var objects = $('.m3_widgetpos_box');
				if (objects.length > 0){
					for (var i = 0; i < objects.length; i++){
						var widget = $(objects[i]);
						if (mouseInDrag(widget, e)) return;
					}
				}
			},
			start: function(e, ui){
				$('#m3_widgetlist_inner').data('scroll', $('#m3_widgetlist_inner').scrollTop());
				$('#m3_widget_window').hide(1000);
			},
			stop: function(e, ui){
				//m3TaskWidget('add', $('.m3_spacer'), e.target.id, m3InsertIndex);// NG in IE
				m3TaskWidget('add', $('.m3_spacer'), this.id, m3InsertIndex);
				//$('.m3_spacer').remove();
				
				$('#m3_widget_window').show(500, function(){
					$('#m3_widgetlist_inner').scrollTop($('#m3_widgetlist_inner').data('scroll'));
				});
			}
		});
	}
	// ウィジェットリストからのドラッグ処理
	var mouseInDrag = function(obj, e){
		var width = obj.width();
		var height = obj.height();
		var left = obj.offset().left;
		var top = obj.offset().top;

		if (left <= e.pageX && e.pageX <= left + width &&
			top <= e.pageY && e.pageY <= top + height){

			// ドロップする位置
			var children = obj.children('.m3_widget_sortable');
			var spacePos = -1;
			for (var i = 0; i < children.length; i++){
				var child = $(children[i]);
				if (child.hasClass('m3_spacer')){
					spacePos = i;
					break;
				}
			}
			
			var insertPos = -1;
			var i = 0;
			var play = 0;
			for (i = 0; i < children.length; i++){
				var child = $(children[i]);
				var childLeft = child.offset().left;
				var childTop = child.offset().top;
				var childWidth = child.width();
				var childHeight = child.height();

				play = 0;
				if (spacePos != -1){
					if (i == spacePos - 1){
						play = -10;
					} else if (i == spacePos + 1){
						play = 10;
					} else if (i == spacePos){
						if (childLeft <= e.pageX && e.pageX <= childLeft + childWidth &&
							childTop <= e.pageY && e.pageY <= childTop + childHeight){
							break;
						}
					}
				}
				if (e.pageY < child.offset().top + childHeight / 2 + play){
					break;
				}
			}
			var spacing = true;
			if (spacePos == -1){
				insertPos = i;
			} else {
				if (insertPos < spacePos){
					insertPos = i;
				} else {
					if (insertPos == spacePos || insertPos == spacePos +1){
						spacing = false;
					} else {
						insertPos = i -1;
					}
				}
			}
			if (spacing){
				$('.m3_spacer').remove();
				
				children = obj.children('.m3_widget_sortable');
				
				var spacerHtml = '<div class="m3_spacer"></div>';
				if (insertPos < children.length){
					$(children[insertPos]).before(spacerHtml);
				} else {
					obj.append(spacerHtml);
				}
				m3InsertIndex = insertPos;
			}
			return true;
		} else {
			return false;
		}
	};
	var createUrl = function(){
		var url;
		var urlParams = m3_getUrlParam(document.URL);
		var page = urlParams['_page'];
		var sub = urlParams['_sub'];
		url = M3_DEFAULT_ADMIN_URL + '?cmd=getwidgetinfo' + '&_page=' + page + '&_sub=' + sub;
		return url;
	};
	$(document).ready(function(){
		var widgetWindow = '<div id="m3_widget_window">';
		widgetWindow += '<table border="1px" width="250" bgcolor="#424242" cellspacing="0">';
		widgetWindow += '<tr>';
		widgetWindow += '<td width="100%">';
		widgetWindow += '<table border="0" width="100%" cellspacing="0" cellpadding="0">';
		widgetWindow += '<tr>';
		widgetWindow += '<td width="100%">';
		widgetWindow += '<font color="#FFFFFF">ウィジェット</font>';
		widgetWindow += '</td>';
		widgetWindow += '</tr>';
		widgetWindow += '<tr>';
		widgetWindow += '<td width="100%" bgcolor="#FFFFFF" style="padding:4px">';
		widgetWindow += '<div id="m3_widget_window_inner">';
		widgetWindow += '<div id="m3_message"></div>';
		widgetWindow += '<div id="m3_widgetlist" class="m3_widget_tab">';
		//widgetWindow += '<h2>ウィジェット</h2>';
		widgetWindow += '<div id="m3_widgetlist_inner" class="m3_widgetlist_box">';
		widgetWindow += '</div>';	// widgetlist_box end
		widgetWindow += '</div>';	// widgetlist end
		widgetWindow += '</div>';	// m3_widget_window_inner end
		widgetWindow += '</td>';
		widgetWindow += '</tr>';
		widgetWindow += '</table>';
		widgetWindow += '</td>';
		widgetWindow += '</tr>';
		widgetWindow += '</table>';
		widgetWindow += '</div>';
		// コンテキストメニュー
		widgetWindow += '<div class="m3_contextmenu" id="m3_widgetmenu" style="visibility:hidden;">';
		widgetWindow += '<ul>';
		widgetWindow += '<li id="m3_wadjust"><img src="' + M3_ROOT_URL + '/images/system/adjust_widget.png" />&nbsp;<span>タイトル・位置調整</span></li>';
		widgetWindow += '<li id="m3_wconfig"><img src="' + M3_ROOT_URL + '/images/system/config.png" />&nbsp;<span>ウィジェットの設定</span></li>';
		widgetWindow += '<li id="m3_wshared"><img src="' + M3_ROOT_URL + '/images/system/shared.png" />&nbsp;<span>ページ共通属性</span></li>';
		widgetWindow += '<li id="m3_wdelete"><img src="' + M3_ROOT_URL + '/images/system/delete.png" />&nbsp;<span>このウィジェットを削除</span></li>';
		widgetWindow += '</ul>';
		widgetWindow += '</div>';
		$("body").append(widgetWindow);
		
		$('#m3_widget_window').draggable({
			cursor: 'move',
			cancel: '#m3_widget_window_inner'
		});
	
		setupSortable();
		
		$.ajax({	url: createUrl() + '&task=list',
					type:		'get',
					success:	function(data, textStatus){
									$("#m3_widgetlist_inner").html(data);
									updateWidgetList();
								},
					error:		function(request, textStatus, errorThrown){
									$("#m3_message").html('<font color="red">通信エラー</font>');
								}
		});
		$('div.m3_widgetpos_box').droppable({
			accept: 'dl.m3_widgetlist_item',
			out: function(e, ui){
				$('.m3_spacer').remove();
			}
		});
		
		// テキスト選択停止(Safari,Chromeのウィジェットドラッグ中のテキスト選択の問題を回避)
		document.onselectstart = function(){ return false; }
	});// ready end
})(jQuery);
