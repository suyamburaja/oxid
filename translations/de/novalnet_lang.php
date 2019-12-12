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
 * Script: novalnet_lang.php
 */

$sLangName = 'Deutsch';

$aLang = [
    'charset'                                       	=> 'UTF-8',    
    'NOVALNET_TEST_MODE_MESSAGE'                    	=> 'Die Zahlung wird im Testmodus durchgeführt, daher wird der Betrag für diese Transaktion nicht eingezogen.',
    'NOVALNET_REDIRECT_DESCRIPTION_MESSAGE'         	=> 'Bitte schließen Sie den Browser nach der erfolgreichen Zahlung nicht, bis Sie zum Shop zurückgeleitet wurden.',
    'NOVALNET_CC_REDIRECT_DESCRIPTION_MESSAGE'      	=> 'Nach der erfolgreichen Überprüfung werden Sie auf die abgesicherte Novalnet-Bestellseite umgeleitet, um die Zahlung fortzusetzen<br>Bitte schließen Sie den Browser nach der erfolgreichen Zahlung nicht, bis Sie zum Shop zurückgeleitet wurden.',
    'NOVALNET_PAYPAL_REFERENCE_DESCRIPTION_MESSAGE' 	=> 'Sobald die Bestellung abgeschickt wurde, wird die Zahlung bei Novalnet als Referenztransaktion verarbeitet.',
    'NOVALNET_PAYMENT_TYPE'                       		=> 'Bezahlung mit',
    'NOVALNET_LINK_URL'                           		=> 'https://www.novalnet.de',
    'NOVALNET_CREDITCARD_TYPE'                    		=> 'Kartentyp',
    'NOVALNET_CREDITCARD_HOLDER_NAME'             		=> 'Name des Karteninhabers',
    'NOVALNET_CREDITCARD_NUMBER'                  		=> 'Kreditkartennummer',
    'NOVALNET_CREDITCARD_EXPIRY_DATE'             		=> 'Ablaufdatum',
    'NOVALNET_CREDITCARD_CVC'                     		=> 'CVC/CVV/CID',
    'NOVALNET_CREDITCARD_HOLDER_NAME_PLACEHOLDER' 		=> 'Name auf der Kreditkarte',
    'NOVALNET_CREDITCARD_NUMBER_PLACEHOLDER'      		=> 'XXXX XXXX XXXX XXXX',
    'NOVALNET_CREDITCARD_EXPIRY_DATE_PLACEHOLDER' 		=> 'MM / YYYY',
    'NOVALNET_CREDITCARD_CVC_PLACEHOLDER'         		=> 'XXX',
    'NOVALNET_CREDITCARD_CVC_HINT'                		=> 'Was ist das?',
    'NOVALNET_CREDITCARD_ERROR_TEXT'              		=> 'Ihre Kreditkartendaten sind ungültig',
    'NOVALNET_BIRTH_DATE'                         		=> 'Ihr Geburtsdatum',
    'NOVALNET_SEPA_HOLDER_NAME'                 		=> 'Kontoinhaber',
    'NOVALNET_SEPA_IBAN'                        		=> 'IBAN',    
    'NOVALNET_INVOICE_COMMENTS_TITLE'           		=> '<br><br>Überweisen Sie bitte den Betrag an die unten aufgeführte Bankverbindung unseres Zahlungsdienstleisters Novalnet<br>',
    'NOVALNET_DUE_DATE'                         		=> '<br>Fälligkeitsdatum: ',
    'NOVALNET_ACCOUNT'                          		=> '<br>Kontoinhaber: ',
    'NOVALNET_AMOUNT'                           		=> '<br>Betrag: ',    
    'NOVALNET_INVOICE_MULTI_REF_DESCRIPTION'    		=> '<br>Bitte verwenden Sie einen der unten angegebenen Verwendungszwecke für die Überweisung, da nur so Ihr Geldeingang zugeordnet werden kann:',    
    'NOVALNET_ORDER_NO'                         		=> 'Bestellnummer ',

    'NOVALNET_TRANSACTION_DETAILS'              		=> 'Novalnet-Transaktionsdetails<br>',
    'NOVALNET_TRANSACTION_ID'                   		=> 'Novalnet Transaktions-ID: ',
    'NOVALNET_TEST_ORDER'                       		=> '<br>Testbestellung',
    'NOVALNET_REDIRECT_MESSAGE'                 		=> 'Nach der erfolgreichen Überprüfung werden Sie auf die abgesicherte Novalnet-Bestellseite umgeleitet, um die Zahlung fortzusetzen',
    'NOVALNET_REDIRECT_SUBMIT'                  		=> 'Weiterleitung...',
    'NOVALNET_NEW_CARD_DETAILS'                 		=> 'Neue Kartendaten eingeben',
    'NOVALNET_GIVEN_CARD_DETAILS'               		=> 'Eingegebene Kartendaten',
    'NOVALNET_NEW_ACCOUNT_DETAILS'              		=> 'Neue Kontodaten eingeben',
    'NOVALNET_GIVEN_ACCOUNT_DETAILS'            		=> 'Eingegebene Kontodaten',
    'NOVALNET_PAYPAL_NEW_ACCOUNT_DETAILS'       		=> 'Mit neuen PayPal-Kontodetails fortfahren',
    'NOVALNET_PAYPAL_GIVEN_ACCOUNT_DETAILS'     		=> 'Angegebene PayPal-Kontodetails',
    'NOVALNET_CHECK_HASH_FAILED_ERROR'          		=> 'Während der Umleitung wurden einige Daten geändert. Die Überprüfung des Hashes schlug fehl',
    'NOVALNET_NOSCRIPT_MESSAGE'                 		=> 'Aktivieren Sie bitte JavaScript in Ihrem Browser, um die Zahlung fortzusetzen',
    'NOVALNET_FRAUD_MODULE_PHONE'               		=> 'Telefonnummer',
    'NOVALNET_FRAUD_MODULE_MOBILE'              		=> 'Mobiltelefonnummer',
    'NOVALNET_FRAUD_MODULE_PIN'                 		=> 'PIN zu Ihrer Transaktion',
    'NOVALNET_FRAUD_MODULE_FORGOT_PIN'          		=> 'PIN vergessen?',
    'NOVALNET_FRAUD_MODULE_PHONE_INVALID'       		=> 'Geben Sie bitte Ihre Telefonnummer ein',
    'NOVALNET_FRAUD_MODULE_MOBILE_INVALID'      		=> 'Geben Sie bitte Ihre Mobiltelefonnummer ein',
    'NOVALNET_FRAUD_MODULE_AMOUNT_CHANGE_ERROR' 		=> 'Der Bestellbetrag hat sich geändert, setzen Sie bitte die neue Bestellung fort',
    'NOVALNET_FRAUD_MODULE_PIN_EMPTY'           		=> 'PIN eingeben',
    'NOVALNET_FRAUD_MODULE_PIN_INVALID'         		=> 'Die von Ihnen eingegebene PIN ist falsch',
    'NOVALNET_FRAUD_MODULE_PHONE_MESSAGE'       		=> 'In Kürze erhalten Sie einen Telefonanruf mit der PIN zu Ihrer Transaktion, um die Zahlung abzuschließen',
    'NOVALNET_FRAUD_MODULE_MOBILE_MESSAGE'      		=> 'In Kürze erhalten Sie eine SMS mit der PIN zu Ihrer Transaktion, um die Zahlung abzuschließen',
    'NOVALNET_INVALID_BIRTHDATE_ERROR'          		=> 'Sie müssen mindestens 18 Jahre alt sein',
    'NOVALNET_INVALID_DATE_ERROR'               		=> 'Ungültiges Datumsformat',
    'NOVALNET_EMPTY_BIRTHDATE_ERROR'            		=> 'Geben Sie bitte Ihr Geburtsdatum ein',
    'NOVALNET_PAYPAL_REFERENCE_TID'             		=> 'PayPal Transaktions-ID',
    'NOVALNET_REFERENCE_TID'                    		=> 'Novalnet Transaktions-ID',
    'NOVALNET_TEST_MODE_NOTIFICATION_SUBJECT'   		=> 'Benachrichtigung zu Novalnet-Testbestellungen - OXID eShop',
    'NOVALNET_TEST_MODE_NOTIFICATION_MESSAGE'   		=> 'Sehr geehrte Kundin, <br><br>wir möchten Sie darüber informieren, dass eine Testbestellung %s kürzlich in Ihrem Shop durchgeführt wurde. Stellen Sie bitte sicher, dass für Ihr Projekt im Novalnet-Administrationsportal der Live-Modus gesetzt wurde und Zahlungen über Novalnet in Ihrem Shopsystem aktiviert sind. Ignorieren Sie bitte diese E-Mail, falls die Bestellung von Ihnen zu Testzwecken durchgeführt wurde. <br><br>Mit freundlichen Grüßen <br>Novalnet AG',
    'NOVALNET_PLEASE_SELECT'                    		=> '--Auswählen--',
    'NOVALNET_UPDATE'                           		=> 'Ändern',
    'NOVALNET_DEFAULT_ERROR_MESSAGE'            		=> 'Die Zahlung war nicht erfolgreich. Ein Fehler trat auf',
    'NOVALNET_INVALID_NAME_EMAIL'               		=> 'Ungültige Werte für die Felder Kundenname-/email',
    'NOVALNET_BARZAHLEN_DUE_DATE'               		=> '<br>Verfallsdatum des Zahlscheins: ',
    'NOVALNET_BARZAHLEN_PAYMENT_STORE'          		=> '<br><br>Barzahlen-Partnerfiliale in Ihrer Nähe<br><br>',
    'NOVALNET_BARZAHLEN_BUTTON'                 		=> 'Jetzt mit Barzahlen bezahlen',
    'NOVALNET_PAYMENT_GUARANTEE_COMMENTS'        		=> 'Diese Transaktion wird mit Zahlungsgarantie verarbeitet<br>',  
       
    'NOVALNET_CC_SAVE_CARD_DATA'                   		=> 'Meine Kartendaten für zukünftige Bestellungen speichern',    
    'NOVALNET_SEPA_SAVE_CARD_DATA'                   	=> 'Meine Kontodaten für zukünftige Bestellungen speichern',
    'NOVALNET_PAYPAL_SAVE_CARD_DATA'                   	=> 'Meine PayPal-Daten für zukünftige Bestellungen speichern',
    'NOVALNET_SEPA_INVALID_DETAILS'             		=> 'Ihre Kontodaten sind ungültig',
    'NOVALNET_GUARANTEE_FORCE_DISABLED_MESSAGE' 		=> 'Die Zahlung kann nicht verarbeitet werden, weil die grundlegenden Anforderungen nicht erfüllt wurden',    
    'NOVALNET_GUARANTEE_AMOUNT_ERROR_MESSAGE'           => 'Der Mindestbestellwert beträgt ',
    'NOVALNET_GUARANTEE_CURRENCY_ERROR_MESSAGE'         => 'Als Währung ist nur EUR erlaubt',
    'NOVALNET_GUARANTEE_ADDRESS_MISMATCH_ERROR_MESSAGE' => 'Die Lieferadresse muss mit der Rechnungsadresse übereinstimmen',
    'NOVALNET_GUARANTEE_COUNTRY_ERROR_MESSAGE'          => 'Als Land ist nur Deutschland, Österreich oder Schweiz erlaubt',        
    'NOVALNET_ORDER_CONFIRMATION'                		=> 'Bestellbestätigung - Ihre Bestellung ',
    'NOVALNET_ORDER_CONFIRMATION1'               		=> ' bei ',
    'NOVALNET_ORDER_CONFIRMATION2'               		=> ' wurde bestätigt',
    'NOVALNET_ORDER_CONFIRMATION3'               		=> 'Wir freuen uns Ihnen mitteilen zu können, dass Ihre Bestellung bestätigt wurde',
    'NOVALNET_PAYMENT_INFORMATION'               		=> 'Zahlung Informationen:',
    
    'NOVALNET_IBAN'				 						=> '<br>IBAN: ',
    'NOVALNET_BIC'				 						=> '<br>BIC: ',
    'NOVALNET_BANK'				 						=> '<br>Bank: ',
    'NOVALNET_PAYMENT_REFERENCE_1' 						=> '<br>Zahlungsreferenz 1: ',
    'NOVALNET_PAYMENT_REFERENCE_2'						=> '<br>Zahlungsreferenz 2: ',    
    'NOVALNET_ZEROAMOUNT_BOOKING_MESSAGE'  				=>'Diese Bestellung wird als Nullbetrag gebucht, in dem Ihre Zahlungsdaten für weitere Online-Einkäufe gespeichert werden',
    
    'NOVALNET_CRITICAL_ERROR_MESSAGE1' 	   				=> 'Critical error on shop system ',
    'NOVALNET_CRITICAL_ERROR_MESSAGE2'     				=> ' : order not found for TID: ',
    'NOVALNET_CRITICAL_MESSAGE_SUBJECT'   				=> 'Dear Technic team,<br/><br/>Please evaluate this transaction and contact our payment module team at Novalnet.<br/><br/>',
    'NOVALNET_MERCHANT_ID'                 				=> 'Merchant ID: ',
    'NOVALNET_PROJECT_ID'                  				=> 'Project ID: ',
    'NOVALNET_TID'                         				=> 'TID: ',
    'NOVALNET_TID_STATUS'                  				=> 'TID status: ' ,
    'NOVALNET_ORDER_NO'                    				=> 'Order no: ',
    'NOVALNET_PAYMENT_TYPE'                				=> 'Payment type: ',
    'NOVALNET_EMAIL'                       				=> 'E-mail: ',
    'NOVALNET_REGARDS'                     				=> '<br/><br/>Regards,<br/>Novalnet Team',
    
    'NOVALNET_SEPA_DECLARATION'            				=> '<strong>Ich erteile hiermit das SEPA-Lastschriftmandat</strong> (elektronische Übermittlung) <strong>und bestätige, dass die Bankverbindung korrekt ist.</strong><br/><br/>',
    'NOVALNET_SEPA_AUTHORIZE'              				=> 'Ich ermächtige den Zahlungsempfänger, Zahlungen von meinem Konto mittels Lastschrift einzuziehen. Zugleich weise ich mein Kreditinstitut an, die von dem Zahlungsempfänger auf mein Konto gezogenen Lastschriften einzulösen.<br/><br/>',  
    'NOVALNET_SEPA_CREDITOR_IDENTIFIER'    				=> '<strong>Gläubiger-Identifikationsnummer: DE53ZZZ00000004253</strong><br/><br/>',
    'NOVALNET_SEPA_CLAIM_NOTES'            				=> '<strong>Hinweis:</strong>Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen. Es gelten dabei die mit meinem Kreditinstitut vereinbarten Bedingungen.<br/>',
    'NOVALNET_GUARANTEE_TEXT'                   => '<br><br>Ihre Bestellung ist unter Bearbeitung. Sobald diese bestätigt wurde, erhalten Sie alle notwendigen Informationen zum Ausgleich der Rechnung. Wir bitten Sie zu beachten, dass dieser Vorgang bis zu 24 Stunden andauern kann<br>',
    'NOVALNET_SEPA_GUARANTEE_TEXT'                   => '<br><br>Ihre Bestellung wird derzeit überprüft. Wir werden Sie in Kürze über den Bestellstatus informieren. Bitte beachten Sie, dass dies bis zu 24 Stunden dauern kann.<br>',
    'NOVALNETCREDITCARD_URL' 					=> 'https://www.novalnet.de/zahlungsart-kreditkarte',
    'NOVALNETSEPA_URL' 							=> 'https://www.novalnet.de/sepa-lastschrift',
    'NOVALNETINVOICE_URL' 						=> 'https://www.novalnet.de/kauf-auf-rechnung-online-payment',
    'NOVALNETPREPAYMENT_URL' 					=> 'https://www.novalnet.de/vorkasse-internet-payment',
    'NOVALNETONLINETRANSFER_URL' 				=> 'https://www.novalnet.de/online-ueberweisung-sofortueberweisung',
    'NOVALNETIDEAL_URL' 						=> 'https://www.novalnet.de/ideal-online-ueberweisung',
    'NOVALNETEPS_URL' 							=> 'https://www.novalnet.de/eps-online-ueberweisung',
    'NOVALNETGIROPAY_URL' 						=> 'https://www.novalnet.de/giropay',
    'NOVALNETPRZELEWY24_URL' 					=> 'https://www.novalnet.de/przelewy24',
    'NOVALNETPAYPAL_URL' 						=> 'https://www.novalnet.de/mit-paypal-weltweit-sicher-verkaufen',
    'NOVALNETBARZAHLEN_URL' 					=> 'https://www.novalnet.de/barzahlen',
    'NOVALNET_PAYMENT_FAILED'					=> 'Zahlung fehlgeschlagen',
];
?>

