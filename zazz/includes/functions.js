function trim(string) {
	return string.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
}

function doStuff() {
	$(document).ready(function() {

		/*---------------------------------------Foundational Code--------------------------------------*/

		/**
		 * Centers an object relative to parent div. Useful for centering on a div on screen as long as that
		 * div is a direct child of body.
		 * @returns {$.fn}
		 */
		$.fn.center = function() {
			this.css("position", "absolute");
			this.css("top", Math.max(0, (($(window).height() - $(this).outerHeight()) / 2) +
					$(window).scrollTop()) + "px");
			this.css("left", Math.max(0, (($(window).width() - $(this).outerWidth()) / 2) +
					$(window).scrollLeft()) + "px");
			return this;
		};



		$.fn.promote = function() {
			var rank = this.attr("promote-rank");
			if (typeof(rank) === "undefined")
				rank = 0;
			rank++;
			this.attr("promote-rank", rank);
			if (rank === 1)
				this.css('display', 'inline-block');
			return this;
		};

		$.fn.demote = function() {
			var rank = this.attr("promote-rank");
			if (typeof(rank) === "undefined")
				rank = 0;
			rank--;
			this.attr("promote-rank", rank);
			if (rank === 0)
				this.hide();
			return this;
		};

		/**
		 * Prototype for action associated with view divs and buttons. Registers three functions for a 
		 * button: a function to be called on click (initialize), a function to be called when a view div 
		 * is clicked (action), and a function to be called when another button is clicked (clean). action 
		 * is the only one that is passed arguments, which are: (1) the jQuery object of the clicked div,
		 * (2) the Click Event as in .click(function(event){...}), (3) a boolean denoting if this is the
		 * first click while this button has been selected.
		 * 
		 * @param {function} initialize
		 * @param {function} action
		 * @param {function} clean
		 * @returns {$.fn}
		 */
		$.fn.register = function(initialize, action, clean) {
			var myClass = $(this).attr('class').split(' ')[0];
			//Save functions.
			$.fn.register.initialize[myClass] = initialize;
			$.fn.register.action[myClass] = action;
			$.fn.register.clean[myClass] = clean;
			//Register click event for button.
			$(this).click(function() {
				var myClass = $(this).attr('class').split(' ')[0];
				//We want to check to see if this button is already selected and don't do anything if it is.
				if (myClass !== $.fn.register.current) {
					$.fn.register.first = true;
					//Remove the active-btn class from the old button if possible.
					if ($.fn.register.current !== null) {
						$('.' + $.fn.register.current).removeClass('-zazz-active-btn');
						$(this).register.clean[$.fn.register.current]();
					}
//Make button "active".
					$.fn.register.current = myClass;
					$(this).addClass('-zazz-active-btn');
					$(this).register.initialize[$.fn.register.current]();
				}
			});
			return this;
		};
		//Denotes if this is the first element click for the current action.
		$.fn.register.first = true;
		$.fn.register.current = null;
		$.fn.register.initialize = [];
		$.fn.register.action = [];
		$.fn.register.clean = [];
		/**
		 * A function to reset application back to no buttons being selected.
		 * @returns {$.fn.register}
		 */
		$.fn.register.reset = function() {
			if ($.fn.register.current !== null) {
				$.fn.register.clean[$.fn.register.current]();
				$.fn.register.current = null;
			}
			return this;
		};
		//Register the click event for the actions.
		$('.-zazz-content-view').click(function(e) {
			if ($.fn.register.current !== null) {
				$.fn.register.action[$.fn.register.current]($(e.target).closest('.-zazz-element'),
						e, $.fn.register.first);
				$.fn.register.first = false;
			}
		});
		/**
		 * A helper function to specify a drag action on an object. All functions take as a parameter a
		 * Click Event.
		 * @param {function} start
		 * @param {function} during
		 * @param {function} stop
		 * @returns {$.fn}
		 */
		$.fn.drag = function(start, during, stop) {
			var myClass = $(this).attr('class').split(' ')[0];
			$(this).mousedown(function(e) {
//This if statement is to prevent text boxes from being unselectable.
				if (!$(e.target).is('input')) {
					var myClass = $(this).attr('class').split(' ')[0];
					$.fn.drag.condition[myClass] = true;
					$(document).on('mousemove', $.fn.drag.during[myClass]);
					start(e);
					return false;
				}
			});
			$(this).mouseup(function(e) {
				var myClass = $(this).attr('class').split(' ')[0];
				$.fn.drag.condition[myClass] = false;
				$(document).off('mousemove', $.fn.drag.during[myClass]);
				stop(e);
				return false;
			});
			$.fn.drag.during[myClass] = during;
			return this;
		};
		$.fn.drag.condition = [];
		$.fn.drag.during = [];
		/*-------------------------------------FUNCTIONS-----------------------------------------*/

		function confirm(header, message, callback) {
			$('#-zazz-modal-confirm .-zazz-modal-header')[0].innerHTML = header;
			$('#-zazz-modal-confirm .-zazz-modal-body')[0].innerHTML = message;
			$('#-zazz-modal-confirm .-zazz-modal-button')[0].onclick = callback;
			$('#-zazz-modal-confirm').center().show();
		}

		function warn(header, message, options) {
			if (typeof options === 'undefined') {
				options = '';
			}
			$('#-zazz-modal-alert .-zazz-modal-body').html(message);
			$('#-zazz-modal-alert .-zazz-modal-header').html(header);
			$('#-zazz-modal-alert').attr("style", options).center().show();
		}

		function updateLayout() {
			//Easier to get rid of this here then on server.
			if (typeof $.last_div !== 'undefined') {
				$.last_div.css('box-shadow', '');
			}
			var code = $('.-zazz-content').attr('data-zazz-rid', $.row_id).attr('data-zazz-gid', $.group_id)
					.attr('data-zazz-eid', $.element_id)[0].outerHTML;
			$.post('/zazz/ajax/layout.php', {
				page_id: $('#-zazz-page-id').val(),
				layout: code
			});
			if (typeof $.last_div !== 'undefined') {
				setBoxShadow($.last_div);
			}
		}

		$.codeActions = {
			INSERT: 0,
			DELETE: 1,
			MOVE: 2,
			UNLINK: 3,
			UPDATE: 4
		};

		function updateCode(zazz_id, $block, type, codeAction) {
			if (typeof doDelete === 'undefined') {
				doDelete = false;
			}
			if (typeof doMove === 'undefined') {
				doMove = false;
			}
			var array = {
				zazz_id: zazz_id,
				type: type,
				page_id: $('#-zazz-page-id').val(),
				zazz_order: $block.attr('data-zazz-order')
			};
			if (codeAction === $.codeActions.DELETE) {
				array['deleted'] = true;
				array['code'] = $block.val();
			} else if (codeAction === $.codeActions.MOVE) {
				array['moveTo'] = $.move.to;
				array['zazz_order'] = $.move.from;
			} else if (codeAction === $.codeActions.INSERT) {
				array['insert'] = true;
				array['code'] = $block.val();
			} else if (codeAction === $.codeActions.UNLINK) {
				array['unlink'] = true;
			} else if (codeAction === $.codeActions.UPDATE) {
				array['insert'] = false;
				array['code'] = $block.val();
			}
			$('#-zazz-loader-bar').promote();
			$.post('/zazz/ajax/code.php', array,
					function(data) {
						$('#-zazz-loader-bar').demote();
						var contentHeight = $('.-zazz-content').outerHeight();
						if (zazz_id === 'begin-project' || zazz_id === 'end-project' || zazz_id === 'begin-web-page' || zazz_id ===
								'end-web-page') {
							if (type === 'html') {
								//Modifying the html modifes the potential scripts and stylesheets included,
								//so just refresh the page rather that try to fix that.
								location.reload();
							}
							$('.-zazz-content-view').first().html(data);
						} else {
							$('.-zazz-element[data-zazz-id="' + zazz_id + '"]').html(data);
							$('.-zazz-code-block-' + zazz_id).filter('.-zazz-js-code').each(function() {
								addJSCode($(this).val());
							});
						}
						//The following fixes the content height while images are loading to prevent the page 
						//from scrolling somewhere else.
						var $content = $('.-zazz-content');
						$content.css('height', contentHeight);
						$(data).load(function() {
							$content.css('height', '');
							showWidthAndHeight();
						});
					});
		}

		function getBlockType($this) {
			var type;
			if ($this.hasClass('-zazz-html-code')) {
				type = 'html';
			} else if ($this.hasClass('-zazz-css-code')) {
				type = 'css';
			} else if ($this.hasClass('-zazz-mysql-code')) {
				type = 'mysql';
			} else if ($this.hasClass('-zazz-php-code')) {
				type = 'php';
			} else if ($this.hasClass('-zazz-js-code')) {
				type = 'js';
			}
			return type;
		}

		function getZazzID($block) {
			var classes = $block.attr('class').split(/\s+/);
			var id;
			for (var i = 0; i < classes.length; i++) {
				if (classes[i].indexOf('-zazz-code-block-') >= 0) {
					id = classes[i].substring(17);
				}
			}
			return id;
		}

		//A somewhat fix for textarea scrolling.
		function textareaScroll() {
			//if(!$.ignoreCodeFocus) {
			var $this = $(this);
			var $scroller = $this.parent().parent();
			var scrollOffset = $scroller.children(':visible').first().offset().left;
			var left = $this.offset().left - scrollOffset;
			var width = $this.outerWidth();
			var scrollLeft = $scroller.scrollLeft();
			var scrollWidth = $scroller.outerWidth();
			if (left < scrollLeft) {
				//alert('Left: ' + left + ' Scroll Left: ' + scrollLeft + ' Scroll Offset: ' +scrollOffset);
				$scroller.scrollLeft(left);
			} else if (scrollLeft + scrollWidth < left + width) {
				//Add enough to get textarea fully into view.
				//alert('SL: ' + scrollLeft + ' SW: ' + scrollWidth + ' L: ' + left + ' W: ' + width);
				$scroller.scrollLeft(scrollLeft + left + width - scrollWidth);
			}
			//};
		}

		function addCSSCode(zazz_id, code) {
			var id = "-zazz-css-code-" + zazz_id;
			if ($('#' + id).length === 0) {
				var $style = $('<style></style>').attr("id", id).html(code);
				$('head').append($style);
			} else {
				$('#' + id).html(code);
			}
		}

		function addJSCode(code) {
			/*
			 var id = "-zazz-js-code-" + zazz_id;
			 var script = document.createElement("script");
			 script.id = id;
			 script.type = "text/javascript";
			 script.text = code;
			 document.body.appendChild(script);
			 */
			try {
				eval(code);
			} catch (e) {
				if (!$('#-zazz-modal-alert').is(':visible')) {
					warn("Error", "There was an error in your JavaScript: <br>" + e.message);
				}
			}
		}

		$('.-zazz-modal-close').click(function() {
			$(this).closest('.-zazz-modal').fadeOut(300);
		});
		/*-----------------------------------------FOCUS CODE------------------------------------------*/

		function warnUnload() {
			window.onbeforeunload = function() {
				return "You have unsaved code that will be lost if you " +
						"continue to navigate away. You will need the text area you were working on to lose focus " +
						"in order for your code to be saved.";
			};
		}

		function computeCodeHeight($textarea) {
			if ($textarea.hasClass('-zazz-css-code')) {
				return;
			}
			var value = $textarea.val();
			var count = 1;
			var start = value.indexOf('\n');
			while (start >= 0) {
				count++;
				start = value.indexOf('\n', start + 1);
			}

			var height = parseInt($textarea.css('line-height')) * count +
					parseInt($textarea.css('padding-top')) + parseInt($textarea.css('padding-bottom'));
			if (parseInt($textarea.css('height')) !== height) {
				$textarea.css('height', height);
				return true;
			}
			return false;
		}

		$(document).on('input propertychange', '.-zazz-code-block', function() {
			$.changed = true;
			warnUnload();
			if (computeCodeHeight($(this))) {
				computeCodeLayout();
			}
		}).on('keypress', function(e) {
			if (e.keyCode === 8 || e.which === 8 || e.keyCode === 46 || e.which === 46) {
				$.changed = true;
				warnUnload();
				if (computeCodeHeight($(this))) {
					computeCodeLayout();
				}
			}
		});
		$(document).on('focus', 'textarea', textareaScroll);
		$(document).on('blur', '.-zazz-css-code', function() {
			addCSSCode($.last_div.attr("data-zazz-id"), $(this).val());
		});
		$(document).on('blur', '.-zazz-js-code', function() {
			//addJSCode($.last_div.attr("data-zazz-id"), $(this).val());
		});
		$(document).on('blur', '.-zazz-html-code', function() {
			//$('#' + $.last_div.attr('data-zazz-id')).html($(this).val());
		});
		$(document).on('blur', '.-zazz-code-block', function() {
			var type;
			var $this = $(this);
			var type = getBlockType($this);
			var zazz_id = $.last_div.attr('data-zazz-id');
			window.onbeforeunload = null;
			if ($.changed && !$.ignoreCodeFocus) {
				updateCode(zazz_id, $this, type, $.codeActions.UPDATE);
				$.changed = false;
			}
			$('.-zazz-editor-container').hide();
			$this.attr('data-zazz-cursor', $this.prop("selectionStart")).attr('data-zazz-scroll', $this.scrollTop());
		}).on('focus', '.-zazz-code-block', function() {
			var $textarea = $(this);
			var position = parseInt($textarea.attr('data-zazz-cursor'));
			setTimeout(function() {
				var cursor = $textarea.prop("selectionStart");
				if (cursor === 0) {
					$textarea[0].setSelectionRange(position, position);
					//Doesn't appear to update scroll automatically.
					$textarea.scrollTop(parseInt($textarea.attr('data-zazz-scroll')));
				}
			}, 1);

			if ($textarea.hasClass('-zazz-html-code') && $.last_div.attr('data-zazz-id') !== "begin-project" &&
					$.last_div.attr('data-zazz-id') !== "end-project") {
				$.htmlEdit = $textarea;
				$('.-zazz-editor-container').css('display', 'inline-block');
			}
		});

		function setBoxShadow($div) {
			//#ffff4f
			$div.css('box-shadow', '0px 0px 100px #aaaaaa inset');
		}

		$.codeColumns = 0;
		function addCodeColumn() {
			var $div = $('<div></div>').attr('id', '-zazz-code-column-' + $.codeColumns)
					.addClass('-zazz-code-column');
			$('.-zazz-code-blocks').append($div);
			$.codeColumns++;
			return $div;
		}

		$.ignoreCodeFocus = false;
		function computeCodeLayout(zazz_id, keepFocus) {
			//If true then computeCodeLayout() has already been called.
			if ($.ignoreCodeFocus) {
				return;
			}			
			if (typeof zazz_id === 'undefined') {
				zazz_id = $.last_div.attr('data-zazz-id');
			}
			var curr = 0;
			var $column = $('#-zazz-code-column-0');
			var columnHeight = 0;
			var maxHeight = $('.-zazz-code-blocks').outerHeight() - 10;
			var $focus = $(':focus');
			$.ignoreCodeFocus = true;
			$(".-zazz-code-block-" + zazz_id).each(function() {
				var $this = $(this);
				if (!$this.hasClass('-zazz-css-code')) {
					var height = $this.outerHeight();
					if (columnHeight === 0 || columnHeight + height < maxHeight) {
						$column.append($this).show();
						columnHeight += height;
					} else {
						curr++;
						var $next = $('#-zazz-code-column-' + curr);
						if ($next.length === 0) {
							$next = addCodeColumn();
						}
						$column = $next;
						$column.append($this).show();
						columnHeight = height;
					}
				}
			});
			curr++;
			var nextColumn = $('#-zazz-code-column-' + curr);
			while (nextColumn.length !== 0) {
				nextColumn.hide();
				curr++;
				nextColumn = $('#-zazz-code-column-' + curr);
			}
			if(keepFocus) {
				$focus.focus();
			}
			$.ignoreCodeFocus = false;
		}

		function showWidthAndHeight() {
			var $div = $.last_div;
			if (!$div.parent().hasClass('-zazz-hidden')) {
				var width = getCSSWidth($div);
				var end;
				if (width.indexOf('%') >= 0) {
					end = '%';
				} else {
					end = 'px';
				}
				var height = $div.css('min-height');
				var actualHeight = $div.outerHeight();
				if (parseInt(height) !== actualHeight) {
					height += " (" + actualHeight + "px)";
				}
				$('.-zazz-fixed-status-btn').html('W: ' +
						(Math.floor(parseFloat(width) * 100) / 100) + end +
						" H: " + height);
			} else {
				$('.-zazz-fixed-status-btn').html("W: 0 H: 0");
			}
		}

		//The callback for when the focus is changed among divs.
		$(document).on('focus', '.-zazz-element', function() {
			var $div = $(this);
			//Set up the outline. 
			//if(typeof $.last_div !== 'undefined') {
			//	$.last_div.css('box-shadow', '');
			//}
			if (typeof $.last_div !== 'undefined') {
				$.last_div.css('box-shadow', '');
			}
			//$div.css('box-shadow', '0px 0px 100px #ffff00 inset');
			//Also see update layout.
			setBoxShadow($div);

			var id = $div.attr('data-zazz-id');
			//Change the ID and class text boxes.
			$(".-zazz-id-input").val($div.attr('id'));
			$(".-zazz-class-input").val($div.attr('class').replace('-zazz-element', '')
					.replace('-zazz-outline', ''));
			//Show the correct code boxes.
			if (typeof $.last_div === "undefined" || $div.attr('data-zazz-id') !== $.last_div.attr('data-zazz-id')) {
				$(".-zazz-code-block").hide();
				$(".-zazz-code-block-" + id).fadeIn().each(function() {
					var $this = $(this);
					if (!$this.hasClass('-zazz-css-code')) {
						$this.css("display", "block");
						computeCodeHeight($this);
					}
				});
			}

			computeCodeLayout(id);

			$.last_div = $div;

			showWidthAndHeight();

			return false;
		});
		function updatePageInfo(redirect) {
			var page = $('#-zazz-page-name').val();
			$('#-zazz-loader-bar').promote();
			$.post('/zazz/ajax/page.php', {
				page: page,
				page_id: $('#-zazz-page-id').val(),
				visible: ($('#-zazz-page-visible').val() === 'Yes' ? '1' : '0')
			}, function(data) {
				$('#-zazz-loader-bar').demote();
				if (trim(data) !== "") {
					$('#-zazz-modal-settings .-zazz-modal-message').html(data);
					$('#-zazz-modal-settings').show();
				} else if (redirect) {
					window.location.replace('/zazz/build/' + $('#-zazz-project-name').val() + '/' + page);
				}
			});
		}

		function updateProjectInfo(redirect) {
			var array;
			if (redirect) {
				array = {
					project: $('#-zazz-project-name').val(),
					page_id: $('#-zazz-page-id').val()
				};
			} else {
				array = {
					default_page: $('#-zazz-default-page').val(),
					page_id: $('#-zazz-page-id').val()
				};
			}
			$('#-zazz-loader-bar').promote();
			$.post('/zazz/ajax/project.php', array, function(data) {
				$('#-zazz-loader-bar').demote();
				if (trim(data) !== "") {
					$('#-zazz-modal-project .-zazz-modal-message').html(data);
					$('#-zazz-modal-project').show();
				} else if (redirect) {
					window.location.replace('/zazz/build/' + project + '/' + $('#-zazz-page-name').val());
				}
			});
		}

		$('#-zazz-page-height').blur(function() {
			var $this = $(this);
			if (trim($this.val()) !== '' && $this.val() !== $this.attr('data-zazz-old-height')) {
				var multiply = parseInt($this.val()) / parseInt($this.attr('data-zazz-old-height'));
				$('.-zazz-element').each(function() {
					var $this = $(this);
					$this.css('min-height', Math.round(parseInt($this.css('min-height')) * multiply));
				});
				$this.attr('data-zazz-old-height', parseInt($this.val()));
			}
			updateLayout();
		});

		$('#-zazz-project-name').blur(function() {
			updateProjectInfo(true);
		});
		$('#-zazz-page-name').blur(function() {
			updatePageInfo(true);
		});
		$('#-zazz-page-visible').change(function() {
			updatePageInfo(false);
		});
		$('#-zazz-default-page').change(function() {
			updateProjectInfo(false);
		});
		$('#-zazz-view-btn').click(function() {
			if ($('#-zazz-page-visible').val() === 'No') {
				warn('Warning', 'You cannot view a page that is not visible.');
				return false;
			}
		});

		$('#-zazz-deploy-link').click(function() {
			$(this).attr('href', '/zazz/view/' + $('#-zazz-project-name').val() + '/' +
					$('#-zazz-page-name').val() + '?deploy=' +
					encodeURIComponent($('#-zazz-deploy-password').val()));
		});
		/*--------------------------------------------Mouse Code----------------------------------------*/

		$(document).mousemove(function(e) {
			$.mouse_x = e.pageX;
			$.mouse_y = e.pageY;
		});
		$('.-zazz-content-view').mousemove(function(e) {
			var $target = $(e.target);
			if ($target.hasClass('-zazz-outline')) {
				$target = $.last_div;
			}
			var offset_x = (e.offsetX || e.clientX - $target.offset().left);
			var offset_y = (e.offsetY || e.clientY - $target.offset().top);
			var targetHeight = $target.outerHeight();
			var targetWidth = $target.outerWidth();
			var bodyHeight = $('body').outerHeight();
			var bodyWidth = $('body').outerWidth();
			var $modal = $('#-zazz-modal-mouse');
			var $view = $('.-zazz-content-view');
			var pageHeight = $('.-zazz-content').outerHeight();
			var viewTop = $view.offset().top;
			var viewHeight = $view.outerHeight();
			if (e.pageX > bodyWidth / 2) {
				$modal.css('left', '0').css('right', '');
			} else {
				$modal.css('right', '0').css('left', '');
			}
			if (e.pageY > viewTop + viewHeight / 2) {
				$modal.css('top', viewTop).css('bottom', '');
			} else {
				$modal.css('bottom', bodyHeight - (viewTop + viewHeight)).css('top', '');
			}

			$('#-zazz-modal-mouse-offset').html('<td>Offset (px):</td><td>(' + offset_y + ',</td><td>' +
					($target.outerWidth() - offset_x) + ',</td><td>' + ($(e.target).outerHeight() - offset_y) + ',</td><td>'
					+ offset_x + ')</td>');
			$('#-zazz-modal-mouse-location').html('<td>Page (px):</td><td>(' + (e.pageY - viewTop) +
					',</td><td>' + (bodyWidth - e.pageX) + ',</td><td>' + (pageHeight - (e.pageY - viewTop)) + ',</td><td>' +
					e.pageX + ')</td>');
			$('#-zazz-modal-mouse-offsetp').html('<td>Offset (%):</td><td>(' +
					Math.round(offset_y / targetHeight * 10000) / 100 + ',</td><td>' +
					Math.round((targetWidth - offset_x) / targetWidth * 10000) / 100 + ',</td><td>' +
					Math.round((targetHeight - offset_y) / targetHeight * 10000) / 100 + ',</td><td>' +
					Math.round(offset_x / targetWidth * 10000) / 100 + ')</td>');
			$('#-zazz-modal-mouse-locationp').html('<td>Page (%):</td><td>(' +
					Math.round((e.pageY - viewTop) / pageHeight * 10000) / 100 + ',</td><td>' +
					Math.round((bodyWidth - e.pageX) / bodyWidth * 10000) / 100 + ',</td><td>' +
					Math.round((pageHeight - (e.pageY - viewTop)) / pageHeight * 10000) / 100 + ',</td><td>' +
					Math.round(e.pageX / bodyWidth * 10000) / 100 + ')</td>');
		});
		function textareaMouseMove(e) {
			var $this = $(this);
			var myPos = $this.offset();
			myPos.bottom = $this.offset().top + $this.outerHeight();
			myPos.right = $this.offset().left + $this.outerWidth();
			myPos.left = $this.offset().left;
			if (myPos.bottom > e.pageY && e.pageY > myPos.bottom - 15 && myPos.right > e.pageX &&
					e.pageX > myPos.right - 15) {
				$this.css({cursor: "e-resize"});
			} else if (!$this.hasClass('-zazz-css-code') &&
					18 + myPos.top > e.pageY && e.pageY > myPos.top && myPos.left + 18 > e.pageX &&
					e.pageX > myPos.left) {
				$this.css({cursor: "pointer"});
			} else {
				$this.css({cursor: ""});
			}
		}

		function confirmUnlock($textarea) {
			var $this = $textarea;
			var id = $.last_div.attr('data-zazz-id');
			confirm('Warning', 'This code is linked to the template page (' + $('#-zazz-template-name').html()
					+ ') and cannot be edited. ' +
					'Zazz can unlink the code so that you can edit it, but then changes to the template will ' +
					'not propagate to ' + id + ' of this page.',
					function() {
						updateCode(id, $this, getBlockType($this), $.codeActions.UNLINK);
						$('.-zazz-code-block-' + id).each(function() {
							$(this).removeClass('-zazz-code-locked').removeAttr('readonly');
						});
						$('#-zazz-modal-confirm').hide();
					});
			return;
		}

		function textareaClick(e) {
			var $this = $(this);
			if ($this.hasClass('-zazz-code-locked')) {
				confirmUnlock($this);
			}

			var myPos = $this.offset();
			myPos.left = $this.offset().left;
			if (18 + myPos.top > e.pageY && e.pageY > myPos.top && myPos.left + 18 > e.pageX &&
					e.pageX > myPos.left) {
				var $block = $this;
				var id = getZazzID($block);
				confirm('Warning', 'Continuing will delete this code block permanently.', function() {
					updateCode(id, $block, getBlockType($block), $.codeActions.DELETE);
					$this.remove();
					$('#-zazz-modal-confirm').hide();
					computeCodeLayout();
				});
			}
		}

		$(".-zazz-code-blocks")
				.on('mousemove', '.-zazz-code-block', textareaMouseMove)
				.on('click', '.-zazz-code-block', textareaClick);
		//$("textarea").mousemove(textareaMouseMove);
		//$("textarea").click(textareaClick);

		$(".-zazz-code-area .-zazz-navbar").not('input[type="text"]').drag(
				function() {
				},
				function(e) {
					var offset = 8;
					if ($('.-zazz-divide-navbar').is(":visible")) {
						offset = 40;
					}
					$(".-zazz-code-area").height(
							($('body').height() - (e.pageY - offset)) / $('body').height() * 100 + '%');
					$(".-zazz-view").height((e.pageY - offset) / $('body').height() * 100 + '%');
					$(document).css('cursor: n-resize');
				},
				function() {
					computeCodeLayout();
				}
		).on("selectstart", false).on("dragstart", false);
		function acrossMouse(e) {
			$('.-zazz-horizontal-line-left').css('top', e.pageY).css('right', $('body').width() -
					(e.pageX - 10));
			$('.-zazz-horizontal-line-right').css('top', e.pageY).css('left', e.pageX + 10);
			$('#-zazz-location-display').html('( ' + e.pageX + ' , ' + e.pageY + ' ) ');
		}

		function acrossMouseEnter() {
			$(".-zazz-horizontal-line-left").show();
			$(".-zazz-horizontal-line-right").show();
			$('body').css('cursor', 'crosshair');
			$("#-zazz-modal-mouse").show();
		}

		function acrossMouseLeave() {
			$(".-zazz-horizontal-line-left").hide();
			$(".-zazz-horizontal-line-right").hide();
			$('body').css('cursor', 'default');
			$("#-zazz-modal-mouse").hide();
		}

		function verticalMouse(e) {
			$('.-zazz-vertical-line-top').css('left', e.pageX).css('bottom', $('body').height() -
					(e.pageY - 10));
			$('.-zazz-vertical-line-bottom').css('left', e.pageX).css('top', e.pageY + 10);
		}

		function verticalMouseEnter() {
			$(".-zazz-vertical-line-top").show();
			$(".-zazz-vertical-line-bottom").show();
			$('body').css('cursor', 'crosshair');
			$("#-zazz-modal-mouse").show();
		}

		function verticalMouseLeave() {
			$(".-zazz-vertical-line-top").hide();
			$(".-zazz-vertical-line-bottom").hide();
			$('body').css('cursor', 'default');
			$("#-zazz-modal-mouse").hide();
		}

		$('.-zazz-id-input').blur(function() {
			if (typeof $.last_div !== "undefined") {
				$.last_div.attr("id", $(this).val());
			}
		});
		$('.-zazz-class-input').blur(function() {
			if (typeof $.last_div !== "undefined") {
				$.last_div.attr("class", $(this).val() + ' -zazz-element -zazz-outline');
			}
		});

		$('.-zazz-database-btn').click(function() {
			$('#-zazz-modal-database').center().show();
		});

		$('#-zazz-modal-database-edit').click(function() {
			$('#-zazz-modal-database-form').submit();
		});

		function moveCode(e) {
			var $target = $(e.target);
			if ($target.hasClass('-zazz-code-block')) {
				if (typeof $.move.from === "undefined") {
					if ($target.hasClass("-zazz-css-code")) {
						warn('Warning', 'Cannot move CSS block.');
					} else {
						$.move.from = $target;
					}
				} else if ($target[0] !== $.move.from[0]) {
					$.move.to = parseInt($target.attr('data-zazz-order')) + 1;
					var $textarea = $.move.from;
					$.move.from = $textarea.attr('data-zazz-order');
					updateCode($.last_div.attr('data-zazz-id'), $textarea, getBlockType($textarea),
							$.codeActions.MOVE);
					if ($.move.to === 1) {
						$('#-zazz-code-column-0').prepend($textarea);
					} else {
						$textarea.insertAfter($target);
					}
					$textarea.attr('data-zazz-order', parseInt($target.attr('data-zazz-order')) + 1);
					$textarea.nextAll().each(function() {
						$(this).attr('data-zazz-order', parseInt($(this).attr('data-zazz-order')) + 1);
					});
					delete $.move.to;
					delete $.move.from;
					$(document).off('click', moveCode);
					$('.-zazz-move-btn').removeClass('-zazz-active-btn');
				}
				return false;
			} else if (!$target.hasClass('-zazz-move-btn')) {
				if (typeof $.move.from === 'undefined') {
					delete $.move.from;
				}
				$(document).off('click', moveCode);
				$('.-zazz-move-btn').removeClass('-zazz-active-btn');
			}
		}

		$.move = new Array();
		$('.-zazz-move-btn').click(function() {
			$(this).addClass('-zazz-active-btn');
			$(document).on('click', moveCode);
		});

		/*-----------------------------------------Compatibility----------------------------------------*/

		//< IE10 needs this.
		//$('.-zazz-navbar span, .-zazz-navbar').each(function() {
		//	$(this).attr('unselectable', 'on');
		//});

		/*------------------------------------------Button Code-----------------------------------------*/

		$('.-zazz-select-btn').register(
				function() {
				},
				function() {
				},
				function() {
				}
		);
		function createDiv(id, type) {
			var div = $('<div></div>').addClass(type).attr("id", id);
			if (type === "-zazz-element") {
				div.attr('tabindex', '10').attr("data-zazz-id", id);
				addCSSCodeBlock(id);
			}
			return div;
		}

		function getCSSWidth($div, forcePercent) {
			var $parent = $div.parent();
			if (typeof forcePercent === "undefined") {
				forcePercent = false;
			}
			if (forcePercent || $div.css('z-index') === 'auto') {
				var parentWidth = $parent.outerWidth() - parseInt($parent.css('padding-left')) -
						parseInt($parent.css('padding-right'));
				return ($div.outerWidth() / parentWidth * 100) + '%';
			} else {
				return $div.outerWidth() + 'px';
			}
		}

		$('.-zazz-vertical-btn').register(
				function() {
					$('.-zazz-content-view').on('mousemove', verticalMouse);
					$('.-zazz-content-view').on('mouseenter', verticalMouseEnter);
					$('.-zazz-content-view').on('mouseleave', verticalMouseLeave);
					$("#-zazz-fixed").css('display', 'inline-block');
					$('#-zazz-fixed-vertical').show();
				},
				function($div, e) {
					var fixedWidth = (parseInt($div.css('z-index')) > 0 ? true : false);
					var split = $('#-zazz-fixed-vertical').val();
					if (fixedWidth && split !== 'Both') {
						warn('Warning', 'You cannot have a child element that has a dynamic width for a fixed ' +
								'width element.');
						return;
					}
					if (!fixedWidth && split === 'Both') {
						warn('Warning', 'You cannot have both children elements have fixed width for a dynamic ' +
								'width element.');
						return;
					}

					var divWidth = $div.outerWidth();
					var mouseOffsetLeft = Math.round(e.pageX - $div.offset().left);
					var mouseOffsetRight = Math.round(divWidth - mouseOffsetLeft);
					var $other_div = createDiv('element-' + $.element_id, '-zazz-element');
					$.element_id++;
					if (fixedWidth) {
						$div.css('width', mouseOffsetLeft);
						$other_div.css('width', mouseOffsetRight).css('z-index', '1');
						$other_div.insertAfter($div);
					} else {
						var $container;
						if (split === 'Left' || split === 'Right') {
							if ($div.parent().hasClass('-zazz-container')) {
								$container = $div.parent();
							} else {
								$container = $('<div></div>').addClass('-zazz-container').css('width',
										$div.outerWidth() / $div.parent().outerWidth() * 100 + '%');
								$div.css('width', '');
								$container.insertAfter($div);
								$container.append($div);
							}
						}

						if (split === 'Left') {
							$container.css('padding-left', parseInt($container.css('padding-left')) + mouseOffsetLeft)
									.css('margin-left', parseInt($container.css('margin-left')) - mouseOffsetLeft);
							$other_div.css('width', mouseOffsetLeft).css('z-index', '1');
							$other_div.insertBefore($container);
						} else if (split === 'Right') {
							$container.css('padding-right', parseInt($container.css('padding-right')) + mouseOffsetRight)
									.css('margin-right', parseInt($container.css('margin-right')) - mouseOffsetRight);
							$other_div.css('width', mouseOffsetRight).css('z-index', '1');
							$other_div.insertAfter($container);
						} else if (split === 'None') {
							$container = $div.parent();
							var parentWidth = $container.outerWidth() - parseInt($container.css('padding-left'))
									- parseInt($container.css('padding-right'));
							var percentOffset = mouseOffsetLeft / parentWidth * 100;
							var percentWidth = $div.outerWidth() / parentWidth * 100;
							$div.css('width', percentOffset + '%');
							$other_div.css('width', (percentWidth - percentOffset) + '%');
							$other_div.insertAfter($div);
						}
					}

					$other_div.css("min-height", $div.css('min-height'));
					$div.focus();
					updateLayout();
				},
				function() {
					verticalMouseLeave();
					$('.-zazz-content-view').off('mouseenter', verticalMouseEnter);
					$('.-zazz-content-view').off('mouseleave', verticalMouseLeave);
					$('.-zazz-content-view').off('mousemove', verticalMouse);
					$("#-zazz-fixed").hide();
					$('#-zazz-fixed-vertical').hide();
				}
		);
		$('.-zazz-across-btn').register(
				function() {
					$('.-zazz-content-view').on('mousemove', acrossMouse);
					$('.-zazz-content-view').on('mouseenter', acrossMouseEnter);
					$('.-zazz-content-view').on('mouseleave', acrossMouseLeave);
				},
				function($div, e) {
					//First move div into another div (possibly with a class of row) and then copy the row.
					var $row_group;
					var $row;
					var $other_row;
					var $parent = $div.parent();
					//Check to see if the row has only one div.
					if ($parent.children().length === 1 && $parent.hasClass('-zazz-row')) {
						//If so, then the row group and the row are obvious.
						$row_group = $parent.parent();
						$row = $parent;
					} else {
						//Otherwise we need to create a new row group and insert it where the div was with the right
						//width. This also requires creating a new row to place the div into and placing that row 
						//into the row group.
						$row_group = createDiv('row-group-' + $.group_id, '-zazz-row-group');
						//Need to figure out if width is supposed to be % or px because 
						//$row_group.css('width', $div.css('width')); only sets it to px.
						$row_group.css('width', getCSSWidth($div));
						$div.css('width', '');
						$row_group.insertAfter($div);
						$row = createDiv('row-' + $.row_id, '-zazz-row');
						$.group_id++;
						$.row_id++;
						$row_group.append($row);
						$row.append($div);
					}

					//Make a new row and insert the new row into the row group.
					$other_row = createDiv('row-' + $.row_id, '-zazz-row');
					var $other_div = createDiv('element-' + $.element_id, '-zazz-element');
					$other_row.append($other_div);
					$.row_id++;
					$.element_id++;
					$other_row.insertAfter($row);
					//Now we need to fix the heights of the divs by splitting across the point that was clicked.
					var old_height = $div.outerHeight();
					var top = $div.offset().top;
					var mouseOffsetTop = Math.round(e.pageY - top);
					var mouseOffsetBottom = Math.round(old_height - (e.pageY - top));
					$div.css('min-height', mouseOffsetTop);
					$other_div.css('min-height', mouseOffsetBottom);
					if ($div.css('z-index') !== 'auto') {
						$other_div.css('z-index', $div.css('z-index'));
					}
					$div.focus();
					updateLayout();
				},
				function() {
					acrossMouseLeave();
					$(".-zazz-content-view").off('mousemove', acrossMouse);
					$('.-zazz-content-view').off('mouseenter', acrossMouseEnter);
					$('.-zazz-content-view').off('mouseleave', acrossMouseLeave);
				}
		);
		$('.-zazz-absorb-btn').register(
				function() {
					//$('.-zazz-element').css('cursor', 'pointer');
				},
				function(div, e, changed) {
					if (typeof this.first_div === 'undefined' || changed) {
						this.first_div = div;
					} else if (div[0] !== this.first_div[0]) {
						var $second_div = $(div);
						var second_div_id = $second_div.attr('data-zazz-id');
						var $first_div = $(this.first_div);
						var $second_parent = $second_div.parent();
						var $first_parent = $first_div.parent();
						var $second_grandparent = $second_parent.parent();
						var $first_grandparent = $first_parent.parent();
						//Check to see if they are in the same container or row.
						if (($first_parent.hasClass('-zazz-container') &&
								$second_parent.hasClass('-zazz-container')) ||
								($first_parent.hasClass('-zazz-row') &&
										$second_parent.hasClass('-zazz-row')) &&
								$second_parent.get(0) === $first_parent.get(0)) {
							//Remove the second div clicked on and expand the first div.
							var first_width = getCSSWidth($first_div);
							var second_width = getCSSWidth($second_div);
							$second_div.remove();
							//Either both have fixed width or both have percentage width.
							if (parseInt($first_div.css('z-index')) > 0 && parseInt($second_div.css('z-index')) > 0) {
								$first_div.css("width", (parseInt(first_width) + parseInt(second_width)) + 'px');
							} else {
								$first_div.css("width", (parseFloat(first_width) + parseFloat(second_width)) + '%');
							}
							//Else check to see if rows are in the same row, but not in the same container.
						} else if ($first_parent.hasClass('-zazz-container') &&
								$second_parent.hasClass('-zazz-row') &&
								$second_parent.get(0) === $first_grandparent.get(0)) {
							var second_width_px = $second_div.outerWidth();
							var second_index = $second_div.index();

							$second_div.remove();

							//If this ends up removing all other elements in the row, then we need to remove the 
							//container.
							if ($first_grandparent.children().length === 1) {
								$first_parent.children().each(function() {
									$first_grandparent.append($(this));
								});
								$first_parent.remove();
							} else {
								//Else just fix the padding.
								if (second_index < $first_parent.index()) {
									$first_parent.css('margin-left', parseInt($first_parent.css('margin-left'))
											+ second_width_px);
									$first_parent.css('padding-left', parseInt($first_parent.css('padding-left'))
											- second_width_px);
								} else {
									$first_parent.css('margin-right', parseInt($first_parent.css('margin-right'))
											+ second_width_px);
									$first_parent.css('padding-right', parseInt($first_parent.css('padding-right'))
											- second_width_px);
								}
							}
							//Else check to see if rows are in the same row, but not in the same container.
							//Note that above assumes that the first_div is in the container, while this 
							//assumes that the second_div is in the container.
						} else if ($second_parent.hasClass('-zazz-container') &&
								$first_parent.hasClass('-zazz-row') &&
								$first_parent.get(0) === $second_grandparent.get(0)) {
							warn('Error', 'You cannot absorb a dynamic width element into a fixed width element.');
							//Else check to see if they are in the same column.
						} else if ($second_grandparent.get(0) === $first_grandparent.get(0)) {
							if ($second_parent.children().length !== 1 || $first_parent.children().length !== 1) {
								warn('Error', "Both rows must only have one element before you can combine them.");
							} else {
								//Remove the second div clicked on and expand the first div.
								$first_div.css("min-height", parseInt($second_div.css('min-height')) +
										parseInt($first_div.css('min-height')));
								$second_div.parent().remove();
								//Get the column that contains the row that contains the first div.
								var $row_group = $first_div.parent().parent();
								//We need to check to see if the row-group only has one row, and if so reduce the document
								//structure. Also check for corner case of only one element in document.
								if ($row_group.children().length === 1 &&
										!$row_group.parent().hasClass('-zazz-content')) {
									$first_div.css("width", getCSSWidth($row_group));
									$first_div.insertBefore($row_group);
									$row_group.remove();
								}
							}
							//Else give error message.
						} else {
							warn("Error", "These elements are neither in the same row or column.");
						}
						//Delete absorbed code blocks.
						$('.-zazz-code-block-' + second_div_id).each(function() {
							updateCode(second_div_id, $(this), getBlockType($(this)), $.codeActions.DELETE);
						});
						//Set focus and delete the stored first div.
						$first_div.focus();
						delete this.first_div;
						updateLayout();
					}
				},
				function() {
					$('.-zazz-element').css('cursor', '');
				}
		);
		$('#-zazz-edit-page-btn').click(function() {
			$('#-zazz-modal-settings').show().center();
		});
		$('.-zazz-project-btn').click(function() {
			var pos = $(this).offset();
			$('#-zazz-dropdown-project').show().css('top', pos.top + $(this).outerHeight())
					.css('left', pos.left);
		}).blur(function() {
			setTimeout(function() {
				if (document.activeElement.id === '-zazz-edit-project-btn') {
					$('#-zazz-modal-project').show().center();
				} else if (document.activeElement.id === '-zazz-new-project-btn') {
					$('#-zazz-modal-new-project').show().center();
				} else if (document.activeElement.id === '-zazz-switch-project-btn') {
					$('#-zazz-modal-view-projects').show().center();
				} else if (document.activeElement.id === '-zazz-delete-project-btn') {
					if ($("#-zazz-modal-view-pages .-zazz-links a").length >= 1) {
						confirm('Are you sure?', 'By deleting this project, you will remove all code and pages.',
								function() {
									$('#-zazz-loader-bar').promote();
									$.post('/zazz/ajax/project.php', {
										page_id: $('#-zazz-page-id').val(),
										deleted: 'true'
									}, function() {
										window.location.href = "/zazz/index.php";
									});
								});
					} else {
						warn('Warning', 'You cannot delete your only project.');
					}
				}
				$('#-zazz-dropdown-project').hide();
			}, 1);
		});
		$('.-zazz-build-btn').click(function() {
			var pos = $(this).offset();
			$('#-zazz-dropdown-build').show().css('top', pos.top + $(this).outerHeight())
					.css('left', pos.left);
		}).blur(function() {
			setTimeout(function() {
				//alert(document.activeElement.id);
				if (document.activeElement.id === '-zazz-deploy-project-btn') {
					$('#-zazz-modal-deploy-confirm').center().show();
					$('#-zazz-dropdown-build').hide();
				} else if (document.activeElement.id === '-zazz-view-btn') {
					setTimeout(function() {
						$('#-zazz-dropdown-build').hide();
					}, 500);
				} else if (document.activeElement.id === '-zazz-export-btn') {
					setTimeout(function() {
						$('#-zazz-dropdown-build').hide();
					}, 500);
				} else {
					$('#-zazz-dropdown-build').hide();
				}
			}, 1);
		});
		$('#-zazz-make-new-project').click(function() {
			$('#-zazz-loader-bar').promote();
			$.post('/zazz/ajax/project.php', {
				create: $('#-zazz-new-project-name').val()
			}, function(data) {
				$('#-zazz-loader-bar').demote();
				if (trim(data) !== "") {
					$('#-zazz-modal-new-project .-zazz-modal-message').html(data);
				} else {
					window.location.href = "/zazz/build/" + $('#-zazz-new-project-name').val() + "/";
				}
			});
		});
		$('.-zazz-page-btn').click(function() {
			var pos = $(this).offset();
			$('#-zazz-dropdown-page').show().css('top', pos.top + $(this).outerHeight())
					.css('left', pos.left);
		}).blur(function() {
			setTimeout(function() {
				if (document.activeElement.id === '-zazz-edit-page-btn') {
					$('#-zazz-modal-settings').show().center();
				} else if (document.activeElement.id === '-zazz-new-page-btn') {
					$('#-zazz-modal-new-page').show().center();
				} else if (document.activeElement.id === '-zazz-switch-page-btn') {
					$('#-zazz-modal-view-pages').show().center();
				} else if (document.activeElement.id === '-zazz-delete-page-btn') {
					if ($("#-zazz-modal-view-pages .-zazz-links a").length >= 1) {
						confirm('Are you sure?', 'By deleting this page, you will remove all code.',
								function() {
									$('#-zazz-loader-bar').promote();
									$.post('/zazz/ajax/page.php', {
										page_id: $('#-zazz-page-id').val(),
										deleted: 'true'
									}, function(data) {
										$('#-zazz-loader-bar').demote();
										$('#-zazz-modal-confirm').hide();
										if (trim(data) !== '') {
											warn('Error', data);
										} else {
											window.location.href = "/zazz/index.php";
										}
									});
								});
					} else {
						warn('Warning', 'You cannot delete your only page.');
					}
				}
				$('#-zazz-dropdown-page').hide();
			}, 1);
		});
		$('#-zazz-make-new-page').click(function() {
			$('#-zazz-loader-bar').promote();
			$.post('/zazz/ajax/page.php', {
				page_id: $('#-zazz-page-id').val(),
				create: $('#-zazz-new-page-name').val(),
				template: $('#-zazz-page-template').val()
			}, function(data) {
				$('#-zazz-loader-bar').demote();
				if (trim(data) !== "") {
					$('#-zazz-modal-new-page .-zazz-modal-message').html(data);
				} else {
					window.location.href = "/zazz/build/" + $('#-zazz-project-name').val() +
							'/' + $('#-zazz-new-page-name').val();
				}
			});
		});
		$('#-zazz-upload-filename').focus(function() {
			$('#-zazz-upload-file').click();
			$(this).blur();
		});
		$('#-zazz-upload-file').change(function() {
			var filename = $(this).val();
			$('#-zazz-upload-filename').val(filename);
			var index = filename.lastIndexOf('/');
			if (index < 0) {
				index = filename.lastIndexOf('\\');
				if (index < 0) {
					$('#-zazz-upload-filename').val(filename);
					return;
				}
			}
			filename = filename.slice(index - filename.length + 1);
			$('#-zazz-upload-server').val(filename);
		});
		$('.-zazz-upload-btn').click(function() {
			$('#-zazz-modal-upload').center().show();
		});
		$('#-zazz-upload-do-it').click(function() {
			$('#-zazz-upload-name').val($('#-zazz-upload-server').val());
			$('#-zazz-upload-page-id').val($('#-zazz-page-id').val());
			$('#-zazz-upload-form').submit();
		});
		function addCodeBlock(className, forID) {
			var $locked = $('.-zazz-code-block:visible').first();
			if ($locked.hasClass('-zazz-code-locked')) {
				confirmUnlock($locked);
				return;
			}

			var $forID = $('.-zazz-element[data-zazz-id="' + forID + '"]');
			var order;
			if ($forID.length === 0) {
				order = '0';
			} else {
				order = parseInt($('.-zazz-code-block-' + forID).last().attr('data-zazz-order')) + 1;
			}
			var $textarea = $('<textarea></textarea>').addClass('-zazz-code-block')
					.addClass(className).addClass('-zazz-code-block-' + forID).attr('spellcheck', false)
					.attr('tabindex', '10').attr('data-zazz-order', order).attr('wrap', 'off');
			return $textarea;
		}

		$('.-zazz-editor-btn').click(function() {
			tinymce.get('-zazz-html-editor').setContent($.htmlEdit.val());
			$('#-zazz-modal-html-editor').center().show();
		});

		$('#-zazz-html-editor-code').click(function() {
			$.htmlEdit.html(tinymce.get('-zazz-html-editor').getContent());
			$('#-zazz-modal-html-editor').hide();
			updateCode($.last_div.attr('data-zazz-id'), $.htmlEdit, 'html', $.codeActions.UPDATE);
			computeCodeHeight($.htmlEdit);
			computeCodeLayout();
		});

		function addCodeButton(type) {
			var id = $.last_div.attr("data-zazz-id");
			var $block = addCodeBlock('-zazz-' + type + '-code', id);
			$('.-zazz-code-blocks').append($block);
			computeCodeHeight($block);
			$block.fadeIn().css('display', 'block').focus();
			updateCode(id, $block, type, $.codeActions.INSERT);
			computeCodeLayout($.last_div.attr('data-zazz-id'), false);
		}

		$('.-zazz-html-btn').click(function(e) {
			addCodeButton('html');
		});
		$('.-zazz-php-btn').click(function(e) {
			addCodeButton('php');
		});
		$('.-zazz-mysql-btn').click(function(e) {
			addCodeButton('mysql');
		});
		$('.-zazz-js-btn').click(function(e) {
			addCodeButton('js');
		});
		function addCSSCodeBlock(id) {
			var $block = addCodeBlock('-zazz-css-code', id);
			$block.val('#' + id + ' {\n\n' + '}');
			$('.-zazz-code-blocks').prepend($block);
			$block.hide();
			updateCode(id, $block, 'css', $.codeActions.INSERT);
		}

		function start() {
			addCodeColumn();

			var $content = $('.-zazz-content');
			$('.-zazz-element').first().focus();
			$('.-zazz-select-btn').click();
			$.row_id = $content.attr('data-zazz-rid');
			$.group_id = $content.attr('data-zazz-gid');
			$.element_id = $content.attr('data-zazz-eid');
			$('.-zazz-css-code').each(function() {
				var $this = $(this);
				addCSSCode(getZazzID($this), $this.val());
			});
			$('.-zazz-js-code').each(function() {
				var $this = $(this);
				addJSCode($this.val());
			});
			$('#-zazz-page-height').val($('.-zazz-content').outerHeight()).attr('data-zazz-old-height',
					$('.-zazz-content').outerHeight());
			if (jQueryScriptOutputted) {
				warn('Error',
						'Could not find jQuery file specified in HTML header. Loaded a copy from Zazz instead.');
			}

			tinymce.init({
				height: 400,
				selector: "#-zazz-html-editor",
				plugins: [
					"advlist autolink lists link charmap print preview anchor",
					"searchreplace visualblocks code fullscreen",
					"insertdatetime media table contextmenu paste"
				],
				toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
			});
		}

		start();

		/*--------------------------------------Keyboard Shortcuts--------------------------------------*/

		$(document).keyup(function(e) {
			if (e.which === 13 || e.keyCode === 13) {
				var $modal = $('.-zazz-modal:visible');
				if ($modal.length !== 0) {
					$modal = $modal.first();
					var $buttons = $modal.children('.-zazz-modal-footer').children('.-zazz-modal-button');
					if ($buttons.length !== 0) {
						$buttons.first().click();
					} else {
						$modal.children('.-zazz-modal-footer').children('.-zazz-modal-close').first().click();
					}
				} else {
					var $focus = $(':focus');
					if (!$focus.is('textarea')) {
						$focus.click();
					} else {
						//Autoindent
						setTimeout(function() {
							var position = $focus.prop("selectionStart");
							var text = $focus.val();
							var start = text.substr(0, position);
							var end = text.substr(position, text.length);
							var lines = start.split("\n");
							var lastLine = lines[lines.length - 2];
							var whitespace = lastLine.match(/^\s*/)[0];
							$focus.val(start + whitespace + end);
							$focus[0].setSelectionRange(start.length + whitespace.length,
									start.length + whitespace.length);
						}, 1);
					}
				}
			}

			if (e.keyCode === 27 || e.which === 27) {
				var $modal = $('.-zazz-modal:visible');
				if ($modal.length !== 0) {
					$modal.children('.-zazz-modal-footer').children('.-zazz-modal-close').first().click();
				}
				$('.-zazz-select-btn').click();
			}
			if ((e.keyCode === 120 || e.which === 120) && e.ctrlKey) {
				$('.-zazz-select-btn').focus();
			}
			if ((e.keyCode === 121 || e.which === 121) && e.ctrlKey) {
				$.last_div.focus();
			}
			if ((e.keyCode === 122 || e.which === 122) && e.ctrlKey) {
				$('.-zazz-id-input').focus();
			}
			if ((e.keyCode === 123 || e.which === 123) && e.ctrlKey) {
				var $parent = $('.-zazz-code-blocks');
				var $children = $parent.children(':visible');
				while ($children.length !== 0) {
					$parent = $children.first();
					$children = $parent.children(':visible');
				}
				$parent.focus();
			}
		});

		if ($('#-zazz-bad-html').val()) {
			warn('Error', 'There was an error when Zazz tried to combine the HTML entered for begin-project, ' +
					'end-project, begin-web-page, end-web-page, which forced Zazz to use a default HTML frame ' +
					'instead. Please examine the HTML for those elements so that your HTML frame can be loaded.');
		}
	});
}

var jQueryScriptOutputted = false;
function initJQuery() {
	if (typeof(jQuery) === 'undefined') {
		if (!jQueryScriptOutputted) {
			jQueryScriptOutputted = true;
			document.write('<script type="text/javascript" src="/zazz/js/jquery-1.10.2.js">' +
					'</script><script src="/zazz/js/tinymce.min.js" type="text/javascript"></script>');
		}
		setTimeout("initJQuery()", 50);
	} else {
		doStuff();
	}

}
initJQuery();
