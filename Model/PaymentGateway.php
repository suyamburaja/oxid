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
 * Script: PaymentGateway.php
 */

namespace oe\novalnet\Model;

use oe\novalnet\Classes\NovalnetUtil;

 /**
 * Class PaymentGateway.
 */
class PaymentGateway extends PaymentGateway_parent
{
    /**
     * Get Util class
     *
     * @var string
     */
    protected $_oNovalnetUtil;

    /**
     * Get Error message
     *
     * @var string
     */
    protected $_sLastError;

    /**
     * Executes payment, returns true on success.
     *
     * @param double $dAmount
     * @param object &$oOrder
     *
     * @return boolean
     */
    public function executePayment($dAmount, &$oOrder)
    {
        $this->sCurrentPayment = $oOrder->sCurrentPayment;

        // checks the current payment method is not a Novalnet payment. If yes then skips the execution of this function
        if (!preg_match("/novalnet/i", $this->sCurrentPayment))
            return parent::executePayment($dAmount, $oOrder);

        $this->_oNovalnetUtil    = oxNew(NovalnetUtil::class);
        $sCallbackTid            = $this->_oNovalnetUtil->oSession->getVariable('sCallbackTid' . $this->sCurrentPayment);

        // verifies payment call type is to handle fraud prevention or redirect payment response or proceed payment
        if ($sCallbackTid) { // if true proceeds second call for Novalnet fraud prevention

            // validates the order amount of the transaction and the current cart amount are differed
            if ($this->_validateNovalnetCallbackAmount($dAmount) === false)
                return false;

            // performs the fraud prevention second call for transaction
            $aPinResponse = $this->doFraudModuleSecondCall($this->sCurrentPayment);

            // handles the fraud prevention second call response of the transaction
            if ($this->_validateNovalnetPinResponse($aPinResponse) === false)
                return false;

        } elseif ($this->_oNovalnetUtil->oConfig->getRequestParameter('tid') && $this->_oNovalnetUtil->oConfig->getRequestParameter('status')) {

            // checks to validate the redirect response
            if ($this->_validateNovalnetRedirectResponse() === false)
                return false;

        } else {
            // performs the transaction call
            $aNovalnetResponse = $this->_oNovalnetUtil->doPayment($oOrder);
            if ($aNovalnetResponse['status'] != '100') {
                $this->_sLastError = $this->_oNovalnetUtil->setNovalnetPaygateError($aNovalnetResponse);
                return false;
            }

            $blCallbackEnabled = $this->_oNovalnetUtil->oSession->getVariable('blCallbackEnabled' . $this->sCurrentPayment);

            // checks callback enabled to set the message for fraud prevention type
            if ($blCallbackEnabled) {
                $sFraudModuleMessage = '';
                $this->_oNovalnetUtil->oSession->setVariable('sCallbackTid' . $this->sCurrentPayment, $aNovalnetResponse['tid']);
                $iCallbackType = $this->_oNovalnetUtil->getNovalnetConfigValue('iCallback' . $this->sCurrentPayment);
                if ($iCallbackType == 1) {
                    $sFraudModuleMessage = $this->_oNovalnetUtil->oLang->translateString('NOVALNET_FRAUD_MODULE_PHONE_MESSAGE');
                } elseif ($iCallbackType == 2) {
                    $sFraudModuleMessage = $this->_oNovalnetUtil->oLang->translateString('NOVALNET_FRAUD_MODULE_MOBILE_MESSAGE');
                }
                $this->_sLastError = $sFraudModuleMessage;
                return false;
            }
        }

        return true;
    }

    /**
     * Performs payment confirmation while Fraud module is enabled
     *
     * @param string $sCurrentPayment
     *
     * @return array
     */
    public function doFraudModuleSecondCall($sCurrentPayment)
    {
        $aFirstRequest  = $this->_oNovalnetUtil->oSession->getVariable('aNovalnetGatewayRequest');
        $aFirstResponse = $this->_oNovalnetUtil->oSession->getVariable('aNovalnetGatewayResponse');
        $iRequestType   = $this->_oNovalnetUtil->getNovalnetConfigValue('iCallback' . $sCurrentPayment);
        $aDynValue      = array_map('trim', $this->_oNovalnetUtil->oSession->getVariable('dynvalue'));
        $sRemoteIp      = $this->_oNovalnetUtil->getIpAddress();

        // checks the second call request type of fraud prevention payments
        if ($aDynValue['newpin_' . $sCurrentPayment])
            $sRequestType = 'TRANSMIT_PIN_AGAIN';
        elseif (in_array($iRequestType, ['1', '2']))
            $sRequestType = 'PIN_STATUS';

        $sPinXmlRequest = '<?xml version="1.0" encoding="UTF-8"?>
                              <nnxml>
                                  <info_request>
                                      <vendor_id>' . $aFirstRequest['vendor'] . '</vendor_id>
                                      <vendor_authcode>' . $aFirstRequest['auth_code'] . '</vendor_authcode>
                                      <request_type>' . $sRequestType . '</request_type>
                                      <tid>' . $aFirstResponse['tid'] . '</tid>
                                      <remote_ip>' . $sRemoteIp . '</remote_ip>';

        if ($sRequestType == 'PIN_STATUS')
            $sPinXmlRequest .= '<pin>' . trim($aDynValue['pinno_' . $sCurrentPayment]) . '</pin>';

        $sPinXmlRequest .= '</info_request></nnxml>';
        $sPinXmlResponse = $this->_oNovalnetUtil->doCurlRequest($sPinXmlRequest, $this->_oNovalnetUtil->sPaygateInfoPortUrl, false);

        $xml = simplexml_load_string($sPinXmlResponse);
        $aResponse = json_decode(json_encode((array)$xml), true);
        if (!empty($aResponse['tid_status'])) {
            $aFirstResponse['tid_status'] = $aResponse['tid_status'];
            $this->_oNovalnetUtil->oSession->setVariable('aNovalnetGatewayResponse', $aFirstResponse);
        }
        return $aResponse;
    }

    /**
     * Validates Novalnet redirect payment's response
     *
     * @return boolean
     */
    private function _validateNovalnetRedirectResponse()
    {
        $aNovalnetResponse = $_REQUEST;
        
        $aResponse = $this->_oNovalnetUtil->getDecodeData($aNovalnetResponse);
        
        // checks the transaction status is success
        if (in_array($aResponse['status'], ['100','90'])) {

            // checks the hash value validation for redirect payments
            if ($this->_oNovalnetUtil->checkHash($aNovalnetResponse) === false) {
                $this->_sLastError = $this->_oNovalnetUtil->oLang->translateString('NOVALNET_CHECK_HASH_FAILED_ERROR');
                return false;
            }
            $this->_oNovalnetUtil->oSession->setVariable('aNovalnetGatewayResponse', $aResponse);
        } else {
			
			$aVendorData = ['vendor' => $aResponse['vendor'],
                            'product' => $aResponse['product'],
                            'auth_code' => $aResponse['auth_code'],
                            'tariff' => $aResponse['tariff'],
                            'test_mode' => $aResponse['test_mode']
                        ];
            $sOrderId = $this->_oNovalnetUtil->oSession->getVariable('blSave');
            $oDb              = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
            $oDb->execute("UPDATE oxorder SET OXFOLDER = 'ORDER_STATE_PAYMENTERROR' WHERE oxid = '{$sOrderId}'");
            $sMessage = $this->_oNovalnetUtil->oLang->translateString('NOVALNET_TRANSACTION_DETAILS');
            $sMessage .= $this->_oNovalnetUtil->oLang->translateString('NOVALNET_TRANSACTION_ID') . $aResponse['tid'];
            $sMessage .= !empty($aResponse['test_mode']) ? $this->_oNovalnetUtil->oLang->translateString('NOVALNET_TEST_ORDER') : '';
            $sMessage .= '<br>'.$this->_oNovalnetUtil->oLang->translateString('NOVALNET_PAYMENT_FAILED') . ' - ' . $this->_oNovalnetUtil->setNovalnetPaygateError($aResponse);
			$oDb->execute('INSERT INTO novalnet_transaction_detail ( TID, ORDER_NO, PAYMENT_ID, PAYMENT_TYPE, AMOUNT, GATEWAY_STATUS, CUSTOMER_ID, ORDER_DATE, TOTAL_AMOUNT, MASKED_DETAILS, REFERENCE_TRANSACTION, ZERO_TRXNDETAILS, ZERO_TRXNREFERENCE, ZERO_TRANSACTION, ADDITIONAL_DATA) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [$aResponse['tid'], $aResponse['order_no'], $aResponse['key'], $aResponse['payment_type'], $aResponse['amount'], $aResponse['tid_status'], $aResponse['customer_no'], date('Y-m-d H:i:s'), $aResponse['amount'], '', '', '', '', '', serialize($aVendorData)]);
            $oDb->execute('UPDATE oxorder SET NOVALNETCOMMENTS = CONCAT(IF(NOVALNETCOMMENTS IS NULL, "", NOVALNETCOMMENTS), "' . $sMessage . '") WHERE oxid = "' . $sOrderId . '"');
            $this->_updateArticleStock();
            $this->_sLastError = $this->_oNovalnetUtil->setNovalnetPaygateError($aResponse);
            $this->_oNovalnetUtil->clearNovalnetRedirectSession();
            return false;
        }
        return true;
    }

    /**
     * Validates order amount for Novalnet fraud module
     *
     * @param double $dAmount
     *
     * @return boolean
     */
    private function _validateNovalnetCallbackAmount($dAmount)
    {
        $dCurrentAmount          = str_replace(',', '', number_format($dAmount, 2)) * 100;
        $dNovalnetCallbackAmount = $this->_oNovalnetUtil->oSession->getVariable('dCallbackAmount' . $this->sCurrentPayment);

        // terminates the transaction if cart amount and transaction amount in first call are differed
        if ($dNovalnetCallbackAmount != $dCurrentAmount) {
            $this->_oNovalnetUtil->clearNovalnetSession();
            $this->_sLastError = $this->_oNovalnetUtil->oLang->translateString('NOVALNET_FRAUD_MODULE_AMOUNT_CHANGE_ERROR');
            return false;
        }
        return true;
    }

    /**
     * Validates Novalnet response of fraud prevention second call
     *
     * @param array $aPinResponse
     *
     * @return boolean
     */
    private function _validateNovalnetPinResponse($aPinResponse)
    {
        if ($aPinResponse['status'] != '100') {

            //  hides the payment for the user on next 30 minutes if wrong pin provided more than three times
            if ($aPinResponse['status'] == '0529006') {
                $this->_oNovalnetUtil->oSession->setVariable('blNovalnetPaymentLock' . $this->sCurrentPayment, 1);
                $this->_oNovalnetUtil->oSession->setVariable('sNovalnetPaymentLockTime' . $this->sCurrentPayment, time() + (30 * 60));
            } elseif ($aPinResponse['status'] == '0529008') {
                $this->_oNovalnetUtil->oSession->deleteVariable('sCallbackTid'. $this->sCurrentPayment);
            }
            $this->_sLastError = $this->_oNovalnetUtil->setNovalnetPaygateError($aPinResponse);
            return false;
        }
        return true;
    }

    private function _updateArticleStock() {
        $aOrderArticles = $this->_oNovalnetUtil->oSession->getVariable('aOrderArticles');
        foreach ($aOrderArticles as $oOrderArticle) {
                $oOrderArticle->updateArticleStock($oOrderArticle->oxorderarticles__oxamount->value, $this->_oNovalnetUtil->oConfig->getConfigParam('blAllowNegativeStock'));
         }
    }
}
?>
