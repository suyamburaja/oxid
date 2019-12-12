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
 * Script: OrderController.php
 */

namespace oe\novalnet\Controller;

use oe\novalnet\Classes\NovalnetUtil;

/**
 * Class OrderController.
 */
class OrderController extends OrderController_parent
{
    /**
     * Receives Novalnet response for redirect payment
     *
     * @return boolean
     */
    public function novalnetGatewayReturn()
    {
        $oConfig = $this->getConfig();
        $oNovalnetUtil = oxNew(NovalnetUtil::class);
        $oNovalnetUtil->oSession->deleteVariable('sNovalnetRedirectURL');
        $oNovalnetUtil->oSession->deleteVariable('aNovalnetRedirectRequest');
        $sKey    = $oConfig->getRequestParameter('key') ? $oConfig->getRequestParameter('key') : $oConfig->getRequestParameter('payment_id');

        // checks to verify the current payment is Novalnet payment
        if (in_array($sKey, ['6', '33', '34', '49', '50', '69', '78'])) 
        {
            $sInputVal3                   = $oConfig->getRequestParameter('inputval3');
            $sInputVal4                   = $oConfig->getRequestParameter('inputval4');
            $_POST['shop_lang']           = ($sInputVal3) ? $sInputVal3 : $_POST['shop_lang'] ;
            $_POST['stoken']              = ($sInputVal4) ? $sInputVal4 : $_POST['stoken'];
            $_POST['actcontrol']          = $oConfig->getTopActiveView()->getViewConfig()->getTopActiveClassName();
            $_POST['sDeliveryAddressMD5'] = $this->getDeliveryAddressMD5();
            if (!$oConfig->getRequestParameter('ord_agb') && $oConfig->getConfigParam( 'blConfirmAGB'))
                $_POST['ord_agb'] = 1;

            if ($oConfig->getConfigParam('blEnableIntangibleProdAgreement')) {
                if (!$oConfig->getRequestParameter('oxdownloadableproductsagreement'))
                    $_POST['oxdownloadableproductsagreement'] = 1;

                if (!$oConfig->getRequestParameter('oxserviceproductsagreement'))
                    $_POST['oxserviceproductsagreement'] = 1;
            }        
        }
        $oNovalnetUtil->oLang->setBaseLanguage($oConfig->getRequestParameter('shop_lang'));
        return $this->execute();
    }
}
?>
