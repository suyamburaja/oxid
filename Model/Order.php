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
 * Script: Order.php
 */

namespace oe\novalnet\Model;

use oe\novalnet\Classes\NovalnetUtil;

/**
 * Class Order.
 */
class Order extends Order_parent
{
    /*
     * Get Current Payment
     *
     * @var string
    */
    public  $sCurrentPayment;

    /*
     * Get Novalnet Paid Date
     *
     * @var string
    */
    protected $_sNovalnetPaidDate;

    /*
     * Get Novalnet Util Details
     *
     * @var object
    */
    protected $_oNovalnetUtil;

    /*
     * Get Novalnet Date
     *
     * @var array
    */
    protected $_aNovalnetData;

    /*
     * Get Novalnet Reference
     *
     * @var string
    */
    protected $_sInvoiceRef;

    /*
     * Get Novalnet Reference
     *
     * @var string
    */
    protected $oNovalnetSession;

    /**
     * Finalizes the order in shop
     *
     * @param object  $oBasket
     * @param object  $oUser
     * @param boolean $blRecalculatingOrder
     *
     * @return boolean
     */
     public function finalizeOrder(\OxidEsales\Eshop\Application\Model\Basket $oBasket, $oUser, $blRecalculatingOrder = false)
     {
        $this->sCurrentPayment = $oBasket->getPaymentId(); // to get the current payment
        $this->_sNovalnetPaidDate = '0000-00-00 00:00:00';  // set default value for the paid date of the order for novalnet transaction

        // Checks the current payment method is not a Novalnet payment. If yes then skips the execution of this function
        if (!preg_match("/novalnet/i", $this->sCurrentPayment)) {
            return parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);
        }

        $this->oNovalnetSession = \OxidEsales\Eshop\Core\Registry::getSession();
        $this->oDb  = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        $this->_oNovalnetUtil = oxNew(NovalnetUtil::class);
        $ccRedirectcheck = ($this->_oNovalnetUtil->getNovalnetConfigValue('blCC3DActive') == '1' || $this->_oNovalnetUtil->getNovalnetConfigValue('blCC3DFraudActive') == '1');

        if (!empty($ccRedirectcheck))
        array_push($this->_oNovalnetUtil->aRedirectPayments, 'novalnetcreditcard');

        if (empty($this->_oNovalnetUtil->oConfig->getRequestParameter('tid')) && empty($this->_oNovalnetUtil->oConfig->getRequestParameter('status'))) {
            $sGetChallenge = $this->oNovalnetSession->getVariable('sess_challenge');

            if ($this->_checkOrderExist($sGetChallenge)) {
                \OxidEsales\Eshop\Core\Registry::getUtils()->logger('BLOCKER');
                return self::ORDER_STATE_ORDEREXISTS;
            }

            if (!$blRecalculatingOrder) {
                $this->setId($sGetChallenge);
                if ($iOrderState = $this->validateOrder($oBasket, $oUser))
                    return $iOrderState;
            }

            $this->_setUser($oUser);
            $this->_loadFromBasket($oBasket);
            $oUserPayment = $this->_setPayment($oBasket->getPaymentId());

            $this->oNovalnetSession->setVariable('oUser', $oUser);
            $this->oNovalnetSession->setVariable('oBasket', $oBasket);
            $this->oNovalnetSession->setVariable('oUserPayment', $oUserPayment);

            if (!$blRecalculatingOrder) {
                $this->_setFolder();
            }

            $this->_setOrderStatus('NOT_FINISHED');
            $blSave = $this->save();
            $this->oNovalnetSession->setVariable('blSave', $blSave);
            $aOrderArticles = $this->getOrderArticles();
            $this->oNovalnetSession->setVariable('aOrderArticles', $aOrderArticles);
        }

        if (!in_array($this->sCurrentPayment, $this->_oNovalnetUtil->aRedirectPayments) || !empty($this->_oNovalnetUtil->oConfig->getRequestParameter('tid'))) {
            if (!$blRecalculatingOrder) {
                $oBasket =  $this->oNovalnetSession->getVariable('oBasket');
                $oUser   = $this->oNovalnetSession->getVariable('oUser');
                $oUserPayment = $this->oNovalnetSession->getVariable('oUserPayment');
                $blRet = $this->_executePayment($oBasket, $oUserPayment);
                if ($blRet !== true) {
                    $this->oNovalnetSession->deleteVariable('sess_challenge');
                    return $blRet;
                }
            }
        }

        if (!in_array($this->sCurrentPayment, $this->_oNovalnetUtil->aRedirectPayments) || empty($this->_oNovalnetUtil->oConfig->getRequestParameter('tid'))) {
            if (!$this->oxorder__oxordernr->value) {
                $this->_setNumber();
            } else {
                oxNew(\OxidEsales\Eshop\Core\Counter::class)->update($this->_getCounterIdent(), $this->oxorder__oxordernr->value);
            }
            $this->oNovalnetSession->setVariable('nn_orderno', $this->oxorder__oxordernr->value);
        }

        if (in_array($this->sCurrentPayment, $this->_oNovalnetUtil->aRedirectPayments) && empty($this->_oNovalnetUtil->oConfig->getRequestParameter('tid')))
           return $this->_oNovalnetUtil->doPayment($this);

        if (in_array($this->sCurrentPayment, $this->_oNovalnetUtil->aRedirectPayments))
            $this->oNovalnetSession->deleteVariable('ordrem');


        // logs transaction details in novalnet tables
        if (!$blRecalculatingOrder) {
            $this->_logNovalnetTransaction();
            $this->_updateNovalnetComments();
            if (!in_array($this->sCurrentPayment, $this->_oNovalnetUtil->aRedirectPayments))
               $this->_sendNovalnetPostbackCall(); // to send order number in post back call

            $this->_oNovalnetUtil->clearNovalnetSession();
        }

        $this->_setOrderStatus('OK');

        $sOrderid = ($this->oNovalnetSession->getVariable('nn_orderno')) ? $this->oNovalnetSession->getVariable('nn_orderno') : $this->getId();

        $oBasket->setOrderId($sOrderid);

        $this->_updateWishlist($oBasket->getContents(), $oUser);

        $this->_updateNoticeList($oBasket->getContents(), $oUser);

        if (!$blRecalculatingOrder) {
            $this->_updateOrderDate();
            $this->_markVouchers($oBasket, $oUser);
            if (in_array($this->sCurrentPayment, $this->_oNovalnetUtil->aRedirectPayments)) {
                $sOrderId = $this->oNovalnetSession->getVariable('blSave');
                $this->oDb->execute("UPDATE oxorder SET OXTRANSSTATUS = 'OK' WHERE oxid = '{$sOrderId}'");
                $oBasket = $this->oNovalnetSession->getVariable('oBasket');
                $iRet = $this->_nnSendOrderByEmail($sOrderId, $oBasket);
                } else {

                    $iRet = $this->_sendOrderByEmail($oUser, $oBasket, $oUserPayment);
                }
        } else {

            $iRet = self::ORDER_STATE_OK;
        }

        $this->_oNovalnetUtil->clearNovalnetRedirectSession();

        return $iRet;
    }

    /**
     * Logs Novalnet transaction details into Novalnet tables in shop
     *
     * @param object $oBasket
     */
    private function _logNovalnetTransaction()
    {
        $sMaskedDetails = '';
        $sZeroTrxnDetails       = $sZeroTrxnReference = NULL;
        $blZeroAmountBooking    = $blReferenceTransaction = '0';
        $iOrderNo               = !empty($this->oNovalnetSession->getVariable('nn_orderno')) ? $this->oNovalnetSession->getVariable('nn_orderno') : $this->oxorder__oxordernr->value;
        $aRequest               = $this->oNovalnetSession->getVariable('aNovalnetGatewayRequest');
        $aResponse              = $this->oNovalnetSession->getVariable('aNovalnetGatewayResponse');

        // Delete the refillSepaiban session variable which was stored in novalnetutil file for failure transaction.
        $this->oNovalnetSession->deleteVariable('refillSepaiban');

        $this->_aNovalnetData   = array_merge($aRequest, $aResponse);

        $this->_aNovalnetData['test_mode'] = $aRequest['test_mode'] == '1' ? $aRequest['test_mode'] : $aResponse['test_mode'];

        // checks the current payment is credit card or direct debit sepa, Guaranteed direct debit sepa, Paypal
        if (in_array($this->_aNovalnetData['key'], ['6', '34', '37', '40'])) {

            // checks the shopping type is zero amount booking - if yes need to save the transaction request
            if ($this->_oNovalnetUtil->getNovalnetConfigValue('iShopType' . $this->sCurrentPayment) == '2' && $this->_aNovalnetData['amount'] == 0) {
                if ($this->_aNovalnetData['key'] == '6') {
                    unset($aRequest['unique_id'], $aRequest['pan_hash'], $aRequest['nn_it'], $aRequest['cc_3d']);
                } elseif (in_array($this->_aNovalnetData['key'], [ '37', '40' ])) {
                    $aRequest['sepa_due_date'] = $this->_oNovalnetUtil->getNovalnetConfigValue('iDueDatenovalnetsepa');
                    unset($aRequest['pin_by_callback'], $aRequest['pin_by_sms']);
                }
                unset($aRequest['on_hold'], $aRequest['create_payment_ref']);
                $sZeroTrxnDetails    = serialize($aRequest);
                $sZeroTrxnReference  = $this->_aNovalnetData['tid'];
                $blZeroAmountBooking = '1';
            }
            if (!empty($this->_aNovalnetData['create_payment_ref'])) {
                if ($this->_aNovalnetData['key'] == '6') {
                    $sMaskedDetails = serialize( [ 'cc_type'      => $this->_aNovalnetData['cc_card_type'],
                                                    'cc_holder'    => $this->_aNovalnetData['cc_holder'],
                                                    'cc_no'        => $this->_aNovalnetData['cc_no'],
                                                    'cc_exp_month' => $this->_aNovalnetData['cc_exp_month'],
                                                    'cc_exp_year'  => $this->_aNovalnetData['cc_exp_year']
                                                  ]);
                } elseif ($this->_aNovalnetData['key'] == '34') {
                    $sMaskedDetails = serialize(['paypal_transaction_id' => $this->_aNovalnetData['paypal_transaction_id'],
                                                  'tid'              => $this->_aNovalnetData['tid']
                                                ]);
                } else {
                    $sMaskedDetails = serialize( ['bankaccount_holder' => html_entity_decode($this->_aNovalnetData['bankaccount_holder']),
                                                  'iban'         => $this->_aNovalnetData['iban'],
                                                  'tid'          => $this->aNovalnetData['tid']
                                                 ]);
                }
            }

            $blReferenceTransaction = (!empty($this->_aNovalnetData['payment_ref'])) ? '1' : '0';
        }
        
        if ((in_array($this->_aNovalnetData['key'], ['27', '37', '40', '41','59' ]) || ($this->_aNovalnetData['key'] == '6' && !isset($this->_aNovalnetData['cc_3d'])) && $this->_oNovalnetUtil->getNovalnetConfigValue('blCC3DFraudActive') != '1') || ($this->_aNovalnetData['key'] == '34' && isset($aRequest['payment_ref'])))
                $this->_aNovalnetData['amount'] = $this->_aNovalnetData['amount'] * 100;

          $aVendorData = $aIvoiceBankData = $aBarzahlenData = [];

          $aVendorData = ['vendor' => $this->_aNovalnetData['vendor'],
                            'product' => $this->_aNovalnetData['product'],
                            'auth_code' => $this->_aNovalnetData['auth_code'],
                            'tariff' => $this->_aNovalnetData['tariff'],
                            'test_mode' => $this->_aNovalnetData['test_mode']
                        ];
        // check current payment is invoice or prepayment or guaranteed invoice
        if (in_array($this->_aNovalnetData['key'], ['27', '41' ])) {
            $this->_sInvoiceRef = 'BNR-' . $this->_aNovalnetData['product'] . '-' . $iOrderNo;
            $aIvoiceBankData = [ 'invoice_account_holder'      => $this->_aNovalnetData['invoice_account_holder'],
                                            'invoice_iban'    => $this->_aNovalnetData['invoice_iban'],
                                            'invoice_bic'        => $this->_aNovalnetData['invoice_bic'],
                                            'invoice_bankname' => $this->_aNovalnetData['invoice_bankname'],
                                            'invoice_bankplace'  => $this->_aNovalnetData['invoice_bankplace'],
                                            'due_date'  => $this->_aNovalnetData['due_date'],
                                            'invoice_ref' => $this->_sInvoiceRef,
                                            'tid' => $this->_aNovalnetData['tid'],
                                            'amount' => $this->_aNovalnetData['amount']
                                          ];
        }
        if ($this->_aNovalnetData['key'] == '59') {
            $aStores['nearest_store'] = $this->_oNovalnetUtil->getBarzahlenComments($this->_aNovalnetData,true);
            $aStores['cp_checkout_token'] = $this->_aNovalnetData['cp_checkout_token'] .'|'. $this->_aNovalnetData['test_mode'];
            $aStores['due_date'] = $this->_aNovalnetData['cp_due_date'];
            $aBarzahlenData =  $aStores;
        }
        $aAdditionalData = array_merge($aVendorData, $aIvoiceBankData, $aBarzahlenData );

        // logs the transaction credentials, status and amount details
        $this->oDb->execute('INSERT INTO novalnet_transaction_detail ( TID, ORDER_NO, PAYMENT_ID, PAYMENT_TYPE, AMOUNT, GATEWAY_STATUS, CUSTOMER_ID, ORDER_DATE, TOTAL_AMOUNT, MASKED_DETAILS, REFERENCE_TRANSACTION, ZERO_TRXNDETAILS, ZERO_TRXNREFERENCE, ZERO_TRANSACTION, ADDITIONAL_DATA) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [$this->_aNovalnetData['tid'], $iOrderNo, $this->_aNovalnetData['key'], $this->_aNovalnetData['payment_type'], $this->_aNovalnetData['amount'], $this->_aNovalnetData['tid_status'], $this->_aNovalnetData['customer_no'], date('Y-m-d H:i:s'), $this->_aNovalnetData['amount'], $sMaskedDetails, $blReferenceTransaction, $sZeroTrxnDetails, $sZeroTrxnReference, $blZeroAmountBooking, serialize($aAdditionalData)]);

        // logs the transaction details in callback table
        if (!in_array($this->_aNovalnetData['key'], [ '27', '59', '41', '40']) && $this->_aNovalnetData['status'] == 100 && !in_array($this->_aNovalnetData['tid_status'], array(86, 85, 90))) {
            if (!in_array($this->_aNovalnetData['tid_status'], array(86, 85, 90))) // verifying onhold status
                $this->_sNovalnetPaidDate = date('Y-m-d H:i:s'); // set the paid date of the order for novalnet paid transaction

            $this->oDb->execute('INSERT INTO novalnet_callback_history ( ORDER_NO, AMOUNT, ORG_TID, CALLBACK_DATE ) VALUES ( ?, ?, ?, ?)', [ $iOrderNo, $this->_aNovalnetData['amount'], $this->_aNovalnetData['tid'], date('Y-m-d H:i:s') ]);
        }

        // logs the affiliate orders in affiliate table
        if ($this->oNovalnetSession->getVariable('nn_aff_id'))
            $this->oDb->execute('INSERT INTO novalnet_aff_user_detail ( AFF_ID, CUSTOMER_ID, AFF_ORDER_NO) VALUES ( ?, ?, ?)', [$this->oNovalnetSession->getVariable('nn_aff_id'), $this->_aNovalnetData['customer_no'], $iOrderNo]);
    }

    /**
     * Send order mail
     *
     * @param string $sOrderId
     * @param object $oBasketValue
     *
     * @return boolean
     */
    protected function _nnSendOrderByEmail($sOrderId , $oBasketValue)
    {
        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $oOrder->load($sOrderId);

        $oUser  = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $oUser->load($oOrder->oxorder__oxuserid->value);
        $oOrder->_oUser = $oUser;

        $oPayment = oxNew(\OxidEsales\Eshop\Application\Model\UserPayment::class);
        $oPayment->load($oOrder->oxorder__oxpaymentid->value);
        $oOrder->_oPayment = $oPayment;
        $oOrder->_oBasket = $oBasketValue;

        $oxEmail = oxNew(\OxidEsales\Eshop\Core\Email::class);

        // send order email to user
        if ($oxEmail->sendOrderEMailToUser($oOrder)) {
             // mail to user was successfully sent
            $iRet = self::ORDER_STATE_OK;
        }

        // send order email to shop owner
        $oxEmail->sendOrderEMailToOwner( $oOrder );
        return $iRet;
    }

    /**
     * Updates Novalnet comments for the order in shop
     *
     */
    private function _updateNovalnetComments()
    {
        $iOrderNo = !empty($this->oNovalnetSession->getVariable('nn_orderno')) ? $this->oNovalnetSession->getVariable('nn_orderno') : $this->oxorder__oxordernr->value;
        $sNovalnetComments = '';
        if (in_array($this->_aNovalnetData['key'], ['40', '41'])) {
            $sNovalnetComments .= $this->_oNovalnetUtil->oLang->translateString('NOVALNET_PAYMENT_GUARANTEE_COMMENTS').'<br>';
            if ($this->_aNovalnetData['tid_status'] == '100') {
                $this->_sNovalnetPaidDate = date('Y-m-d H:i:s');
            }
        }

        $sNovalnetComments .= $this->_oNovalnetUtil->oLang->translateString('NOVALNET_TRANSACTION_DETAILS');

        $sNovalnetComments .= $this->_oNovalnetUtil->oLang->translateString('NOVALNET_TRANSACTION_ID') . $this->_aNovalnetData['tid'];

        if (!empty($this->_aNovalnetData['test_mode'])) {
            $sNovalnetComments .= $this->_oNovalnetUtil->oLang->translateString('NOVALNET_TEST_ORDER');
        }

        if ($this->_aNovalnetData['tid_status'] == 75) {
           $sNovalnetComments .= ($this->_aNovalnetData['key'] == '41') ? $this->_oNovalnetUtil->oLang->translateString('NOVALNET_GUARANTEE_TEXT') : $this->_oNovalnetUtil->oLang->translateString('NOVALNET_SEPA_GUARANTEE_TEXT');
        }

        if (in_array($this->_aNovalnetData['key'], ['27', '41'])) {
            $this->_aNovalnetData['invoice_ref'] = $this->_sInvoiceRef;
            $this->_aNovalnetData['order_no']    = $iOrderNo;

            if ($this->_aNovalnetData['tid_status'] != 75) {
                $sNovalnetComments .= $this->_oNovalnetUtil->getInvoiceComments($this->_aNovalnetData);
            }
        }
        if ($this->_aNovalnetData['key'] =='59') {
            $sNovalnetComments       .= $this->_oNovalnetUtil->getBarzahlenComments($this->_aNovalnetData);
        }

        $sUpdateSQL = 'UPDATE oxorder SET OXPAID = "' . $this->_sNovalnetPaidDate . '", NOVALNETCOMMENTS = "' . $sNovalnetComments . '" WHERE OXORDERNR ="' . $iOrderNo . '"';
        $this->oDb->execute($sUpdateSQL);
        $this->oxorder__oxpaid           = new \OxidEsales\Eshop\Core\Field($this->_sNovalnetPaidDate);
        $this->oxorder__novalnetcomments = new \OxidEsales\Eshop\Core\Field($sNovalnetComments);
    }

    /**
     * Sends the postback call to the Novalnet server.
     *
     */
    private function _sendNovalnetPostbackCall()
    {
        $aPostBackParams = ['vendor'    => $this->_aNovalnetData['vendor'],
                            'product'   => $this->_aNovalnetData['product'],
                            'tariff'    => $this->_aNovalnetData['tariff'],
                            'auth_code' => $this->_aNovalnetData['auth_code'],
                            'key'       => $this->_aNovalnetData['key'],
                            'status'    => 100,
                            'tid'       => $this->_aNovalnetData['tid'],
                            'order_no'  => $this->oxorder__oxordernr->value,
                            'remote_ip' => $this->_oNovalnetUtil->getIpAddress()
                           ];

        if (in_array($this->_aNovalnetData['key'], ['27', '41']))
            $aPostBackParams['invoice_ref'] = $this->_sInvoiceRef;

        $this->_oNovalnetUtil->doCurlRequest($aPostBackParams, $this->_oNovalnetUtil->sPaygateUrl);
    }
}
?>
