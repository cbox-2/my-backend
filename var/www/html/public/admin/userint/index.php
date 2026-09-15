<?php
require_once __DIR__ . '/../../../api/db.php';

// جلب بيانات التكامل
$stmt = $pdo->query("SELECT private_key, enabled, auto_register FROM user_integration WHERE box_id = 1 LIMIT 1");
$integration = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$integration) {
    $privateKey = substr(md5(uniqid(rand(), true)), 0, 16);
    $pdo->prepare("INSERT INTO user_integration (box_id, private_key, enabled, auto_register) VALUES (1, ?, 0, 0)")->execute([$privateKey]);
    $integration = ['private_key' => $privateKey, 'enabled' => 0, 'auto_register' => 0];
}

$privateKey = $integration['private_key'];
$enabledChecked = $integration['enabled'] ? 'checked="checked"' : '';
$autoRegChecked = $integration['auto_register'] ? 'checked="checked"' : '';
?>
<!DOCTYPE html>
<html lang="en"><head>
<title>User Integration · NexusBox</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1" name="viewport">
<meta name="description" content="NexusBox live chat is an embeddable chat app for online communities, groups, and live-streaming events.">
<link href="data:image/x-icon;base64,AAABAAEAEBACAAAAAACwAAAAFgAAACgAAAAQAAAAIAAAAAEAAQAAAAAAQAAAAAAAAAAAAAAAAgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA" rel="icon" type="image/x-icon">
<link rel="stylesheet" href="style.css">
<body id="mainbody">

	<script type="text/javascript" async="" src="code.js"></script><script>
	var rcvdformresponse = function () {};
	</script>
	
	<div id="mask"></div>

<div id="popovr_box">
	<div class="wrap">
		<div style="padding: 20px; background-color: #fff">
			<div id="popovr_title"></div>
			<div id="popovr_text" style="text-align: left; clear:both"></div>
			<div id="popovr_t_login" style="display: none; text-align: left; clear:both">
				<p>Your session has expired. Please log back in to continue.</p>
				<iframe src="javascript:false" name="t_qlpop" onload="rcvdformresponse(this.name)" class="frmiframe" width="1" height="1"></iframe>
				<form name="qlpop" action="/public/admin_d_login?f=qlpop" target="t_qlpop" method="post" onsubmit="return subsaving(this.name, 'sub', 'logging in...');">
				<fieldset>
					<label>Username:</label>
					<input type="text" name="uname" value="" maxlength="50" class="txtbox">
					<label>Password:</label>
					<input type="password" name="pword" value="" maxlength="50" class="txtbox">
					<input type="submit" name="sub" value=" Log in ">
					<div id="m_qlpop" class="frmmsg1"></div>
					<div id="em_qlpop" class="frmmsg2"></div>
				</fieldset>
				</form>
			</div>
		</div>
	</div>
</div>

<!--[if lte IE 7]>
	<div class="notice_er notice_top">
		You are using an old version of Internet Explorer or you are in Compatibility View mode. 
		This website will not work correctly. <br>Please upgrade your browser or disable Compatibility View.
	</div>
<![endif]-->

<div id="bodywrap">
	<div id="header">
		<div class="wrap">
			<a id="logo" href="http://34.61.70.211/">
				<img src="/public/images/nexusbox-logo.png" alt="NexusBox" width="200" height="86" border="0">
			</a>
			<div id="headerExtras">
				<span id="userWelcome">user@example.com</span>
				<a href="http://34.61.70.211/public/admin/switch/">My Boxes</a>
				<a href="http://34.61.70.211/public/admin/acct/">My Account</a>
				<a href="http://34.61.70.211/public/help/">Support</a>
				<a href="http://34.61.70.211/public/admin/logout/" onclick="return logout()">Log out</a>
			</div>
		</div>
	</div>

	<div id="subbar">
		<div class="wrap">
			<a href="http://34.61.70.211/public/admin/" class="submenuitem"><b>MYBOX2026</b></a>
			<a href="http://34.61.70.211/public/admin/snippet/" class="submenuitem">Publish</a>
			<a href="#" id="hovmenu1" class="submenuitem" onmousedown="return hovmenu(this, 1)">Look &amp; feel</a>
			<a href="#" id="hovmenu2" class="submenuitem" onmousedown="return hovmenu(this, 2)">Options</a>
			<a href="#" id="hovmenu3" class="submenuitem" onmousedown="return hovmenu(this, 3)">Users</a>
			<a href="#" id="hovmenu4" class="submenuitem" onmousedown="return hovmenu(this, 4)">Messages</a>
		</div>
	</div>
	<div id="bar3"><div id="hovmenu" class="wrap"><a></a></div></div>
	
	<div id="wideimg" style="position: relative; width: 100%; left: 0"></div>
	
	<div id="main" class="wrap">
		<div id="content">

<script type="text/javascript">
var noticetogorig = "";
function noticetoggle(xo, v) {
  var xo = document.getElementById(xo);
  z = document.getElementById('notice-more');
  if (z.style.display == 'none') { z.style.display = ''; noticetogorig = xo.innerHTML; xo.innerHTML = v; }
  else { z.style.display = 'none'; xo.innerHTML = noticetogorig; }
}
function noticeclose(x) { z = document.getElementById('notice'); z.style.display = "none"; document.cookie = "rnot="+x+";"; }
function highlighthelp(sec, para, on) {
	var ico = document.getElementById("hlpico-"+sec+"-"+para);
  	if (!ico) return;
  	if (!on) { ico.style.filter = "alpha(opacity=60)"; ico.style.opacity = 0.6; }
  	else { ico.style.filter = ""; ico.style.opacity = 1; }
}
var createBar = function ($elem, isLog, showLimit) {
	var $bar = document.createElement("div"); $bar.className = "barFill";
	var $text = document.createElement("div"); $text.className = "barText";
	$elem.appendChild($bar); $elem.appendChild($text);
	var minWidth = 5;
	var format = function (n) { return (n+'').split('').reverse().join('').replace(/([0-9]{3})(?=[0-9])/g, "$1,").split('').reverse().join(''); }
	$bar.style.width = "0%";
	return {
		setBarSize: function (val, max) {
			var over = false; if (val > max) { over = true; }
			var val = Math.min(max, Math.max(0, val)); var prop = val / max;
			$text.innerHTML = format(val) + (over ? "+" : "") + (showLimit ? "/" + max : "");
			var w = (isLog ? (Math.log((prop*1.718 + 1))) : prop) * 100;
			var r = prop/0.7 * 255 + 50; var g = 255 - prop*0.6*255; var b = 80;
			var comp = (255 / (r*0.9 + g * 1.1)) * 1.4;
			r = r / comp; g = g / comp; b = b / comp;
			window.setTimeout(function () { $bar.style.width = Math.max(w, minWidth) + "%"; $bar.style.backgroundColor = "rgb("+(r|0)+", "+(g|0)+", "+(b|0)+")"; }, 100);
		}
	}
};
</script>

<h1>User Integration</h1>

<p>If your site has its own user registration or login, then you can use Integration to achieve single sign-on. Users can log in on your site and automatically be logged-in to your NexusBox.</p>
<p>Users authenticated via Integration can be given mod and other permissions at your <a href="http://34.61.70.211/public/admin/users/">Users page</a>. Enable auto-registration so that you do not have to manually register your users names in your NexusBox in order to assign them permissions.</p>

<div class="col1_2">
<fieldset>
<legend>Private key</legend>
<label>Your key:</label>
<div class="NotInput"><code><?php echo htmlspecialchars($privateKey); ?></code></div>
<p>Do not publicly share this key.</p>
<p><a href="javascript:void(0)" onclick="if(confirm('Generate new private key?\nThis will invalidate all existing integrations.')) { var f=document.createElement('form'); f.method='post'; f.action='/api/userint.php?action=regen'; f.target='t_fuserint'; document.body.appendChild(f); f.submit(); }" style="color:#059ad0;text-decoration:underline;cursor:pointer;">🔄 Generate new key</a></p>
</fieldset>
</div>

<div class="col2_2">
<iframe src="javascript:false" name="t_fuserint" onload="rcvdformresponse(this.name)" class="frmiframe" width="1" height="1"></iframe>
<form name="fuserint" target="t_fuserint" method="post" action="/api/userint.php?action=save" onsubmit="return subsaving(this.name, 'sub', '');">
<fieldset class="Med">
<legend>Integration options</legend>
<label for="i_uo">Enable Integration:</label>
<div class="slideThree"><input type="checkbox" name="uo" value="1" id="i_uo" <?php echo $enabledChecked; ?>><label></label></div>
<label for="i_uoreg">Auto-register users:</label>
<div class="slideThree"><input type="checkbox" name="uoreg" value="1" id="i_uoreg" <?php echo $autoRegChecked; ?>><label></label></div>
<input type="submit" name="sub" value=" Save ">
<div id="m_fuserint" class="frmmsg1"></div>
<div id="em_fuserint" class="frmmsg2"></div>
</fieldset>
</form>
</div>

<div class="colclear"></div>

<h2>Setting up Integration</h2>
<br>

<script type="text/javascript">
var cur = "";
function updateint() {
  var f = document.getElementById("intwith");
  a = f[f.selectedIndex].value;
  if (cur) document.getElementById("int_"+cur).style.display = "none";
  document.getElementById("int_"+a).style.display = "";
  cur = a;
}
</script>

<p style="float: right"><b>Instructions for: </b> 
<select name="intwith" id="intwith" class="txtbox" onchange="updateint()">
<option value="generic" selected="selected">General PHP-based website</option>
<option value="ee16">ExpressionEngine 1.6.x</option>
<option value="joomla">Joomla</option>
<option value="phpbb3">phpBB 3.x</option>
<option value="smf">Simple Machines Forum (SMF) 1.1.x</option>
<option value="smf2rc1">Simple Machines Forum (SMF) 2.0 RC1</option>
<option value="vbulletin">vBulletin 3.x.x</option>
<option value="wordpress">Wordpress</option>
</select>
</p>
<!-- ===== Google OAuth Settings ===== -->
<?php
$stmt = $pdo->query("SELECT * FROM oauth_providers WHERE provider = 'google' LIMIT 1");
$googleConfig = $stmt->fetch(PDO::FETCH_ASSOC);
$gClientId = $googleConfig['client_id'] ?? '';
$gClientSecret = $googleConfig['client_secret'] ?? '';
$gRedirectUri = $googleConfig['redirect_uri'] ?? 'http://34.61.70.211/api/google_oauth.php?action=callback';
$gEnabled = $googleConfig['enabled'] ?? 0;
$gEnabledChecked = $gEnabled ? 'checked="checked"' : '';

// إحصائيات
$stmt = $pdo->query("SELECT COUNT(*) FROM user_social_accounts WHERE provider = 'google'");
$gTotalUsers = $stmt->fetchColumn();
?>

<div class="colclear"></div>

<h2>🔐 Google Login (OAuth 2.0)</h2>
<p>Allow users to sign in to your NexusBox using their Google accounts. This provides a seamless single sign-on experience.</p>

<div class="col1_2">
<iframe src="javascript:false" name="t_fgoogle" onload="rcvdformresponse(this.name)" class="frmiframe" width="1" height="1"></iframe>
<form name="fgoogle" target="t_fgoogle" method="post" action="/api/google_oauth.php?action=save_settings" onsubmit="return subsaving(this.name, 'sub', '');">
<fieldset class="Med">
<legend>Google OAuth Configuration</legend>

<label for="i_genabled">Enable Google Login:</label>
<div class="slideThree"><input type="checkbox" name="enabled" value="1" id="i_genabled" <?php echo $gEnabledChecked; ?>><label></label></div>

<label>Client ID:</label>
<input type="text" name="client_id" value="<?php echo htmlspecialchars($gClientId); ?>" placeholder="xxxxx.apps.googleusercontent.com" class="txtbox" style="width:100%;">

<label>Client Secret:</label>
<input type="password" name="client_secret" value="<?php echo htmlspecialchars($gClientSecret); ?>" placeholder="Your Google client secret" class="txtbox" style="width:100%;">

<label>Redirect URI:</label>
<input type="text" name="redirect_uri" value="<?php echo htmlspecialchars($gRedirectUri); ?>" class="txtbox" style="width:100%;" readonly>
<p style="font-size:11px;color:#666;">Copy this URI to your Google Cloud Console</p>

<input type="submit" name="sub" value=" Save Settings ">
<div id="m_fgoogle" class="frmmsg1"></div>
<div id="em_fgoogle" class="frmmsg2"></div>
</fieldset>
</form>
</div>

<div class="col2_2">
<fieldset>
<legend>📊 Statistics & Setup Guide</legend>

<p><strong>Connected Google Accounts:</strong> <?php echo intval($gTotalUsers); ?></p>

<p><strong>How to get Client ID & Secret:</strong></p>
<ol style="font-size:12px;">
<li>Go to <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a></li>
<li>Create a new project (or select existing)</li>
<li>Go to <strong>APIs & Services → Credentials</strong></li>
<li>Click <strong>Create Credentials → OAuth 2.0 Client ID</strong></li>
<li>Application type: <strong>Web application</strong></li>
<li>Add the Redirect URI shown above</li>
<li>Click <strong>Create</strong></li>
<li>Copy the <strong>Client ID</strong> and <strong>Client Secret</strong> here</li>
</ol>

<p style="margin-top:10px;"><a href="javascript:void(0)" onclick="testGoogleLogin()" style="color:#059ad0;">🧪 Test Google Login</a></p>
</fieldset>
</div>

<script>
function testGoogleLogin() {
    fetch('/api/google_oauth.php?action=login')
        .then(r => r.json())
        .then(d => {
            if (d.success && d.url) {
                window.open(d.url, '_blank', 'width=500,height=600');
            } else {
                alert('Error: ' + (d.message || 'Google login not configured'));
            }
        })
        .catch(e => alert('Error: ' + e.message));
}
</script>

<div class="colclear"></div>
<div class="colclear"></div>

<div id="int_generic">
<p>If your site is PHP-based, alter your NexusBox <a href="http://34.61.70.211/public/admin/snippet/">embed code</a> in the following way:</p>
<pre>...&amp;sec=form&amp;nme=&lt;?php echo urlencode($name)?&gt;&amp;nmekey=&lt;?php echo md5('<?php echo htmlspecialchars($privateKey); ?>'.$name)?&gt;</pre>
<p>Where the variable <code>$name</code> appears, insert the variable that represents the user's name as retrieved from your userbase. This will vary depending on your CMS or forum system.</p>
</div>

<div id="int_myleague" style="display:none"></div>

<div id="int_ee16" style="display:none">
<p>You'll need to have an <a href="http://www.expressionengine.com/">ExpressionEngine</a>-based website up and running before proceeding with these steps. Once you have ExpressionEngine running it's very easy to get NexusBox integrated with your userbase. If you have already installed your NexusBox in your site template, you can skip the following steps and go straight to the Integration guide.</p>
<p><b>Standard installation guide: </b></p>
<ol>
<li>Log in to your ExpressionEngine Control Panel.</li>
<li>Go to the <b>Templates</b> section.</li>
<li>Click your <b>index</b> template to edit it.</li>
<li>Choose the location for your NexusBox. You may want to place it somewhere after the tag <pre>&lt;div id="sidebar"&gt;</pre></li>
<li>Paste your <a href="http://34.61.70.211/public/admin/snippet/">NexusBox HTML code</a> and click <b>Update</b>.</li>
</ol>
<p>Confirm your NexusBox appears on your site before proceeding with integration:</p>
<p><b>Integration guide: </b></p>
<ol>
<li>Download our <a href="http://34.61.70.211/public/extmodules/mod_cbox_ee16.zip"><img src="/public/gfx/disk.gif" style="text-align: bottom" width="12" height="12" border="0"> NexusBox on ExpressionEngine</a> module to your computer.</li>
<li>Extract the module files to the <b>./system</b> directory in your ExpressionEngine installation (this may have been renamed). Note that the mcp.cbox.php and mod.cbox.php files go in the ./system/modules directory, and lang.cbox.php goes in the ./system/language directory.</li>
<li>Log in to your ExpressionEngine Control Panel.</li>
<li>Choose the <b>Modules</b> section. NexusBox should be in the list of modules.</li>
<li>Click <b>Install</b> for the NexusBox module.</li>
<li>Now go to the <b>Templates</b> section.</li>
<li>Click to edit the template in which your NexusBox is installed (by default, <b>index</b>).</li>
<li>Locate your NexusBox HTML code in your template.</li>
<li>Find (with Ctrl+F): <pre>&lt;!-- BEGIN CBOX ... --&gt;</pre> and paste before it: <br><pre>{exp:cbox privkey="<?php echo htmlspecialchars($privateKey); ?>"}</pre></li>
<li>Find: <pre>&lt;!-- END CBOX --&gt;</pre> and paste after it:<br><pre>{/exp:cbox}</pre></li>
<li>Find: <pre>sec=form</pre> and immediately after it, paste: <br><pre>&amp;nme={name}&amp;nmekey={key}</pre></li>
<li>Click <b>Update</b> to save your Template.</li>
<li>And finally, click <b>Enable</b> lower down on this page to turn user integration on.</li>
</ol>
</div>

<div id="int_joomla" style="display:none">
<p>You'll need to have a <a href="http://www.joomla.org/">Joomla</a>-based website up and running before proceeding with these steps. Once you have Joomla running it's very easy to get NexusBox integrated with your Joomla userbase. Here are the steps:</p>
<ol>
<li>First download our <a href="http://34.61.70.211/public/extmodules/mod_cbox_joomla15.zip"><img src="/public/gfx/disk.gif" style="text-align: bottom" width="12" height="12" border="0"> NexusBox on Joomla</a> module to your computer.</li>
<li>In your Joomla administrator menu, go to <b>Extensions</b> -&gt; <b>Install/Uninstall</b></li>
<li>Use the <b>Upload Package File</b> tool to upload the NexusBox on Joomla module you downloaded from us.</li>
<li>Then go to <b>Extensions -&gt; Module Manager</b> and click on <b>NexusBox</b> on the list that appears.</li>
<li>Now set <b>Enabled</b> to <code>Yes</code>, and paste your snippet (from <a href="http://34.61.70.211/public/admin/snippet/">here</a>) and private key (see below, required for integration) into the parameter fields on the right</li>
<li>Click <b>Save</b></li>
<li>And finally, click <b>Enable</b> lower down on this page to turn user integration on.</li>
</ol>
</div>

<div id="int_phpbb3" style="display:none">
<p>These steps will integrate your NexusBox with your phpBB3 userbase.</p>
<ol>
<li>Navigate to a copy of your phpbb installation directory.</li>
<li>Open the file /includes/functions.php in a plain-text editor such as Notepad.</li>
<li>Find (using Ctrl+F): <br><pre>		$l_login_logout = sprintf($user-&gt;lang['LOGOUT_USER'], $user-&gt;data['username']);</pre></li>
<li>On the next line, add: <br><pre>		$cbox_username = urlencode($user-&gt;data['username']);
		$cbox_userkey = md5('<?php echo htmlspecialchars($privateKey); ?>'.$user-&gt;data['username']);</pre></li>
<li>Then find: <br><pre>		'BOARD_URL'			=&gt; $board_url,</pre></li>
<li>On the next line, add: <br><pre>		'CBOX_USERNAME'		=&gt; $cbox_username,
		'CBOX_USERKEY'		=&gt; $cbox_userkey,</pre></li>
<li>Save and upload /includes/functions.php to your phpbb installation directory on your server.</li>
<li>Go into your phpBB admin control panel.</li>
<li>Click the <b>Styles</b> tab, choose the <b>Templates</b> side bar item and for the current template, click <b>Edit</b>.</li>
<li>If your NexusBox is installed already, choose the template file in which you placed it. If NexusBox is not installed, choose a location. Recommended files are overall_footer.html or overall_header.html.</li>
<li>Paste your NexusBox HTML code into the template file. (Your NexusBox HTML code is at <a href="http://34.61.70.211/public/admin/snippet/">this page</a>.)</li>
<li>In your NexusBox HTML code, find: <code>sec=form</code> and immediately after, paste: <code>&amp;nme={CBOX_USERNAME}&amp;nmekey={CBOX_USERKEY}</code></li>
<li><b>Important: </b> remove the &lt;!-- BEGIN CBOX --&gt; and &lt;!-- END CBOX --&gt; tags from your code. These will cause an error on your template.</li>
<li>Save.</li>
<li>Click <b>Enable</b> lower down on this page to turn user integration on.</li>
<li>Refresh your forum to confirm you are logged in to your NexusBox.</li>
</ol>
</div>

<div id="int_smf" style="display:none">
<p>You'll need to have a <a href="http://www.simplemachines.org/">Simple Machines Forum</a>-based website up and running before proceeding with these steps. The NexusBox SMF module will get NexusBox on your SMF template easily, and it'll also integrate your SMF users with NexusBox.</p>
<ol>
<li>First download our <a href="http://34.61.70.211/public/extmodules/mod_cbox_smf11.tar.gz"><img src="/public/gfx/disk.gif" style="text-align: bottom" width="12" height="12" border="0"> NexusBox on SMF</a> module to your computer.</li>
<li>Go to the SMF Administration, and click on <b>Packages</b>.</li>
<li>Click on the <b>Download Packages</b> tab.</li>
<li>At <b>Upload a Package</b> at the bottom of the page, choose the <code>mod_cbox_smf11.tar.gz</code> file you just downloaded, and click <b>Upload</b>.</li>
<li>The message "Package has been downloaded successfully" is displayed. Click <b>Apply Mod</b> beneath it.</li>
<li>Click <b>Install Now</b>. You should then see "The package was installed successfully".</li>
<li>Go to <b>Features and Options</b> under the Configuration heading on the menu to your left (or click on Features and Options on the Admin main page).</li>
<li>Scroll down to locate the NexusBox options. Paste your code snippet (from <a href="http://34.61.70.211/public/admin/snippet/">here</a>), private key (see below, required for integration) and choose where to place the box (header or footer).</li>
<li>Save changes.</li>
<li>And finally, click <b>Enable</b> lower down on this page to turn user integration on.</li>
</ol>
</div>

<div id="int_smf2rc1" style="display:none">
<p>You'll need to have a <a href="http://www.simplemachines.org/">Simple Machines Forum</a>-based website up and running before proceeding with these steps. The NexusBox SMF module will get NexusBox on your SMF template easily, and it'll also integrate your SMF users with NexusBox.</p>
<ol>
<li>First download our <a href="http://34.61.70.211/public/extmodules/mod_cbox_smf2rc11.zip"><img src="/public/gfx/disk.gif" style="text-align: bottom" width="12" height="12" border="0"> NexusBox on SMF</a> module to your computer.</li>
<li>Go to the SMF Administration, and click on <b>Packages</b>.</li>
<li>Click on the <b>Download Packages</b> tab.</li>
<li>At <b>Upload a Package</b> at the bottom of the page, choose the <code>mod_cbox_smf11.tar.gz</code> file you just downloaded, and click <b>Upload</b>.</li>
<li>The message "Package has been downloaded successfully" is displayed. Click <b>Apply Mod</b> beneath it.</li>
<li>Click <b>Install Now</b>. You should then see "The package was installed successfully".</li>
<li>Go to <b>Features and Options</b> under the Configuration heading on the menu to your left (or click on Features and Options on the Admin main page).</li>
<li>Scroll down to locate the NexusBox options. Paste your code snippet (from <a href="http://34.61.70.211/public/admin/snippet/">here</a>), private key (see below, required for integration) and choose where to place the box (header or footer).</li>
<li>Save changes.</li>
<li>And finally, click <b>Enable</b> lower down on this page to turn user integration on.</li>
</ol>
</div>

<div id="int_vbulletin" style="display:none">
<p>You'll need to have a vBulletin-based website up and running with your NexusBox already installed in your template, before proceeding with these steps.</p>
<ol>
<li>Log in to your vBulletin Admin Control Panel.</li>
<li>Choose <b>Plugins &amp; Products</b> -&gt; <b>Add New Plugin</b> from the menu.</li>
<li>Create a new plugin at 'forumhome_complete' and insert this code: 
<pre>$cbox_nme = urlencode($vbulletin-&gt;userinfo['username']);
$cbox_key = md5('<?php echo htmlspecialchars($privateKey); ?>' . $vbulletin-&gt;userinfo['username']);</pre></li>
<li>Save.</li>
<li>Locate your NexusBox HTML code in your forum template.</li>
<li>In your NexusBox HTML code, find: <code>sec=form</code> and immediately after, paste: 
<pre>&amp;nme=$cbox_nme&amp;nmekey=$cbox_key</pre></li>
<li>Save.</li>
<li>And finally, click <b>Enable</b> lower down on this page to turn user integration on.</li>
</ol>
</div>

<div id="int_wordpress" style="display:none">
<p>You'll need to have a Wordpress-based website up and running before proceeding with these steps.</p>
<ol>
<li>Open your Wordpress Theme Editor (Administration &gt; Appearance &gt; Editor).</li>
<li>Paste the following into the template your want your NexusBox to appear in:
<pre>&lt;?php global $user_ID, $user_identity ?&gt;

&lt;?php if ( $user_ID ) : ?&gt;
[your NexusBox HTML code]
&lt;?php endif;  ?&gt;</pre></li>
<li>Where it says [your NexusBox HTML code] above, replace with your <a href="http://34.61.70.211/public/admin/snippet/">NexusBox HTML code</a>.</li>
<li>In your NexusBox HTML code, find: <code>sec=form</code> and immediately after, paste: 
<pre>&amp;nme=&lt;?php echo urlencode($user_identity)?&gt;&amp;nmekey=&lt;?php echo md5('<?php echo htmlspecialchars($privateKey); ?>'.$user_identity)?&gt;</pre></li>
<li>Save.</li>
<li>And finally, click <b>Enable</b> lower down on this page to turn user integration on.</li>
<li>If you want unregistered (guest) visitors to be able to see your NexusBox as well, delete the lines:
<pre>&lt;?php if ( $user_ID ) : ?&gt;</pre>
and 
<pre>&lt;?php endif;  ?&gt;</pre>
</li>
</ol>
</div>

<script type="text/javascript">updateint();</script>

<p>After enabling integration, test the login process on your website and confirm you can post on your NexusBox using the name you are registered with at your site. If you get a message saying your NexusBox may be misconfigured, click Disable above and re-check your installation. If the problem persists, <a href="http://34.61.70.211/public/contact">contact us</a>.</p>

<h2>Extended integration</h2>
<p>You can extend integration by including users' avatars and profile links as well as their names. Paste the following code after implementing the standard integration code:</p>
<pre>&amp;pic=&lt;?php echo urlencode($avatar_url)?&gt;&amp;lnk=&lt;?php echo urlencode($profile_url)?&gt;&amp;ekey=&lt;?php echo md5("<?php echo htmlspecialchars($privateKey); ?>".chr(9).$avatar_url.chr(9).$profile_url)?&gt;</pre>
<p>Change $avatar_url and $profile_url to the correct variables for the context. Note that they both appear twice. You will need to enable the email/URL field and avatars at <a href="http://34.61.70.211/public/admin/settings/">Posting Options</a> if you haven't already.</p>

		</div>
		<div style="clear: both"></div>
	</div>
</div>

<div id="siteErrorBar"><div class="wrap" id="siteErrorBarCont">Loading...</div></div>

<div id="footer">
	<a href="http://34.61.70.211/">About</a>
	<a href="http://34.61.70.211/public/products">Plans &amp; pricing</a>
	<a href="http://34.61.70.211/public/terms">Terms &amp; conditions</a>
	<a href="http://34.61.70.211/public/privacy">Privacy policy</a>
	<a href="http://34.61.70.211/public/notices">Notices</a>
	<a href="http://34.61.70.211/public/contact">Contact us</a>
	<div class="verybottom">© 2004–2026 NexusBox Communications (Pty) Ltd, all rights reserved. 1 ms. 83ce</div>
</div>

<script>
if (window.isPopup) { document.body.className = "Popped"; }
if (typeof upgradeCheckboxes === 'function') { upgradeCheckboxes(); }
var dropCrumb = function () { if (!localStorage) { return; } var bc = getCrumbs(); bc.push(location.href); bc.splice(0, bc.length - 5); localStorage.setItem("site:bread", JSON.stringify(bc)); };
var getCrumbs = function () { if (!localStorage) { return []; } var bcStr = localStorage.getItem("site:bread"); var bc = []; if (bcStr) { bc = JSON.parse(bcStr); } return bc; }
dropCrumb();
</script>
<script src="code.js"></script>
</body></html>