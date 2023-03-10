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
	function thumb_table_head($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML = "<div class='tableborder'><table width='100%'  border='0 align='center' cellpadding='2' cellspacing='2'>
          <tr class='acptablesubhead'>
            <td colspan=3>{$rwdInfo->lang['screenshots']}</td></tr>
          </table>
		  <table width='100%'  border='0' cellspacing='1' cellpadding='3'><tr class='mainrow'>";
//--END--//
		return $SHTML;
	}

	function thumb_plain($data)
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

	function thumb_table_foot()
	{
		global $rwdInfo;
//--START--//
		$SHTML = "</tr></table>
</div>";
//--END--//
		return $SHTML;
	}

	function thumb_end_row($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML = "</tr><tr class='mainrow'>";
//--END--//
		return $SHTML;
	}

	function thumb_cell($data)
	{
		global $rwdInfo;
//--START--//
		$SHTML = "<td width='{$data['cellwidth']}'>
<table border=0 cellspacing=0 cellpadding=2 width=100%>
<tr>
<td><div align='center'><a href='{$data['imageurl']}'>
<img src='{$data['thumburl']}' {$data['dims']} border=0></a>
</div>
</td></tr>
{$data['editrow']}
</table></td>";
//--END--//
		return $SHTML;
	}

	function thumb_empty($data)
	{
		global $rwdInfo;
//--START--//
		$SHTML = "<td width='{$data['cellwidth']}'>&nbsp;</td>";
//--END--//
		return $SHTML;
	}

	function thumb_editrow($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML = "<tr>
<td><div align='center'>[ <a href='{$data['editlink']}'>{$rwdInfo->lang['delete']}</a> ]</div>
</td>
</tr>";
//--END--//
		return $SHTML;
	}
}

?>
