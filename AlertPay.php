<?php
error_reporting(E_STRICT | E_ALL);
require_once('config.php');
require_once('db.php');

define("IPN_SECURITY_CODE", "24Dq8QvZWF7DXQvG");
define("MY_MERCHANT_EMAIL", "administrador@quebuenaestoy.com");

//Setting information about the transaction
$receivedSecurityCode = $_POST['ap_securitycode'];
$receivedMerchantEmailAddress = $_POST['ap_merchant'];	
$transactionStatus = $_POST['ap_status'];
$testModeStatus = $_POST['ap_test'];	 
$purchaseType = $_POST['ap_purchasetype'];
$totalAmountReceived = $_POST['ap_totalamount'];
$feeAmount = $_POST['ap_feeamount'];
$netAmount = $_POST['ap_netamount'];
$transactionReferenceNumber = $_POST['ap_referencenumber'];
$currency = $_POST['ap_currency']; 	
$transactionDate= $_POST['ap_transactiondate'];
$transactionType= $_POST['ap_transactiontype'];

//Setting the customer's information from the IPN post variables
$customerFirstName = $_POST['ap_custfirstname'];
$customerLastName = $_POST['ap_custlastname'];
$customerAddress = $_POST['ap_custaddress'];
$customerCity = $_POST['ap_custcity'];
$customerState = $_POST['ap_custstate'];
$customerCountry = $_POST['ap_custcountry'];
$customerZipCode = $_POST['ap_custzip'];
$customerEmailAddress = $_POST['ap_custemailaddress'];

//Setting information about the purchased item from the IPN post variables
$myItemName = $_POST['ap_itemname'];
$myItemCode = $_POST['ap_itemcode'];
$myItemDescription = $_POST['ap_description'];
$myItemQuantity = $_POST['ap_quantity'];
$myItemAmount = $_POST['ap_amount'];

//Setting extra information about the purchased item from the IPN post variables
$additionalCharges = $_POST['ap_additionalcharges'];
$shippingCharges = $_POST['ap_shippingcharges'];
$taxAmount = $_POST['ap_taxamount'];
$discountAmount = $_POST['ap_discountamount'];
 
//Setting your customs fields received from the IPN post variables
$myCustomField_1 = $_POST['apc_1'];
$myCustomField_2 = $_POST['apc_2'];
$myCustomField_3 = $_POST['apc_3'];
$myCustomField_4 = $_POST['apc_4'];
$myCustomField_5 = $_POST['apc_5'];
$myCustomField_6 = $_POST['apc_6'];

if ($receivedMerchantEmailAddress != MY_MERCHANT_EMAIL) {
        error_log('$receivedMerchantEmailAddress != MY_MERCHANT_EMAIL');
}
else {	
        //Check if the security code matches
        if ($receivedSecurityCode != IPN_SECURITY_CODE) {
            error_log('$receivedSecurityCode != IPN_SECURITY_CODE');
        }
        else {
            if ($transactionStatus == "Success") {
                    if ($testModeStatus == "1") {
                        error_log('$testModeStatus == "1"');
                    }
                    else {
                        error_log('Compra');
                        $creditos = 0;
                        switch ($myItemCode)
                        {
                            case '10c':
                                $creditos = 10;
                                break;
                            case '50c':
                                $creditos = 50;
                                break;
                            case '100c':
                                $creditos = 100;
                                break;
                        }
                        
                        $c = 'INSERT INTO credito (creditos, ID_cuenta) VALUES ('.$creditos.',(SELECT ID_cuenta FROM cuentas WHERE correo="'.$customerEmailAddress.'"))';
                        $r = db::consultar($c);
                    }			
                }
                else {
                    error_log('Transaccion cancelada');
                }
        }
}
?>