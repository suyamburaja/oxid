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
 * Script: NovalnetThankyou.php
 */
namespace oe\novalnet\Controller;

class NovalnetThankyou extends NovalnetThankyou_parent {

    public function render() {
        $sTemplate = parent::render();
        $oOrder     = $this->getOrder();
        $oDb = \OxidEsales\Eshop\Core\DatabaseProvider::getDb(\OxidEsales\Eshop\Core\DatabaseProvider::FETCH_MODE_ASSOC);
        $iOrderNr = $oOrder->oxorder__oxordernr->value;
        
        if ($oOrder->oxorder__oxpaymenttype->value == 'novalnetbarzahlen') {
            $sSql = 'SELECT ADDITIONAL_DATA FROM novalnet_transaction_detail where ORDER_NO = "' . $iOrderNr . '"';
            $aPaymentRef = $oDb->getRow($sSql);
            $sData = unserialize($aPaymentRef['ADDITIONAL_DATA']);
            $aToken = explode('|', $sData['cp_checkout_token']);
            $sBarzahlenLink = ($aToken[1] == 1) ? 'https://cdn.barzahlen.de/js/v2/checkout-sandbox.js' : 'https://cdn.barzahlen.de/js/v2/checkout.js';    
            $this->_aViewData['aNovalnetBarzahlensUrl'] = $sBarzahlenLink;
            $this->_aViewData['aNovalnetToken'] = $aToken[0];
        }
        return $sTemplate;
    }
}
