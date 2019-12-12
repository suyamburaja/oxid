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
 * Script: AccountOrderController.php
 */

namespace oe\novalnet\Controller;

use oe\novalnet\Classes\NovalnetUtil;

/**
 * Class AccountOrderController.
 */
class AccountOrderController extends AccountOrderController_parent {

    /**
     * Gets Novalnet payment name for given order
     *
     * @param string $sPaymentType
     *
     * @return string
     */
    public function getNovalnetPaymentName($sPaymentType)
    {
        $oPayment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $oPayment->load($sPaymentType);
        return $oPayment->oxpayments__oxdesc->rawValue;
    }

    /**
     * Gets current theme
     *
     * @return string
     */
    public function novalnetGetTheme()
    {
        $oTheme = oxNew('oxTheme');
        return $oTheme->getActiveThemeId();
    }
}
