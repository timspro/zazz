$(document).ready(function() {

	/*---------------------------------------Foundational Code--------------------------------------*/

	$.fn.register = function(initialize, action, clean) {
		var myClass = $(this).attr('class').split(' ')[0];
		$.fn.register.initialize[myClass] = initialize;
		$.fn.register.action[myClass] = action;
		$.fn.register.clean[myClass] = clean;
		$(this).click(function() {
			var myClass = $(this).attr('class').split(' ')[0];
			if (myClass !== $.fn.register.current) {
				$.fn.register.first = true;
				if ($.fn.register.current !== null) {
					$('.' + $.fn.register.current).removeClass('-zazz-active-btn');
					$(this).register.clean[$.fn.register.current]();
				}
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
	$.fn.register.reset = function() {
		if ($.fn.register.current !== null) {
			$.fn.register.clean[$.fn.register.current]();
			$.fn.register.current = null;
		}
	};
	$('.-zazz-element').click(function(e) {
		if ($.fn.register.current !== null) {
			$.fn.register.action[$.fn.register.current]($(this), e, $.fn.register.first);
			$.fn.register.first = false;
		}
	});

	$.fn.drag = function(start, during, stop) {
		var myClass = $(this).attr('class').split(' ')[0];
		$(this).mousedown(function(e) {
			var myClass = $(this).attr('class').split(' ')[0];
			$.fn.drag.condition[myClass] = true;
			$(document).on('mousemove', $.fn.drag.during[myClass]);
			start(e);
			//e.originalEvent.preventDefault();
		});
		$(this).mouseup(function(e) {
			var myClass = $(this).attr('class').split(' ')[0];
			$.fn.drag.condition[myClass] = false;
			$(document).off('mousemove', $.fn.drag.during[myClass]);
			stop(e);
		});
		$.fn.drag.during[myClass] = during;
		return this;
	};
	$.fn.drag.condition = [];
	$.fn.drag.during = [];

	function textareaScroll() {
		$(this)[0].scrollIntoView();
	}

	$(document).on('focus', 'textarea', textareaScroll);

	$(document).on('focus', '.-zazz-content-view div', function() {
		var $div = $(this);
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
		$(".-zazz-id-input").val($div.attr('id'));
		$(".-zazz-class-input").val($div.attr('class'));
		$.last_div = $div;
		return false;
	}).on('blur', '.-zazz-content-view div', function() {
		//$(".-zazz-outline").hide();
	});

	/*--------------------------------------------Mouse Code----------------------------------------*/

	$(document).mousemove(function(e) {
		$.mouse_x = e.pageX;
		$.mouse_y = e.pageY;
	});
	
	$('.-zazz-element').mousemove(function(e) {
		var offset_x = (e.offsetX || e.clientX - $(e.target).offset().left);
		var offset_y = (e.offsetY || e.clientY - $(e.target).offset().top);
		$('.-zazz-offset-btn').html('Offset: ( T' + offset_y + ', L' + offset_x + ', B' + 
			($(this).outerHeight() - offset_y) + ', R' + ($(this).outerWidth() - offset_x) + ' )');
		$('.-zazz-location-btn').html('Location: ( T' + e.pageY + ' , L' + e.pageX + ', B' +
			($('body').outerHeight() - e.pageY) + ', R' + ($('body').outerWidth() - e.pageX) + ' )');
	});

	function textareaMouseMove(e) {
		var myPos = $(this).offset();
		myPos.bottom = $(this).offset().top + $(this).outerHeight();
		myPos.right = $(this).offset().left + $(this).outerWidth();

		if (myPos.bottom > e.pageY && e.pageY > myPos.bottom - 16 && myPos.right > e.pageX &&
			e.pageX > myPos.right - 16) {
			$(this).css({cursor: "e-resize"});
		} else if (15 + myPos.top > e.pageY && e.pageY > myPos.top && myPos.right > e.pageX && 
			e.pageX > myPos.right - 15) {
			$(this).css({cursor: "pointer"});
		} else {
			$(this).css({cursor: "text"});
		}
	}
	
	function textareaMouseMoveNoRemove(e) {
		var myPos = $(this).offset();
		myPos.bottom = $(this).offset().top + $(this).outerHeight();
		myPos.right = $(this).offset().left + $(this).outerWidth();

		if (myPos.bottom > e.pageY && e.pageY > myPos.bottom - 16 && myPos.right > e.pageX &&
			e.pageX > myPos.right - 16) {
			$(this).css({cursor: "e-resize"});
		} else {
			$(this).css({cursor: "text"});
		}
	}	
	
	function textareaClick(e) {
		var myPos = $(this).offset();
		myPos.right = $(this).offset().left + $(this).outerWidth();
		if (15 + myPos.top > e.pageY && e.pageY > myPos.top && myPos.right > e.pageX &&
			e.pageX > myPos.right - 15) {
			$(this).remove();
		}
	}

	$(".-zazz-css-code").mousemove(textareaMouseMoveNoRemove);
	//$("textarea").mousemove(textareaMouseMove);
	//$("textarea").click(textareaClick);

	$(".-zazz-code-area .-zazz-navbar").drag(
		function() {
		},
		function(e) {
			var offset = 8;
			if($('.-zazz-divide-navbar').is(":visible")) {
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
		$('body').css('cursor','crosshair');
		$(".-zazz-display").css('display','inline-block');
	}

	function acrossMouseLeave() {
		$(".-zazz-horizontal-line-left").hide();
		$(".-zazz-horizontal-line-right").hide();
		$('body').css('cursor','default');
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
		$('body').css('cursor','crosshair');
		$(".-zazz-display").css('display','inline-block');
	}

	function verticalMouseLeave() {
		$(".-zazz-vertical-line-top").hide();
		$(".-zazz-vertical-line-bottom").hide();
		$('body').css('cursor','default');
		$(".-zazz-display").hide();
	}
	
	$('.-zazz-id-input').blur(function() {
		if(typeof $.last_div !== "undefined") {
			$.last_div.attr("id", $(this).val());
		}
	});
	
	$('.-zazz-class-input').blur(function() {
		if(typeof $.last_div !== "undefined") {
			$.last_div.attr("class", $(this).val());
		}
	});

	/*-----------------------------------------Compatibility----------------------------------------*/

	//< IE10 needs this.
	$('.-zazz-navbar span, .-zazz-navbar').each(function() {
		$(this).attr('unselectable', 'on');
	});

	/*------------------------------------------Button Code-----------------------------------------*/

	$('.-zazz-select-btn').register(
		function(){},
		function(){},
		function(){}
	);
		
	$('.-zazz-select-btn').click();

	$('.-zazz-vertical-btn').register(
		function() {
			$('.-zazz-content-view').on('mousemove', verticalMouse);
			$('.-zazz-content-view').on('mouseenter', verticalMouseEnter);
			$('.-zazz-content-view').on('mouseleave', verticalMouseLeave);
		},
		function($div, e) {
			var $other_div = $div.clone(true);
			var left = $div.offset().left;
			var old_width = $div.outerWidth();
			$div.css('width', e.pageX - left);
			$other_div.css('width', old_width - (e.pageX - left));
			$other_div.insertAfter($div);
			$div.focus();
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
				$row_group = $('<div></div>').width(width).addClass("-zazz-row-group").attr("tabindex","1");
				$row = $('<div></div>').addClass("-zazz-row").attr("tabindex","1");
				$row_group.append($row);
				$row_group.insertAfter($div);
				$row.append($div);

				$div.css('width', '100%');
			}

			//Clone the current row and insert the clone into the row group.
			$other_row = $row.clone(true);
			$other_row.children().removeAttr('id');
			$other_row.insertAfter($row);

			//Now we need to fix the heights of the divs by splitting across the point that was clicked.
			var top = $div.offset().top;
			var old_height = $div.outerHeight();
			var new_other_height = old_height - (e.pageY - top);
			var new_height = e.pageY - top;
			$other_row.height(new_other_height);
			$row.height(new_height);

			$div.focus();
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
					while ($good_parent.children().length === 1 && $good_parent.attr('id') !== 'content') {
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
					$('#modal-alert .modal-body').html("These elements are neither in the same row or column.");
					$('#modal-alert').modal();
				}
				//Set focus and delete the stored first div.
				$first_div.focus();
				delete this.first_div;
			}
		},
		function() {
			$('.-zazz-element').css('cursor', '');
		}
	);
		
	function addCodeBlock(className) {
		var $textarea = $('<textarea></textarea>').addClass('-zazz-code-block')
			.addClass(className).attr('spellcheck', false).attr('tabindex', '1')
			.on('focus', textareaScroll).on('mousemove', textareaMouseMove).click(textareaClick);
		return $textarea;
	}
		
	$('.-zazz-html-btn').click(function(){
		var $block = addCodeBlock('-zazz-html-code');
		$('.-zazz-code-blocks').append($block);
		$block.focus();
	});
	$('.-zazz-php-btn').click(function(){
		var $block = addCodeBlock('-zazz-php-code');
		$('.-zazz-code-blocks').append($block);
		$block.focus();
	});
	$('.-zazz-mysql-btn').click(function(){
		var $block = addCodeBlock('-zazz-mysql-code');
		$('.-zazz-code-blocks').append($block);
		$block.focus();
	});
	$('.-zazz-js-btn').click(function(){
		var $block = addCodeBlock('-zazz-js-code');
		$('.-zazz-code-blocks').append($block);
		$block.focus();
	});


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

});
