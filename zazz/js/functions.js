function trim(n){return n.replace(/^\s\s*/,"").replace(/\s\s*$/,"")}
function doStuff(){$(document).ready(function(){function n(a,b,c){$("#-zazz-modal-confirm .-zazz-modal-header")[0].innerHTML=a;$("#-zazz-modal-confirm .-zazz-modal-body")[0].innerHTML=b;$("#-zazz-modal-confirm .-zazz-modal-button")[0].onclick=c;$("#-zazz-modal-confirm").center().show()}function f(a,b,c){"undefined"===typeof c&&(c="");$("#-zazz-modal-alert .-zazz-modal-body").html(b);$("#-zazz-modal-alert .-zazz-modal-header").html(a);$("#-zazz-modal-alert").attr("style",c).center().show()}function p(a,
b){"undefined"!==typeof $.last_div&&$.last_div.css("box-shadow","");var c=$(".-zazz-content").attr("data-zazz-rid",$.row_id).attr("data-zazz-gid",$.group_id).attr("data-zazz-eid",$.element_id)[0].outerHTML,c={page_id:$("#-zazz-page-id").val(),layout:c};"undefined"!==typeof a&&"undefined"!==typeof b&&(c.from=a,c.to=b);$("#-zazz-loader-bar").promote();$.post("/zazz/ajax/layout.php",c,function(a){$("#-zazz-loader-bar").demote();""!==trim(a)&&f("Error",a)});"undefined"!==typeof $.last_div&&y($.last_div)}
function k(a,b,c,d){"undefined"===typeof doDelete&&(doDelete=!1);"undefined"===typeof doMove&&(doMove=!1);var e={zazz_id:a,type:c,page_id:$("#-zazz-page-id").val(),zazz_order:b.attr("data-zazz-order")};d===$.codeActions.DELETE?(e.deleted=!0,e.code=b.val()):d===$.codeActions.MOVE?(e.moveTo=$.move.to,e.zazz_order=$.move.from):d===$.codeActions.INSERT?(e.insert=!0,e.code=b.val()):d===$.codeActions.UNLINK?e.unlink=!0:d===$.codeActions.UPDATE&&(e.insert=!1,e.code=b.val(),"undefined"!==typeof $.lastUpdate&&
($.lastLastUpdate=$.lastUpdate,$.lastLastUpdateDiv=$.lastUpdateDiv),$.lastUpdate=b,$.lastUpdateDiv=$.last_div);$("#-zazz-loader-bar").promote();$.post("/zazz/ajax/code.php",e,function(b){function d(){z++;z===f&&$(".-zazz-content").css("height","")}$("#-zazz-loader-bar").demote();var e=$(".-zazz-content").outerHeight();"begin-project"===a||"end-project"===a||"begin-web-page"===a||"end-web-page"===a?("html"===c&&location.reload(),$(".-zazz-content-view").first().html(b)):($('.-zazz-element[data-zazz-id="'+
a+'"]').html(b),$(".-zazz-code-block-"+a).filter(".-zazz-js-code").each(function(){A($(this).val())}));b=$(".-zazz-content").find("img");var f=b.length,z=0,k=$(".-zazz-content");0!==f&&k.css("height",e);b.load(d).each(function(){this.complete&&d()})})}function q(a){var b;a.hasClass("-zazz-html-code")?b="html":a.hasClass("-zazz-css-code")?b="css":a.hasClass("-zazz-mysql-code")?b="mysql":a.hasClass("-zazz-php-code")?b="php":a.hasClass("-zazz-js-code")&&(b="js");return b}function B(a){a=a.attr("class").split(/\s+/);
for(var b,c=0;c<a.length;c++)0<=a[c].indexOf("-zazz-code-block-")&&(b=a[c].substring(17));return b}function C(a,b){var c="-zazz-css-code-"+a;0===$("#"+c).length?(c=$("<style></style>").attr("id",c).html(b),$("head").append(c)):$("#"+c).html(b)}function A(a){try{eval(a)}catch(b){$("#-zazz-modal-alert").is(":visible")||f("Error","There was an error in your JavaScript: <br>"+b.message)}}function D(){window.onbeforeunload=function(){return"You have unsaved code that will be lost if you continue to navigate away. You will need the text area you were working on to lose focus in order for your code to be saved."}}
function r(a){if(a.hasClass("-zazz-css-code"))return!1;for(var b=a.val(),c=1,d=b.indexOf("\n");0<=d;)c++,d=b.indexOf("\n",d+1);b=a.parent().outerHeight();c=parseInt(a.css("line-height"))*c+parseInt(a.css("padding-top"))+parseInt(a.css("padding-bottom"));d=parseInt(a.css("height"));return d!==c&&(d<b||c<b)?(a.css("height",c),!0):!1}function y(a){a.css("box-shadow","0px 0px 100px #aaaaaa inset")}function E(){var a=$("<div></div>").attr("id","-zazz-code-column-"+$.codeColumns).addClass("-zazz-code-column");
$(".-zazz-code-blocks").append(a);$.codeColumns++;return a}function l(a){if(!$.ignoreCodeFocus){"undefined"===typeof a&&(a=$.last_div.attr("data-zazz-id"));var b=0,c=$("#-zazz-code-column-0"),d=0,e=$(".-zazz-code-blocks").outerHeight()-10,g=$(":focus"),m=0;$.ignoreCodeFocus=!0;$(".-zazz-code-block-"+a).each(function(){var a=$(this);if(!a.hasClass("-zazz-css-code")){var g=a.outerHeight();if(0===d||d+g<e)c.append(a).show(),d+=g,m++;else{b++;var f=$("#-zazz-code-column-"+b);0===f.length&&(f=E());c=f;
c.append(a).show();m=1;d=g}}});b++;for(a=$("#-zazz-code-column-"+b);0!==a.length;)a.hide(),b++,a=$("#-zazz-code-column-"+b);g.focus();$.ignoreCodeFocus=!1}}function N(){var a=$.last_div;if(a.parent().hasClass("-zazz-hidden"))$(".-zazz-fixed-status-btn").html("W: 0 H: 0");else{var b=s(a),c;c=0<=b.indexOf("%")?"%":"px";var d=a.css("min-height"),a=a.outerHeight();parseInt(d)!==a&&(d+=" ("+a+"px)");$(".-zazz-fixed-status-btn").html("W: "+Math.floor(100*parseFloat(b))/100+c+" H: "+d)}}function F(a){var b=
$("#-zazz-page-name").val();$("#-zazz-loader-bar").promote();$.post("/zazz/ajax/page.php",{page:b,page_id:$("#-zazz-page-id").val(),visible:"Yes"===$("#-zazz-page-visible").val()?"1":"0"},function(c){$("#-zazz-loader-bar").demote();""!==trim(c)?($("#-zazz-modal-settings .-zazz-modal-message").html(c),$("#-zazz-modal-settings").show()):a&&window.location.replace("/zazz/build/"+$("#-zazz-project-name").val()+"/"+b)})}function G(a){var b;b=a?{project:$("#-zazz-project-name").val(),page_id:$("#-zazz-page-id").val()}:
{default_page:$("#-zazz-default-page").val(),page_id:$("#-zazz-page-id").val()};$("#-zazz-loader-bar").promote();$.post("/zazz/ajax/project.php",b,function(b){$("#-zazz-loader-bar").demote();""!==trim(b)?($("#-zazz-modal-project .-zazz-modal-message").html(b),$("#-zazz-modal-project").show()):a&&window.location.replace("/zazz/build/"+project+"/"+$("#-zazz-page-name").val())})}function H(a){var b=$.last_div.attr("data-zazz-id");n("Warning","This code is linked to the template page ("+$("#-zazz-template-name").html()+
") and cannot be edited. Zazz can unlink the code so that you can edit it, but then changes to the template will not propagate to "+b+" of this page.",function(){k(b,a,q(a),$.codeActions.UNLINK);$(".-zazz-code-block-"+b).each(function(){$(this).removeClass("-zazz-code-locked").removeAttr("readonly")});$("#-zazz-modal-confirm").hide()})}function I(a){$(".-zazz-horizontal-line-left").css("top",a.pageY).css("right",$("body").width()-(a.pageX-10));$(".-zazz-horizontal-line-right").css("top",a.pageY).css("left",
a.pageX+10);$("#-zazz-location-display").html("( "+a.pageX+" , "+a.pageY+" ) ")}function J(){$(".-zazz-horizontal-line-left").show();$(".-zazz-horizontal-line-right").show();$("body").css("cursor","crosshair");$("#-zazz-modal-mouse").show()}function v(){$(".-zazz-horizontal-line-left").hide();$(".-zazz-horizontal-line-right").hide();$("body").css("cursor","default");$("#-zazz-modal-mouse").hide()}function K(a){$(".-zazz-vertical-line-top").css("left",a.pageX).css("bottom",$("body").height()-(a.pageY-
10));$(".-zazz-vertical-line-bottom").css("left",a.pageX).css("top",a.pageY+10)}function L(){$(".-zazz-vertical-line-top").show();$(".-zazz-vertical-line-bottom").show();$("body").css("cursor","crosshair");$("#-zazz-modal-mouse").show()}function w(){$(".-zazz-vertical-line-top").hide();$(".-zazz-vertical-line-bottom").hide();$("body").css("cursor","default");$("#-zazz-modal-mouse").hide()}function x(a){a=$(a.target);if(a.hasClass("-zazz-code-block")){if("undefined"===typeof $.move.from)a.hasClass("-zazz-css-code")?
f("Warning","Cannot move CSS block."):$.move.from=a;else if(a[0]!==$.move.from[0]){$.move.to=parseInt(a.attr("data-zazz-order"))+1;var b=$.move.from;$.move.from=b.attr("data-zazz-order");k($.last_div.attr("data-zazz-id"),b,q(b),$.codeActions.MOVE);1===$.move.to?$("#-zazz-code-column-0").prepend(b):b.insertAfter(a);b.attr("data-zazz-order",parseInt(a.attr("data-zazz-order"))+1);b.nextAll().each(function(){$(this).attr("data-zazz-order",parseInt($(this).attr("data-zazz-order"))+1)});delete $.move.to;
delete $.move.from;$(document).off("click",x);$(".-zazz-move-btn").removeClass("-zazz-active-btn")}return!1}a.hasClass("-zazz-move-btn")||("undefined"===typeof $.move.from&&delete $.move.from,$(document).off("click",x),$(".-zazz-move-btn").removeClass("-zazz-active-btn"))}function t(a,b){var c=$("<div></div>").addClass(b).attr("id",a);if("-zazz-element"===b&&(c.attr("tabindex","10").attr("data-zazz-id",a),0===$(".-zazz-code-block-"+a).length)){var d=M("-zazz-css-code",a);d.val("#"+a+" {\n\n}");$(".-zazz-code-blocks").prepend(d);
d.hide();k(a,d,"css",$.codeActions.INSERT)}return c}function s(a,b){var c=a.parent();"undefined"===typeof b&&(b=!1);return b||"auto"===a.css("z-index")?(c=c.outerWidth()-parseInt(c.css("padding-left"))-parseInt(c.css("padding-right")),a.outerWidth()/c*100+"%"):a.outerWidth()+"px"}function M(a,b){var c=$(".-zazz-code-block:visible").first();if(c.hasClass("-zazz-code-locked"))H(c);else return c=0===$('.-zazz-element[data-zazz-id="'+b+'"]').length?"0":parseInt($(".-zazz-code-block-"+b).last().attr("data-zazz-order"))+
1,$("<textarea></textarea>").addClass("-zazz-code-block").addClass(a).addClass("-zazz-code-block-"+b).attr("spellcheck",!1).attr("tabindex","10").attr("data-zazz-order",c).attr("wrap","off")}function u(a){var b=$.last_div.attr("data-zazz-id"),c=M("-zazz-"+a+"-code",b);$(".-zazz-code-blocks").append(c);r(c);c.css("display","block");k(b,c,a,$.codeActions.INSERT);l();setTimeout(function(){c.focus()},1)}$.fn.center=function(){this.css("position","absolute");this.css("top",Math.max(0,($(window).height()-
$(this).outerHeight())/2+$(window).scrollTop())+"px");this.css("left",Math.max(0,($(window).width()-$(this).outerWidth())/2+$(window).scrollLeft())+"px");return this};$.fn.promote=function(){var a=this.attr("promote-rank");"undefined"===typeof a&&(a=0);a++;this.attr("promote-rank",a);1===a&&this.css("display","inline-block");return this};$.fn.demote=function(){var a=this.attr("promote-rank");"undefined"===typeof a&&(a=0);a--;this.attr("promote-rank",a);0===a&&this.hide();return this};$.fn.register=
function(a,b,c){var d=$(this).attr("class").split(" ")[0];$.fn.register.initialize[d]=a;$.fn.register.action[d]=b;$.fn.register.clean[d]=c;$(this).click(function(){var a=$(this).attr("class").split(" ")[0];a!==$.fn.register.current&&($.fn.register.first=!0,null!==$.fn.register.current&&($("."+$.fn.register.current).removeClass("-zazz-active-btn"),$(this).register.clean[$.fn.register.current]()),$.fn.register.current=a,$(this).addClass("-zazz-active-btn"),$(this).register.initialize[$.fn.register.current]())});
return this};$.fn.register.first=!0;$.fn.register.current=null;$.fn.register.initialize=[];$.fn.register.action=[];$.fn.register.clean=[];$.fn.register.reset=function(){null!==$.fn.register.current&&($.fn.register.clean[$.fn.register.current](),$.fn.register.current=null);return this};$(".-zazz-content-view").click(function(a){null!==$.fn.register.current&&($.fn.register.action[$.fn.register.current]($(a.target).closest(".-zazz-element"),a,$.fn.register.first),$.fn.register.first=!1)});$.fn.drag=
function(a,b,c){var d=$(this).attr("class").split(" ")[0];$(this).mousedown(function(b){if(!$(b.target).is("input")){var c=$(this).attr("class").split(" ")[0];$.fn.drag.condition[c]=!0;$(document).on("mousemove",$.fn.drag.during[c]);a(b);return!1}});$(this).mouseup(function(a){var b=$(this).attr("class").split(" ")[0];$.fn.drag.condition[b]=!1;$(document).off("mousemove",$.fn.drag.during[b]);c(a);return!1});$.fn.drag.during[d]=b;return this};$.fn.drag.condition=[];$.fn.drag.during=[];$.codeActions=
{INSERT:0,DELETE:1,MOVE:2,UNLINK:3,UPDATE:4};$(".-zazz-modal-close").click(function(){$(this).closest(".-zazz-modal").fadeOut(300)});$(document).on("input propertychange",".-zazz-code-block",function(){$.changed=!0;D();r($(this))&&l()}).on("keypress",function(a){if(8===a.keyCode||8===a.which||46===a.keyCode||46===a.which)$.changed=!0,D(),r($(this))&&l()});$(document).on("focus","textarea",function(){var a=$(this),b=a.parent().parent(),c=b.children(":visible").first().offset().left,c=a.offset().left-
c,a=a.outerWidth(),d=b.scrollLeft(),e=b.outerWidth();c<d?b.scrollLeft(c):d+e<c+a&&b.scrollLeft(d+c+a-e)});$(document).on("blur",".-zazz-css-code",function(){C($.last_div.attr("data-zazz-id"),$(this).val())});$(document).on("blur",".-zazz-js-code",function(){});$(document).on("blur",".-zazz-html-code",function(){});$(document).on("blur",".-zazz-code-block",function(){var a,b=$(this);a=q(b);var c=$.last_div.attr("data-zazz-id");window.onbeforeunload=null;$.changed&&!$.ignoreCodeFocus&&(k(c,b,a,$.codeActions.UPDATE),
$.changed=!1);$(".-zazz-editor-container").hide();b.attr("data-zazz-cursor",b.prop("selectionStart")).attr("data-zazz-scroll",b.scrollTop())}).on("focus",".-zazz-code-block",function(){var a=$(this),b=parseInt(a.attr("data-zazz-cursor"));setTimeout(function(){0===a.prop("selectionStart")&&(a[0].setSelectionRange(b,b),a.scrollTop(parseInt(a.attr("data-zazz-scroll"))))},1);a.hasClass("-zazz-html-code")&&"begin-project"!==$.last_div.attr("data-zazz-id")&&"end-project"!==$.last_div.attr("data-zazz-id")&&
($.htmlEdit=a)});$.codeColumns=0;$.ignoreCodeFocus=!1;$(document).on("focus",".-zazz-element",function(){var a=$(this);"undefined"!==typeof $.last_div&&$.last_div.css("box-shadow","");y(a);var b=a.attr("data-zazz-id");$(".-zazz-id-input").val(a.attr("id"));$(".-zazz-class-input").val(a.attr("class").replace("-zazz-element","").replace("-zazz-outline",""));if("undefined"===typeof $.last_div||a.attr("data-zazz-id")!==$.last_div.attr("data-zazz-id"))$(".-zazz-code-block").hide(),$(".-zazz-code-block-"+
b).each(function(){var a=$(this);a.hasClass("-zazz-css-code")?a.css("display","inline-block"):(a.css("display","block"),r(a))});$(".-zazz-code-block:visible").first().hasClass("-zazz-code-locked")?$(".-zazz-relink").hide():$(".-zazz-relink").each(function(){$(this).css("display","inline-block")});l(b);$.last_div=a;N();return!1});$("#-zazz-page-height").blur(function(){var a=$(this);if(""!==trim(a.val())&&a.val()!==a.attr("data-zazz-old-height")){var b=parseInt(a.val())/parseInt(a.attr("data-zazz-old-height"));
$(".-zazz-element").each(function(){var a=$(this);a.css("min-height",Math.round(parseInt(a.css("min-height"))*b))});a.attr("data-zazz-old-height",parseInt(a.val()))}p()});$("#-zazz-project-name").blur(function(){G(!0)});$("#-zazz-page-name").blur(function(){F(!0)});$("#-zazz-page-visible").change(function(){F(!1)});$("#-zazz-default-page").change(function(){G(!1)});$("#-zazz-view-btn").click(function(){if("No"===$("#-zazz-page-visible").val())return f("Warning","You cannot view a page that is not visible."),
!1});$("#-zazz-deploy-link").click(function(){$(this).attr("href","/zazz/view/"+$("#-zazz-project-name").val()+"/"+$("#-zazz-page-name").val()+"?deploy="+encodeURIComponent($("#-zazz-deploy-password").val()))});$(document).mousemove(function(a){$.mouse_x=a.pageX;$.mouse_y=a.pageY});$(".-zazz-content-view").mousemove(function(a){var b=$(a.target);b.hasClass("-zazz-outline")&&(b=$.last_div);var c=Math.round(a.offsetX||a.clientX-b.offset().left),d=Math.round(a.offsetY||a.clientY-b.offset().top),e=b.outerHeight(),
g=b.outerWidth(),m=$("body").outerHeight(),h=$("body").outerWidth(),f=$("#-zazz-modal-mouse"),k=$(".-zazz-content-view"),n=$(".-zazz-content").outerHeight(),l=k.offset().top,k=k.outerHeight();a.pageX>h/2?f.css("left","0").css("right",""):f.css("right","0").css("left","");a.pageY>l+k/2?f.css("top",l).css("bottom",""):f.css("bottom",m-(l+k)).css("top","");$("#-zazz-modal-mouse-offset").html("<td>Offset (px):</td><td>("+d+",</td><td>"+(b.outerWidth()-c)+",</td><td>"+($(a.target).outerHeight()-d)+",</td><td>"+
c+")</td>");$("#-zazz-modal-mouse-location").html("<td>Page (px):</td><td>("+(a.pageY-l)+",</td><td>"+(h-a.pageX)+",</td><td>"+(n-(a.pageY-l))+",</td><td>"+a.pageX+")</td>");$("#-zazz-modal-mouse-offsetp").html("<td>Offset (%):</td><td>("+Math.round(d/e*1E3)/10+",</td><td>"+Math.round((g-c)/g*1E3)/10+",</td><td>"+Math.round((e-d)/e*1E3)/10+",</td><td>"+Math.round(c/g*1E3)/10+")</td>");$("#-zazz-modal-mouse-locationp").html("<td>Page (%):</td><td>("+Math.round((a.pageY-l)/n*1E3)/10+",</td><td>"+Math.round((h-
a.pageX)/h*1E3)/10+",</td><td>"+Math.round((n-(a.pageY-l))/n*1E3)/10+",</td><td>"+Math.round(a.pageX/h*1E3)/10+")</td>")});$(".-zazz-code-blocks").on("mousemove",".-zazz-code-block",function(a){var b=$(this),c=b.offset();c.bottom=b.offset().top+b.outerHeight();c.right=b.offset().left+b.outerWidth();c.left=b.offset().left;c.bottom>a.pageY&&a.pageY>c.bottom-15&&c.right>a.pageX&&a.pageX>c.right-15?b.css({cursor:"e-resize"}):!b.hasClass("-zazz-css-code")&&18+c.top>a.pageY&&a.pageY>c.top&&c.left+18>a.pageX&&
a.pageX>c.left?b.css({cursor:"pointer"}):b.css({cursor:""})}).on("click",".-zazz-code-block",function(a){var b=$(this);b.hasClass("-zazz-code-locked")&&H(b);var c=b.offset();c.left=b.offset().left;if(18+c.top>a.pageY&&a.pageY>c.top&&c.left+18>a.pageX&&a.pageX>c.left&&!b.hasClass("-zazz-css-code")){var d=B(b);n("Warning","Continuing will delete this code block permanently.",function(){k(d,b,q(b),$.codeActions.DELETE);b.remove();$("#-zazz-modal-confirm").hide();l()})}});$(".-zazz-code-area .-zazz-navbar").not("input").drag(function(){},
function(a){var b=8;$(".-zazz-divide-navbar").is(":visible")&&(b=40);$(".-zazz-code-area").height(($("body").height()-(a.pageY-b))/$("body").height()*100+"%");$(".-zazz-view").height((a.pageY-b)/$("body").height()*100+"%");$(document).css("cursor: n-resize")},function(){l()}).on("selectstart",!1).on("dragstart",!1);$(".-zazz-relink-btn").click(function(){var a=$.last_div.attr("data-zazz-id");n("Are you sure?","Do you want to remove the code from the current elment ("+a+") and restore the code from the template?",
function(){$("#-zazz-modal-confirm").hide();$("#-zazz-loader-bar").promote();$.post("/zazz/ajax/code.php",{relink:a,page_id:$("#-zazz-page-id").val()},function(a){""!==trim(a)?($("#-zazz-loader-bar").demote(),f("Error",a)):location.reload()})})});$(".-zazz-id-input").blur(function(){var a=$.last_div.attr("id"),b=$(this).val();"begin-project"===a||"end-project"===a||"begin-page"===a||"end-page"===a?(f("Error","Cannot change the ID for element "+a+"."),$(this).val(a)):a!==b&&("undefined"!==typeof $.last_div&&
(0===$("#"+b).length?($.last_div.attr("id",b),$.last_div.attr("data-zazz-id",b),$(".-zazz-code-block-"+a).each(function(){$(this).removeClass("-zazz-code-block-"+a).addClass("-zazz-code-block-"+b)})):(f("Error","There is already an element with the ID of "+b),$(this).val(a))),p(a,b))});$(".-zazz-class-input").blur(function(){"undefined"!==typeof $.last_div&&$.last_div.attr("class",$(this).val()+" -zazz-element -zazz-outline");p()});$(".-zazz-database-btn").click(function(){$("#-zazz-modal-database").center().show()});
$("#-zazz-modal-database-edit").click(function(){$("#-zazz-modal-database-form").submit()});$.move=[];$(".-zazz-move-btn").click(function(){$(this).addClass("-zazz-active-btn");$(document).on("click",x)});$(".-zazz-select-btn").register(function(){},function(){},function(){});$(".-zazz-vertical-btn").register(function(){$(".-zazz-content-view").on("mousemove",K);$(".-zazz-content-view").on("mouseenter",L);$(".-zazz-content-view").on("mouseleave",w);$("#-zazz-fixed").css("display","inline-block");
$("#-zazz-fixed-vertical").show()},function(a,b){var c=0<parseInt(a.css("z-index"))?!0:!1,d=$("#-zazz-fixed-vertical").val();if(c&&"Both"!==d)f("Warning","You cannot have a child element that has a dynamic width for a fixed width element.");else if(c||"Both"!==d){var e=a.outerWidth(),g=Math.round(b.pageX-a.offset().left),m=Math.round(e-g),e=t("element-"+$.element_id,"-zazz-element");$.element_id++;if(c)a.css("width",g),e.css("width",m).css("z-index","1"),e.insertAfter(a);else{var h;if("Left"===d||
"Right"===d)a.parent().hasClass("-zazz-container")?h=a.parent():(h=$("<div></div>").addClass("-zazz-container").css("width",a.outerWidth()/a.parent().outerWidth()*100+"%"),a.css("width",""),h.insertAfter(a),h.append(a));"Left"===d?(h.css("padding-left",parseInt(h.css("padding-left"))+g).css("margin-left",parseInt(h.css("margin-left"))-g),e.css("width",g).css("z-index","1"),e.insertBefore(h)):"Right"===d?(h.css("padding-right",parseInt(h.css("padding-right"))+m).css("margin-right",parseInt(h.css("margin-right"))-
m),e.css("width",m).css("z-index","1"),e.insertAfter(h)):"None"===d&&(h=a.parent(),c=h.outerWidth()-parseInt(h.css("padding-left"))-parseInt(h.css("padding-right")),g=g/c*100,c=a.outerWidth()/c*100,a.css("width",g+"%"),e.css("width",c-g+"%"),e.insertAfter(a))}e.css("min-height",a.css("min-height"));a.focus();p()}else f("Warning","You cannot have both children elements have fixed width for a dynamic width element.")},function(){w();$(".-zazz-content-view").off("mouseenter",L);$(".-zazz-content-view").off("mouseleave",
w);$(".-zazz-content-view").off("mousemove",K);$("#-zazz-fixed").hide();$("#-zazz-fixed-vertical").hide()});$(".-zazz-across-btn").register(function(){$(".-zazz-content-view").on("mousemove",I);$(".-zazz-content-view").on("mouseenter",J);$(".-zazz-content-view").on("mouseleave",v)},function(a,b){var c,d,e;c=a.parent();1===c.children().length&&c.hasClass("-zazz-row")?(c.parent(),d=c):(c=t("row-group-"+$.group_id,"-zazz-row-group"),c.css("width",s(a)),a.css("width",""),c.insertAfter(a),d=t("row-"+$.row_id,
"-zazz-row"),$.group_id++,$.row_id++,c.append(d),d.append(a));e=t("row-"+$.row_id,"-zazz-row");c=t("element-"+$.element_id,"-zazz-element");e.append(c);$.row_id++;$.element_id++;e.insertAfter(d);e=a.outerHeight();var g=a.offset().top;d=Math.round(b.pageY-g);e=Math.round(e-(b.pageY-g));a.css("min-height",d);c.css("min-height",e);"auto"!==a.css("z-index")&&c.css("z-index",a.css("z-index"));a.focus();p()},function(){v();$(".-zazz-content-view").off("mousemove",I);$(".-zazz-content-view").off("mouseenter",
J);$(".-zazz-content-view").off("mouseleave",v)});$(".-zazz-absorb-btn").register(function(){},function(a,b,c){if("undefined"===typeof this.first_div||c)this.first_div=a;else if(a[0]!==this.first_div[0]){b=$(a);var d=b.attr("data-zazz-id");a=$(this.first_div);var e=b.parent();c=a.parent();var g=e.parent(),m=c.parent();c.hasClass("-zazz-container")&&e.hasClass("-zazz-container")||c.hasClass("-zazz-row")&&e.hasClass("-zazz-row")&&e.get(0)===c.get(0)?(c=s(a),e=s(b),b.remove(),0<parseInt(a.css("z-index"))&&
0<parseInt(b.css("z-index"))?a.css("width",parseInt(c)+parseInt(e)+"px"):a.css("width",parseFloat(c)+parseFloat(e)+"%")):c.hasClass("-zazz-container")&&e.hasClass("-zazz-row")&&e.get(0)===m.get(0)?(e=b.outerWidth(),g=b.index(),b.remove(),1===m.children().length?(c.children().each(function(){m.append($(this))}),c.remove()):g<c.index()?(c.css("margin-left",parseInt(c.css("margin-left"))+e),c.css("padding-left",parseInt(c.css("padding-left"))-e)):(c.css("margin-right",parseInt(c.css("margin-right"))+
e),c.css("padding-right",parseInt(c.css("padding-right"))-e))):e.hasClass("-zazz-container")&&c.hasClass("-zazz-row")&&c.get(0)===g.get(0)?f("Error","You cannot absorb a dynamic width element into a fixed width element."):g.get(0)===m.get(0)?1!==e.children().length||1!==c.children().length?f("Error","Both rows must only have one element before you can combine them."):(a.css("min-height",parseInt(b.css("min-height"))+parseInt(a.css("min-height"))),b.parent().remove(),b=a.parent().parent(),1!==b.children().length||
b.parent().hasClass("-zazz-content")||(a.css("width",s(b)),a.insertBefore(b),b.remove())):f("Error","These elements are neither in the same row or column.");$(".-zazz-code-block-"+d).each(function(){k(d,$(this),q($(this)),$.codeActions.DELETE)});a.focus();delete this.first_div;p()}},function(){$(".-zazz-element").css("cursor","")});$("#-zazz-edit-page-btn").click(function(){$("#-zazz-modal-settings").show().center()});$(".-zazz-project-btn").click(function(){var a=$(this).offset();$("#-zazz-dropdown-project").show().css("top",
a.top+$(this).outerHeight()).css("left",a.left)}).blur(function(){setTimeout(function(){"-zazz-edit-project-btn"===document.activeElement.id?$("#-zazz-modal-project").show().center():"-zazz-new-project-btn"===document.activeElement.id?$("#-zazz-modal-new-project").show().center():"-zazz-switch-project-btn"===document.activeElement.id?$("#-zazz-modal-view-projects").show().center():"-zazz-delete-project-btn"===document.activeElement.id&&(1<=$("#-zazz-modal-view-pages .-zazz-links a").length?n("Are you sure?",
"By deleting this project, you will remove all code and pages.",function(){$("#-zazz-loader-bar").promote();$.post("/zazz/ajax/project.php",{page_id:$("#-zazz-page-id").val(),deleted:"true"},function(){window.location.href="/zazz/index.php"})}):f("Warning","You cannot delete your only project."));$("#-zazz-dropdown-project").hide()},1)});$(".-zazz-build-btn").click(function(){var a=$(this).offset();$("#-zazz-dropdown-build").show().css("top",a.top+$(this).outerHeight()).css("left",a.left)}).blur(function(){setTimeout(function(){"-zazz-deploy-project-btn"===
document.activeElement.id?($("#-zazz-modal-deploy-confirm").center().show(),$("#-zazz-dropdown-build").hide()):"-zazz-view-btn"===document.activeElement.id?setTimeout(function(){$("#-zazz-dropdown-build").hide()},500):"-zazz-export-btn"===document.activeElement.id?setTimeout(function(){$("#-zazz-dropdown-build").hide()},500):$("#-zazz-dropdown-build").hide()},1)});$("#-zazz-make-new-project").click(function(){$("#-zazz-loader-bar").promote();$.post("/zazz/ajax/project.php",{create:$("#-zazz-new-project-name").val()},
function(a){$("#-zazz-loader-bar").demote();""!==trim(a)?$("#-zazz-modal-new-project .-zazz-modal-message").html(a):window.location.href="/zazz/build/"+$("#-zazz-new-project-name").val()+"/"})});$(".-zazz-page-btn").click(function(){var a=$(this).offset();$("#-zazz-dropdown-page").show().css("top",a.top+$(this).outerHeight()).css("left",a.left)}).blur(function(){setTimeout(function(){"-zazz-edit-page-btn"===document.activeElement.id?$("#-zazz-modal-settings").show().center():"-zazz-new-page-btn"===
document.activeElement.id?$("#-zazz-modal-new-page").show().center():"-zazz-switch-page-btn"===document.activeElement.id?$("#-zazz-modal-view-pages").show().center():"-zazz-delete-page-btn"===document.activeElement.id&&(1<=$("#-zazz-modal-view-pages .-zazz-links a").length?n("Are you sure?","By deleting this page, you will remove all code.",function(){$("#-zazz-loader-bar").promote();$.post("/zazz/ajax/page.php",{page_id:$("#-zazz-page-id").val(),deleted:"true"},function(a){$("#-zazz-loader-bar").demote();
$("#-zazz-modal-confirm").hide();""!==trim(a)?f("Error",a):window.location.href="/zazz/index.php"})}):f("Warning","You cannot delete your only page."));$("#-zazz-dropdown-page").hide()},1)});$("#-zazz-make-new-page").click(function(){$("#-zazz-loader-bar").promote();$.post("/zazz/ajax/page.php",{page_id:$("#-zazz-page-id").val(),create:$("#-zazz-new-page-name").val(),template:$("#-zazz-page-template").val()},function(a){$("#-zazz-loader-bar").demote();""!==trim(a)?$("#-zazz-modal-new-page .-zazz-modal-message").html(a):
window.location.href="/zazz/build/"+$("#-zazz-project-name").val()+"/"+$("#-zazz-new-page-name").val()})});$("#-zazz-upload-filename").focus(function(){$("#-zazz-upload-file").click();$(this).blur()});$("#-zazz-upload-file").change(function(){var a=$(this).val();$("#-zazz-upload-filename").val(a);var b=a.lastIndexOf("/");if(0>b&&(b=a.lastIndexOf("\\"),0>b)){$("#-zazz-upload-filename").val(a);return}a=a.slice(b-a.length+1);$("#-zazz-upload-server").val(a)});$(".-zazz-upload-btn").click(function(){$("#-zazz-modal-upload").center().show()});
$("#-zazz-upload-do-it").click(function(){$("#-zazz-upload-name").val($("#-zazz-upload-server").val());$("#-zazz-upload-page-id").val($("#-zazz-page-id").val());$("#-zazz-upload-form").submit()});$(".-zazz-editor-btn").click(function(){$("#-zazz-modal-html-editor").center().show()});$("#-zazz-html-editor-code").click(function(){$("#-zazz-modal-html-editor").hide();k($.last_div.attr("data-zazz-id"),$.htmlEdit,"html",$.codeActions.UPDATE);r($.htmlEdit);l()});$(".-zazz-html-btn").click(function(a){u("html")});
$(".-zazz-php-btn").click(function(a){u("php")});$(".-zazz-mysql-btn").click(function(a){u("mysql")});$(".-zazz-js-btn").click(function(a){u("js")});(function(){E();var a=$(".-zazz-content");$(".-zazz-element").first().focus();$(".-zazz-select-btn").click();$.row_id=a.attr("data-zazz-rid");$.group_id=a.attr("data-zazz-gid");$.element_id=a.attr("data-zazz-eid");$(".-zazz-css-code").each(function(){var a=$(this);C(B(a),a.val())});$(".-zazz-js-code").each(function(){var a=$(this);A(a.val())});$("#-zazz-page-height").val($(".-zazz-content").outerHeight()).attr("data-zazz-old-height",
$(".-zazz-content").outerHeight());jQueryScriptOutputted&&f("Error","Could not find jQuery file specified in HTML header. Loaded a copy from Zazz instead.")})();$(document).keyup(function(a){if(13===a.which||13===a.keyCode){var b=$(".-zazz-modal:visible");if(0!==b.length){var b=b.first(),c=b.children(".-zazz-modal-footer").children(".-zazz-modal-button");0!==c.length?c.first().click():b.children(".-zazz-modal-footer").children(".-zazz-modal-close").first().click()}else{var d=$(":focus");d.is("textarea")?
setTimeout(function(){var a=d.scrollTop(),b=d.prop("selectionStart"),c=d.val(),f=c.substr(0,b),b=c.substr(b,c.length),c=f.split("\n"),c=c[c.length-2].match(/^\s*/)[0];d.val(f+c+b);d[0].setSelectionRange(f.length+c.length,f.length+c.length);d.scrollTop(a)},1):d.click()}}if(27===a.keyCode||27===a.which)b=$(".-zazz-modal:visible"),0!==b.length&&b.children(".-zazz-modal-footer").children(".-zazz-modal-close").first().click(),$(".-zazz-select-btn").click();117!==a.keyCode&&117!==a.which&&51!==a.keyCode&&
51!==a.which||!a.ctrlKey||($('.-zazz-element[data-zazz-id="begin-project"]').focus(),$(".-zazz-code-block-begin-project").first().focus());118!==a.keyCode&&118!==a.which&&52!==a.keyCode&&52!==a.which||!a.ctrlKey||"undefined"===typeof $.lastLastUpdate||($('.-zazz-element[data-zazz-id="'+$.lastLastUpdateDiv.attr("data-zazz-id")+'"]').focus(),$.lastLastUpdate.focus());119!==a.keyCode&&119!==a.which&&53!==a.keyCode&&53!==a.which||!a.ctrlKey||"undefined"===typeof $.lastUpdate||($('.-zazz-element[data-zazz-id="'+
$.lastUpdateDiv.attr("data-zazz-id")+'"]').focus(),$.lastUpdate.focus());120!==a.keyCode&&120!==a.which&&54!==a.keyCode&&54!==a.which||!a.ctrlKey||$(".-zazz-select-btn").focus();121!==a.keyCode&&121!==a.which&&55!==a.keyCode&&55!==a.which||!a.ctrlKey||$.last_div.focus();122!==a.keyCode&&122!==a.which&&56!==a.keyCode&&56!==a.which||!a.ctrlKey||$(".-zazz-id-input").focus();if((123===a.keyCode||123===a.which||57===a.keyCode||57===a.which)&&a.ctrlKey){a=$(".-zazz-code-blocks");for(b=a.children(":visible");0!==
b.length;)a=b.first(),b=a.children(":visible");a.focus()}});$("#-zazz-bad-html").val()&&f("Error","There was an error when Zazz tried to combine the HTML entered for begin-project, end-project, begin-web-page, end-web-page, which forced Zazz to use a default HTML frame instead. Please examine the HTML for those elements so that your HTML frame can be loaded.")})}var jQueryScriptOutputted=!1;
function initJQuery(){"undefined"===typeof jQuery?(jQueryScriptOutputted||(jQueryScriptOutputted=!0,document.write('<script type="text/javascript" src="/zazz/js/jquery-1.10.2.js">\x3c/script>')),setTimeout("initJQuery()",50)):doStuff()}initJQuery();
