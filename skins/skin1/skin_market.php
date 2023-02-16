<?php
/*
 * Copyright (c) 2023.
 * RW::Software
 * Dave Conley
 * https://www.rwscripts.com/
 */
// skin_market.php
// Skin file auto generated by RW::Download v4.1
// It is highly recommended you do NOT edit this file
// Doing so could break the skinning engine and the script
// This skin can be edited by using the Skin Controls in the Admin CP
class skin_market
{
	function product_price_row($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML .= <<<RWS
<tr class='mainrow'>
					<td valign='top'>{$rwdInfo->lang['market_price']} {$data['defcurrency_name']}:</td>
					<td valign='top'><input name='price' type='text' style='width:100%' value='{$data['price']}'></td>
				</tr>
RWS;
//--END--//
		return $SHTML;
	}

	function ucp_nav($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML .= <<<RWS
<table width='100%'  border='0' cellspacing='1' cellpadding='3'>
                            <tr class='tableheader'>
                              <td>RW::Marketplace</td>
                            </tr>
RWS;
//startif
		if ($data['allowcredits']) {
			$SHTML .= <<<RWS
                            <tr class='mainrow'>
                              <td><a href='index.php?ACT=market&area=credit'>Purchase Credits</a></td>
                            </tr>
RWS;
		}//endif
		$SHTML .= <<<RWS
                            <tr class='mainrow'>
                              <td><a href='index.php?ACT=market&area=products'>Standalone Products</a></td>
                            </tr>
                            <tr class='mainrow'>
                              <td><a href='index.php?ACT=market&area=mycart'>My Cart</a></td>
                            </tr>
                            <tr class='mainrow'>
                              <td><a href='index.php?ACT=market&area=mysubs'>My Current Subscriptions</a></td>
                            </tr>
                            <tr class='mainrow'>
                              <td><a href='index.php?ACT=market&area=history'>Purchase History</a></td>
                            </tr>
                          </table>
RWS;
//--END--//
		return $SHTML;
	}

	function ucp_profile($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML .= <<<RWS
	<tr class='mainrow'>
		<td width='150'>{$rwdInfo->lang['def_currency']}:</td>
		<td>{$data['currency']}</td>
	</tr>
RWS;
//--END--//
		return $SHTML;
	}

	function ucp_mysubshead($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML .= <<<RWS
<table width='100%'  border='0' cellspacing='1' cellpadding='3'>
                          <tr class='tableheader'>
                            <td>Product</td>
                            <td>Purchased</td>
                            <td>Expires</td>
                            <td>Downloads Remaining</td>
                            <td>Subscription Status</td>
                          </tr>
RWS;
//--END--//
		return $SHTML;
	}

	function ucp_mysubsrow($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML .= <<<RWS
<tr class='mainrow'>
                            <td width='150'>{$data['product']}</td>
                            <td>{$data['purchased']}</td>
                            <td>{$data['expires']}</td>
                            <td>{$data['files']}</td>
                            <td>{$data['status']}</td>
                          </tr>
RWS;
//--END--//
		return $SHTML;
	}

	function ucp_mysubsfoot($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML .= <<<RWS
</table>
RWS;
//--END--//
		return $SHTML;
	}

	function ucp_productshead($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML .= <<<RWS
<table width='100%'  border='0' cellspacing='1' cellpadding='3'>
                          <tr class='tableheader'>
                            <td width='150'>Product</td>
                            <td>Subscription Length</td>
                            <td>Price</td>
                            <td>Additional Downloads</td>
                            <td>Additional Bandwidth</td>
                            <td>Usergroup Promotion</td>
                            <td width='100'>Purchase</td>
                          </tr>
RWS;
//--END--//
		return $SHTML;
	}

	function ucp_productsrow($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML .= <<<RWS
<tr class='mainrow'>
                            <td><b>{$data['product']}</b></td>
                            <td>{$data['length']}</td>
                            <td>{$data['price']}</td>
                            <td>{$data['downloads']}</td>
                            <td>{$data['bandwidth']}</td>
                            <td>{$data['group']}</td>
                            <td><b><a href='index.php?ACT=market&area=buynow&buynow=1&prodid={$data['prodid']}&sa=1'>Buy Now</a> | <a href='index.php?ACT=market&area=addtocart&prodid={$data['prodid']}'>Add to Cart</a></b></td>
                          </tr>
<tr class='mainrow'>
                            <td colspan=7>{$data['description']}</td>
                          </tr>
RWS;
//--END--//
		return $SHTML;
	}

	function ucp_productsfoot($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML .= <<<RWS
</table>
RWS;
//--END--//
		return $SHTML;
	}

	function ucp_mycarthead($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML .= <<<RWS
<table width='100%'  border='0' cellspacing='1' cellpadding='3'>
                          <tr class='tableheader'>
                            <td>Product</td>
                            <td width='100'>Remove</td>
                          </tr>
RWS;
//--END--//
		return $SHTML;
	}

	function ucp_mycartrow($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML .= <<<RWS
<tr class='mainrow'>
                            <td width='150'>{$data['product']}</td>
                            <td><a href='index.php?ACT=market&area=removeitem&cid={$data['cid']}'>{$rwdInfo->lang['remove']}</a></td>
                          </tr>
RWS;
//--END--//
		return $SHTML;
	}

	function ucp_mycartfoot($data = NULL)
	{
		global $rwdInfo;
//--START--//
		$SHTML .= <<<RWS
</table><form method='post' enctype='multipart/form-data' action='index.php?ACT=market&area=checkout'>
<input type='submit' name='buycart' value='Checkout Now'></form>
RWS;
//--END--//
		return $SHTML;
	}
}

?>
