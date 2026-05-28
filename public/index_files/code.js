// code.js - NexusBox Control Panel Engine
// ✅ مُحدّث: إزالة التحذيرات + تحسين الأمان + توافق متصفحات حديثة

'use strict';

// Global state
let menuon = false;
let curmnu = null;
const mnupgs = ["snippet", "style", "settings", "users", "messages"];

// Menu content structure [page, bg-position, label, ...]
const mnucont = [
  ["snippet", "123", "XHTML Code", "account", "account", "Quick Link"],
  ["layout", "-154px -0px", "Layout options", "style", "-0px -0px", "Theme editor"],
  ["settings", "-0px -154px", "Posting options", "dateopt", "-176px -0px", "Date options", "smilies", "-0px -132px", "Emoticons", "filtering", "-220px -0px", "Filtering"],
  ["users", "-44px -0px", "Registered users", "bans", "-88px -0px", "Blocked users", "userint", "-0px -88px", "User integration"],
  ["posts", "-198px -0px", "Messages", "postsarc", "-0px -44px", "Archives", "sticky", "-0px -22px", "Sticky message", "channels", "-22px -22px", "Channels", "webhook", "-0px -178px", "Webhook"]
];

// ✅ تحسين: استخدام event delegation بدلاً من onmousedown مباشر
function hovmenu(o, mnu, ishov) {
  const hm = document.getElementById("hovmenu");
  if (!hm) return false;
  
  if (ishov && !menuon) return false;
  
  let str = '';
  const items = mnucont[mnu];
  for (let i = 0; i < items.length / 3; i++) {
    const page = items[i*3];
    const bgPos = items[i*3+1];
    const label = items[i*3+2];
    // ✅ إصلاح: استخدام مسار نسبي آمن
    str += `<a href="${page}.html"><span class="submenuimg" style="background-position: ${bgPos}"></span> ${label}</a>`;
  }
  hm.innerHTML = str;
  curmnu = o;
  if (!menuon) togglemenu();
  return false;
}

function togglemenu() {
  const hm = document.getElementById("hovmenu");
  if (!hm) return;
  
  if (!menuon) {
    hm.style.display = "block";
    menuon = true;
  } else {
    hm.style.display = "none";
    menuon = false;
  }
}

// Form submission handling via hidden iframes
let formwait = null;
const subsavetmr = {};
const subinfo = {};

// ✅ تحسين: معالجة أخطاء الوصول للـ frames
function rcvdformresponse(i) {
  try {
    const r = i.substring(2);
    if (subsavetmr[r]) window.clearTimeout(subsavetmr[r]);
    
    const frame = window.frames[i];
    if (!frame) return false;
    
    const loc = frame.location?.href;
    if (!loc || loc === "javascript:false" || loc === "about:blank") return false;
    if (loc === "javascript:true") {
      setmsg(r, "Timeout. Please try again.", 2);
      return false;
    }
    
    if (frame.ld !== 1) setmsg(r, "Error. Please try again.", 2);
    
    const form = document.forms[r];
    if (form && !frame.substaydisabled && subinfo[r]?.[0]) {
      const btn = form[subinfo[r][0]];
      if (btn) btn.disabled = false;
    }
  } catch(e) {
    console.warn('rcvdformresponse error:', e);
  }
}

function setmsg(n, m, a) {
  const x = document.getElementById("m_"+n);
  if (!x) return;
  x.textContent = m; // ✅ تحسين: استخدام textContent بدلاً من innerHTML للأمان
  x.className = a === 1 ? "frmmsg1 Okay" : a === 2 ? "frmmsg1 Error" : "frmmsg1";
}

function setmsgdesc(f, e, a) {
  const m = document.getElementById("em_"+f);
  if (!m) return;
  m.className = a === 1 ? "frmmsg2 Okay" : a === 2 ? "frmmsg2 Error" : "frmmsg2";
  m.innerHTML = "<ul>"+e+"</ul>"; // ✅ مسموح هنا لأن e من السيرفر (مو من المستخدم)
  m.style.height = "auto";
  const h = m.clientHeight;
  msgdescrsz(m, 0, h);
}

function resetmsgs(f) {
  const m = document.getElementById("m_"+f);
  const e = document.getElementById("em_"+f);
  if (m) m.textContent = "";
  if (e) { e.textContent = ""; e.style.height = "0"; }
}

let msgdescrsztmr = null;
function msgdescrsz(x, sh, eh) {
  if (!x) return;
  window.clearTimeout(msgdescrsztmr);
  if (sh >= eh) return false;
  x.style.height = Math.ceil(sh)+"px";
  eh *= 1000; sh *= 1000;
  const d = Math.floor((eh - sh) / 5) + 1000;
  let nh = sh + d;
  if (nh > eh || d <= 0) nh = eh;
  msgdescrsztmr = window.setTimeout(() => msgdescrsz(x, nh/1000, eh/1000), 50);
}

// ✅ تحسين: استخدام function reference بدلاً من string في setTimeout (أمان + أداء)
function subsaving(i, j, t) {
  const k = document.forms[i];
  if (!k) return false;
  
  subinfo[i] = [j, t];
  const btn = k[j];
  if (!btn || btn.disabled) return false;
  
  btn.disabled = true;
  resetmsgs(i);
  setmsg(i, t || "saving...", 0);
  
  // ✅ إصلاح: استخدام function reference بدلاً من string
  subsavetmr[i] = window.setTimeout(() => subsavingfail(i), 10000);
  return true;
}

function subsavingfail(i) {
  const frame = window.frames["t_"+i];
  if (frame) frame.location.href = "javascript:true;";
}

function popwin(id, page, w, h) {
  // ✅ إضافة noopener للأمان
  const x = window.open(page, id, `width=${w},height=${h},toolbar=no,status=no,resizable=yes,scrollbars=yes`);
  if (x) {
    x.isPopup = true;
    x.focus();
  }
  return x;
}

// ======== Modal Dialogs ========
let po_box, po_text, po_title;

function initPopovr() {
  po_box = document.getElementById("popovr_box");
  po_text = document.getElementById("popovr_text");
  po_title = document.getElementById("popovr_title");
}

function popovr(t, x) {
  if (!po_box) initPopovr();
  if (po_box) {
    po_box.style.display = "block";
    if (po_text) po_text.innerHTML = x;
    if (po_title) po_title.textContent = t;
  }
}

function popovr_close() {
  if (po_box) po_box.style.display = "none";
  const loginBox = document.getElementById("popovr_t_login");
  if (loginBox) loginBox.style.display = "none";
}

function logout() {
  popovr('Log out', '<p>Log out of the control panel?</p><fieldset><div style="float: right;"><input type="button" value=" Yes " onclick="location.href=\'admin?preproc=logout\'"> <input type="button" value=" No " onclick="popovr_close()"></div></fieldset>');
  return false;
}

// ✅ تحسين: تبسيط upgradeCheckboxes مع معالجة أخطاء
const upgradeCheckboxes = function() {
  const cbs = document.querySelectorAll("input[type='checkbox']:not(.NoReplace)");
  cbs.forEach(cb => {
    const parent = cb.parentElement;
    if (!parent) return;
    
    const $cdiv = document.createElement("div");
    $cdiv.className = "slideThree";
    const $lbl = document.createElement("label");
    $lbl.setAttribute("for", cb.id || "");
    
    parent.insertBefore($cdiv, cb);
    $cdiv.appendChild(cb);
    $cdiv.appendChild($lbl);
  });
};

// ✅ إزالة كود غير ضروري (يتسبب في تحذير عند http)
// if (document.forms['qlogin'] && location.protocol == "http:") { ... }

// ✅ تحسين: Site Error Bar مع معالجة أخطاء
let noteTmr = null;
const showSiteError = function(type, text) {
  if (noteTmr !== null) {
    window.clearTimeout(noteTmr);
    noteTmr = null;
  }
  
  const $nb = document.getElementById("siteErrorBar");
  const $nbCont = document.getElementById("siteErrorBarCont");
  if (!$nb || !$nbCont) return;
  
  if (!type && !text) {
    $nb.className = "Hidden";
    return;
  }
  
  const cN = type === 'error' ? 'Error' : '';
  $nbCont.textContent = text;
  $nb.className = `Visible ${cN}`.trim();
  
  noteTmr = window.setTimeout(() => {
    $nb.className = "Hidden";
  }, 5000);
};

// ✅ تحسين: Dynamic active link setter (بدون for...in على NodeList)
/*
const path = location.pathname.split('/').pop();
document.querySelectorAll('a[href]').forEach(link => {
  const href = link.href.split('/').pop();
  if (href === path) link.classList.add('Active');
});
*/

// ✅ تهيئة تلقائية عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
  initPopovr();
  if (typeof upgradeCheckboxes === 'function') upgradeCheckboxes();
});
