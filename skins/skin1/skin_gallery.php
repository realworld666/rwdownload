<?php
/*
 * Copyright (c) 2023.
 * RW::Software
 * Dave Conley
 * https://www.rwscripts.com/
 */
// skin_files.php
// Skin file auto generated by RW::Download v4.0 Beta 3 [14/05/04]
// It is highly recommended you do NOT edit this file
// Doing so could break the skinning engine and the script
// This skin can be edited by using the Skin Controls in the Admin CP
class skin_gallery
{
	function browser_view($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML = <<<RWS
<div align='center'><a href='{$data['imageurl']}'><img src='{$data['imageurl']}' border='0' width='{$data['maxWidth']}' height='{$data['maxHeight']}' alt=''></a></div>
<table width='100%'  border='0' align='center' cellpadding='0' cellspacing='0' summary='Back &amp; Next table'>
<tr><td align='left' width='33%'><-- {$data['previous']}</td>
<td align='center' width='33%'><a href='javascript:window.close();'>Close</a></td>
<td align='right' width='33%'>{$data['next']} --></td>
</tr></table>
RWS;
//--END--//
		return $SHTML;
	}

	function thumb_table_head($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML = <<<RWS
<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
<!--
function launch_gallery_browser(url) {
    remote = open(url,"Browser","width={$data['gallWidth']},height={$data['gallHeight']},resizable=yes,scrollbars=yes");
}
// -->
</SCRIPT>
<div class='tableborder'>
<div id="banner">
	  <img id="leftcap" src="{$rwdInfo->skinurl}/images/headerleft.gif" width="18" height="29">
	  <img id="rightcap" src="{$rwdInfo->skinurl}/images/headerright.gif" width="31" height="29">
	  <div id='catname'>&gt; {$rwdInfo->lang['screenshots']}</div>
	</div>
<table width='100%'  border='0' align='center' cellpadding='0' cellspacing='0'>
          <tr>
            <td colspan='3'><table width='100%'  border='0' cellspacing='1' cellpadding='3'><tr class='mainrow'>
RWS;
//--END--//
		return $SHTML;
	}

	function thumb_table_foot($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML = <<<RWS
</table></td>
          </tr>
  </table>
</div>
RWS;
//--END--//
		return $SHTML;
	}

	function thumb_end_row($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML = <<<RWS
</tr><tr class='mainrow'>
RWS;
//--END--//
		return $SHTML;
	}

	function thumb_cell($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML = <<<RWS
<td width='{$data['cellwidth']}'>
<table border=0 cellspacing=0 cellpadding=2 width=100%>
<tr>
<td><div align='center'><a href='javascript:launch_gallery_browser("{$data['imageurl']}")'>
<img src='{$data['thumburl']}' {$data['dims']} border=0></a>
</div>
</td></tr>
{$data['editrow']}
</table></td>
RWS;
//--END--//
		return $SHTML;
	}

	function thumb_empty($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML = <<<RWS
<td width='{$data['cellwidth']}'>&nbsp;</td>
RWS;
//--END--//
		return $SHTML;
	}

	function thumb_plain($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML = <<<RWS
<div align='center'><a href='{$data['imageurl']}'>
<img src='{$data['thumburl']}' {$data['dims']} border=0></a>
</div>
RWS;
//--END--//
		return $SHTML;
	}

	function thumb_editrow($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML = <<<RWS
<tr>
<td><div align='center'>[ <a href='{$data['editlink']}'>{$rwdInfo->lang['delete']}</a> ]</div>
</td>
</tr>
RWS;
//--END--//
		return $SHTML;
	}
}

?>
