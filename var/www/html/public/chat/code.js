// NexusBox Chat - Simplified JavaScript
var cbx = { ver: 1063, lp: 0 };
var lang = [], config = {};

cbx.debug = {
    loadTime: (new Date()).getTime(),
    history: [],
    log: function(msg, group) {
        var time = (((new Date()).getTime() - cbx.debug.loadTime) / 1000).toFixed(3);
        cbx.debug.history.push({ time: time, msg: msg, group: group || "" });
        if (cbx.debug.history.length > 50) cbx.debug.history.shift();
        console.log("[" + time + "] [" + (group||"") + "] " + msg);
    }
};

function classToggle(elem, className, force) {
    if (!elem) return false;
    var classes = elem.className ? elem.className.split(" ") : [];
    var idx = classes.indexOf(className);
    var changed = false;
    if (idx === -1 && (force === true || force === undefined)) {
        classes.push(className);
        changed = true;
    } else if (idx !== -1 && (force === false || force === undefined)) {
        classes.splice(idx, 1);
        changed = true;
    }
    elem.className = classes.join(" ");
    return changed;
}

window.addEventListener('DOMContentLoaded', function() {
    cbx.debug.log("NexusBox Chat initialized");
    var form = document.forms.cbox;
    if (form) {
        var pst = form.pst;
        var submitBtn = document.getElementById('btnSubmit');
        if (pst) {
            pst.addEventListener('input', function() {
                if (pst.value.trim().length > 0) {
                    submitBtn.classList.remove('Disabled');
                    submitBtn.classList.add('Interactive');
                } else {
                    submitBtn.classList.add('Disabled');
                    submitBtn.classList.remove('Interactive');
                }
            });
            pst.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    doPost();
                }
            });
        }
        form.onsubmit = function(e) {
            e.preventDefault();
            doPost();
            return false;
        };
    }
    setupButtons();
});

function doPost() {
    var form = document.forms.cbox;
    if (!form) return false;
    var msg = form.pst.value.trim();
    var name = form.nme.value.trim() || 'Anonymous';
    if (!msg) {
        showStatus("Please type a message.", "Warn");
        form.pst.focus();
        return false;
    }
    addMessage({
        id: 'L' + Date.now(),
        name: escapeHtml(name),
        message: escapeHtml(msg),
        time: new Date(),
        flag: 'jo.png'
    });
    form.pst.value = '';
    form.pst.style.height = 'auto';
    var submitBtn = document.getElementById('btnSubmit');
    submitBtn.classList.add('Disabled');
    submitBtn.classList.remove('Interactive');
    showStatus("Message posted", "Okay");
    return false;
}

function addMessage(data) {
    var messages = document.getElementById('messages');
    if (!messages) return;
    var msgDiv = document.createElement('div');
    msgDiv.className = 'msg';
    msgDiv.setAttribute('data-id', data.id);
    msgDiv.setAttribute('data-time', Math.floor(data.time.getTime() / 1000));
    msgDiv.setAttribute('data-uid', '0');
    msgDiv.setAttribute('data-lvl', '1');
    var timeStr = formatTime(data.time);
    msgDiv.innerHTML = 
        '<span class="pic Empty"></span>' +
        '<img class="flag" src="flags/' + (data.flag || 'jo.png') + '">' +
        '<div class="dtxt" title="' + data.time.toLocaleString() + '">' + timeStr + '</div>' +
        '<div class="nme">' + data.name + '</div>' +
        '<div class="body">' + data.message + '</div>';
    var msgHead = document.getElementById('msgHead');
    if (msgHead) {
        messages.insertBefore(msgDiv, msgHead);
    } else {
        messages.appendChild(msgDiv);
    }
    messages.scrollTop = messages.scrollHeight;
}

function formatTime(date) {
    var now = new Date();
    var diff = Math.floor((now - date) / 1000);
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + ' min ago';
    if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
    return Math.floor(diff / 86400) + ' days ago';
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showStatus(msg, type) {
    var barNotice = document.getElementById('barNotice');
    var barDefault = document.getElementById('barDefault');
    var notice = document.getElementById('notice');
    if (!barNotice || !barDefault || !notice) return;
    notice.textContent = msg;
    barNotice.className = 'bar ' + (type || 'Okay');
    barDefault.className = 'bar Hide';
    setTimeout(function() {
        barNotice.className = 'bar Hide';
        barDefault.className = 'bar';
    }, 5000);
}

function setupButtons() {
    var btnVolume = document.getElementById('btnVolume');
    if (btnVolume) {
        btnVolume.addEventListener('click', function() {
            showStatus("Notification options", "Warn");
        });
    }
    var btnPop = document.getElementById('btnPop');
    if (btnPop) {
        btnPop.addEventListener('click', function() {
            window.open(window.location.href, 'nexusbox_pop', 'width=600,height=600');
        });
    }
    var btnSmilies = document.getElementById('btnSmilies');
    if (btnSmilies) {
        btnSmilies.addEventListener('click', function() {
            showStatus("Smilies panel", "Warn");
        });
    }
    var btnUser = document.getElementById('btnUser');
    if (btnUser) {
        btnUser.addEventListener('click', function() {
            showStatus("User profile", "Warn");
        });
    }
    // Profile button - NO CODE HERE (auth.js handles it)
    var btnRefresh = document.getElementById('btnRefresh');
    if (btnRefresh) {
        btnRefresh.addEventListener('click', function(e) {
            e.preventDefault();
            showStatus("Refresh", "Okay");
            return false;
        });
    }
}

window.CBXINIT = function(cfg, lng) {
    config = cfg;
    lang = lng;
    cbx.debug.log("Config loaded", "INIT");
};

console.log("NexusBox Chat loaded");