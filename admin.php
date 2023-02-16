<?php/* * Copyright (c) 2023. * RW::Software * Dave Conley * https://www.rwscripts.com/ */require_once("./functions/rwd_crypt_constants.php");// we will do our own error handlingerror_reporting(0);// user defined error handling functionfunction userErrorHandler($errno, $errmsg, $filename, $linenum, $vars){	// timestamp for the error entry	$dt = date("Y-m-d H:i:s (T)");	// define an assoc array of error string	// in reality the only entries we should	// consider are E_WARNING, E_NOTICE, E_USER_ERROR,	// E_USER_WARNING and E_USER_NOTICE	$errortype = array(		E_ERROR => 'Error',		E_WARNING => 'Warning',		E_PARSE => 'Parsing Error',		E_NOTICE => 'Notice',		E_CORE_ERROR => 'Core Error',		E_CORE_WARNING => 'Core Warning',		E_COMPILE_ERROR => 'Compile Error',		E_COMPILE_WARNING => 'Compile Warning',		E_USER_ERROR => 'User Error',		E_USER_WARNING => 'User Warning',		E_USER_NOTICE => 'User Notice',		E_STRICT => 'Runtime Notice',		E_RECOVERABLE_ERRROR => 'Catchable Fatal Error'	);	// set of errors for which a var trace will be saved	$user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);	$err = "<errorentry>\n";	$err .= "\t<datetime>" . $dt . "</datetime>\n";	$err .= "\t<errornum>" . $errno . "</errornum>\n";	$err .= "\t<errortype>" . $errortype[$errno] . "</errortype>\n";	$err .= "\t<errormsg>" . $errmsg . "</errormsg>\n";	$err .= "\t<scriptname>" . $filename . "</scriptname>\n";	$err .= "\t<scriptlinenum>" . $linenum . "</scriptlinenum>\n";	if (in_array($errno, $user_errors)) {		$err .= "\t<vartrace>" . wddx_serialize_value($vars, "Variables") . "</vartrace>\n";	}	$err .= "</errorentry>\n\n";	// for testing	//echo $err."<br>";}$old_error_handler = set_error_handler("userErrorHandler");// Create our superglobal wotsit so we can save doing the same things over and overclass wotsit{	var $path = "";	var $url = "";	var $skinurl = "";	var $cat_cache = array();	var $cats_saved = 0;	var $image_cache = array();	var $imgs_saved = 0;	var $user_cache = array();	var $error_log = "";	var $nav = "";	var $userbar = "";	var $links = "";	var $lang = array();	var $loaded_templates = array();	var $skin_global;	var $skin_wrapper;}$rwdInfo = new wotsit();// Load config$CONFIG = array();require_once(ROOT_PATH . "/globalvars.php");// Load required librariesrequire_once(ROOT_PATH . "/engine/mysql.php");require_once(ROOT_PATH . "/functions/rwd_crypt_functions.php");require_once(ROOT_PATH . "/engine/users.php");require_once(ROOT_PATH . "/engine/lang.php");require_once(ROOT_PATH . "/engine/output.php");require_once(ROOT_PATH . "/modules/module_plugs.php");require_once(ROOT_PATH . "/engine/modloader.php");require_once(ROOT_PATH . "/engine/taskmanager.php");// Global functions$std = new rwdcryptfunc();// Get data from global arrays$IN = $std->saveGlobals();// Language class$clang = new language();// Task Manager$taskmgr = new TaskManager();// Load the database$dbinfo = array("sqlhost" => $CONFIG["sqlhost"],	"sqlusername" => $CONFIG["sqlusername"],	"sqlpassword" => $CONFIG["sqlpassword"],	"sqldatabase" => $CONFIG["sqldatabase"],	"sql_tbl_prefix" => $CONFIG["sqlprefix"]);$DB = new mysql($dbinfo);$std->loadNewStyleConfig();// Create helper globals because I'm too lazy to type $CONFIG["array"] all the time$rwdInfo->path = $CONFIG['sitepath'];$rwdInfo->url = $CONFIG['siteurl'];$rwdInfo->skinurl = $CONFIG["siteurl"] . "/skins/admin/";$rwdInfo->skinpath = ROOT_PATH . "/skins/admin/";// Our skin handler$OUTPUT = new CDisplay();// Global module class$modloader = new modloader();$module = new module_plugs();if (!$IN['area'])	$area = "main";else	$area = $IN['area'];$guser = new user();$guser->initialise();$clang->loadLangFile("lang_ad_{$area}");$clang->loadLangFile("lang_global");$clang->loadLangFile("lang_warn");$clang->loadLangFile("lang_error");require_once(ROOT_PATH . "/functions/admin/ad_modules.php");// Module extensions for the ACP$modules = new admin_modules();// Get the session ID$sid = $IN['sid'];// Check for old files in the temp folder$dirpath = $CONFIG["sitepath"] . "/temp/";$dir_handle = @opendir($dirpath) or die("Unable to open $dirpath");while ($file = readdir($dir_handle)) {	if ($file != "." and $file != ".." and $file != "index.htm") {		$filetime = filectime($dirpath . $file);		// If file is older than 12 hours old then remove		if (time() - $filetime > 43200) {			if (!@unlink($dirpath . $file))				//$err_msg .= error("Could not remove file: ".$file);				continue;		}	}}// Check if we are auto logging inif ($IN['login'] != '1') {	// If there is no session ID then login	if (!$sid) {		login();		return;	}	$hResult = $DB->query("SELECT * FROM dl_adminsessions WHERE sID = '$sid'");	if (!$DB->affected_rows($hResult)) {		// No match for sid so login		$std->error(GETLANG("er_sessExpired"));		login();		return;	} else {		// Check session has not expired		$myrow = $DB->fetch_row($hResult);		$timenow = time();		if (($timenow - $myrow["sTime"]) > ($CONFIG["session"] * 60)) {			$login = ". [ <a href='admin.php'>" . GETLANG("login") . "</a> ]";			$DB->query("DELETE FROM dl_adminsessions WHERE sID = '$sid'");			$std->warning(GETLANG("er_sessExpired") . $login);			login();			return;		} else if ($myrow['ip'] != $IN['ipaddr'] and CHECK_IP) {			$login = ". [ <a href='admin.php'>" . GETLANG("login") . "</a> ]";			$std->warning(GETLANG("er_adminIPChange") . $login);			$DB->query("DELETE FROM dl_adminsessions WHERE sID = '$sid'");			login();			return;		} else {			// Session has not expired so update with current time as user is still active			$time = time();			$DB->query("UPDATE dl_adminsessions SET sTime='$time' WHERE sID = '$sid'");			if (!$guser->errormsg) {				// Check user exists. If not, log in.				if (!$guser->adminLogin($sid)) {					login();					return;				}				// Check if this user should be here. If not, log in				if (!$guser->isAdmin) {					$std->error(GETLANG("er_adminAuth") . $guser->userlevel);					login();					return;				}			}		}	}} // Otherwise the login button was clickedelse {	// Check if the information has been filled in	if ($IN["username"] == '') {		// No login information		$std->error(GETLANG("warn_missing"));		login();		return;	} else {		if (!$guser->errormsg) {			if ($CONFIG["usertype"] == "vb3") {				foreach ($IN as $t => $u) {					$IN[$t] = $std->undoHTMLChars($u);				}			}			// Check user exists			if (!$guser->do_login()) {				$std->error(GETLANG("er_nomatch"));				login();				return;			} else {				// Check if authorised to view this page				if (!$guser->isAdmin) {					$std->error(GETLANG("er_adminAuth") . $guser->userlevel);					login();					return;				}				srand($std->make_seed());				$session = md5(time() + $guser->userid + rand());				// Update the user record				$time = time();				// Check if sid already present				$sr = $DB->query("SELECT * FROM dl_adminsessions WHERE uid = '$guser->userid'");				// if rows returned then update				$dbentry = array("uid" => $guser->userid,					"sID" => $session,					"sTime" => $time,					"ip" => $IN['ipaddr']);				if ($myrow = $DB->fetch_row($sr)) {					$DB->update($dbentry, "dl_adminsessions", "uid = {$guser->userid}");				} else    // else add new row					$DB->insert($dbentry, "dl_adminsessions");				$sid = $session;				redirect();				return;			}		} else {			login();			return;		}	}}// Create session for downloading of files$std->createSession();if (!empty($IN["area"])) {	$DB->query("SELECT * FROM `dl_links` WHERE `approved`=0");	$unapp = $DB->num_rows();	if ($CONFIG['usertype'] == USER_DEFAULT) {		$DB->query("SELECT * FROM `dl_users` WHERE `group`=6");		$unappu = $DB->num_rows();	}	if ($IN['area'] == "modules")		$modules->loadModule();	else {		require ROOT_PATH . "/functions/admin/ad_" . $IN["area"] . ".php";	}	main_template();} else {	build_frames();}// Run any outstanding tasks$taskmgr->RunTasks();function login(){	global $OUTPUT, $CONFIG, $IN, $DB, $guser;	$output = "";	$output = admin_head("RW::Download ACP", "Login");	$output .= "<div align='center'><form method='post' enctype='multipart/form-data' action='admin.php' target='_top' " . $guser->userdb_loginCallback() . ">";	$output .= new_table(-1, "", "", "300");	$output .= GETLANG("username") . ":";	$output .= new_col();	$output .= "<input type='text' name='username'>";	$output .= new_row();	$output .= GETLANG("password") . ":";	$output .= new_col();	$output .= "<input type='password' name='userpw'>";	$output .= new_row();	$output .= "&nbsp;";	$output .= new_col();	$output .= "<input type='hidden' name='login' value='1'>";	$output .= "<input type='hidden' name='hash_passwrd' value=''>";	$output .= "<input type='submit' name='submit' value='" . GETLANG("login") . "'>";	$output .= end_table();	$output .= "</form></div>";	$output .= admin_foot();	$OUTPUT->add_output($output);	main_template();	// TODO: Move this into some kind of cron tool like IPB has	// Prune logs of all files older than cutoff time	if ($CONFIG['logPruneTime'] != 0) {		$cutoff = time() - ($CONFIG['logPruneTime'] * 60 * 60 * 24);		$DB->query("DELETE FROM `dl_logs` WHERE `time` <= '{$cutoff}'");	}}function main_template(){	global $OUTPUT, $std, $guser;	// Catch any user db errors	if ($guser->errormsg)		$std->error($guser->errormsg);	//$OUTPUT->add_output($main_content);	$OUTPUT->print_output();}function redirect(){	global $OUTPUT, $sid;	$url = "admin.php?sid=$sid";	$output = "<SCRIPT LANGUAGE='JavaScript'>	<!--	function redirect()	{		parent.location.href='{$url}'	}	-->	</SCRIPT>	<SCRIPT LANGUAGE='JavaScript'>	<!--			setTimeout('redirect()',2000)	-->	</SCRIPT>";	$output .= admin_head("RW::Download ACP", "Logging in");	$output .= "<div align='center'>Proceding to the admin control panel now. Please click <a href='{$url}'>here</a> if you are not automatically redirected.</div>";	$output .= admin_foot();	$OUTPUT->add_output($output);	main_template();}function build_frames(){	global $OUTPUT, $version, $sid;	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">        <html>		 <head>		 <meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>		 <title>RW::Download ACP - Version $version</title></head>		   <frameset cols='200, *' frameborder='no' border='0' framespacing='0'>			<frame name='menu' noresize scrolling='auto' src='admin.php?sid=$sid&amp;area=nav'>			<frame name='body' noresize scrolling='auto' src='admin.php?sid=$sid&amp;area=main'>		   </frameset>	   </html>";}function admin_head($title = "RW::Download", $subtitle = "Admin CP"){	global $rwdInfo;	$output = "<div class='tableborder'>	<table summary='admin_head' width='100%' border='0' cellspacing='0' cellpadding='0' align='center'>  <tr>    <td class='top1'>&nbsp;$title </td>  </tr>  <tr>    <td class='top2'><table summary='blah' width='100%'  border='0' cellspacing='0' cellpadding='0'>      <tr>        <td width='250' bgcolor='#333333' class='smallheadtext'>+ $subtitle </td>        <td width='18'><img src='$rwdInfo->skinurl/images/smallhead.gif' width='18' height='12' alt=''></td>        <td>&nbsp;</td>      </tr>    </table> </td>  </tr>  <tr>    <td class='main_frame_bg'>";	return $output;}function admin_foot(){	$output = "</td>		  </tr>		</table></div>";	return $output;}// Nice hack to save modifying all the new_table calls I mad in the admin sectionfunction new_table($colspan = -1, $class = "", $tdclass = "", $width = "100%", $colwidth = "", $padding = 2){	global $OUTPUT;	$output = $OUTPUT->new_table($colspan, $class, $tdclass, $width, $colwidth, $padding);	return $output;}function new_row($colspan = -1, $class = "", $tdclass = "", $width = ""){	global $OUTPUT;	$output = $OUTPUT->new_row($colspan, $class, $tdclass, $width);	return $output;}function new_col($colspan = -1, $tdclass = "", $width = ""){	global $OUTPUT;	$output = $OUTPUT->new_col($colspan, $tdclass, $width = "");	return $output;}function end_table(){	global $OUTPUT;	$output = $OUTPUT->end_table();	return $output;}?>