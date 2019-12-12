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
 * Script: OrderOverview.php
 */

namespace oe\novalnet\Controller\Admin;

use oe\novalnet\Classes\NovalnetUtil;

/**
 * Class OrderOverview.
 */
class OrderOverview extends OrderOverview_parent
{
    /**
     * Novalnet payments
     *
     * @var array
     */
    public $aNovalnetPayments = ['novalnetcreditcard', 'novalnetsepa', 'novalnetinvoice', 'novalnetprepayment', 'novalnetpaypal', 'novalnetonlinetransfer', 'novalnetideal', 'novalneteps' , 'novalnetgiropay', 'novalnetprzelewy24','novalnetbarzahlen'];

    /**
     * Returns name of template to render
     *
     * @return string
     */
    public function render()
    {
        $sTemplate = parent::render();
        $sOxId = $this->getEditObjectId();
        if (isset($sOxId) && $sOxId != "-1") {
            $oOrder = $this->_aViewData['edit'];
            if (in_array($oOrder->oxorder__oxpaymenttype->value, $this->aNovalnetPayments)) {
                $this->_aViewData['aNovalnetPayments'] = $this->aNovalnetPayments;
                $this->_aViewData['aNovalnetActions']  = $this->_displayNovalnetActions($oOrder->oxorder__oxordernr->value);
            }
        }
        return $sTemplate;
    }

    /**
     * Gets Novalnet transaction credentials
     *
     * @param integer $iOrderNo
     *
     * @return array
     */
    private function _getTransactionCredentials($iOrderNo)
    {
        $this->oDb = \OxidEsales\Eshop\Core\DatabaseProvider::getDb(\OxidEsales\Eshop\Core\DatabaseProvider::FETCH_MODE_ASSOC);
        $sSQL = 'SELECT PAYMENT_TYPE, TID, ZERO_TRXNDETAILS, ZERO_TRXNREFERENCE, MASKED_DETAILS, AMOUNT, TOTAL_AMOUNT, REFUND_AMOUNT, GATEWAY_STATUS, PAYMENT_ID, ZERO_TRANSACTION, ADDITIONAL_DATA FROM novalnet_transaction_detail WHERE ORDER_NO = "' . $iOrderNo . '"';

        return $this->oDb->getRow($sSQL);
    }

    /**
     * Handles the Novalnet extension features visibility features
     *
     * @param integer $iOrderNo
     */
    private function _displayNovalnetActions($iOrderNo)
    {
        $oOrder        = $this->_aViewData['edit'];
        $aTransDetails = $this->_getTransactionCredentials($iOrderNo);

        $aAdditionalData = unserialize($aTransDetails['ADDITIONAL_DATA']);
        $sOrderDate    = strtotime(date('Y-m-d', strtotime($oOrder->oxorder__oxorderdate->value)));
        $this->_aViewData['dNovalnetAmount'] = $aTransDetails['TOTAL_AMOUNT'];

        $this->_aViewData['dOrderAmount']    = $oOrder->oxorder__oxtotalordersum->value * 100;

        if (in_array($aTransDetails['PAYMENT_ID'], [ '27', '41', '59' ])) {
            $sSql = 'SELECT SUM(AMOUNT) AS PAID_AMOUNT FROM novalnet_callback_history where ORDER_NO = "' . $iOrderNo . '"';
            $aResult = $this->oDb->getRow($sSql);
        }

        $dAmount = isset($aResult['PAID_AMOUNT']) ? $aResult['PAID_AMOUNT'] : '';
        if (!empty($aTransDetails['REFUND_AMOUNT']))
           $dAmount =  $aTransDetails['REFUND_AMOUNT'] + $aResult['PAID_AMOUNT'];

       if ($oOrder->oxorder__oxtotalordersum->value !== '0') {
         $this->_aViewData['blZeroBook']       = !empty($aTransDetails['ZERO_TRANSACTION']) && $aTransDetails['AMOUNT'] === '0' && in_array($aTransDetails['PAYMENT_ID'], ['6', '34', '37' ]) && $aTransDetails['GATEWAY_STATUS'] != '103';
         $this->_aViewData['blOnHold']         = $aTransDetails['AMOUNT'] !== '0' && in_array($aTransDetails['GATEWAY_STATUS'], [ '85', '91', '98', '99' ]);
         $this->_aViewData['blAmountUpdate']   = $aTransDetails['TOTAL_AMOUNT'] !== '0' && $aTransDetails['PAYMENT_ID'] == '37' && $aTransDetails['GATEWAY_STATUS']      == '99';
         $this->_aViewData['blDuedateUpdate']  = $aTransDetails['TOTAL_AMOUNT'] !== '0' && (in_array($aTransDetails['PAYMENT_ID'] , ['59','27'])) && $aTransDetails['GATEWAY_STATUS'] == '100' && ($aTransDetails['TOTAL_AMOUNT'] > $dAmount);
        
         $this->_aViewData['sKey']             = $aTransDetails['PAYMENT_ID'];
         $this->_aViewData['sNovalnetDueDate'] = !empty($this->_aViewData['blDuedateUpdate']) ? $aAdditionalData['due_date'] : '';
         $this->_aViewData['blAmountRefund']   = $aTransDetails['TOTAL_AMOUNT'] !== '0' && $aTransDetails['GATEWAY_STATUS'] == '100';
         $this->_aViewData['blRefundRef']      = !empty($this->_aViewData['blAmountRefund']) && $sOrderDate < strtotime(date('Y-m-d'));
       }
     
    }

    /**
     * Performs the Novalnet extension actions
     *
     */
    public function performNovalnetAction()
    {
        $aData = \OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter('novalnet');
        if ($this->_validateNovalnetRequest($aData)) {
            $oNovalnetUtil = oxNew(NovalnetUtil::class);
            $aTransDetails = $this->_getTransactionCredentials($aData['iOrderNo']);
            $aVendorData = unserialize($aTransDetails['ADDITIONAL_DATA']);
            $aRequest['vendor']    = $aVendorData['vendor'];
            $aRequest['product']   = $aVendorData['product'];
            $aRequest['tariff']    = $aVendorData['tariff'];
            $aRequest['auth_code'] = $aVendorData['auth_code'];
            $aRequest['key']       = $aTransDetails['PAYMENT_ID'];
            $aRequest['tid']       = $aTransDetails['TID'];
            if ($aData['sRequestType'] == 'status_change') {
                $aRequest['status']      = $aData['sTransStatus'];
                $aRequest['edit_status'] = 1;
            } elseif (in_array($aData['sRequestType'], ['amount_update', 'amount_duedate_update'])) {
                $aRequest['status']            = 100;
                $aRequest['amount']            = $aData['sUpdateAmount'];
                $aRequest['edit_status']       = 1;
                $aRequest['update_inv_amount'] = 1;
                if ($aData['sRequestType'] == 'amount_duedate_update')
                    $aRequest['due_date'] = date('Y-m-d', strtotime($aData['sUpdateDate']));
            } elseif ($aData['sRequestType'] == 'amount_refund') {
                if (!empty($aData['sRefundRef']))
                    $aRequest['refund_ref'] = $aData['sRefundRef'];

                $aRequest['refund_param']   = $aData['sRefundAmount'];
                $aRequest['refund_request'] = 1;
            } elseif ($aData['sRequestType'] == 'amount_book') {
                $aRequest                = unserialize($aTransDetails['ZERO_TRXNDETAILS']);
                $aRequest['amount']      = $aData['sBookAmount'];
                $aRequest['order_no']    = $aData['iOrderNo'];
                $aRequest['payment_ref'] = $aTransDetails['ZERO_TRXNREFERENCE'];
                if ($aRequest['key'] == '37')
                    $aRequest['sepa_due_date'] = date('d.m.Y', strtotime('+' . (empty($aRequest['sepa_due_date']) || $aRequest['sepa_due_date'] <= 6 ? 7 : $aRequest['sepa_due_date']) . ' days'));
            }
            $aRequest['remote_ip']  = $oNovalnetUtil->getIpAddress();
            $aResponse              = $oNovalnetUtil->doCurlRequest($aRequest, $oNovalnetUtil->sPaygateUrl);

            $aResponse['child_tid'] = !empty($aResponse['tid']) ? $aResponse['tid'] : '';
            if ($aResponse['status'] == '100') {
                $aData = array_merge($aResponse, $aRequest, $aData);
                $aData['test_mode']      = $aVendorData['test_mode'];
                $aData['payment_type']   = $aTransDetails['PAYMENT_TYPE'];
                $aData['masked_details'] = '';
                if ($aData['key'] == '34')
                    $aData['masked_details'] = $aTransDetails['MASKED_DETAILS'];
                $this->_updateNovalnetComments($aData);
            } else {
                $sError = $oNovalnetUtil->setNovalnetPaygateError($aResponse);
                if ($aData['sRequestType'] == 'status_change')
                    $this->_aViewData['sOnHoldFailure'] = $sError;
                elseif ($aData['sRequestType'] == 'amount_update')
                    $this->_aViewData['sAmountUpdateFailure'] = $sError;
                elseif ($aData['sRequestType'] == 'amount_refund')
                    $this->_aViewData['sAmountRefundFailure'] = $sError;
                elseif ($aData['sRequestType'] == 'amount_duedate_update')
                    $this->_aViewData['sDuedateUpdateFailure'] = $sError;
                elseif ($aData['sRequestType'] == 'amount_book')
                    $this->_aViewData['sZeroBookFailure'] = $sError;
            }
        }
    }

    /**
     * Validates the extension requests
     *
     * @param array $aData
     *
     * @return boolean
     */
    private function _validateNovalnetRequest($aData)
    {
        $oNovalnetUtil = oxNew(NovalnetUtil::class);
        $blError = true;
        if ($aData['sRequestType'] == 'amount_duedate_update') {
            $sRequestDate    = strtotime($aData['sUpdateDate']);
            $sRequestDueDate = date('Y-m-d', $sRequestDate);
            $sCurrentDate    = strtotime(date('Y-m-d'));
            if ($sRequestDueDate != $aData['sUpdateDate']) {
                $blError = false;
                $this->_aViewData['sDuedateUpdateFailure'] = $oNovalnetUtil->oLang->translateString('NOVALNET_INVALID_DUEDATE');
            } elseif ($sRequestDate < $sCurrentDate) {
                $blError = false;
                $this->_aViewData['sDuedateUpdateFailure'] = $oNovalnetUtil->oLang->translateString('NOVALNET_INVALID_PAST_DUEDATE');
            }
        }
        return $blError;
    }

    /**
     * Updates Novalnet comments in orders
     *
     * @param array $aData
     */
    private function _updateNovalnetComments($aData)
    {
        $aData['sTransStatus'] = isset($aData['sTransStatus']) ? $aData['sTransStatus'] : $aData['tid_status'];
        $oNovalnetUtil = oxNew(NovalnetUtil::class);
        $aTransDetails = $this->_getTransactionCredentials($aData['iOrderNo']);
        $sOxId  = $this->getEditObjectId();
        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $oOrder->load($sOxId);
        if ($aData['sRequestType'] == 'status_change') {
            $sMessage = $aData['sTransStatus'] == 100 ? sprintf($oNovalnetUtil->oLang->translateString('NOVALNET_STATUS_UPDATE_CONFIRMED_MESSAGE'), date('Y-m-d'), date('H:i:s')) : sprintf($oNovalnetUtil->oLang->translateString('NOVALNET_STATUS_UPDATE_CANCELED_MESSAGE'), date('Y-m-d'), date('H:i:s'));
            $sSQL = 'UPDATE novalnet_transaction_detail SET GATEWAY_STATUS = "' . $aData['sTransStatus'] . '" WHERE ORDER_NO = "' . $aData['iOrderNo'] . '"';
            $this->oDb->execute($sSQL);
            if( $aData['sTransStatus'] == 100 && $aData['key'] == '34') {
                $sUpdateSQL = 'UPDATE oxorder SET OXPAID = "' . date('Y-m-d H:i:s') . '" WHERE OXORDERNR ="' . $aData['iOrderNo'] . '"';
                $this->oDb->execute($sUpdateSQL);
            }
            if ($aData['masked_details'] != '' && !empty($aData['paypal_transaction_id'])) {
                $sMaskedDetails = serialize([ 'paypal_transaction_id' => $aData['paypal_transaction_id']]);
                $sSQL = "UPDATE novalnet_transaction_detail SET GATEWAY_STATUS = '" . $aData['sTransStatus'] . "', MASKED_DETAILS = '" . $sMaskedDetails . "' WHERE ORDER_NO = '" . $aData['iOrderNo'] . "'";
                $this->oDb->execute($sSQL);
            }
            if (in_array($aData['key'], array('27', '41')) && $aData['status'] != '103') {
                    $aInvoiceDetails = unserialize($aTransDetails['ADDITIONAL_DATA']);
                    $aInvoiceDetails['due_date'] = $aData['due_date'];
                    $aInvoiceDetails['currency'] = $oOrder->oxorder__oxcurrency->value;

                    $aInvoiceComments = '';
                    if ($aData['key'] == '41') {
                        $aInvoiceComments .= '<br>'.$oNovalnetUtil->oLang->translateString('NOVALNET_PAYMENT_GUARANTEE_COMMENTS');
                        $sUpdateSQL = 'UPDATE oxorder SET OXPAID = "' . date('Y-m-d H:i:s') . '" WHERE OXORDERNR ="' . $aData['iOrderNo'] . '"';
						$this->oDb->execute($sUpdateSQL);
                    }
                    $aInvoiceComments .= '<br>'.$oNovalnetUtil->oLang->translateString('NOVALNET_TRANSACTION_ID') . $aData['tid'];

                    if (!empty($aData['test_mode'])) {
                        $aInvoiceComments .= $oNovalnetUtil->oLang->translateString('NOVALNET_TEST_ORDER');
                    }

                    $aInvoiceComments .= $oNovalnetUtil->getInvoiceComments($aInvoiceDetails);
                    $sMessage .= '<br>'.$aInvoiceComments;
                    $sLang = $this->oDb->getRow('SELECT OXLANG FROM oxorder where OXORDERNR = "' . $aData['iOrderNo']. '"');
                    $oNovalnetUtil->sendPaymentNotificationMail($sLang['OXLANG'], $aInvoiceComments, $aData['iOrderNo']);
                }
        } elseif ($aData['sRequestType'] == 'amount_update') {
            $sFormattedAmount = $oNovalnetUtil->oLang->formatCurrency($aData['sUpdateAmount']/100) . ' ' . $oOrder->oxorder__oxcurrency->rawValue;
            $sMessage         = sprintf($oNovalnetUtil->oLang->translateString('NOVALNET_AMOUNT_UPDATED_MESSAGE'), $sFormattedAmount, date('Y-m-d'), date('H:i:s'));

            $sSQL = 'UPDATE novalnet_transaction_detail SET TOTAL_AMOUNT = "' . $aData['sUpdateAmount'] . '" WHERE ORDER_NO = "' . $aData['iOrderNo'] . '"';
            $this->oDb->execute($sSQL);
        } elseif ($aData['sRequestType'] == 'amount_book') {
            $sFormattedAmount = $oNovalnetUtil->oLang->formatCurrency($aData['sBookAmount']/100) . ' ' . $oOrder->oxorder__oxcurrency->rawValue;

            $sMessage = '<br><br>'.$oNovalnetUtil->oLang->translateString('NOVALNET_TRANSACTION_ID') . $aData['tid'];
             if (!empty($aData['test_mode']))
                    $sMessage .= $oNovalnetUtil->oLang->translateString('NOVALNET_TEST_ORDER');

            $sMessage         .= sprintf($oNovalnetUtil->oLang->translateString('NOVALNET_AMOUNT_BOOKED_MESSAGE'), $sFormattedAmount, $aData['tid']);

            $sSQL = 'UPDATE novalnet_transaction_detail SET GATEWAY_STATUS = "' . $aData['sTransStatus'] . '", AMOUNT = "' . $aData['amount'] . '", TOTAL_AMOUNT = "' . $aData['amount'] . '", TID = "' . $aData['tid'] . '" WHERE ORDER_NO = "' . $aData['iOrderNo'] . '"';
            $this->oDb->execute($sSQL);

            $sSQL = 'UPDATE novalnet_callback_history SET AMOUNT = "' . $aData['amount'] . '", ORG_TID = "' . $aData['tid'] . '" WHERE ORDER_NO = "' . $aData['iOrderNo'] . '"';
            $this->oDb->execute($sSQL);
        } elseif ($aData['sRequestType'] == 'amount_duedate_update') {
            $sFormattedAmount = $oNovalnetUtil->oLang->formatCurrency($aData['sUpdateAmount']/100) . ' ' . $oOrder->oxorder__oxcurrency->rawValue;
            $sAmountUpdateMessage = ($aData['key'] == '59') ? $oNovalnetUtil->oLang->translateString('NOVALNET_AMOUNT_SLIP_EXPIRY_DATE_UPDATED_MESSAGE') : $oNovalnetUtil->oLang->translateString('NOVALNET_AMOUNT_DATE_UPDATED_MESSAGE');
            $sMessage = sprintf($sAmountUpdateMessage, $sFormattedAmount, date('d.m.Y', strtotime($aData['due_date'])));            
            $sMessage .= '<br><br>' . $oNovalnetUtil->oLang->translateString('NOVALNET_TRANSACTION_DETAILS');
            $sMessage .= $oNovalnetUtil->oLang->translateString('NOVALNET_TRANSACTION_ID') . $aData['tid'];
            if (!empty($aData['test_mode']))
                    $sMessage .= $oNovalnetUtil->oLang->translateString('NOVALNET_TEST_ORDER');

            if ($aData['key'] == '27') {
                $aInvoiceDetails = unserialize($aTransDetails['ADDITIONAL_DATA']);
                $aInvoiceDetails['amount']   = $aData['amount'];
                $aInvoiceDetails['due_date'] = $aData['due_date'];
                $aInvoiceDetails['currency'] = $oOrder->oxorder__oxcurrency->value;
                $sMessage .= $oNovalnetUtil->getInvoiceComments($aInvoiceDetails);
            } else if ($aData['key'] == '59') {
                $aBarzahlenDetails = unserialize($aTransDetails['ADDITIONAL_DATA']);
                $aBarzahlenDetails['due_date'] = $aData['due_date'];
                $aBarzahlenDetails['nearest_store']['due_date'] = $aData['due_date'];             
                $sMessage .= $oNovalnetUtil->getBarzahlenComments($aBarzahlenDetails['nearest_store']);                
            }
            $aSerializeData = !empty($aInvoiceDetails) ? serialize($aInvoiceDetails) : serialize($aBarzahlenDetails);
            $sSQL = "UPDATE novalnet_transaction_detail SET TOTAL_AMOUNT = '" . $aData['amount'] . "', ADDITIONAL_DATA = '".$aSerializeData."' WHERE ORDER_NO = '" . $aData['iOrderNo'] . "'";
            $this->oDb->execute($sSQL);
            $sSql = 'SELECT SUM(amount) AS paid_amount FROM novalnet_callback_history where ORDER_NO = "' . $aData['iOrderNo'] . '"';
            $aResult     = $this->oDb->getRow($sSql);
            $dPaidAmount = isset($aResult['paid_amount']);
            if (!empty($dPaidAmount) && ($dPaidAmount <= $aData['amount'])) {
                $sUpdateSQL = 'UPDATE oxorder SET OXPAID = "' . date('Y-m-d H:i:s') . '" WHERE OXORDERNR ="' . $aData['iOrderNo'] . '"';
                $this->oDb->execute($sUpdateSQL);
            }
        }

       if ($aData['sRequestType'] == 'amount_refund') {
            $sFormattedAmount = $oNovalnetUtil->oLang->formatCurrency($aData['sRefundAmount']/100) . ' ' . $oOrder->oxorder__oxcurrency->rawValue;
            $sMessage         = sprintf($oNovalnetUtil->oLang->translateString('NOVALNET_AMOUNT_REFUNDED_PARENT_TID_MESSAGE'), $aData['tid'], $sFormattedAmount);
            if (!empty($aData['child_tid'])) {
                $sMessage = $sMessage . sprintf($oNovalnetUtil->oLang->translateString('NOVALNET_AMOUNT_REFUNDED_CHILD_TID_MESSAGE'), $aData['child_tid']);
            }

            $sSQL = 'UPDATE novalnet_transaction_detail SET GATEWAY_STATUS = "' . $aData['sTransStatus'] . '", REFUND_AMOUNT = REFUND_AMOUNT+' . $aData['sRefundAmount'] . ' WHERE TID = "' . $aData['tid'] . '"';
            $this->oDb->execute($sSQL);
            }

            $sSQL = 'UPDATE oxorder SET NOVALNETCOMMENTS = CONCAT(IF(NOVALNETCOMMENTS IS NULL, "", NOVALNETCOMMENTS), "' . $sMessage . '") WHERE OXORDERNR = "' . $aData['iOrderNo'] . '"';
            $this->oDb->execute($sSQL);
    }
}
?>
