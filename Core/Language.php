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
 * Script: Language.php
 */

namespace oe\novalnet\Core;

/**
 * Class Language.
 */
class Language extends Language_parent
{
   public function getBaseLanguage()
   {
        if ($this->_iBaseLanguageId === null) {
            $myConfig = $this->getConfig();
            $blAdmin = $this->isAdmin();

            // languages and search engines
            if ($blAdmin && (($iSeLang = \OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter('changelang')) !== null))
                $this->_iBaseLanguageId = $iSeLang;

             $sKey    = $myConfig->getRequestParameter('key') ? $myConfig->getRequestParameter('key') : $myConfig->getRequestParameter('payment_id');

            // checks to verify the current payment is Novalnet payment
            if (!empty($sKey) && in_array($sKey, ['6', '33', '34', '49', '50', '69', '78']))
                unset($_POST['lang']);

            // Recurring order
            if (!empty($myConfig->getRequestParameter('signup_tid'))
                && !empty($myConfig->getRequestParameter('subs_billing'))) {
                $oDb = \OxidEsales\Eshop\Core\DatabaseProvider::getDb(\OxidEsales\Eshop\Core\DatabaseProvider::FETCH_MODE_ASSOC);
                $sSql     = 'SELECT trans.ORDER_NO FROM novalnet_transaction_detail trans JOIN oxorder o ON o.OXORDERNR = trans.ORDER_NO where trans.tid = "' . $myConfig->getRequestParameter('signup_tid') . '"';
                $aOrderDetails = $oDb->getRow($sSql);
                $aOrderDetails = $oDb->getRow('SELECT OXLANG FROM oxorder where OXORDERNR = "' . $aOrderDetails['ORDER_NO']. '"');
                $_POST['lang'] = $aOrderDetails['OXLANG'];
            }

            if (is_null($this->_iBaseLanguageId))
                $this->_iBaseLanguageId = \OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter('lang');

            //or determining by domain
            $aLanguageUrls = $myConfig->getConfigParam('aLanguageURLs');

            if (!$blAdmin && is_array($aLanguageUrls)) {
                foreach ($aLanguageUrls as $iId => $sUrl) {
                    if ($sUrl && $myConfig->isCurrentUrl($sUrl)) {
                        $this->_iBaseLanguageId = $iId;
                        break;
                    }
                }
            }

            if (is_null($this->_iBaseLanguageId)) {
                $this->_iBaseLanguageId = \OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter('language');
                if (!isset($this->_iBaseLanguageId)) {
                    $this->_iBaseLanguageId = \OxidEsales\Eshop\Core\Registry::getSession()->getVariable('language');
                }
            }

            // if language still not set and not search engine browsing,
            // getting language from browser
            if (is_null($this->_iBaseLanguageId) && !$blAdmin && !\OxidEsales\Eshop\Core\Registry::getUtils()->isSearchEngine()) {
                // getting from cookie
                $this->_iBaseLanguageId = \OxidEsales\Eshop\Core\Registry::getUtilsServer()->getOxCookie('language');

                if (is_null($this->_iBaseLanguageId))
                    $this->_iBaseLanguageId = $this->detectLanguageByBrowser();

            }

            if (is_null($this->_iBaseLanguageId))
                $this->_iBaseLanguageId = $myConfig->getConfigParam('sDefaultLang');

            $this->_iBaseLanguageId = (int) $this->_iBaseLanguageId;

            // validating language
            $this->_iBaseLanguageId = $this->validateLanguage($this->_iBaseLanguageId);

            \OxidEsales\Eshop\Core\Registry::getUtilsServer()->setOxCookie('language', $this->_iBaseLanguageId);
        }

        return $this->_iBaseLanguageId;
   }
}
?>
