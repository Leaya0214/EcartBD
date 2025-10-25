<?php
namespace App\Library\SslCommerz;

class SslCommerzNotification extends AbstractSslCommerz
{
    protected $data = [];
    protected $config = [];
    /**
     * Holds the response data returned from SSLCOMMERZ order validation
     * Declared to avoid undefined property errors when assigning in validate().
     *
     * @var mixed
     */
    protected $sslc_data = null;

    private $successUrl;
    private $cancelUrl;
    private $failedUrl;
    private $error;

    /**
     * SslCommerzNotification constructor.
     */
    public function __construct()
    {
        $this->config = config('sslcommerz');

        $this->setStoreId($this->config['apiCredentials']['store_id']);
        $this->setStorePassword($this->config['apiCredentials']['store_password']);
    }

    public function orderValidate($post_data, $trx_id = '', $amount = 0, $currency = "BDT")
    {
        if ($post_data == '' && $trx_id == '' && !is_array($post_data)) {
            $this->error = "Please provide valid transaction ID and post request data";
            return $this->error;
        }

        $validation = $this->validate($trx_id, $amount, $currency, $post_data);

        if ($validation) {
            return true;
        } else {
            return false;
        }
    }


    # VALIDATE SSLCOMMERZ TRANSACTION
    protected function validate($merchant_trans_id, $merchant_trans_amount, $merchant_trans_currency, $post_data)
    {
        # MERCHANT SYSTEM INFO
        if ($merchant_trans_id != "" && $merchant_trans_amount != 0) {

            # CALL THE FUNCTION TO CHECK THE RESULT
            $post_data['store_id'] = $this->getStoreId();
            $post_data['store_pass'] = $this->getStorePassword();

            if ($this->SSLCOMMERZ_hash_verify($post_data, $this->getStorePassword())) {

                $val_id = urlencode($post_data['val_id']);
                $store_id = urlencode($this->getStoreId());
                $store_passwd = urlencode($this->getStorePassword());
                $requested_url = ($this->config['apiDomain'] . $this->config['apiUrl']['order_validate'] . "?val_id=" . $val_id . "&store_id=" . $store_id . "&store_passwd=" . $store_passwd . "&v=1&format=json");

                $handle = curl_init();
                curl_setopt($handle, CURLOPT_URL, $requested_url);
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

                 if ($this->config['connect_from_localhost']) {
                     curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);
                     curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);
                 } else {
                     curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 2);
                     curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 2);
                 }


                $result = curl_exec($handle);

                $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

                if ($code == 200 && !(curl_errno($handle))) {

                    # TO CONVERT AS ARRAY
                    # $result = json_decode($result, true);
                    # $status = $result['status'];

                    # TO CONVERT AS OBJECT
                    $result = json_decode($result);
                    $this->sslc_data = $result;

                    # TRANSACTION INFO
                    $status = $result->status;
                    $tran_date = $result->tran_date;
                    $tran_id = $result->tran_id;
                    $val_id = $result->val_id;
                    $amount = $result->amount;
                    $store_amount = $result->store_amount;
                    $bank_tran_id = $result->bank_tran_id;
                    $card_type = $result->card_type;
                    $currency_type = $result->currency_type;
                    $currency_amount = $result->currency_amount;

                    # ISSUER INFO
                    $card_no = $result->card_no;
                    $card_issuer = $result->card_issuer;
                    $card_brand = $result->card_brand;
                    $card_issuer_country = $result->card_issuer_country;
                    $card_issuer_country_code = $result->card_issuer_country_code;

                    # API AUTHENTICATION
                    $APIConnect = $result->APIConnect;
                    $validated_on = $result->validated_on;
                    $gw_version = $result->gw_version;

                    # GIVE SERVICE
                    if ($status == "VALID" || $status == "VALIDATED") {
                        if ($merchant_trans_currency == "BDT") {
                            if (trim($merchant_trans_id) == trim($tran_id) && (abs($merchant_trans_amount - $amount) < 1) && trim($merchant_trans_currency) == trim('BDT')) {
                                return true;
                            } else {
                                # DATA TEMPERED
                                $this->error = "Data has been tempered";
                                return false;
                            }
                        } else {
                            //echo "trim($merchant_trans_id) == trim($tran_id) && ( abs($merchant_trans_amount-$currency_amount) < 1 ) && trim($merchant_trans_currency)==trim($currency_type)";
                            if (trim($merchant_trans_id) == trim($tran_id) && (abs($merchant_trans_amount - $currency_amount) < 1) && trim($merchant_trans_currency) == trim($currency_type)) {
                                return true;
                            } else {
                                # DATA TEMPERED
                                $this->error = "Data has been tempered";
                                return false;
                            }
                        }
                    } else {
                        # FAILED TRANSACTION
                        $this->error = "Failed Transaction";
                        return false;
                    }
                } else {
                    # Failed to connect with SSLCOMMERZ
                    $this->error = "Faile to connect with SSLCOMMERZ";
                    return false;
                }
            } else {
                # Hash validation failed
                $this->error = "Hash validation failed";
                return false;
            }
        } else {
            # INVALID DATA
            $this->error = "Invalid data";
            return false;
        }
    }

    # FUNCTION TO CHECK HASH VALUE
    protected function SSLCOMMERZ_hash_verify($post_data, $store_passwd = "")
    {
        if (isset($post_data) && isset($post_data['verify_sign']) && isset($post_data['verify_key'])) {
            # NEW ARRAY DECLARED TO TAKE VALUE OF ALL POST
            $pre_define_key = explode(',', $post_data['verify_key']);

            $new_data = array();
            if (!empty($pre_define_key)) {
                foreach ($pre_define_key as $value) {
//                    if (isset($post_data[$value])) {
                        $new_data[$value] = ($post_data[$value]);
//                    }
                }
            }
            # ADD MD5 OF STORE PASSWORD
            $new_data['store_passwd'] = md5($store_passwd);

            # SORT THE KEY AS BEFORE
            ksort($new_data);

            $hash_string = "";
            foreach ($new_data as $key => $value) {
                $hash_string .= $key . '=' . ($value) . '&';
            }
            $hash_string = rtrim($hash_string, '&');

            if (md5($hash_string) == $post_data['verify_sign']) {

                return true;

            } else {
                $this->error = "Verification signature not matched";
                return false;
            }
        } else {
            $this->error = 'Required data mission. ex: verify_key, verify_sign';
            return false;
        }
    }

    /**
     * @param array $requestData
     * @param string $type
     * @param string $pattern
     * @return false|mixed|string
     */
    public function makePayment(array $requestData, $type = 'checkout', $pattern = 'json')
    {
        if (empty($requestData)) {
            return "Please provide a valid information list about transaction with transaction id, amount, success url, fail url, cancel url, store id and pass at least";
        }

        $header = [];

        $this->setApiUrl($this->config['apiDomain'] . $this->config['apiUrl']['make_payment']);

        // Set the required/additional params
        $this->setParams($requestData);

        // Set the authentication information
        $this->setAuthenticationInfo();

        // Now, call the Gateway API
        $response = $this->callToApi($this->data, $header, $this->config['connect_from_localhost']);

        $formattedResponse = $this->formatResponse($response, $type, $pattern); // Here we will define the response pattern

        if ($type == 'hosted') {
            if (isset($formattedResponse['GatewayPageURL']) && $formattedResponse['GatewayPageURL'] != '') {
                $this->redirect($formattedResponse['GatewayPageURL']);
            } else {
                return $formattedResponse['failedreason'];
            }
        } else {
            return $formattedResponse;
        }
    }


    protected function setSuccessUrl()
    {
        $this->successUrl = url('/') . $this->config['success_url'];
    }

    protected function getSuccessUrl()
    {
        return $this->successUrl;
    }

    protected function setFailedUrl()
    {
        $this->failedUrl = url('/') . $this->config['failed_url'];
    }

    protected function getFailedUrl()
    {
        return $this->failedUrl;
    }

    protected function setCancelUrl()
    {
        $this->cancelUrl = url('/') . $this->config['cancel_url'];
    }

    protected function getCancelUrl()
    {
        return $this->cancelUrl;
    }

    public function setParams($requestData)
    {
        ##  Integration Required Parameters
        $this->setRequiredInfo($requestData);

        ##  Customer Information
        $this->setCustomerInfo($requestData);

        ##  Shipment Information
        $this->setShipmentInfo($requestData);

        ##  Product Information
        $this->setProductInfo($requestData);

        ##  Customized or Additional Parameters
        // $this->setAdditionalInfo($requestData);
    }

    public function setAuthenticationInfo()
    {
        $this->data['store_id'] = $this->getStoreId();
        $this->data['store_passwd'] = $this->getStorePassword();

        return $this->data;
    }

    public function setRequiredInfo(array $info)
    {
        $this->data['total_amount'] = $info['total_amount']; // decimal (10,2)	Mandatory - The amount which will process by SSLCommerz. It shall be decimal value (10,2). Example : 55.40. The transaction amount must be from 10.00 BDT to 500000.00 BDT
        $this->data['currency'] = $info['currency']; // string (3)	Mandatory - The currency type must be mentioned. It shall be three characters. Example : BDT, USD, EUR, SGD, INR, MYR, etc. If the transaction currency is not BDT, then it will be converted to BDT based on the current convert rate. Example : 1 USD = 82.22 BDT.
        $this->data['tran_id'] = $info['tran_id']; // string (30)	Mandatory - Unique transaction ID to identify your order in both your end and SSLCommerz
        $this->data['product_category'] = $info['product_category']; // string (50)	Mandatory - Mention the product category. It is a open field. Example - clothing,shoes,watches,gift,healthcare, jewellery,top up,toys,baby care,pants,laptop,donation,etc

        // Set the SUCCESS, FAIL, CANCEL Redirect URL before setting the other parameters
        $this->setSuccessUrl();
        $this->setFailedUrl();
        $this->setCancelUrl();

        $this->data['success_url'] = $this->getSuccessUrl(); // string (255)	Mandatory - It is the callback URL of your website where user will redirect after successful payment (Length: 255)
        $this->data['fail_url'] = $this->getFailedUrl(); // string (255)	Mandatory - It is the callback URL of your website where user will redirect after any failure occure during payment (Length: 255)
        $this->data['cancel_url'] = $this->getCancelUrl(); // string (255)	Mandatory - It is the callback URL of your website where user will redirect if user canceled the transaction (Length: 255)

        /*
         * IPN is very important feature to integrate with your site(s).
         * Some transaction could be pending or customer lost his/her session, in such cases back-end IPN plays a very important role to update your backend office.
         *
         * Type: string (255)
         * Important! Not mandatory, however better to use to avoid missing any payment notification - It is the Instant Payment Notification (IPN) URL of your website where SSLCOMMERZ will send the transaction's status (Length: 255).
         * The data will be communicated as SSLCOMMERZ Server to your Server. So, customer session will not work.
         * */
        $this->data['ipn_url'] = (isset($info['ipn_url'])) ? $info['ipn_url'] : null;

        /*
         * Type: string (30)
         * Do not Use! If you do not customize the gateway list - You can control to display the gateway list at SSLCommerz gateway selection page by providing this parameters.
         * Multi Card:
            brac_visa = BRAC VISA
            dbbl_visa = Dutch Bangla VISA
            city_visa = City Bank Visa
            ebl_visa = EBL Visa
            sbl_visa = Southeast Bank Visa
            brac_master = BRAC MASTER
            dbbl_master = MASTER Dutch-Bangla
            city_master = City Master Card
            ebl_master = EBL Master Card
            sbl_master = Southeast Bank Master Card
            city_amex = City Bank AMEX
            qcash = QCash
            dbbl_nexus = DBBL Nexus
            bankasia = Bank Asia IB
            abbank = AB Bank IB
            ibbl = IBBL IB and Mobile Banking
            mtbl = Mutual Trust Bank IB
            bkash = Bkash Mobile Banking
            dbblmobilebanking = DBBL Mobile Banking
            city = City Touch IB
            upay = Upay
            tapnpay = Tap N Pay Gateway
         * GROUP GATEWAY
            internetbank = For all internet banking
            mobilebank = For all mobile banking
            othercard = For all cards except visa,master and amex
            visacard = For all visa
            mastercard = For All Master card
            amexcard = For Amex Card
         * */
        $this->data['multi_card_name'] = (isset($info['multi_card_name'])) ? $info['multi_card_name'] : null;

        /*
         * Type: string (255)
         * Do not Use! If you do not control on transaction - You can provide the BIN of card to allow the transaction must be completed by this BIN. You can declare by coma ',' separate of these BIN.
         * Example: 371598,371599,376947,376948,376949
         * */
        $this->data['allowed_bin'] = (isset($info['allowed_bin'])) ? $info['allowed_bin'] : null;

        ##   Parameters to Handle EMI Transaction ##
        $this->data['emi_option'] = (isset($info['emi_option'])) ? $info['emi_option'] : null; // integer (1)	Mandatory - This is mandatory if transaction is EMI enabled and Value must be 1/0. Here, 1 means customer will get EMI facility for this transaction
        $this->data['emi_max_inst_option'] = (isset($info['emi_max_inst_option'])) ? $info['emi_max_inst_option'] : null; // integer (2)	Max instalment Option, Here customer will get 3,6, 9 instalment at gateway page
        $this->data['emi_selected_inst'] = (isset($info['emi_selected_inst'])) ? $info['emi_selected_inst'] : null; // integer (2)	Customer has selected from your Site, So no instalment option will be displayed at gateway page
        $this->data['emi_allow_only'] = (isset($info['emi_allow_only'])) ? $info['emi_allow_only'] : 0; 
        
        return $this->data;
    }

    public function setCustomerInfo(array $info)
    {
        $this->data['cus_name'] = $info['cus_name']; // string (50)	Mandatory - Your customer name to address the customer in payment receipt email
        $this->data['cus_email'] = $info['cus_email']; // string (50)	Mandatory - Valid email address of your customer to send payment receipt from SSLCommerz end
        $this->data['cus_add1'] = $info['cus_add1']; // string (50)	Mandatory - Address of your customer. Not mandatory but useful if provided
        $this->data['cus_add2'] = $info['cus_add2']; // string (50)	Address line 2 of your customer. Not mandatory but useful if provided
        $this->data['cus_city'] = $info['cus_city']; // string (50)	Mandatory - City of your customer. Not mandatory but useful if provided
        $this->data['cus_state'] = (isset($info['cus_state'])) ? $info['cus_state'] : null; // string (50)	State of your customer. Not mandatory but useful if provided
        $this->data['cus_postcode'] = $info['cus_postcode']; // string (30)	Mandatory - Postcode of your customer. Not mandatory but useful if provided
        $this->data['cus_country'] = $info['cus_country']; // string (50)	Mandatory - Country of your customer. Not mandatory but useful if provided
        $this->data['cus_phone'] = $info['cus_phone']; // string (20)	Mandatory - The phone/mobile number of your customer to contact if any issue arises
        $this->data['cus_fax'] = (isset($info['cus_fax'])) ? $info['cus_fax'] : null; // string (20)	Fax number of your customer. Not mandatory but useful if provided

        return $this->data;
    }

    public function setShipmentInfo(array $info)
    {
        // dd($info);

        $this->data['shipping_method'] = $info['shipping_method']; // string (50)	Mandatory - Shipping method of the order. Example: YES or NO or Courier
        $this->data['num_of_item'] = isset($info['num_of_item']) ? $info['num_of_item'] : 1; // integer (1)	Mandatory - No of product will be shipped. Example: 1 or 2 or etc
        $this->data['ship_name'] = $info['ship_name'] ? $info['ship_name'] : 'COD'; // string (50)	Mandatory, if shipping_method is YES - Shipping Address of your order. Not mandatory but useful if provided
        $this->data['ship_add1'] = $info['ship_add1']; // string (50)	Mandatory, if shipping_method is YES - Additional Shipping Address of your order. Not mandatory but useful if provided
        $this->data['ship_add2'] = (isset($info['ship_add2'])) ? $info['ship_add2'] : null; // string (50)	Additional Shipping Address of your order. Not mandatory but useful if provided
        $this->data['ship_city'] = $info['ship_city'] ? $info['ship_city'] : 'Dhaka'; // string (50)	Mandatory, if shipping_method is YES - Shipping city of your order. Not mandatory but useful if provided
        $this->data['ship_state'] = (isset($info['ship_state'])) ? $info['ship_state'] : null; // string (50)	Shipping state of your order. Not mandatory but useful if provided
        $this->data['ship_postcode'] = (isset($info['ship_postcode'])) ? $info['ship_postcode'] : null; // string (50)	Mandatory, if shipping_method is YES - Shipping postcode of your order. Not mandatory but useful if provided
        $this->data['ship_country'] = (isset($info['ship_country'])) ? $info['ship_country'] : null; // string (50)	Mandatory, if shipping_method is YES - Shipping country of your order. Not mandatory but useful if provided

        return $this->data;
    }

   public function setProductInfo(array $info)
{
    // dd($info);
    $this->data['product_name'] = $info['product_name'] ?? '';
    $this->data['product_category'] = $info['product_category'] ?? '';
    $this->data['product_profile'] = $info['product_profile'] ?? 'physical-goods';

    $this->data['cart'] = $info['cart'] ?? null;

    $this->data['product_amount'] = $info['product_amount'] ?? null;
    $this->data['vat'] = $info['vat'] ?? null;
    $this->data['discount_amount'] = $info['discount_amount'] ?? null;
    $this->data['convenience_fee'] = $info['convenience_fee'] ?? null;

    return $this->data;
}

    public function setAdditionalInfo(array $info)
    {
    // additional info is optional — do not dd() here (leftover debug).
        $this->data['value_a'] = (isset($info['value_a'])) ? $info['value_a'] : null; // value_a [ string (255)	- Extra parameter to pass your meta data if it is needed. Not mandatory]
        $this->data['value_b'] = (isset($info['value_b'])) ? $info['value_b'] : null; // value_b [ string (255)	- Extra parameter to pass your meta data if it is needed. Not mandatory]
        $this->data['value_c'] = (isset($info['value_c'])) ? $info['value_c'] : null; // value_c [ string (255)	- Extra parameter to pass your meta data if it is needed. Not mandatory]
        $this->data['value_d'] = (isset($info['value_d'])) ? $info['value_d'] : null; // value_d [ string (255)	- Extra parameter to pass your meta data if it is needed. Not mandatory]

        return $this->data;
    }
}
