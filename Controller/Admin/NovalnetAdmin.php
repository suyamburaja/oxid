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
 * Script: NovalnetAdmin.php
 */

namespace oe\novalnet\Controller\Admin;

/**
 * Class NovalnetAdmin.
 */
class NovalnetAdmin extends \OxidEsales\Eshop\Application\Controller\Admin\ShopConfiguration
{
    protected $_sThisTemplate = 'novalnetadmin.tpl';

    /**
     * Returns name of template to render
     *
     * @return string
     */
    public function render()
    {
        return $this->_sThisTemplate;
    }
    
     /**
     * Gets current language
     *
     * @return string
     */
    public function getNovalnetLanguage()
    {
        $iLang = \OxidEsales\Eshop\Core\Registry::getLang()->getTplLanguage();
        return \OxidEsales\Eshop\Core\Registry::getLang()->getLanguageAbbr($iLang);
    }

    /**
     * Get Image path
     *
     * @param $image
     *
     * @return string
     */
    public function getImagePath($image)
    {
        $viewConfig = oxNew(\OxidEsales\Eshop\Core\ViewConfig::class);
        return  $viewConfig->getModuleUrl('novalnet', 'out/admin/img/updates/'.$this->getNovalnetLanguage()."/".$image);
    }
}
