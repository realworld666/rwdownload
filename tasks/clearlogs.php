<?php
/*
 * Copyright (c) 2023.
 * RW::Software
 * Dave Conley
 * https://www.rwscripts.com/
 */

class TaskClearLogs
{
	function TaskClearLogs()
	{
	}

	function RunTask($thistask)
	{
		global $DB;
		$timenow = time() + 604800;
		$DB->query("DELETE FROM `dl_sessions` WHERE `sTime`<'{$timenow}'");
	}
}

$loader = new TaskClearLogs();
?>
