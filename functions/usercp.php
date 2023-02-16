<?php/* * Copyright (c) 2023. * RW::Software * Dave Conley * https://www.rwscripts.com/ */require_once ROOT_PATH . "/functions/files.php";class usercp extends files{	var $ucphtml;	var $files;	var $output;	function usercp()	{		global $IN, $OUTPUT, $guser, $std;		$this->ucphtml = $OUTPUT->load_template("skin_usercp");		$this->html = $OUTPUT->load_template("skin_files");		$this->files = new files();		if ($guser->isGuest) {			$std->error(GETLANG("er_noperms"));			return;		}		$std->updateNav(" > " . GETLANG("nav_usercp"), 0);		switch ($IN["area"]) {			case 'files':				$main = $this->showfiles(1);				break;			case 'pending':				$main = $this->showfiles(0);				break;			case 'profile':				$main = $this->editPrefs();				break;			case 'editdl':				$main = $this->ucpeditdl();				break;			case 'handleInput':				$main = $this->ucpHandleInput();				break;			case 'home':			default:				$main = $this->showhome();				break;		}		$this->ucp_wrapper($main, $IN["area"]);		$OUTPUT->add_output($this->output);	}	function ucp_wrapper($main, $section)	{		global $std, $guser, $module;		$data = array("ucp_main" => $main,			"module_nav" => $module->ucp_modulenav());		switch ($section) {			case 'files':				$data['appclass'] = "ucpon";				break;			case 'pending':				$data['unappclass'] = "ucpon";				break;			case 'profile':				$data['profileclass'] = "ucpon";				break;			case 'editdl':				$data['homeclass'] = "ucpon";				break;			case 'home':			default:				$data['homeclass'] = "ucpon";				break;		}		if ($guser->userid == 0) {			$std->warning(GETLANG("warn_notloggedin"));			return;		}		$this->output .= $module->html_skincall("ucp_main", $this->ucphtml, $data);	}	function showhome()	{		global $DB, $OUTPUT, $guser, $std, $module;		$id = $guser->userid;		$guser->updateMemberExtra("`{$guser->db_id}`='$id'", "`mid`='$id'");		$userdata = $std->updateLimits($guser->userdetails);		if ($guser->getPermissions() & k_noRestrict) {			$bandwidth = GETLANG("unlimited");			$timeStr = "N/A";		} else {			$bandwidth = $std->my_filesize($userdata["dlLimitSize"]) . " remaining";			if ($userdata["dlLimitSize"] <= 0)				$bandwidth = "<font color='red'><b>" . $bandwidth . "</b></font>";			$time = $userdata["limitSizePeriod"] - time();			if ($time < 0)				$time = 0;			$timeArray = $std->calc_time($time);			$timeStr = "$timeArray[days] Days, $timeArray[hours] Hours, $timeArray[minutes] Minutes";		}		if ($guser->getPermissions() & k_noRestrict) {			$files = GETLANG("unlimited");			$timeStr2 = "N/A";		} else {			$files = $userdata["dlLimitFiles"] . " files remaining";			if ($userdata["dlLimitFiles"] == 0)				$files = "<font color='red'><b>" . $files . "</b></font>";			$time = $userdata["limitFilesPeriod"] - time();			if ($time < 0)				$time = 0;			$timeArray = $std->calc_time($time);			$timeStr2 = "$timeArray[days] Days, $timeArray[hours] Hours, $timeArray[minutes] Minutes";		}		$downloaded = $guser->userdetails['downloaded'] . " Downloaded";		$uploaded = $guser->userdetails['uploaded'] . " Uploaded";		$limits_info = GETLANG("limits_desc");		if ($guser->getPermissions() & k_noRestrict)			$limits_info .= ".<br><b>" . GETLANG("limits_off") . "</b>";		else if ($guser->getPermissions() & k_resetOnExpire)			$limits_info .= ".<br><b>" . GETLANG("limit_reset_on") . "</b>";		else			$limits_info .= ".<br><b>" . GETLANG("limit_reset_off") . "</b>";		$data = array("limits_info" => $limits_info,			"bandwidth" => $bandwidth,			"bandwidth_time" => $timeStr,			"files" => $files,			"files_time" => $timeStr2,			"filesup" => $uploaded,			"filesdown" => $downloaded,			"username" => $guser->username,			"email" => $guser->userdetails["email"],			"registered" => ($guser->userdetails["regDate"] > 0) ? $std->formatDate($guser->userdetails["regDate"]) : "Unknown",			"group" => $guser->userdetails[$guser->db_g_title]);		return $module->html_skincall("ucp_home", $this->ucphtml, $data);	}	function showfiles($approved = 1)	{		global $CONFIG, $IN, $DB, $std, $module, $guser;		$owner = $guser->userid;		if (!$IN["limit"])			$limit = 0;		else			$limit = intval($IN["limit"]);		$main_content = "";		// Attempt to get custom download field data		$customFields = array();		if ($CONFIG['doCustomFields']) {			$DB->query("SELECT * FROM dl_custom");			while ($cfrow = $DB->fetch_row()) {				$index = "custom_" . $cfrow['cid'];				$customFields[$index] = $cfrow;			}		}		if ($guser->userdetails['canApproveUploads'] and $approved == 0) {			// This extra query is just to get the muber of files			$result = $DB->query("SELECT l.*, sym.*                        FROM dl_symlinks sym                        LEFT JOIN dl_links l ON (l.did=sym.sym_did)                        WHERE l.approved=$approved");			$numfiles = $DB->num_rows($result);			$result = $DB->query("SELECT l.*, cd.*, sym.*                        FROM dl_symlinks sym                        LEFT JOIN dl_links l ON (l.did=sym.sym_did)                        LEFT JOIN dl_custom_data cd ON (cd.uid=l.did)                        WHERE l.approved=$approved                        LIMIT $limit , {$CONFIG[links_per_page]}");		} else {			// This extra query is just to get the muber of files			$result = $DB->query("SELECT l.*, sym.*                        FROM dl_symlinks sym                        LEFT JOIN dl_links l ON (l.did=sym.sym_did)                        WHERE l.owner=$owner AND l.approved=$approved");			$numfiles = $DB->num_rows($result);			$result = $DB->query("SELECT l.*, cd.*, sym.*                        FROM dl_symlinks sym                        LEFT JOIN dl_links l ON (l.did=sym.sym_did)                        LEFT JOIN dl_custom_data cd ON (cd.uid=l.did)                        WHERE l.owner=$owner AND l.approved=$approved                        LIMIT $limit , {$CONFIG['links_per_page']}");		}		if (!$myrow = $DB->fetch_row($result)) {			$main_content .= $std->info("No files to display");			return $main_content;		}		if ($approved)			$area = "files";		else			$area = "pending";		$pages = $std->pages($numfiles, $CONFIG["links_per_page"], "index.php?ACT=usercp&area=$area");		$forminput = "index.php?ACT=usercp&area=handleInput";		$data = array("pages" => "$pages",			"approved" => $approved,			"form_approve" => "$forminput",			"canModerate" => $std->canEdit($guser->userid));		$main_content .= $module->html_skincall("ucp_listing_head", $this->ucphtml, $data);		do {			$id = $myrow["did"];			$symid = $myrow['sym_id'];			$date = $std->convertDate($myrow["date"]);			$name = $myrow["name"];			if ($std->isRecent($myrow["date"]))				$new = "NEW";			else				$new = "";			if ($std->isRecent($myrow["lastEdited"]))				$updated = "UPDATED";			else				$updated = "";			if ($myrow["thumb"]) {				$thumb = "<img src='" . $CONFIG['filesfolder'] . "/" . $myrow["thumb"] . "'>";			} else {				$thumb = GETLANG("nothumb");			}			$description = nl2br($myrow["description"]);			$adminreview = nl2br($myrow["adminreview"]);			if ($myrow["userrating"])				$userrating = $myrow["userrating"];			else				$userrating = "n/a";			if ($myrow["adminrating"] == 0)				$adminrating = "n/a";			else				$adminrating = $myrow["adminrating"];			$delete = "<a href='index.php?ACT=usercp&area=deletedl&did={$id}&symid={$symid}'>" . GETLANG("delete") . "</a>";			$edit = "<a href='index.php?ACT=usercp&area=editdl&did={$id}&symid={$symid}'>" . GETLANG("edit") . "</a>";			$download_link = "<a href='index.php?dl=" . $myrow["did"] . "'>" . GETLANG("download_button") . "</a>";			$data = array("name" => $name,				"new" => $new,				"updated" => $updated,				"date" => $date,				"filesize" => $myrow["filesize"],				"author" => $myrow["author"],				"downloads" => $myrow["downloads"],				"adminrating" => $adminrating,				"userrating" => $userrating,				"download_link" => "$download_link",				"description" => "$description",				"adminreview" => "$adminreview",				"edit" => "$edit",				"editvalue" => "$id",				"canModerate" => $std->canEdit($guser->userid));			$main_content .= $module->html_skincall("ucp_listing_row", $this->ucphtml, $data);		} while ($myrow = $DB->fetch_row($result));		$data = array("order_boxes" => "",			"displayApprove" => $this->showApprove($approved),			"pages" => $pages,			"canModerate" => $std->canEdit($guser->userid));		$main_content .= $module->html_skincall("ucp_listing_foot", $this->ucphtml, $data);		return $main_content;	}	function showApprove($test1)	{		global $guser;		if ($test1)			return false;		if ($guser->userdetails['canApproveUploads'])			return true;		return false;	}	function ucpHandleInput()	{		global $IN, $DB, $std, $rwdInfo;		$ids = count($IN["dlid"]);		if ($ids == 0) {			$std->error(GETLANG("er_noselect"));			return;		}		if ($IN["deleteChecked"]) {			if ($IN["confirm"]) {				for ($i = 0; $i < $ids; $i++) {					$id = $IN["dlid"]["$i"];					if (!$this->deleteLink($id))						$std->error(GETLANG("er_stderrprefix"));				}				$std->info(GETLANG("deldl") . "<p>" . "<a href='index.php?ACT=usercp&area=home'>" . GETLANG("continue") . "</a>");			} else if ($IN["cancel"]) {				$std->info(GETLANG("delcancel") . "<p>" . "<a href='index.php?ACT=usercp&area=home'>" . GETLANG("continue") . "</a>");			} else {				$warntext = "<p><form method='post' action='index.php?ACT=usercp&area=handleInput'>";				for ($j = 0; $j < $ids; $j++)					$warntext .= "<input type='hidden' name='dlid[]' value='" . $IN["dlid"][$j] . "'>";				$warntext .= "<input type='hidden' name='deleteChecked' value='1'>";				$warntext .= "<input type='Submit' name='confirm' value='" . GETLANG("yes") . "'> <input type='Submit' name='cancel' value='" . GETLANG("no") . "'> </form>";				$std->warning(GETLANG("warn_dldel") . $warntext);			}		}		if ($IN["mergeChecked"]) {			if ($IN["confirm"]) {				/* CHECKPOINT				TODO								- Userrating (needs testing)				- Thumbnails (needs testing)				- Deleting of old files (needs testing)				*/				$filesprocessed = "0";				$downloads = "0";				$comments = "0";				$views = "0";				$sdid = implode("','", $IN["dlid"]);				$sdid = "'" . $sdid . "'";				$files = $DB->query("					SELECT *					FROM dl_links					WHERE did IN ($sdid)					ORDER BY did ASC				");				// Go through files getting info				while ($file = mysql_fetch_array($files)) {					// If no file has been processed before (and we know that that means its the oldest file due to sort order in query):					if ($filesprocessed == "0") {						$oldid = $file["did"];					}					// Execute on all files					$filesprocessed = $filesprocessed + 1;					if (!isset($dids)) {						$dids = "'" . $file["did"] . "'";					} else {						$dids = $dids . ",'" . $file["did"] . "'";					}					$downloads = $downloads + $file["downloads"];					$comments = $comments + $file["comments"];					$views = $views + $file["views"];					// If file is newest					if (mysql_num_rows($files) == $filesprocessed) {						$newfile = $file;					} else {						// Else delete						$file2 = $CONFIG['filesfolder'] . "/" . $file["maskName"];						if (is_file($file2)) {							unlink($file2);						}					}				}				//$std->info ("Oldest: ".$oldid."<br>Newest: ".$newfile["did"]);				$DB->query("UPDATE dl_links SET name = '" . $newfile["name"] . "', description = '" . $newfile["description"] . "', owner = '" . $newfile["owner"] . "', author = '" . $newfile["author"] . "', mirrors = '" . $newfile["mirrors"] . "', mirrornames = '" . $newfile["mirrornames"] . "', version = '" . $newfile["version"] . "', adminrating = '" . $newfile["adminrating"] . "', adminreview = '" . $newfile["version"] . "', filesize = '" . $newfile["filesize"] . "', realsize = '" . $newfile["realsize"] . "', maskName = '" . $newfile["maskName"] . "', fileType = '" . $newfile["fileType"] . "', downloads = '" . $downloads . "', approved = '" . $newfile["approved"] . "', comments = '" . $newfile["comments"] . "', views = '" . $newfile["views"] . "', price = '" . $newfile["price"] . "' WHERE dl_links.did = '" . $oldid . "'");				if ($comments > 0) {					$DB->query("UPDATE dl_comments SET did = '" . $oldid . "' WHERE did IN (" . $dids . ")");				}				$DB->query("UPDATE dl_images SET dlid = '" . $oldid . "' WHERE dlid IN (" . $dids . ")");				$DB->query("UPDATE dl_ratings SET dlid = '" . $oldid . "' WHERE dlid IN (" . $dids . ")");				$DB->query("DELETE FROM dl_symlinks WHERE sym_did IN (" . $dids . ") AND sym_did != '" . $oldid . "'");				$std->info(GETLANG("mergedl") . "<p>" . "<a href='index.php?ACT=usercp&area=home'>" . GETLANG("continue") . "</a>");				$std->info("DIDS: " . $dids . "<br>Downloads: " . $downloads . "<br>Comments: " . $comments . "<br>" . $query);			} else if ($IN["cancel"]) {				$std->info(GETLANG("mergecancel") . "<p>" . "<a href='index.php?ACT=usercp&area=home'>" . GETLANG("continue") . "</a>");			} else {				$warntext = "<p><form method='post' action='index.php?ACT=usercp&area=handleInput'>";				for ($j = 0; $j < $ids; $j++)					$warntext .= "<input type='hidden' name='dlid[]' value='" . $IN["dlid"][$j] . "'>";				$warntext .= "<input type='hidden' name='mergeChecked' value='1'>";				$warntext .= "<input type='Submit' name='confirm' value='" . GETLANG("yes") . "'> <input type='Submit' name='cancel' value='" . GETLANG("no") . "'> </form>";				$std->warning(GETLANG("warn_dlautomerge") . $warntext);			}		} else if ($IN['approveChecked']) {			for ($i = 0; $i < $ids; $i++) {				$id = $_POST["dlid"]["$i"];				$DB->query("UPDATE dl_links SET approved='1' WHERE did=$id");				$result = $DB->query("SELECT l.name, l.owner, sym.sym_catid FROM dl_links l                                        LEFT JOIN dl_symlinks sym ON (sym.sym_did={$id})                                        WHERE l.did='{$id}'");				while ($myrow = $DB->fetch_row($result)) {					$this->incrementCounter($myrow["sym_catid"], $id, $myrow['name'], $myrow['owner']);				}			}			$std->info(GETLANG("dlapproved") . ".<br>" . "<a href='index.php?ACT=usercp&area=pending'>" . GETLANG("continue") . "</a>");		}	}	function ucpeditdl()	{		global $IN, $DB, $std, $rwdInfo;		$symid = $IN['symid'];		$result = $DB->query("SELECT l.*, sym.*							  FROM dl_symlinks sym							  LEFT JOIN dl_links l ON (l.did=sym.sym_did)							  WHERE sym.sym_id={$symid}");		$newdata = $myrow = $DB->fetch_row();		$dlid = $myrow['did'];		$DB->query("SELECT sym_catid FROM dl_symlinks WHERE sym_did=$dlid");		while (($myrow2 = $DB->fetch_row()))			$newdata['catlist'][] = $myrow2['sym_catid'];		if (!$std->canEdit($myrow["owner"])) {			$std->error(GETLANG("er_noperms"));			return $main_content;		}		if ($IN["ACT"] == "deleteimg") {			$this->files_deleteimg();			return $main_content;		}		if ($IN["removefile"]) {			$table = "dl_links";			$sqlid = "did={$dlid}";			$type = "maskName";			$DB->query("SELECT * FROM dl_links WHERE did={$dlid}");			$myrow = $DB->fetch_row();			if ($this->removeFile($rwdInfo->path, $table, $sqlid, $dlid, $type)) {				$this->output .= GETLANG("filedl") . "<br><br>";			} else {				$this->output .= GETLANG("er_unlink") . "<br><br>";			}			$main_content .= "+ <a href='index.php?ACT=usercp&area=files'>" . GETLANG("backto") . " " . GETLANG("approved") . "</a><br>";			$main_content .= "+ <a href='index.php?ACT=usercp&area=pending'>" . GETLANG("backto") . " " . GETLANG("unapproved") . "</a><br>";			$main_content .= "+ <a href='index.php?ACT=usercp&area=editdl&did={$dlid}'>" . GETLANG("edit") . " " . $myrow['name'] . "</a><br>";			return $main_content;		}		if ($IN["confirm"]) {			if ($this->saveEdit($symid, $dlid)) {				$main_content .= "+ <a href='index.php?ACT=usercp&area=editdl&did={$dlid}'>" . GETLANG("backto") . " " . GETLANG("nav_editdl") . " " . $IN["name"] . "</a><br>";				$main_content .= "+ <a href='index.php?dlid={$dlid}'>" . GETLANG("backto") . " " . $IN["name"] . "</a><br>";				$main_content .= "+ <a href='index.php?ACT=usercp'>" . GETLANG("backto") . " " . GETLANG("nav_usercp") . "</a><br>";			}			return $main_content;		}		if ($IN["preview"] and !$IN["updateform"]) {			$postlink = "$post";			$this->previewEdit($postlink);			return $main_content;		}		// Else display the edit form		$formpost = "<form method='post' enctype='multipart/form-data' action='index.php?ACT=usercp&area=editdl&did={$dlid}&symid={$symid}'>";		$main_content .= $this->files_dlMainForm($dlid, $symid, $newdata, 0, $formpost);		return $main_content;	}	function editPrefs()	{		global $CONFIG, $DB, $guser, $IN, $std, $module;		if (!empty($IN['submit'])) {			$update = array();			if ($guser->getPermissions() & k_canChangeSkin)				$update["skin"] = $IN['skinchoice'];			$update["lang"] = $IN['langchoice'];			$update['receive_email'] = intval($IN['receive_email']);			$module->ucp_moduleprofile(&$update);			$DB->update($update, "dl_memberextra", "mid={$guser->userid}");			if ($CONFIG["usertype"] == USER_DEFAULT) {				$update = array();				if ($IN['email'] != $guser->userdetails[$guser->db_email]) {					if ($IN['email'] != $IN['email2']) {						$std->error(GETLANG("er_emails_no_match"));					}					$update["email"] = $IN['email'];				}				if ($IN['password'] and $IN['password2']) {					if ($IN['password'] != $IN['password2']) {						$std->error(GETLANG("er_nomatch"));						return $main;					}					$update["password"] = md5($IN["password"]);					$std->rw_setcookie("rwd_password", $update["password"]);				}				if (!empty($update))					$DB->update($update, "dl_users", "id={$guser->userid}");			}			$main .= GETLANG("settingsSaved");			return $main;		}		$checked = ($guser->userdetails['receive_email']) ? "checked" : "";		$getmail = "<input type='checkbox' name='receive_email' value='1' $checked>";		$data = array("username" => $guser->username,			"email" => $guser->userdetails[$guser->db_email],			"skin" => ($guser->getPermissions() & k_canChangeSkin) ? $std->skinListBox($guser->userdetails["skin"]) : GETLANG("er_feature_disabled"),			"lang" => $std->langListBox($guser->userdetails["lang"]),			"showoptions" => ($CONFIG["usertype"] == USER_DEFAULT),			"module_settings" => $module->ucp_moduleprofile(),			"receive_email" => $getmail);		return $main = $module->html_skincall("ucp_profile", $this->ucphtml, $data);	}}$loader = new usercp();?>