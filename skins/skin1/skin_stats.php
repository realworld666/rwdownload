<?php
/*
 * Copyright (c) 2023.
 * RW::Software
 * Dave Conley
 * https://www.rwscripts.com/
 */
// skin_login.php
// Skin file auto generated by RW::Download v4.0 Beta 3 [14/05/04]
// It is highly recommended you do NOT edit this file
// Doing so could break the skinning engine and the script
// This skin can be edited by using the Skin Controls in the Admin CP
class skin_stats
{
	function topdownloads($data)
	{
		global $rwdInfo;
//--START--//
		$SHTML = <<<RWS
- <a href='index.php?dlid={$data['sym_id']}'>{$data['name']}</a> ({$data['downloads']})<br>
RWS;
//--END--//
		return $SHTML;
	}

	function topRatedDownloads($data)
	{
		global $rwdInfo;
//--START--//
		$SHTML = <<<RWS
- <a href='index.php?dlid={$data['sym_id']}'>{$data['name']}</a> ({$data['userrating']})<br>
RWS;
//--END--//
		return $SHTML;
	}

	function latestDownloads($data)
	{
		global $rwdInfo;
//--START--//
		$SHTML = <<<RWS
- <a href='index.php?dlid={$data['sym_id']}'>{$data['name']}</a><br>
RWS;
//--END--//
		return $SHTML;
	}

	function totalDownloads($data)
	{
		global $rwdInfo;
//--START--//
		$SHTML = <<<RWS
Total files available: {$data['total']}<br>
Number of files downloaded: {$data['downloads']}<br>
{$data['size']} of files available for download<br>
RWS;
//--END--//
		return $SHTML;
	}

	function randomDownload($data)
	{
		global $rwdInfo;
//--START--//
		$SHTML = <<<RWS
Download name: {$data['name']}<br>
Desc: {$data['description']}<br>
RWS;
//--END--//
		return $SHTML;
	}
}

?>
