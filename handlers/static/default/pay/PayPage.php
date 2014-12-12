<?php

import('base.controller.BasePage');

class PayPage extends BasePage {
    const ID_1MO = 1;
    const ID_6MO = 2;
    const ID_1YR = 3;

    public function __construct() {
        $this->addRule(array(
            'success' => '(success): null',
            'pending' => '(pending): null',
            'redirect' => '(redirect): null',
            '2checkout' => '(2checkout): null',
            'robokassa' => '(robokassa): null',
            'paypalSucces' => '(paypalSucces): null',
            'paypalNotify' => '(paypalNotify): null',            
        ));
    }

    public function handleDefault(array $request = array()) {
        if (!Access::loggedIn()) {
            return $this->showLogin();
        }

        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('premium');

        $this->T->include('this.pay_' . $this->ENV->language, 'content');

        $this->T->page_title = $this->LNG->Premium;
        return $this->T->return('templates.inner');
    }

    public function handleRedirect(array $request = array()) {
        if (!Access::loggedIn()) {
            return $this->showLogin();
        }

        $option = array_get($request, 'option', '2checkout');
        switch ($option) {
            default:
            case '2checkout':
                return $this->handle2checkout($request);

            case 'paypal':
                return $this->handle2paypal($request);
            case 'robokassa':
                return $this->handleRobokassa($request);
        }
    }

    public function handle2checkout(array $request = array()) {
        $product_id = array_get($request, 'product_id', 1);
        $params = array(
            'product_id' => $product_id,
            '42goals_user' => $this->ENV->UID,
            'quantity' => 1,
            'sid' => $this->CNF->twocheckout->vendor
        );

        $url = 'https://www.2checkout.com/checkout/purchase?' . http_build_query($params, null, '&');

        header('Location: ' . $url);
        return true;
    }

    public function handle2paypal(array $request = array()) { 
        $product_id = array_get($request, 'product_id', 1);
        $prodDescArr = $this->getProductDesc($product_id);
        $item_name = $prodDescArr['item_name'];
        $item_desc = $prodDescArr['inv_desc'];
        $user_id = $this->ENV->UID;
        $currencyCode = 'USD';
        $paypal_email = $this->CNF->paypal->paypalemail;///Business
        $return_url = $this->CNF->paypal->host1.$this->CNF->paypal->returnurl;//"pay/paypalSucces";
        $cancel_url = $this->CNF->paypal->host1.$this->CNF->paypal->cancelurl;//"pay/pending";
        $notify_url = $this->CNF->paypal->host1.$this->CNF->paypal->notifyurl;//"pay/paypalnotify";
        
  
        
//        $MerchantCode = "PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest";
      
        // Firstly Append paypal account to querystring ?cmd=_ap-payment
        $querystring .= "?paykey=".md5($user_id);
	
        $querystring .= "&business=" . urlencode($paypal_email) . "&";

        // Append amount& currency  to quersytring 
        //The item name and amount can be brought in dynamically by querying the $_POST['item_number'] variable.
        $querystring .= "item_name=" . urlencode($item_desc) . "&";
        $querystring .= "amount=" . urlencode($prodDescArr['out_sum']) . "&";
        $querystring .= "currency_code=" . urlencode($currencyCode) . "&";
        $querystring .= "item_number=" . urlencode($item_name) . "&";
        $querystring .= "invoice=" . urlencode(md5($user_id.":".$item_name)) . "&";
        $querystring .= "custom=" . urlencode($user_id) . "&";
        
        
//        $querystring .= "no_note=" . urlencode(1) . "&";
        $querystring .= "first_name=" . urlencode("FirstNameTest") . "&";
        $querystring .= "last_name=" . urlencode("LastNameTest") . "&";
        $querystring .= "txnID=" . urlencode("txnID1234") . "&";
        
//        
//        $value = urlencode(stripslashes($MerchantCode));
//        $value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i','${1}%0D%0A${3}',$value);
//        $querystring .= "bn=" .$value . "&";
//        $querystring .= "lc=" ."USA" . "&";
        $querystring .= "cmd=" .urlencode(stripslashes('_xclick')) . "&";

	// Append paypal return addresses
	$querystring .= "return=".urlencode(stripslashes($return_url))."&";
	$querystring .= "cancel_return=".urlencode(stripslashes($cancel_url))."&";
	$querystring .= "notify_url=".urlencode($notify_url);
	
	// Append querystring with custom field
	//$querystring .= "&custom=".USERID;
	
	// Redirect to paypal IPN
//	header('location:https://www.sandbox.paypal.com/cgi-bin/webscr'.$querystring);

	header('location:'.$this->CNF->paypal->paypalurl.$querystring);

	exit();        
    }
    public function handlePaypalSucces(array $request = array()) {    
            $this->T->include('this.success', 'content');
            $this->T->page_title = $this->LNG->Payment_success;
            return $this->T->return('templates.inner');
    }  
    public function handlePaypalNotify(array $request = array()) {    


    
        import('model.Invoices');
        import('lib.json');
        import('lib.validate');
        import('lib.email');

        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('premium');

        $user_id = array_get($request, 'custom', $this->ENV->UID);
        $type = array_get($request, 'item_number', '1mo');
        
        $order = array_get($request, 'txn_id', 0);
        $total = array_get($request, 'mc_gross', 0);
        if (!$order || !$total) {
            // no order number
            return $this->handleError();
        }

        $str = $user_id.":". $type;
        //describe($str, 1);
        $check_key = (md5($str));
        //describe(array($check_key, $request['key']), 1);
        if ($check_key === $request['invoice']) {
            // create an invoice
            $Invoices = new InvoicesModel;

            if ($Invoices->count('txn_id', SQL::quote('txn_id = ?', $order))) {
                // this order has already been processed
                return $this->handleError();
            }

            
            $quantity = (int) array_get($request, 'quantity', 1);

            $data = array(
                'user' => $user_id,
                'date' => date('Y-m-d'),
                'txn_id' => $order,
                'type' => $type,
                'quantity' => $quantity,
                'payment_gateway' => 'paypal',
                'data' => json::encode($request)
            );
            $invoice_added = $Invoices->add($data);
            if ($invoice_added === false) {
                // cannot create invoice
                return $this->handleError();
            }

            // update user premium status
            $Users = new UsersModel;
            $user = $Users->view($user_id, array('name', 'email', 'paid_till'));
            $paid_till = $user['paid_till'];
            $months = 0;
            switch ($type) {
                default:
                case '1mo':
                    $months = $quantity;
                    break;
                case '6mo':
                    $months = 6 * $quantity;
                    break;
                case '1yr':
                    $months = 12 * $quantity;
                    break;
            }
            if (validate::date($paid_till)) {
                $time = strtotime($paid_till);
                if ($time < time()) {
                    $paid_till = date('Y-m-d');
                }
                $paid_till = date('Y-m-d', strtotime("$paid_till +$months months"));
            } else {
                $paid_till = date('Y-m-d', strtotime("+$months months"));
            }
            $user_updated = $Users->edit($user_id, array('paid_till' => $paid_till));
            if ($user_updated === false) {
                // cannot update user
                return $this->handleError();
            }

            // send email
            $this->T->user = $user;
            $this->T->order = $request;
            $message = $this->T->return_template('this.email_success');

            $to = $user['email'];
            $from = "{$this->CNF->site->admin} <{$this->CNF->site->email}>";
            $subject = $this->LNG->Payment_success;

            $headers = "From: $from\n"
                    . "Content-Type: text/plain; charset=utf-8";
            email::send($to, $subject, $message, $headers);

            // render page
            $this->T->include('this.success', 'content');

            $this->T->page_title = $this->LNG->Payment_success;
            return $this->T->return('templates.inner');
        } else {
            return $this->handleError();
        }
        return describe($_REQUEST);
    }  
    public function getProductDesc($product_id) {
        
        switch ($product_id) {
            case self::ID_6MO:
                $prodDescArr['item_name'] = '6mo';
                $prodDescArr['inv_desc'] = '6 months premium account subscription for 42goals.com';
                $prodDescArr['out_sum'] = '25.00';
                break;

            case self::ID_1YR:
                $prodDescArr['item_name'] = '1yr';
                $prodDescArr['inv_desc'] = '12 months premium account subscription for 42goals.com';
                $prodDescArr['out_sum'] = '45.00';
                break;
            default:
            case self::ID_1MO:
                $prodDescArr['item_name'] = '1mo';
                $prodDescArr['inv_desc'] = '1 month premium account subscription for 42goals.com';
                $prodDescArr['out_sum'] = '5.00';
                break;
        }

        return $prodDescArr;
    }

    public function handleRobokassa(array $request = array()) {
        // your registration data
        $mrh_login = $this->CNF->robokassa->login;
        $mrh_pass1 = $this->CNF->robokassa->pass1;

        $product_id = array_get($request, 'product_id');
        $invoice = array(
            'user' => $this->ENV->UID,
            'date' => date('Y-m-d'),
            'quantity' => 1,
            'data' => 'robokassa'
        );
        switch ($product_id) {
            default:
            case self::ID_1MO:
                $invoice['type'] = '1mo';
                $inv_desc = '1 month premium account subscription for 42goals.com';
                $out_summ = '160.00';
                break;

            case self::ID_6MO:
                $invoice['type'] = '6mo';
                $inv_desc = '6 months premium account subscription for 42goals.com';
                $out_summ = '800.00';
                break;

            case self::ID_1YR:
                $invoice['type'] = '1yr';
                $inv_desc = '12 months premium account subscription for 42goals.com';
                $out_summ = '1400.00';
                break;
        }

        import('model.Invoices');
        $Invoices = new InvoicesModel;
        $invoice_id = $Invoices->add($invoice);
        if ($invoice_id === false) {
            // cannot create invoice
            return $this->handleError();
        }


        // build CRC value
        $crc = md5("$mrh_login:$out_summ:$inv_id:$mrh_pass1");

        // build URL
        $params = array(
            'MrchLogin' => $mrh_login,
            //'IncCurrLabel' => 'WMZM',
            'OutSum' => $out_summ,
            'InvId' => $inv_id,
            'Desc' => $inv_desc,
            'SignatureValue' => $crc,
            'Culture' => $this->ENV->language
        );
        if ($this->CNF->robokassa->demo) {
            $url = 'http://test.robokassa.ru/Index.aspx';
        } else {
            $url = 'http://merchant.roboxchange.com/Index.aspx';
        }
        $url .= '?' . http_build_query($params, null, '&');

        header('Location: ' . $url);
        return true;
    }

    public function handleSuccess(array $request = array()) {
        import('model.Invoices');
        import('lib.json');
        import('lib.validate');
        import('lib.email');

        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('premium');

        $user_id = array_get($request, '42goals_user', $this->ENV->UID);

        $secret = $this->CNF->twocheckout->secret;
        $vendor = $this->CNF->twocheckout->vendor;
        $order = array_get($request, 'order_number', 0);
        $total = array_get($request, 'total', 0);
        if (!$order || !$total) {
            // no order number
            return $this->handleError();
        }

        $str = $secret . $vendor . $order . $total;
        //describe($str, 1);
        $check_key = strtoupper(md5($str));
        //describe(array($check_key, $request['key']), 1);
        if ($this->CNF->twocheckout->demo || $check_key === $request['key']) {
            // create an invoice
            $Invoices = new InvoicesModel;

            if ($Invoices->count('number', SQL::quote('number = ?', $order))) {
                // this order has already been processed
                return $this->handleError();
            }

            $type = array_get($request, 'merchant_product_id', '1mo');
            $quantity = (int) array_get($request, 'quantity', 1);

            $data = array(
                'user' => $user_id,
                'date' => date('Y-m-d'),
                'number' => $order,
                'type' => $type,
                'quantity' => $quantity,
                'data' => json::encode($request)
            );
            $invoice_added = $Invoices->add($data);
            if ($invoice_added === false) {
                // cannot create invoice
                return $this->handleError();
            }

            // update user premium status
            $Users = new UsersModel;
            $user = $Users->view($user_id, array('name', 'email', 'paid_till'));
            $paid_till = $user['paid_till'];
            $months = 0;
            switch ($type) {
                default:
                case '1mo':
                    $months = $quantity;
                    break;
                case '6mo':
                    $months = 6 * $quantity;
                    break;
                case '1yr':
                    $months = 12 * $quantity;
                    break;
            }
            if (validate::date($paid_till)) {
                $time = strtotime($paid_till);
                if ($time < time()) {
                    $paid_till = date('Y-m-d');
                }
                $paid_till = date('Y-m-d', strtotime("$paid_till +$months months"));
            } else {
                $paid_till = date('Y-m-d', strtotime("+$months months"));
            }
            $user_updated = $Users->edit($user_id, array('paid_till' => $paid_till));
            if ($user_updated === false) {
                // cannot update user
                return $this->handleError();
            }

            // send email
            $this->T->user = $user;
            $this->T->order = $request;
            $message = $this->T->return_template('this.email_success');

            $to = $user['email'];
            $from = "{$this->CNF->site->admin} <{$this->CNF->site->email}>";
            $subject = $this->LNG->Payment_success;

            $headers = "From: $from\n"
                    . "Content-Type: text/plain; charset=utf-8";
            email::send($to, $subject, $message, $headers);

            // render page
            $this->T->include('this.success', 'content');

            $this->T->page_title = $this->LNG->Payment_success;
            return $this->T->return('templates.inner');
        } else {
            return $this->handleError();
        }
        return describe($_REQUEST);
    }

    public function handleError(array $request = array()) {
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('premium');

        $this->T->include('this.error', 'content');

        $this->T->page_title = $this->LNG->Payment_error;
        return $this->T->return('templates.inner');
    }

    public function handlePending(array $request = array()) {
        $this->Conf->loadLanguage('site');
        $this->Conf->loadLanguage('premium');

        $this->T->include('this.pending', 'content');

        $this->T->page_title = $this->LNG->Payment_pending;
        return $this->T->return('templates.inner');
    }

}
