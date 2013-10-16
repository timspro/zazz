function trim(string) {
	return string.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
}

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
	$('.-zazz-content').click(function(e) {
		if ($.fn.register.current !== null) {
			$.fn.register.action[$.fn.register.current]($(e.target), e, $.fn.register.first);
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
		$('#-zazz-modal-confirm .-zazz-modal-message')[0].innerHTML = message;
		$('#-zazz-modal-confirm .-zazz-modal-button')[0].onclick = callback;
		$('#-zazz-modal-confirm').center().show();
	}

	function warn(header, message, options) {
		if(typeof options === 'undefined') {
			options = '';
		}
		$('#-zazz-modal-alert .-zazz-modal-body').html(message);
		$('#-zazz-modal-alert .-zazz-modal-header').html(header);
		$('#-zazz-modal-alert').attr("style", options).center().show();
	}

	function updateLayout() {
		var code = $('.-zazz-content').attr('_zazz-rid', $.row_id).attr('_zazz-gid', $.group_id)
			.attr('_zazz-eid', $.element_id)[0].outerHTML;
		$.post('/zazz/ajax/layout.php', {
			page_id: $('#-zazz-page-id').val(),
			layout: code
		});
	}

	function updateCode(zazz_id, $block, type, insert) {
		if($('#-zazz-is-demo').val()) {
				var html = '';
				$('.-zazz-code-block-' + zazz_id).filter('.-zazz-html-code').each(function() {
					html += $(this).val();
				});
				$('.-zazz-element[_zazz-id="' + zazz_id + '"]').html(html);
				$('.-zazz-code-block-' + zazz_id).filter('.-zazz-js-code').each(function() {
					addJSCode($(this).val());			
				});
		} else {
			$.post('/zazz/ajax/code.php', {
				zazz_id: zazz_id,
				type: type,
				code: $block.val(),
				page_id: $('#-zazz-page-id').val(),
				zazz_order: $block.attr('_zazz-order'),
				insert: insert
			},
			function(data) {
				$('.-zazz-element[_zazz-id="' + zazz_id + '"]').html(data);
				$('.-zazz-code-block-' + zazz_id).filter('.-zazz-js-code').each(function() {
					addJSCode($(this).val());
				});
				updateLayout();
			});
		}
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
		$(this)[0].scrollIntoView();
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
		eval(code);
	}

	$('.-zazz-modal-close').click(function() {
		$(this).closest('.-zazz-modal').fadeOut(300);
	});

	/*-----------------------------------------FOCUS CODE------------------------------------------*/

	$(document).on('input propertychange', '.-zazz-code-block', function() {
		$.changed = true;
	}).on('keypress', function(e) {
		if (e.keyCode === 8 || e.which === 8 || e.keyCode === 46 || e.which === 46) {
			$.changed = true;
		}
	});

	$(document).on('focus', 'textarea', textareaScroll);
	$(document).on('blur', '.-zazz-css-code', function() {
		addCSSCode($.last_div.attr("_zazz-id"), $(this).val());
	});
	$(document).on('blur', '.-zazz-js-code', function() {
		//addJSCode($.last_div.attr("_zazz-id"), $(this).val());
	});
	$(document).on('blur', '.-zazz-html-code', function() {
		//$('#' + $.last_div.attr('_zazz-id')).html($(this).val());
	});
	$(document).on('blur', '.-zazz-code-block', function() {
		var type;
		var $this = $(this);
		var type = getBlockType($this);

		var zazz_id = $.last_div.attr('_zazz-id');
		if ($.changed) {
			updateCode(zazz_id, $this, type, false);
			$.changed = false;
		}
	});


	//The callback for when the focus is changed among divs.
	$(document).on('focus', '.-zazz-element', function() {
		var $div = $(this);
		//Set up the outline. Unfortunately, this is the best way to do this so that children elements
		//aren't moved.
		var $container = $(".-zazz-outline-top").parent();
		var offset_top = $div.offset().top - $container.offset().top;
		var offset_left = $div.offset().left;
		$(".-zazz-outline").show();
		$(".-zazz-outline-left").css("top", offset_top).css("left", offset_left)
			.css("height", $div.outerHeight());
		$(".-zazz-outline-right").css("top", offset_top)
			.css("left", offset_left + $div.outerWidth() - 4).css("height", $div.outerHeight());
		$(".-zazz-outline-top").css("top", offset_top)
			.css("left", offset_left).css("width", $div.outerWidth());
		$(".-zazz-outline-bottom").css("top", offset_top + $div.outerHeight() - 4)
			.css("left", offset_left).css("width", $div.outerWidth());
		var id = $div.attr('_zazz-id');
		//Change the ID and class text boxes.
		$(".-zazz-id-input").val($div.attr('id'));
		$(".-zazz-class-input").val($div.attr('class').replace('-zazz-element', ''));
		//Show the correct code boxes.
		if (typeof $.last_div === "undefined" || $div.attr('_zazz-id') !== $.last_div.attr('_zazz-id')) {
			$(".-zazz-code-block").hide();
			$(".-zazz-code-block-" + id).fadeIn(300);
		}
		$.last_div = $div;
		return false;
	});

	function updatePageInfo(redirect) {
		var page = $('#-zazz-page-name').val();
		$.post('/zazz/ajax/page.php', {
			page: page,
			background_image: $('#-zazz-background-image').val(),
			page_id: $('#-zazz-page-id').val()
		}, function(data) {
			if(trim(data) !== "") {
				$('#-zazz-modal-settings .-zazz-modal-message').html(data);
				$('#-zazz-modal-settings').show();
			} else if (redirect) {
				window.location.replace('/zazz/build/' + $('#-zazz-project-name').val() + '/' + page);
			}
		});
	}

	function updateProjectInfo(redirect) {
		var project = $('#-zazz-project-name').val();
		$.post('/zazz/ajax/project.php', {
			project: project,
			page_id: $('#-zazz-page-id').val()
		}, function(data) {
			if(trim(data) !== "") {
				$('#-zazz-modal-project .-zazz-modal-message').html(data);
				$('#-zazz-modal-project').show();
			} else if (redirect) {
				window.location.replace('/zazz/build/' + project + '/' + $('#-zazz-page-name').val());
			}
		});
	}

	$('#-zazz-project-name').blur(function() {
		updateProjectInfo(true);
	});
	
	$('#-zazz-default-page').blur(function(){
		$.post('/zazz/ajax/project.php', {
			page_id: $('#-zazz-page-id').val(),
			default_page: $('#-zazz-default-page').val()
		}, function(data) {
			if(trim(data) !== "") {
				$('#-zazz-modal-project .-zazz-modal-message').html(data);
				$('#-zazz-modal-project').show();
			}
		});	
	});

	$('#-zazz-page-name').blur(function() {
		updatePageInfo(true);
	});

	$('#-zazz-background-image').blur(function() {
		$('.-zazz-content').first().css('background-image', 'url(' + $(this).val() + ')');
		updateLayout();
		updatePageInfo(false);
	});

	/*--------------------------------------------Mouse Code----------------------------------------*/

	$(document).mousemove(function(e) {
		$.mouse_x = e.pageX;
		$.mouse_y = e.pageY;
	});

	$('.-zazz-content').mousemove(function(e) {
		var offset_x = (e.offsetX || e.clientX - $(e.target).offset().left);
		var offset_y = (e.offsetY || e.clientY - $(e.target).offset().top);
		$('.-zazz-offset-btn').html('Offset: ( T' + offset_y + ', L' + offset_x + ', B' +
			($(e.target).outerHeight() - offset_y) + ', R'
			+ ($(e.target).outerWidth() - offset_x) + ' )');
		$('.-zazz-location-btn').html('Location: ( T' + e.pageY + ' , L' + e.pageX + ', B' +
			($('body').outerHeight() - e.pageY) + ', R' + ($('body').outerWidth() - e.pageX) + ' )');
	});

	function textareaMouseMove(e) {
		var myPos = $(this).offset();
		myPos.bottom = $(this).offset().top + $(this).outerHeight();
		myPos.right = $(this).offset().left + $(this).outerWidth();
		myPos.left = $(this).offset().left;

		if (myPos.bottom > e.pageY && e.pageY > myPos.bottom - 15 && myPos.right > e.pageX &&
			e.pageX > myPos.right - 15) {
			$(this).css({cursor: "e-resize"});
		} else if (18 + myPos.top > e.pageY && e.pageY > myPos.top && myPos.left + 18 > e.pageX &&
			e.pageX > myPos.left) {
			$(this).css({cursor: "pointer"});
		} else {
			$(this).css({cursor: "text"});
		}
	}

	function textareaMouseMoveNoRemove(e) {
		var myPos = $(this).offset();
		myPos.bottom = $(this).offset().top + $(this).outerHeight();
		myPos.right = $(this).offset().left + $(this).outerWidth();

		if (myPos.bottom > e.pageY && e.pageY > myPos.bottom - 15 && myPos.right > e.pageX &&
			e.pageX > myPos.right - 15) {
			$(this).css({cursor: "e-resize"});
		} else {
			$(this).css({cursor: "text"});
		}
	}

	function textareaClick(e) {
		var myPos = $(this).offset();
		myPos.left = $(this).offset().left;
		if (18 + myPos.top > e.pageY && e.pageY > myPos.top && myPos.left + 18 > e.pageX &&
			e.pageX > myPos.left) {
			var $block = $(this);
			var id = getZazzID($block);
			$.post('/zazz/ajax/code.php', {
				zazz_id: id,
				type: getBlockType($block),
				page_id: $('#-zazz-page-id').val(),
				zazz_order: $block.attr('_zazz-order'),
				delete: true
			},
			function(data) {
				$('#' + id).html(data);
				$('.-zazz-code-block-' + id).filter('.-zazz-js-code').each(function() {
					addJSCode($(this).val());
				});
				updateLayout();
			});
			$(this).remove();
		}
	}

	var noCSS = ".-zazz-html-code, .-zazz-php-code, .-zazz-mysql-code, .-zazz-js-code";
	$(".-zazz-code-blocks").on('focus', noCSS, textareaScroll)
		.on('mousemove', noCSS, textareaMouseMove)
		.on('click', noCSS, textareaClick)
		.on('mousemove', ".-zazz-css-code", textareaMouseMoveNoRemove);
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
				Math.round(($('body').height() - (e.pageY - offset)) / $('body').height() * 100) + '%');
			$(".-zazz-view").height(Math.round((e.pageY - offset) / $('body').height() * 100) + '%');
			$(document).css('cursor: n-resize');
		},
		function() {
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
		$(".-zazz-display").css('display', 'block');
	}

	function acrossMouseLeave() {
		$(".-zazz-horizontal-line-left").hide();
		$(".-zazz-horizontal-line-right").hide();
		$('body').css('cursor', 'default');
		$(".-zazz-display").hide();
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
		$(".-zazz-display").css('display', 'block');
	}

	function verticalMouseLeave() {
		$(".-zazz-vertical-line-top").hide();
		$(".-zazz-vertical-line-bottom").hide();
		$('body').css('cursor', 'default');
		$(".-zazz-display").hide();
	}

	$('.-zazz-id-input').blur(function() {
		if (typeof $.last_div !== "undefined") {
			$.last_div.attr("id", $(this).val());
		}
	});

	$('.-zazz-class-input').blur(function() {
		if (typeof $.last_div !== "undefined") {
			$.last_div.attr("class", $(this).val() + ' -zazz-element');
		}
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
			div.attr('tabindex', '1').attr('_zazz-order', '1').attr("_zazz-id", id);
			addCSSCodeBlock(id);
		}
		return div;
	}

	$('.-zazz-vertical-btn').register(
		function() {
			$('.-zazz-content-view').on('mousemove', verticalMouse);
			$('.-zazz-content-view').on('mouseenter', verticalMouseEnter);
			$('.-zazz-content-view').on('mouseleave', verticalMouseLeave);
		},
		function($div, e) {
			var $other_div = createDiv('element-' + $.element_id, '-zazz-element');
			var left = $div.offset().left;
			var old_width = $div.outerWidth();
			$div.css('width', e.pageX - left);
			$other_div.css('width', old_width - (e.pageX - left));
			$.element_id++;
			$other_div.insertAfter($div);
			$div.focus();

			updateLayout();
		},
		function() {
			verticalMouseLeave();
			$('.-zazz-content-view').off('mouseenter', verticalMouseEnter);
			$('.-zazz-content-view').off('mouseleave', verticalMouseLeave);
			$('.-zazz-content-view').off('mousemove', verticalMouse);
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
			var width = $div.outerWidth();
			var $row_group;
			var $row;
			var $other_row;
			//Check to see if the row has only one div.
			if ($div.parent().children().length === 1 && $div.parent().hasClass('-zazz-row')) {
				//If so, then the row group and the row are obvious.
				$row_group = $div.parent().parent();
				$row = $div.parent();
			} else {
				//Otherwise we need to create a new row group and insert it where the div was with the right
				//width. This also requires creating a new row to place the div into and placing that row 
				//into the row group.
				$row_group = createDiv('row-group-' + $.group_id, '-zazz-row-group').width(width);
				$row = createDiv('row-' + $.row_id, '-zazz-row');
				$.group_id++;
				$.row_id++;
				$row_group.append($row);
				$row_group.insertAfter($div);
				$row.append($div);

				$div.css('width', '100%');
			}

			//Make a new row and insert the new row into the row group.
			$other_row = createDiv('row-' + $.row_id, '-zazz-row');
			$other_row.append(createDiv('element-' + $.element_id, '-zazz-element'));
			$.row_id++;
			$.element_id++;
			$other_row.insertAfter($row);

			//Now we need to fix the heights of the divs by splitting across the point that was clicked.
			var top = $div.offset().top;
			var old_height = $div.outerHeight();
			var new_other_height = old_height - (e.pageY - top);
			var new_height = e.pageY - top;
			$other_row.height(new_other_height);
			$row.height(new_height);

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
			$('.-zazz-element').css('cursor', 'pointer');
		},
		function(div, e, changed) {
			if (typeof this.first_div === 'undefined' || changed) {
				this.first_div = div;
			} else if (div[0] !== this.first_div[0]) {
				var $second_div = $(div);
				var $first_div = $(this.first_div);
				//Check to see if they are in the same row.
				if ($second_div.parent().get(0) === $first_div.parent().get(0)) {
					//Remove the second div clicked on and expand the first div.
					var first_width = $first_div.outerWidth();
					var second_width = $second_div.outerWidth();
					$second_div.remove();
					$first_div.css("width", first_width + second_width);
					//Else check to see if rows are in the same column (also checks that there is only one element
					//in row).
				} else if ($second_div.parent().parent().get(0) === $first_div.parent().parent().get(0) &&
					$second_div.parent().children().length === 1 &&
					$first_div.parent().children().length === 1) {
					//Remove the second div clicked on and expand the first div.
					$first_div.parent().css("height", $second_div.parent().outerHeight() +
						$first_div.parent().outerHeight());
					$second_div.parent().remove();
					//Get the column that contains the row that contains the first div.
					var $good_parent = $first_div.parent().parent();
					var $old_parent = null;
					//We need to keep the structure of the document well maintained and ensure that we don't have
					//things like a column that just contains a row that just contains a column that just contains
					//a row that just contains a div.
					//Check that the row that contains the column doesn't just contain the column (also checks
					//corner case of having only one div on entire page).
					while ($good_parent.children().length === 1 && $good_parent.attr('_zazz-id') !== 'content') {
						$old_parent = $good_parent;
						$good_parent = $good_parent.parent();
					}
					//If so, then remove the column, and replace it with the first div (expand width accordingly).
					if (null !== $old_parent) {
						$first_div.css("width", $old_parent.outerWidth());
						$first_div.insertBefore($old_parent);
						$old_parent.remove();
					}
					//Else give error message.
				} else {
					warn("Error", "These elements are neither in the same row or column.");
				}
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
			} else if (document.activeElement.id === '-zazz-deploy-project-btn') {
				confirm('Are you sure?', 'By deploying this project, you will make it publicly visible at the ' +
					'root URL.',
					function() {
						window.location.href = "/zazz/view.php?project=" + $('#-zazz-project-name').val() + '&page=' +
							$('#-zazz-default-page').val() + '&deploy=true';
					}
				);
			} else if (document.activeElement.id === '-zazz-delete-project-btn') {
				confirm('Are you sure?','By deleting this project, you will remove all code and pages.',
				function(){
					$.post('/zazz/ajax/project.php', {
						page_id: $('#-zazz-page-id').val(),
						delete: 'true'
					}, function(){
						window.location.href = "/zazz/index.php";
					});
				});
			}
			$('#-zazz-dropdown-project').hide();
		}, 1);
	});

	$('#-zazz-make-new-project').click(function() {
		$.post('/zazz/ajax/project.php', {
			create: $('#-zazz-new-project-name').val()
		}, function(data) {
			if(trim(data) !== "") {
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
				confirm('Are you sure?','By deleting this page, you will remove all code.',
				function(){
					$.post('/zazz/ajax/page.php', {
						page_id: $('#-zazz-page-id').val(),
						delete: 'true'
					}, function() {
						window.location.href = "/zazz/index.php";
					});
				});
			}
			$('#-zazz-dropdown-page').hide();
		}, 1);
	});
	
	$('#-zazz-make-new-page').click(function() {
		$.post('/zazz/ajax/page.php', {
			page_id: $('#-zazz-page-id').val(),
			create: $('#-zazz-new-page-name').val()
		}, function(data) {
			if(trim(data) !== "") {
				$('#-zazz-modal-new-page .-zazz-modal-message').html(data);
			} else {
				window.location.href = "/zazz/build/" + $('#-zazz-project-name').val() +
					'/' + $('#-zazz-new-page-name').val();
			}
		});
	});

	$('.-zazz-view-btn').click(function() {
		window.location.href = "/zazz/view/" 
			+ $('#-zazz-project-name').val() + '/' +	$('#-zazz-page-name').val();
	});

	function addCodeBlock(className, forID) {
		var $forID = $('.-zazz-element[_zazz-id="' + forID + '"]');
		var order;
		if ($forID.length === 0) {
			order = '0';
		} else {
			order = $forID.attr('_zazz-order');
		}
		var $textarea = $('<textarea></textarea>').addClass('-zazz-code-block')
			.addClass(className).addClass('-zazz-code-block-' + forID).attr('spellcheck', false)
			.attr('tabindex', '1').attr('_zazz-order', order);
		$forID.attr('_zazz-order', parseInt($forID.attr('_zazz-order')) + 1);
		return $textarea;
	}

	function addCodeButton(type) {
		var id = $.last_div.attr("_zazz-id");
		var $block = addCodeBlock('-zazz-' + type + '-code', id);
		$('.-zazz-code-blocks').append($block);
		$block.fadeIn(300).focus();
		updateCode(id, $block, type, true);
	}

	$('.-zazz-html-btn').click(function() {
		addCodeButton('html');
	});
	$('.-zazz-php-btn').click(function() {
		addCodeButton('php');
	});
	$('.-zazz-mysql-btn').click(function() {
		addCodeButton('mysql');
	});
	$('.-zazz-js-btn').click(function() {
		addCodeButton('js');
	});

	function addCSSCodeBlock(id) {
		var $block = addCodeBlock('-zazz-css-code', id);
		$block.val('#' + id + ' {\n\n' + '}');
		$('.-zazz-code-blocks').append($block);
		updateCode(id, $block, 'css', true);
	}

	function start() {
		//$('.-zazz-content, .-zazz-row-group, .-zazz-row, .-zazz-element').each(function(){
		//	addCSSCodeBlock($(this).attr("_zazz-id"));
		//});
		var $content = $('.-zazz-content');
		$('.-zazz-element').first().focus();
		$('.-zazz-select-btn').click();
		$.row_id = $content.attr('_zazz-rid');
		$.group_id = $content.attr('_zazz-gid');
		$.element_id = $content.attr('_zazz-eid');
		$('.-zazz-css-code').each(function() {
			var $this = $(this);
			addCSSCode(getZazzID($this), $this.val());
		});
		$('.-zazz-js-code').each(function() {
			var $this = $(this);
			addJSCode($this.val());
		});

		$('.-zazz-content').first().css('background-image',
			'url(' + $('#-zazz-background-image').val() + ')');
	}

	start();

	/*--------------------------------------Keyboard Shortcuts--------------------------------------*/

	$('[tabindex]').keyup(function(e) {
		if (e.which === 13 || e.keyCode === 13) {
			$(this).click();
		}
	});

	$(document).keyup(function(e) {
		if (e.keyCode === 27 || e.which === 27) {
			$('.-zazz-select-btn').click();
		}
	});

	if($('#-zazz-is-demo').val()) {
		warn("Welcome!","Thanks for trying out the demo. <br><br> Since this is the demo, many of the features\n\
		of Zazz have been disabled. However, you can still play around with the layout editing options in the\n\
		top left corner of the screen, and add HTML, CSS, and JavaScript code to the document! <br><br> Enjoy!",
		"width: 500px");
	}

});
