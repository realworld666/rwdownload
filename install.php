<?php
/*
 * Copyright (c) 2023.
 * RW::Software
 * Dave Conley
 * https://www.rwscripts.com/
 */

// Set warning level
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_magic_quotes_runtime(0);

define('INSTALL', "RWD4");
define('ROOT_PATH', "./");

// Create our superglobal wotsit so we can save doing the same things over and over
class wotsit
{
	var $path = "";
	var $url = "";
	var $skinurl = "";
	var $cat_cache = array();
	var $cats_saved = 0;
	var $image_cache = array();
	var $imgs_saved = 0;
	var $user_cache = array();
	var $error_log = "";
	var $nav = "EMPTY";
	var $userbar = "EMPTY";
	var $links = "EMPTY";
	var $lang = array();
	var $loaded_templates = array();
	var $skin_global;
	var $skin_wrapper;
}

$rwdInfo = new wotsit();

// Load required libraries
require_once(ROOT_PATH . "/functions/global_functions.php");
require_once(ROOT_PATH . "/functions/lang.php");
require_once(ROOT_PATH . "/functions/output.php");

// Load config
$CONFIG = array();
require_once(ROOT_PATH . "/globalvars.php");

// Create helper globals because I'm too lazy to type $CONFIG["array"] all the time
$rwdInfo->path = $CONFIG["sitepath"];
$rwdInfo->url = $CONFIG["siteurl"];
$rwdInfo->skinurl = "skins/install/";
$rwdInfo->skinpath = ROOT_PATH . "/skins/install/";

// Our skin handler
$OUTPUT = new CDisplay();
// Global functions
$std = new func();
// Get data from global arrays
$IN = $std->saveGlobals();

$lang = array();
$langpref = "1";
//require_once (ROOT_PATH."/lang/".$langpref."/lang_install.php");
//$lang_1 = $lang;
require_once(ROOT_PATH . "/lang/" . $langpref . "/lang_global.php");
$lang_2 = $lang;
require_once(ROOT_PATH . "/lang/" . $langpref . "/lang_warn.php");
$lang_3 = $lang;
require_once(ROOT_PATH . "/lang/" . $langpref . "/lang_error.php");
$lang_4 = $lang;
$lang = array_merge($lang_2, $lang_3, $lang_4);

$step = $IN["step"];

ob_start();

switch ($step) {
	case '1':
		setpaths();
		break;

	case '2':
		createuser();
		break;

	case '3':
		finish_up();
		break;

	default:
		welcome();
		break;
}

$content = ob_get_contents();
ob_end_clean();

$skin_url = ROOT_PATH . "/skins/install";
$style = ROOT_PATH . "/style.css";

$OUTPUT->add_output($content);
$OUTPUT->print_output();

function welcome()
{
	global $err_msg, $std;
	if (file_exists(ROOT_PATH . '/install.lock')) {
		$std->error("The installer has been locked for security. If you need to re-install remove the \"install.lock\" file from the root directory.");
		return;
	}
	if (!minimum_version("4.1.0")) {

		$std->error("Incorrect PHP version. PHP 4.1 is required to run this script");
		return;
	}
	$file = ROOT_PATH . "/globalvars.php";
	if (!file_exists($file)) {
		$std->error("Cannot locate the file 'globalvars.php'.");
		return;
	}
	if (!is_writeable($file)) {
		$std->error("Cannot write to 'globalvars.php'. You can do this by using FTP to CHMOD to 0777");
		return;
	}

	if (!is_writable(ROOT_PATH . "/temp/")) {
		$std->error("Cannot write to '/temp/'. These folders must be chmodded to 0777 to allow files to be uploaded. You can do this by using FTP");
		return;
	}
	install_head("RW::Download", "INSTALL");
	new_table();
	echo "<p>Welcome to RW::Download. This script will now attempt to install
              RW::Download correctly onto your server. Before continuing, please 
              ensure that you have successfully uploaded all files from the UPLOAD 
              folder onto your server. Also make sure all files have the correct 
              properties set as stated in the docs/install.htm file.</p>
            <p>During the install you may need to know the <em>server path </em>however 
              we will attempt to calculate this automatically. You WILL need to 
              know the name of a mySQL database wish you will need to create BEFORE 
              continuing. You will also need to know the username and password 
              required to access this database. If you are unsure of any of this, 
              please contact your hosts support team for more information.</p>
            <p><strong>Please note: If the database tables already exist then they will be removed and replaced.</strong></p>
			  <p align='right'><a href='?step=1'>CONTINUE -></a></p>";
	end_table();
	install_foot();
}

function geturl()
{
	global $HTTP_SERVER_VARS;

	// Find out where on the server we are at the moment
	$home_url = str_replace("/install.php", "", $HTTP_SERVER_VARS['HTTP_REFERER']);

	if (!$home_url) {

		$home_url = substr($HTTP_SERVER_VARS['REQUEST_URI'], 0, -19);

		$home_url = 'http://' . $HTTP_SERVER_VARS['SERVER_NAME'] . $home_url;
	}

	return $home_url;
}

function getpath()
{
	// Find out where on the server we are at the moment
	$home_path = str_replace("/install.php", "", $_SERVER['SCRIPT_FILENAME']);

	if (!$home_path) {
		$home_path = substr($_SERVER['REQUEST_URI'], 0, -12);

		if ($home_path == '') {
			$home_path == './';
		}
	}

	return $home_path;
}

function setpaths()
{
	$home_path = getpath();
	$home_url = geturl();

	install_head("RW::Download", "Server Paths");

	echo "<form method='post' action='install.php'>";
	new_table();
	new_row(-1, "", "", "250");
	echo "<span class='red'>FULL server path to script root:</span>";
	new_col();
	echo "<input name='sitepath' type='text' size='50' value='" . $home_path . "'>";
	new_row();
	echo "<span class='red'>FULL url to script root:</span>";
	new_col();
	echo "<input name='siteurl' type='text' size='50' value='" . $home_url . "'>";
	end_table();

	install_foot();
	echo "<p>";

	install_head("RW::Download", "Database Setup");

	new_table();
	new_row(2);
	echo "You must now setup a database configuration. This requires an
              existing mySQL database to be created. If you do not know how, speak 
              to your host about how to set this up.";
	new_row(-1, "", "", "250");
	echo "SQL Host [usually localhost]:";
	new_col();
	echo "<input name='sqlhost' type='text' size='30' value='localhost'>";
	new_row();
	echo "SQL Database Name:";
	new_col();
	echo "<input name='sqldatabase' type='text' size='30'>";
	new_row();
	echo "SQL Username:";
	new_col();
	echo "<input name='sqlusername' type='text' size='30'>";
	new_row();
	echo "SQL Password:";
	new_col();
	echo "<input name='sqlpassword' type='password' size='30'>";
	new_row();
	echo "SQL Table Prefix:";
	new_col();
	echo "<input name='sqlprefix' type='text' size='10' value='dl_'>";
	end_table();
	echo "<input name='submit' type='submit' class='ibutton' value='Proceed -&gt;'>
	      <input type='hidden' name='step' value='2'>";
	echo "</form>";
}

function createuser()
{
	global $std, $IN;

	if (!$db = @mysql_connect($IN["sqlhost"], $IN["sqlusername"], $IN["sqlpassword"])) {
		$std->error("Could not connect to database. Please click back and check the values you entered are correct. You must create the databse manually before running this install script.");
		return;
	}
	if (!@mysql_select_db($IN["sqldatabase"], $db)) {
		$std->error("Could not select database called '{$IN['sqldatabase']}'");
		return;
	}

	$SQL = get_sql();

	foreach ($SQL as $q) {
		if ($IN['sqlprefix'] != "dl_") {
			$q = preg_replace("/dl_(\S+?)([\s\.,]|$)/", $IN['sqlprefix'] . "\\1\\2", $q);
		}

		// Begin drop table query for each table
		if (preg_match("/CREATE TABLE (\S+) \(/", $q, $match)) {
			if ($match[1]) {
				$the_query = "DROP TABLE if exists " . $match[1];
				if (!mysql_query($the_query, $db)) {
					$std->error("Could not create table");
					echo "The query was: " . $the_query;
					return;
				}
			}
		}
		// Add table
		if (!mysql_query($q, $db)) {
			$std->error("Could not add data. The query was: " . $q);
			return;
		}
	}

	install_head("RW::Download", "Database Setup");

	echo "<form method='post' action='install.php'>";

	new_table();
	new_row(2);

	echo "SQL Database tables added successfully. Final Step is to create
                an admin username and password. Do not lose these as there is 
                no recovery system";
	new_row();
	echo "Admin Username:";
	new_col();
	echo "<input name='adminuser' type='text' value='Admin' size='30' maxlength='200'>";
	new_row();
	echo "Admin Password:";
	new_col();
	echo "<input name='adminpass' type='password' size='30' maxlength='40'>";
	new_row();
	echo "Confirm Password:";
	new_col();
	echo "<input name='adminpass2' type='password' size='30' maxlength='40'>";
	new_row();
	echo "Admin Email:";
	new_col();
	echo "<input name='adminemail' type='text' size='30' maxlength='200'>";
	end_table();
	echo "<input name='submit' type='submit' value='Proceed -&gt;'>
				<input type='hidden' name='sitepath' value='$IN[sitepath]'>
				<input type='hidden' name='siteurl' value='$IN[siteurl]'>
				<input type='hidden' name='sqlhost' value='$IN[sqlhost]'>
				<input type='hidden' name='sqldatabase' value='$IN[sqldatabase]'>
				<input type='hidden' name='sqlusername' value='$IN[sqlusername]'>
				<input type='hidden' name='sqlpassword' value='$IN[sqlpassword]'>
                <input type='hidden' name='sqlprefix' value='$IN[sqlprefix]'>
				<input type='hidden' name='step' value='3'>";
	echo "</form>";
}

function finish_up()
{
	global $CONFIG, $IN, $std;

	if ($IN["adminpass"] != $IN["adminpass2"]) {
		$std->error("Passwords do not match. Click back to correct the problem");
		return;
	} else {
		require_once ROOT_PATH . "/functions/mysql.php";
		// Load the database
		$dbinfo = array("sqlhost" => $IN["sqlhost"],
			"sqlusername" => $IN["sqlusername"],
			"sqlpassword" => $IN["sqlpassword"],
			"sqldatabase" => $IN["sqldatabase"],
			"sql_tbl_prefix" => $IN["sqlprefix"]);

		$DB = new mysql($dbinfo);

		skins_php2sql(1, $DB);

		// Assume install of default database therfore default encryption
		$crypt = md5($IN["adminpass"]);
		$sql = "INSERT INTO {$IN['sqlprefix']}users (`username`, `email`, `password`, `group`) VALUES ('$IN[adminuser]','$IN[adminemail]','$crypt','1')";
		if (!$result = mysql_query($sql)) {
			$std->error("<br>Sorry. There was an error adding admin to database. Please quote the above error and report this to the admin ");
			return;
		} else {
			// Load config
			if (!$CONFIG) {
				$CONFIG = array();
				require_once(ROOT_PATH . "/globalvars.php");
			}
			$CONFIG['sitepath'] = $IN['sitepath'];
			$CONFIG['siteurl'] = $IN['siteurl'];
			$CONFIG['sqlhost'] = $IN['sqlhost'];
			$CONFIG['sqldatabase'] = $IN['sqldatabase'];
			$CONFIG['sqlusername'] = $IN['sqlusername'];
			$CONFIG['sqlpassword'] = $IN['sqlpassword'];
			$CONFIG['sqlprefix'] = $IN['sqlprefix'];
			$CONFIG['email'] = $IN['adminemail'];
			$CONFIG['defaultSkin'] = '1';
			$CONFIG['filesfolder'] = $CONFIG['sitepath'] . "/downloads/";
			$CONFIG['filesurl'] = $CONFIG['siteurl'] . "/downloads/";
			$CONFIG['imagesfolder'] = $CONFIG['sitepath'] . "/downloads/";
			$CONFIG['imagesurl'] = $CONFIG['siteurl'] . "/downloads/";
			$CONFIG['guestref'] = '1';
			$r = parse_url($IN['siteurl']);
			$CONFIG['referers'] = $r['host'];

			$std->saveConfig();

			if ($locker = @fopen($IN["sitepath"] . '/install.lock', 'w')) {
				@fwrite($locker, 'locked', 6);
				@fclose($locker);

				@chmod($IN['sitepath'] . '/install.lock', 0666);

				$msg = "The installer is now locked (to re-install, remove the file 'install.lock') It is reccommended that you remove the install.php file before continuing.";
			} else {
				$msg = "You must remove this file from your server now. leaving it here is a huge security risk";
			}

			install_head("RW::Download", "Install Complete");

			new_table();
			echo "<p>We have saved your settings to your server.</p>
					  <p>You can now proceed to the admin control panel where you can
						log in with the information provided in the last step </p>
					  <p class='red'>$msg</p>
					  <p align='center'><a href='index.php'>Continue</a></p>";
			end_table();

			install_foot();
		}
	}
}

function minimum_version($vercheck)
{
	$minver = explode(".", $vercheck);
	$curver = explode(".", phpversion());
	if (($curver[0] < $minver[0])
		|| (($curver[0] == $minver[0])
			&& ($curver[1] < $minver[1]))
		|| (($curver[0] == $minver[0]) && ($curver[1] == $minver[1])
			&& ($curver[2][0] < $minver[2][0])))
		return false;
	else
		return true;
}

function get_sql()
{

	$SQL = array();

	$SQL[] = "CREATE TABLE `dl_categories` (
  `cid` int(11) NOT NULL auto_increment,
  `parentid` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `thumb` varchar(255) NOT NULL default '',
  `downloads` int(11) NOT NULL default '0',
  `sortorder` int(11) NOT NULL default '0',
  `lastid` int(11) NOT NULL default '0',
  `lastTitle` varchar(255) NOT NULL default '',
  `lastDate` int(11) NOT NULL default '0',
  `canBrowse` varchar(255) NOT NULL default '',
  `canDL` varchar(255) NOT NULL default '',
  `canUL` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`cid`)
)";

	$SQL[] = "CREATE TABLE `dl_comments` (
  `id` int(11) NOT NULL auto_increment,
  `date` datetime default NULL,
  `uid` int(11) NOT NULL default '0',
  `name` varchar(40) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `comments` text NOT NULL,
  `did` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)";

	$SQL[] = "CREATE TABLE `dl_custom` (
  `cid` int(11) NOT NULL auto_increment,
  `caption` varchar(200) NOT NULL default '',
  `description` varchar(250) NOT NULL default '',
  `field` varchar(4) NOT NULL default '',
  `size` tinyint(3) NOT NULL default '0',
  `max` tinyint(3) NOT NULL default '0',
  `options` text NOT NULL,
  `admins` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`cid`)
)";

	$SQL[] = "CREATE TABLE `dl_custom_data` (
  `uid` int(11) NOT NULL default '0'
)";

	$SQL[] = "CREATE TABLE `dl_filetypes` (
  `fid` int(11) NOT NULL auto_increment,
  `mimetype` varchar(255) NOT NULL default '',
  `maxsize` int(11) NOT NULL default '0',
  `icon` varchar(255) NOT NULL default '',
  `allowed` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`fid`)
)";

	$SQL[] = "CREATE TABLE `dl_groups` (
  `gid` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `users` int(11) NOT NULL default '0',
  PRIMARY KEY  (`gid`)
)";

	$SQL[] = "CREATE TABLE `dl_groupsextra` (
  `gid` int(11) NOT NULL default '0',
  `canSearch` tinyint(1) NOT NULL default '1',
  `uploadType` tinyint(1) NOT NULL default '0',
  `no_restrict` tinyint(1) NOT NULL default '0',
  `limitFilesPeriod` tinyint(1) NOT NULL default '0',
  `dlLimitFiles` int(11) NOT NULL default '0',
  `limitSizePeriod` tinyint(1) NOT NULL default '0',
  `dlLimitSize` int(11) NOT NULL default '0',
  `resetOnExpire` tinyint(1) NOT NULL default '0',
  `moderateAll` tinyint(1) NOT NULL default '0',
  `moderateOwn` tinyint(1) NOT NULL default '1',
  `acpAccess` tinyint(1) NOT NULL default '0',
  `approveUL` tinyint(1) NOT NULL default '1',
  `canApproveUploads` tinyint(1) NOT NULL default '0',
  `addComments` tinyint(1) NOT NULL default '1',
  `editComments` tinyint(1) NOT NULL default '1',
  `delComments` tinyint(1) NOT NULL default '0',
  `postHTML` tinyint(1) NOT NULL default '0'
)";

	$SQL[] = "CREATE TABLE `dl_images` (
  `id` int(11) NOT NULL auto_increment,
  `realName` varchar(255) NOT NULL default '',
  `dlid` int(11) NOT NULL default '0',
  `size` varchar(9) NOT NULL default '',
  `type` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
)";

	$SQL[] = "CREATE TABLE `dl_langsets` (
  `lid` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `author` varchar(255) NOT NULL default '',
  UNIQUE KEY `lid` (`lid`)
)";

	$SQL[] = "CREATE TABLE `dl_links` (
  `did` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `owner` int(11) NOT NULL default '0',
  `author` varchar(255) NOT NULL default '',
  `thumb` varchar(255) NOT NULL default '',
  `download` text NOT NULL,
  `mirrors` text NOT NULL,
  `mirrornames` text NOT NULL,
  `version` varchar(75) NOT NULL default '',
  `categoryid` int(11) NOT NULL default '0',
  `adminrating` tinyint(10) NOT NULL default '0',
  `adminreview` text NOT NULL,
  `filesize` varchar(255) NOT NULL default '',
  `realsize` int(11) NOT NULL default '0',
  `maskName` varchar(255) NOT NULL default '',
  `fileType` varchar(255) NOT NULL default '',
  `downloads` int(11) NOT NULL default '0',
  `date` datetime default NULL,
  `lastEdited` datetime default '2003-09-01 00:00:00',
  `approved` tinyint(1) NOT NULL default '0',
  `userrating` float NOT NULL default '0',
  `votes` int(11) NOT NULL default '0',
  `comments` int(11) NOT NULL default '0',
  `views` int(11) NOT NULL default '0',
  `pinned` varchar(7) NOT NULL default '',
  PRIMARY KEY  (`did`),
  KEY `did` (`did`)
)";

	$SQL[] = "CREATE TABLE `dl_logs` (
  `lid` int(11) NOT NULL auto_increment,
  `type` tinyint(2) NOT NULL default '0',
  `file` int(11) NOT NULL default '0',
  `filename` varchar(255) NOT NULL default '',
  `referer` text NOT NULL,
  `time` int(10) NOT NULL default '0',
  `IP` varchar(15) NOT NULL default '',
  `userid` int(11) NOT NULL default '0',
  UNIQUE KEY `lid` (`lid`)
)";

	$SQL[] = "CREATE TABLE `dl_memberextra` (
  `mid` int(11) NOT NULL default '0',
  `gid` int(11) NOT NULL default '0',
  `downloaded` int(11) NOT NULL default '0',
  `uploaded` int(11) NOT NULL default '0',
  `approving` int(11) NOT NULL default '0',
  `bandwidth` double NOT NULL default '0',
  `files` int(11) NOT NULL default '0',
  `bandwidth_time` int(11) NOT NULL default '0',
  `files_time` int(11) NOT NULL default '0',
  `receive_email` tinyint(1) NOT NULL default '1',
  `skin` int(11) NOT NULL default '0',
  `lang` int(11) NOT NULL default '0',
  PRIMARY KEY  (`mid`),
  UNIQUE KEY `mid` (`mid`)
)";

	$SQL[] = "CREATE TABLE `dl_moderators` (
  `mid` int(11) NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `member_name` varchar(255) NOT NULL default '',
  `member_id` int(11) NOT NULL default '0',
  `canedit` tinyint(1) NOT NULL default '0',
  `canmove` tinyint(1) NOT NULL default '0',
  `candelete` tinyint(1) NOT NULL default '0',
  `edit_comments` tinyint(1) NOT NULL default '0',
  `del_comments` tinyint(1) NOT NULL default '0',
  UNIQUE KEY `mid` (`mid`)
)";

	$SQL[] = "CREATE TABLE `dl_rating` (
  `rid` int(11) NOT NULL auto_increment,
  `dlid` int(11) NOT NULL default '0',
  `ip` varchar(32) NOT NULL default '',
  `rating` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`rid`)
)";

	$SQL[] = "CREATE TABLE `dl_regbot` (
  `rid` int(11) NOT NULL auto_increment,
  `ip` varchar(15) NOT NULL default '',
  `regKey` varchar(32) NOT NULL default '',
  `regtime` int(11) NOT NULL default '0',
  PRIMARY KEY  (`rid`)
)";

	$SQL[] = "CREATE TABLE `dl_sessions` (
  `id` int(11) NOT NULL default '0',
  `sID` varchar(32) NOT NULL default '',
  `sTime` int(11) NOT NULL default '0',
  `ip` varchar(16) NOT NULL,
  PRIMARY KEY  (`id`)
)";

	$SQL[] = "CREATE TABLE `dl_skinsets` (
  `setid` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `author` varchar(255) NOT NULL default '',
  UNIQUE KEY `setid` (`setid`)
)";

	$SQL[] = "CREATE TABLE `dl_symlinks` (
  `sym_id` int(11) NOT NULL auto_increment,
  `sym_did` int(11) NOT NULL default '0',
  `sym_catid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`sym_id`),
  UNIQUE KEY `sym_id` (`sym_id`)
  )";

	$SQL[] = "CREATE TABLE `dl_templates` (
  `tid` int(11) NOT NULL auto_increment,
  `setid` int(11) NOT NULL default '0',
  `groupname` varchar(100) NOT NULL default '',
  `content` text NOT NULL,
  `funcname` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`tid`),
  KEY `tid` (`tid`)
)";

	$SQL[] = "CREATE TABLE `dl_users` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(40) NOT NULL default '',
  `password` varchar(40) NOT NULL default '',
  `group` int(11) NOT NULL default '0',
  `email` varchar(255) NOT NULL default '',
  `regKey` varchar(32) NOT NULL default '',
  `iplog` text NOT NULL,
  `regDate` int(11) NOT NULL default '0',
  `regIP` varchar(16) NOT NULL default '',
  PRIMARY KEY  (`id`)
)";

	$SQL[] = "CREATE TABLE `dl_version` (
  `upg_id` mediumint(8) NOT NULL auto_increment,
  `version` varchar(5) NOT NULL default '',
  `date` int(11) NOT NULL default '0',
  UNIQUE KEY `upg_id` (`upg_id`)
)";

	$SQL[] = "INSERT INTO `dl_categories` VALUES (1, 0, 'RW::Download 4.0', 'Example category', '', 1, 0, 1, 'Example File', '0', '|6|1|2|3|4|5', '|6|1|2|3|4|5', '|6|1|2|3|4|5')";
	$SQL[] = "INSERT INTO `dl_links` VALUES (1, 'Example File', 'This is an example of a file in RW::Download. The download link for this file will take you to our technical support forums rather than download a file. This file and category can be deleted at any time', 1, 'Real World', '', 'http://www.rwscripts.com/forum/', '', '', '', 1, 10, 'We hope you enjoy RW::Download 4.0. If you have any questions, bugs or problems let us know in our forums', '', 0, '', '', 0, '2005-03-18 16:13:27', '2003-09-01 00:00:00', 1, 0, 0, 0, 0, '')";
	$SQL[] = "INSERT INTO `dl_symlinks` VALUES (1, 1, 1)";
	$SQL[] = "INSERT INTO `dl_filetypes` VALUES (1, 'application/zip', 2097152, 'zip.gif', 1)";
	$SQL[] = "INSERT INTO `dl_filetypes` VALUES (2, 'application/octet-stream', 1048576, 'text.gif', 1)";
	$SQL[] = "INSERT INTO `dl_filetypes` VALUES (3, 'image/gif', 512000, 'gif.gif', 1)";
	$SQL[] = "INSERT INTO `dl_filetypes` VALUES (4, 'image/jpeg', 512000, 'jpg.gif', 1)";
	$SQL[] = "INSERT INTO `dl_filetypes` VALUES (5, 'application/msword', 1048576, 'word.gif', 1)";
	$SQL[] = "INSERT INTO `dl_filetypes` VALUES (6, 'text/html', 102400, 'html.gif', 1)";
	$SQL[] = "INSERT INTO `dl_filetypes` VALUES (7, 'audio/mpeg', 5120000, 'mp3.gif', 1)";
	$SQL[] = "INSERT INTO `dl_filetypes` VALUES (8, 'video/quicktime', 5120000, 'quicktime.gif', 1)";
	$SQL[] = "INSERT INTO `dl_filetypes` VALUES (9, 'audio/x-realaudio', 2097152, 'real_audio.gif', 1)";
	$SQL[] = "INSERT INTO `dl_filetypes` VALUES (10, 'application/pdf', 1048576, 'pdf.gif', 1)";
	$SQL[] = "INSERT INTO `dl_filetypes` VALUES (11, 'application/postscript', 1048576, 'postscript.gif', 1)";
	$SQL[] = "INSERT INTO `dl_filetypes` VALUES (12, 'text/plain', 102400, 'txt.gif', 1)";
	$SQL[] = "INSERT INTO `dl_filetypes` VALUES (13 , 'image/pjpeg', 512000, 'jpg.gif', 1)";
	$SQL[] = "INSERT INTO `dl_filetypes` VALUES (14, 'application/x-zip-compressed', 2097152, 'zip.gif', 1)";
	$SQL[] = "INSERT INTO `dl_filetypes` VALUES (15, 'application/x-compressed', 2097152, 'zip.gif', 1)";
	$SQL[] = "INSERT INTO `dl_filetypes` VALUES (16, 'application/download', 2097152, 'zip.gif', 1)";
	$SQL[] = "INSERT INTO `dl_filetypes` VALUES (17, 'applicaton/zip', 2097152, 'zip.gif', 1)";
	$SQL[] = "INSERT INTO `dl_users` VALUES (1, 'Guest', '', 5, '', '', '', '', 'unknown')";

	$SQL[] = "INSERT INTO `dl_groups` VALUES (6, 'Unapproved', 0)";
	$SQL[] = "INSERT INTO `dl_groups` VALUES (1, 'Super-Admin', 1)";
	$SQL[] = "INSERT INTO `dl_groups` VALUES (2, 'Admin', 0)";
	$SQL[] = "INSERT INTO `dl_groups` VALUES (3, 'Moderator', 0)";
	$SQL[] = "INSERT INTO `dl_groups` VALUES (4, 'Members', 0)";
	$SQL[] = "INSERT INTO `dl_groups` VALUES (5, 'Guests', 0)";
	$SQL[] = "INSERT INTO `dl_groupsextra` VALUES (6, 1, 1, 1, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0, 1, 1, 0, 0)";
	$SQL[] = "INSERT INTO `dl_groupsextra` VALUES (1, 1, 1, 1, 0, 5, 0, 5242880, 0, 1, 1, 1, 1, 0, 1, 1, 0, 0)";
	$SQL[] = "INSERT INTO `dl_groupsextra` VALUES (2, 1, 1, 1, 0, 5, 0, 5242880, 0, 1, 1, 1, 1, 0, 1, 1, 0, 0)";
	$SQL[] = "INSERT INTO `dl_groupsextra` VALUES (3, 1, 1, 1, 0, 0, 0, 0, 0, 1, 1, 0, 1, 0, 1, 1, 0, 0)";
	$SQL[] = "INSERT INTO `dl_groupsextra` VALUES (4, 1, 1, 1, 0, 0, 0, 0, 0, 0, 1, 0, 1, 1, 0, 1, 1, 0)";
	$SQL[] = "INSERT INTO `dl_groupsextra` VALUES (5, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 1, 0, 0)";
	$SQL[] = "INSERT INTO `dl_langsets` VALUES (1, 'English (UK)', 'RW::Scripts')";
	$SQL[] = "INSERT INTO `dl_skinsets` VALUES (1, 'RWD Default Skin', 'RW::Scripts');";
	$SQL[] = "INSERT INTO `dl_version` VALUES(1, '408', '" . time() . "')";

	return $SQL;
}

function skins_php2sql($id, $DB)
{
	global $rwdInfo, $std;

	$skin_dir = ROOT_PATH . "/skins/skin" . $id;

	if (!file_exists($skin_dir)) {
		$std->error("Template set could not be found. Try rebuilding from database.");
		return 0;
	}

	if (!is_readable($skin_dir)) {
		$std->error("Cannot write into '$skin_dir', please check the CHMOD value, and if needed, CHMOD to 0777 via FTP.");
		return 0;
	}

	if (is_dir($skin_dir)) {
		if ($handle = opendir($skin_dir)) {
			// Remove the old skin data
			$DB->query("DELETE FROM `dl_templates` WHERE `setid`='$id'");

			while (($filename = readdir($handle)) !== false) {
				if (($filename != ".") && ($filename != "..")) {
					if (preg_match("/\.php$/", $filename)) {
						$name = preg_replace("/^(\S+)\.(\S+)$/", "\\1", $filename);

						if ($FH = fopen($skin_dir . "/" . $filename, 'r')) {
							$fdata = fread($FH, filesize($skin_dir . "/" . $filename));
							fclose($FH);
						} else {
							$std->warning("Could not open $filename for reading, skipping file...");
							continue;
						}

						// Convert windows line breaks and strip unnecessary new lines
						$fdata = str_replace("\r", "\n", $fdata);
						$fdata = str_replace("\n\n", "\n", $fdata);

						if (!preg_match("/\n/", $fdata)) {
							$std->error("Could not find any line endings in $filename, skipping file...");
							continue;
						}

						$farray = explode("\n", $fdata);

						$functions = array();
						$flag = 0;
						foreach ($farray as $f) {
							if (preg_match("/^function\s*([\w\_]+)\s*\((.*)\)/i", $f, $matches)) {
								$functions[$matches[1]] = '';
								$config[$matches[1]] = $matches[2];
								$flag = $matches[1];
								continue;
							}

							if ($flag) {
								$functions[$flag] .= $f . "\n";
								continue;
							}

						}

						$final = "";
						$flag = 0;

						foreach ($functions as $fname => $ftext) {
							// Get the useful bit out
							preg_match("#//--START--//(.+?)//--END--//#s", $ftext, $matches);

							//$content = preg_replace('/{lang.([^}]+)}/i','{$rwdInfo->lang[\'$1\']}',$content);
							$str = preg_replace("/\\\$data\['([^']+)'\]/i", '#$1#', $matches[1]);
							$str = preg_replace("/\\\$rwdInfo->lang\['([^']+)'\]/i", 'lang.$1', $str);
							//$str = preg_replace("/\\\$rwdInfo->([^']+)/i",'$2',$str);
							$code = preg_replace("/\\\$SHTML\s+?\.?= <<<RWS\n(.+?)\nRWS;\s?/si", '$1', $str);
							$code = trim($code);
							// FILTHY HACK
							if ($code == "\$SHTML .= <<<RWS\nRWS;")
								$code = "";
							if ($code) {
								// Unconvert(?) the logic stuff first
								$code = preg_replace("#else if\s+?\((.+?)\)\s+?{(.+?)}//endif(\n)?#ise", "skins_unparse_if('\\1', '\\2', 'else if')", $code);
								$code = preg_replace("#//startif\nif\s+?\((.+?)\)\s+?{(.+?)}//endif(\n)?#ise", "skins_unparse_if('\\1', '\\2', 'if')", $code);
								$code = preg_replace("#else\s+?{(.+?)}//endelse(\n)?#ise", "skins_unparse_else( '\\1' )", $code);

								$code = str_replace("//startif\n", "\n", $code);
								$code = preg_replace("#(</if>|</else>)\s+?(<if|<else)#is", "\\1\n\\2", $code);
							}
							$insert = array("setid" => $id,
								"groupname" => $name,
								"content" => addslashes($code),
								"funcname" => $fname);

							$DB->insert($insert, "dl_templates");
						}

						//reset the array
						$functions = array();
					}
				}
			}

			closedir($handle);

		} else {
			echo "Could not open directory $skin_dir for reading!.";
			return 0;
		}
	} else {
		echo "$skin_dir is not a directory, please check the \$root_path variable in admin.php.";
		return 0;
	}

	echo "Completed database rebuild from PHP cache files.";
	return 1;
}

function skins_parse_if($code, $html)
{
	// TODO: AND OR logic?
	// Get rid of curly braces
	$code = preg_replace('/{([^}]+)}/i', '$1', $code);
	// Trim whitespace
	$html = trim($html);
	return "\n//startif\nif ( $code )\n{\n\$SHTML .= <<<RWS\n$html\nRWS;\n}//endif\n";
}

function skins_parse_elseif($code, $html)
{
	// Trim whitespace
	$html = trim($html);
	return "\nelse if ( $code )\n{\n\$SHTML .= <<<RWS\n$html\nRWS;\n}//endif\n";
}

// Unparse if's and if else's
function skins_unparse_if($code, $php, $start = 'if')
{
	$code = trim($code);
	$code = preg_replace('/#([^}]+)#/i', '{#$1#}', $code);
	return "\n<" . $start . "=\"" . $code . "\">\n" . trim($php) . "\n</if>\n";
}

function skins_parse_else($html)
{
	// Trim whitespace
	$html = trim($html);
	return "\nelse\n{\n\$SHTML .= <<<RWS\n$html\nRWS;\n}//endelse\n";
}

function skins_unparse_else($php)
{
	return "<else>\n" . trim($php) . "\n</else>\n";

}

function install_head($title = "RW::Download", $subtitle = "Admin CP")
{
	global $skin_url;
	echo "<table width='90%'  border='0' cellspacing='0' cellpadding='0' align='center'>
  <tr>
    <td class='top1'>&nbsp;$title </td>
  </tr>
  <tr>
    <td class='top2'><table width='100%'  border='0' cellspacing='0' cellpadding='0'>
      <tr>
        <td width='250' bgcolor='#333333' class='smallheadtext'>+ $subtitle </td>
        <td width='18'><img src='skins/install/images/smallhead.gif' width='18' height='12'></td>
        <td>&nbsp;</td>
      </tr>
    </table> </td>
  </tr>
  <tr>
    <td class='main_frame_bg'>";
}

function install_foot()
{
	echo "</td>
		  </tr>
		</table>";
}

// Nice hack to save modifying all the new_table calls I mad in the admin section
function new_table($colspan = -1, $class = "", $tdclass = "", $width = "100%", $colwidth = "", $padding = 2)
{
	global $OUTPUT;
	$output = $OUTPUT->new_table($colspan, $class, $tdclass, $width, $colwidth, $padding);
	echo $output;
}

function new_row($colspan = -1, $class = "", $tdclass = "", $width = "")
{
	global $OUTPUT;
	$output = $OUTPUT->new_row($colspan, $class, $tdclass, $width);
	echo $output;
}

function new_col($colspan = -1, $tdclass = "")
{
	global $OUTPUT;
	$output = $OUTPUT->new_col($colspan, $tdclass);
	echo $output;
}

function end_table()
{
	global $OUTPUT;
	$output = $OUTPUT->end_table();
	echo $output;
}

