<?php
/**
 * Novalnet payment module
 *
 * This file is used for real time processing of transaction.
 *
 * This is free contribution made by request.
 * If you have found this file useful a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * @author    Novalnet AG
 * @copyright Copyright by Novalnet
 * @license   https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 *
 * Script: ShopControl.php
 */

namespace oe\novalnet\Core;

use oe\novalnet\Classes\NovalnetUtil;

/**
 * Class ShopControl.
 */
class ShopControl extends ShopControl_parent
{
    public function __construct()
    {
        $oNovalnetUtil = oxNew(NovalnetUtil::class);

        // checks the Novalnet affiliate id is passed
        if ($sNovalnetAffiliateId = $oNovalnetUtil->oConfig->getRequestParameter('nn_aff_id'))
            $oNovalnetUtil->oSession->setVariable('nn_aff_id', $sNovalnetAffiliateId);
    }
}
?>
