<?php
/*
 * Copyright (c) 2023.
 * RW::Software
 * Dave Conley
 * https://www.rwscripts.com/
 */
// skin_search.php
// Skin file auto generated by RW::Download v4.0.5
// It is highly recommended you do NOT edit this file
// Doing so could break the skinning engine and the script
// This skin can be edited by using the Skin Controls in the Admin CP
class skin_search
{
	function search_form($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML .= <<<RWS
<form method='post' enctype='multipart/form-data' action='index.php?ACT=dosearch'>
<div class='tableborder'>
<div id="banner">
	  <img id="leftcap" src="{$rwdInfo->skinurl}/images/headerleft.gif" width="18" height="29">
	  <img id="rightcap" src="{$rwdInfo->skinurl}/images/headerright.gif" width="31" height="29">
	  <div id='catname'>> {$rwdInfo->lang['search']}</div>
	</div>
<table width='100%'  border='0' align='center' cellpadding='0' cellspacing='0'>
          <tr>
            <td colspan='3'><table width='100%'  border='0' cellspacing='1' cellpadding='3'>
				<tr class='mainrow'>
					<td width='100'>{$rwdInfo->lang['search_terms']}: </td>
					<td valign='top'>
					<input type='text' name='terms'>
					</td>
				</tr>
				
		</table></td>
          </tr>
          <tr class='tablefooter'>
            <td>&nbsp;</td>
            <td><div align='center'>
			<input type='submit' name='submit' value='Submit'>
			</div></td>
            <td>&nbsp;</td>
          </tr>
  </table>
</div>
</form>
RWS;
//--END--//
		return $SHTML;
	}

	function search_foot($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML .= <<<RWS
</table></td>
          </tr>
          <tr class='tablefooter'>
            <td>&nbsp;</td>
            <td><table width='100%'  border='0' align='center' cellpadding='0' cellspacing='0'>
			<tr><td>{$data['pages']}</td><td><div align='right'>&nbsp;</div></td></tr></table></td>
            <td>&nbsp;</td>
          </tr>
  </table>
</div></form>
RWS;
//--END--//
		return $SHTML;
	}

	function search_head($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML .= <<<RWS
{$data['pages']}
<form method='post' enctype='multipart/form-data' action='index.php?ACT=massmod'>
<div class='tableborder'>
<div id="banner">
	  <img id="leftcap" src="{$rwdInfo->skinurl}/images/headerleft.gif" width="18" height="29">
	  <img id="rightcap" src="{$rwdInfo->skinurl}/images/headerright.gif" width="31" height="29">
	  <div id='catname'>&gt; {$rwdInfo->lang['search_results']}</div>
	</div>
<table width='100%'  border='0' align='center' cellpadding='0' cellspacing='0'>
          <tr>
            <td colspan='3'><table width='100%'  border='0' cellspacing='1' cellpadding='3'>
                <tr class='tableheader'>
                  <td width='20'>&nbsp;</td>
                  <td>{$rwdInfo->lang['name']}</td>
                  <td><div align='center'>{$rwdInfo->lang['author']}</div></td>
                  <td><div align='center'>{$rwdInfo->lang['date']}</div></td>
                  <td><div align='center'>{$rwdInfo->lang['downloads']}</div></td>
                </tr>
RWS;
//--END--//
		return $SHTML;
	}

	function search_row($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML .= <<<RWS
<tr class='mainrow'>
                  <td>&nbsp;</td>
                  <td>{$data['name']} {$data['pinned']} {$data['new']} {$data['updated']}</td>
                  <td><div align='center'>{$data['author']}</div></td>
                  <td><div align='center'>{$data['date']}</div></td>
				  <td><div align='center'>{$data['downloads']}</div></td>
                </tr>
RWS;
//--END--//
		return $SHTML;
	}
}

?>
