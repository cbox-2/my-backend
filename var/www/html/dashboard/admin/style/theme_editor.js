var NexusBoxIsReady = false;
var callQ = [];
var callNexusBox = function (call, data) {
	var $NexusBox = document.getElementById("cboxform");
	var rpc = {};
	for (var k in data) { rpc[k] = data[k]; }
	rpc['_call'] = call;
	if (NexusBoxIsReady && $NexusBox) {
		$NexusBox.contentWindow.postMessage(JSON.stringify(rpc), "*");
		return;
	}
	callQ.push(rpc);
}

window.addEventListener("message", function (m) {
	var $NexusBox = document.getElementById("cboxform");
	// التحقق من الـ origin - نسمح بالطلبات من VPS نفسه
	if (!m.origin.match(/34\.61\.70\.211/i) && !m.origin.match(/localhost/i)) {
		return;
	}
	var obj = null;
	try { obj = JSON.parse(m.data); } catch (e) {};
	if ($NexusBox && obj && obj.event == "ready" && callQ.length) {
		for (var i = 0; i < callQ.length; i++) {
			$NexusBox.contentWindow.postMessage(JSON.stringify(callQ[i]), "*");
		}
		NexusBoxIsReady = true;
	}
})

var helpshown = false;
function togglehelp () {
	var h = document.getElementById("colourhelp");
	helpshown = !helpshown;
	if (helpshown) { h.style.display = ""; }
	else { h.style.display = "none"; }
}

function initThemeEditor (theme, onchange) {
	callNexusBox("loadCSS", {which: theme});
	var isInit = true;
	var f = document.forms["basicstyle"];
	fon = false;
	var els = f.elements;
	for (j = 0; j < els.length; j++) {
		if (els[j].tagName !== "INPUT" || els[j].disabled) { continue; }
		if (els[j].name == 'fsmain' || els[j].name == 'fsdate' || els[j].name == 'fsform') {
			chkvalfontsize(els[j]);
			els[j].onchange = (function (j) { return function () { chkvalfontsize(els[j]); } }(j));
		}
		else if (els[j].name == 'font') {
			chkvalfont(els[j]);
			els[j].onchange = (function (j) { return function () { chkvalfont(els[j]); } }(j));
		}
		else if (els[j].name != 'sub') {
			chkval(els[j]);
			els[j].onchange = (function (j) { return function () { chkval(els[j]); } }(j));
		}
		var sw = document.getElementById("sw_"+els[j].name);
		if (sw !== null) {
			sw.onmousedown = (function (j, sw) {
				return function (e) {
					var e = e || window.event;
					openPal(sw, els[j]);
					e.cancelBubble = true;
					return false;
				}
			}(j, sw));
		}
	}
	isInit = false;
	var $pal = document.getElementById("colpal");
	$pal.onmouseover = palmouseover;
	var helpshown = false;
	window.fclick = function (n) { if (fon) return; fon = true; f[n].focus(); f[n].select(); }
	window.fclickup = function () { fon = false; }
	function openPal ($sw, $output) {
		var origDocMD = document.onmousedown;
		curHexInp = $output;
		var $pal = document.getElementById("palFloat");
		document.body.appendChild($pal);
		$pal.style.display = "block";
		var dim = $sw.getBoundingClientRect();
		var pdim = $pal.getBoundingClientRect();
		var stop = (document.documentElement && document.documentElement.scrollTop) || document.body.scrollTop;
		$pal.style.left = (dim.left - (pdim.right - pdim.left)) + "px";
		$pal.style.top = dim.bottom + stop + "px";
		$pal.onmousedown = function (e) { var e = e || window.event; e.cancelBubble = true; return false; }
		document.onmousedown = function () { $pal.style.display = "none"; document.onmousedown = origDocMD; }
	}
	function fblur(obj) {}
	function setstyle (sel, prop, val) {
		callNexusBox("setStyle", {sel: sel, prop: prop, val: val});
		if (isInit) { return; }
		onchange();
	}
	function chkval(i) {
		var invalid = false;
		if (i.value.length == 0) { i.style.color = ""; dostyle(i.name, "transparent"); return true; }
		if (i.value.substring(0, 1) !== "#") { i.value = "#"+i.value; }
		if (i.value.length != 7) { invalid = true; }
		else {
			for (l = 1; l < 7; l++) {
				k = i.value.charCodeAt(l);
				if ((k < 48 || k > 57) && (k < 65 || k > 70) && (k < 97 || k > 102)) { invalid = true; break; }
			}
		}
		if (invalid) { i.style.color = "#ff0000"; return; }
		i.value = i.value.toUpperCase();
		i.style.color = "";
		dostyle(i.name, i.value);
	}
	function chkvalfont(obj) {
		v = obj.value;
		setstyle('td', 'fontFamily', v+', sans-serif');
		setstyle('.stxt', 'fontFamily', v+', sans-serif');
		setstyle('.stxt2', 'fontFamily', v+', sans-serif');
		setstyle('.frmtb', 'fontFamily', v+', sans-serif');
		setstyle('.frmbtn', 'fontFamily', v+', sans-serif');
	}
	function chkvalfontsize(obj) {
		v = obj.value;
		if (v >= 0 && v <= 1000 ) {
			obj.style.color = "";
			if (obj.name == 'fsmain') {
				setstyle('td', 'fontSize', v+'pt');
				setstyle('.stxt', 'fontSize', v+'pt');
				setstyle('.stxt2', 'fontSize', v+'pt');
			}
			else if (obj.name == 'fsdate') { setstyle('.dtxt, .dtxt2', 'fontSize', v+'pt'); }
			else if (obj.name == 'fsform') {
				setstyle('.fmbdy', 'fontSize', v+'pt');
				setstyle('.frmtb', 'fontSize', v+'pt');
				setstyle('.frmbtn', 'fontSize', v+'pt');
			}
		}
		else obj.style.color = "#ff0000";
	}
	function dostyle(item, oval) {
		var val = oval;
		var d = document.getElementById("sw_"+item);
		try { d.style.backgroundColor = oval; } catch (e) {}
		switch (item) {
			case 'stcol': sel = '.stxt, .stxt2';prop = 'color';break;
			case 'stcol2': sel = '.stxt2';prop = 'color';break;
			case 'sdcol': sel = '.dtxt, .dtxt2, .status';prop = 'color';break;
			case 'sdcol2': sel = '.dtxt2';prop = 'color';break;
			case 'tbcol': sel = '.frmtb';prop = 'color';break;
			case 'btncol': sel = '.frmbtn';prop = 'color';break;
			case 'bg2': sel = '.fmbdy';prop = 'backgroundColor';break;
			case 'stbg': sel = '.mnbdy';prop = 'backgroundColor'; setstyle(sel, prop, val); sel = '.stxt, .stxt2';prop = 'backgroundColor';break;
			case 'stbg2': sel = '.stxt2';prop = 'backgroundColor';break;
			case 'tbbg': sel = '.frmtb';prop = 'backgroundColor';break;
			case 'link': sel = 'a:link';prop = 'color'; setstyle('a:visited', 'color', val); setstyle('.lnk', 'color', val);break;
			case 'linkactive': sel = 'a:hover';prop = 'color'; setstyle('a:active', 'color', val);break;
			case 'btnbg': sel = '.frmbtn';prop = 'backgroundColor';break;
			case 'tbbdr': sel = '.frmtb';prop = 'border'; if (val != 'transparent') val = val+" 1px solid"; else val = "none"; setstyle('.frmbtn', 'border', val);break;
			case 'sface': sel = 'body';prop = 'scrollbarFaceColor'; setstyle(sel, prop, val); sel = 'body';prop = 'scrollbarHighlightColor'; setstyle(sel, prop, val); sel = 'body';prop = 'scrollbarShadowColor';break;
			case 'strack': sel = 'body';prop = 'scrollbarBaseColor'; setstyle(sel, prop, val); sel = 'body';prop = 'scrollbarTrackColor';break;
			case 's3dlight': sel = 'body';prop = 'scrollbar3dLightColor'; setstyle(sel, prop, val); sel = 'body';prop = 'scrollbarDarkShadowColor';break;
			case 'sarrow': sel = 'body';prop = 'scrollbarArrowColor';break;
			case 'pn_std': sel = '.pn_std';prop = 'color';if (val == 'transparent') val = '';break;
			case 'pn_reg': sel = '.pn_reg';prop = 'color';if (val == 'transparent') val = '';break;
			case 'pn_mod': sel = '.pn_mod';prop = 'color';if (val == 'transparent') val = '';break;
			case 'pn_adm': sel = '.pn_adm';prop = 'color';if (val == 'transparent') val = '';break;
			default: sel = '';prop = '';
		}
		setstyle(sel, prop, val);
	}
}

function slide(evnt, orientation, length, from, to, count, decimals, display) {
	if (!evnt) evnt = window.event;
	sliderObj = (evnt.target) ? evnt.target : evnt.srcElement;
	sliderObj.pxLen = length;
	sliderObj.valCount = count ? count - 1 : length;
	sliderObj.scale = (to - from) / length;
	sliderObj.fromVal = from;
	xMax = length;
	pxLeft = parseInt(sliderObj.style.left);
	if (isNaN(pxLeft)) pxLeft = 0;
	xCoord = evnt.screenX;
	mouseover = true;
	document.onmousemove = moveSlider;
	document.onmouseup = sliderMouseUp;
	return false;
}

function sliderMouseUp() {
	mouseover = false;
	pos = (satval - sliderObj.fromVal)/(sliderObj.scale);
	sliderObj.style.left = pos+"px";
	if (document.removeEventListener) {
		document.removeEventListener('mousemove', moveSlider, false);
		document.removeEventListener('mouseup', sliderMouseUp, false);
	}
	else if (document.detachEvent) {
		document.detachEvent('onmousemove', moveSlider);
		document.detachEvent('onmouseup', sliderMouseUp);
	}
}

function moveSlider(evnt) {
	var evnt = (!evnt) ? window.event : evnt;
	if (mouseover) {
		x = pxLeft + evnt.screenX - xCoord;
		if (x > xMax) x = xMax;
		if (x < 0) x = 0;
		sliderObj.style.left = x+"px";
		sliderVal = x;
		sliderPos = (sliderObj.pxLen / sliderObj.valCount) * Math.round(sliderObj.valCount * sliderVal / sliderObj.pxLen);
		satval = Math.round(sliderPos * sliderObj.scale + sliderObj.fromVal);
		g = document.getElementById("colpal-sat");
		g.style.opacity = satval/100;
		g.style.filter = "alpha(opacity="+satval+")";
		return false;
	}
	return;
}

function findPos(obj) {
	var curleft = curtop = 0;
	if (obj.offsetParent) {
		do { curleft += obj.offsetLeft; curtop += obj.offsetTop; } while (obj = obj.offsetParent);
	}
	return [curleft,curtop];
}

var satval = 100;

function colourHSLtoRGB (hue, lightness, saturation) {
	var x = lightness;
	var y = hue % 1;
	var v = saturation;
	var rng = [[1, 0, 0], [1, 1, 0], [0, 1, 0], [0, 1, 1], [0, 0, 1], [1, 0, 1]];
	var dta = [[0, 1, 0], [-1, 0, 0], [0, 0, 1], [0, -1, 0], [1, 0, 0], [0, 0, -1]];
	var k = Math.floor(y*6);
	var r = rng[k][0] + dta[k][0]*(y*6)%1;
	var g = rng[k][1] + dta[k][1]*(y*6)%1;
	var b = rng[k][2] + dta[k][2]*(y*6)%1;
	if (x > 0.5) { r += (1-r) * ((x-0.5)/0.5); g += (1-g) * ((x-0.5)/0.5); b += (1-b) * ((x-0.5)/0.5); }
	else { r = r * (x)/0.5; g = g * (x)/0.5; b = b * (x)/0.5; }
	r += (r > x) ? (r-x)*(v-1) : (x-r)*(1- v);
	g += (g > x) ? (g-x)*(v-1) : (x-g)*(1- v);
	b += (b > x) ? (b-x)*(v-1) : (x-b)*(1- v);
	return {r: (r*256)|0, g: (g*256)|0, b: (b*256)|0};
}

var colourGetHex = function (col) {
	var r = col.r, b = col.b, g = col.g;
	function toHex(N) {
		if (N==null) return "00";
		N=parseInt(N); if (N==0 || isNaN(N)) return "00";
		N=Math.max(0,N); N=Math.min(N,255); N=Math.round(N);
		return "0123456789ABCDEF".charAt((N-N%16)/16) + "0123456789ABCDEF".charAt(N%16);
	}
	return toHex(r)+toHex(g)+toHex(b)
};

var colourPerceivedLightness = function (col) {
	var r = col.r, b = col.b, g = col.g;
	var pbr = Math.sqrt(r*r*0.241 + g*g*0.691 + b*b*0.068);
	return pbr/256;
}

var curHexInp = null;

var colourPicked = function (col) {
	var r = col.r, b = col.b, g = col.g;
	if (curHexInp) {
		curHexInp.value = colourGetHex(col);
		curHexInp.onchange();
	}
}

function palmouseover(e) {
	var e = e || window.event;
	var $pal = document.getElementById("colpal");
	$pal.onmouseover = null;
	t = document.getElementById("colpal");
	z = findPos(t);
	var ppxLeft = z[0];
	var ppxTop = z[1];
	var $mask = document.getElementById("palMask");
	$mask.style.display = "block";
	$mask.style.backgroundColor = "white";
	$mask.style.filter = "alpha(opacity=0)";
	$mask.style.opacity = 0;
	var $palSel = document.getElementById("palColCircle");
	$palSel.style.display = "block";
	var getColAtCurPos = function (e) {
		var evnt = e || window.event;
		if (evnt.pageX || evnt.pageY) { posx = evnt.pageX; posy = evnt.pageY; }
		else if (evnt.clientX || evnt.clientY) {
			posx = evnt.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
			posy = evnt.clientY + document.body.scrollTop + document.documentElement.scrollTop;
		}
		$palSel.style.left = posx-ppxLeft - 18 + "px";
		$palSel.style.top = posy-ppxTop - 18 + "px";
		var x = posx-ppxLeft;
		var y = posy-ppxTop;
		return colourHSLtoRGB(Math.min(1, y / 256), Math.min(1, x / 128), Math.min(1, satval/100));
	}
	$mask.onmousemove = function (e) {
		var e = e || window.event;
		var col = getColAtCurPos(e);
		var b = colourPerceivedLightness(col);
		$palSel.style.background = "#"+colourGetHex(col);
		$palSel.style.borderColor = "#"+ colourGetHex({r: (1-b)*256, g: (1-b)*256, b: (1-b)*256});
		return false;
	}
	$mask.onmousedown = function (e) {
		var e = e || window.event;
		var col = getColAtCurPos(e);
		colourPicked(col);
		if (e.preventDefault) e.preventDefault();
		e.cancelBubble = true;
		return false;
	}
	$mask.onmouseout = function () {
		$palSel.style.display = "none";
		$mask.style.display = "none";
		window.setTimeout(function () { $pal.onmouseover = palmouseover; }, 100);
	}
}

var initCSSEditor = function (maxlen) {
	var updateCSS = function () {
		var f = document.forms["styleadv"];
		callNexusBox("replaceCSS", {cssText: f["advancedcss"].value});
	}
	var $el = document.forms["styleadv"]["advancedcss"];
	var tmrUpdate = null;
	$el.onkeyup = function () {
		if (tmrUpdate) { window.clearTimeout(tmrUpdate); tmrUpdate = null; }
		tmrUpdate = window.setTimeout(updateCSS, 500);
		var len = $el.value.length;
		if (len > maxlen) { $el.value = $el.value.substring(0, maxlen); }
	}
	$el.onchange = updateCSS;
	$el.onmousedown = updateCSS;
	$el.onkeydown = function(e) {
		if(e.keyCode !== 9) { return; }
		if (typeof this.selectionStart === "undefined") { return; }
		var start = this.selectionStart;
		var end = this.selectionEnd;
		var len = $el.value.length;
		var target = $el;
		var value = target.value;
		var regex = /(\n|^)/g;
		var repl = "$1\t";
		if (e.shiftKey) { regex = /(\n|^)\t/g; repl = "$1"; }
		target.value = value.substring(0, start) + value.substring(start, end).replace(regex, repl) + value.substring(end);
		var numnew = (target.value.length)-len;
		if (start == end) { this.selectionStart = end + numnew; }
		else { this.selectionStart = start; }
		this.selectionEnd = end + numnew;
		e.preventDefault();
	};
	updateCSS();
};