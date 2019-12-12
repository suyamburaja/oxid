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
 * Script: RedirectController.php
 */

namespace oe\novalnet\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use oe\novalnet\Classes\NovalnetUtil;

/**
 * Class RedirectController.
 */
class RedirectController extends FrontendController
{
    /**
     * Returns name of template to render
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        $oNovalnetUtil = oxNew(NovalnetUtil::class);
        $oUser    = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $sUserID  = $oNovalnetUtil->oSession->getVariable('usr');
        $oUser->load($sUserID);
        if (!$oUser->getUser())
             \OxidEsales\Eshop\Core\Registry::getUtils()->redirect($oNovalnetUtil->oConfig->getShopMainUrl(), false);

        $this->_aViewData['sNovalnetFormAction'] = $oNovalnetUtil->oSession->getVariable('sNovalnetRedirectURL');
        $this->_aViewData['aNovalnetFormData']   = $oNovalnetUtil->oSession->getVariable('aNovalnetRedirectRequest');

        // checks to verify the redirect payment details available
        if (empty($this->_aViewData['sNovalnetFormAction']) || empty($this->_aViewData['aNovalnetFormData']))
        {
             \OxidEsales\Eshop\Core\Registry::getUtils()->redirect($oNovalnetUtil->oConfig->getShopMainUrl() . 'index.php?cl=payment', false);
         }
        elseif (!empty($this->_aViewData['sNovalnetFormAction']) && !empty($this->_aViewData['aNovalnetFormData']))
        {
            return 'novalnetredirect.tpl';
        }
    }
}
