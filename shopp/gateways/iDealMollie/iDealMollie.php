<?php
/**
 * iDeal Mollie
 *
 * @author Jonathan Davis
 * @version 1.1
 * @copyright Ingenesis Limited, 24 February, 2009
 * @package shopp
 * @since 1.1
 * @subpackage iDealMollie
 * 
 * $Id$
 **/

require_once(SHOPP_PATH."/core/model/XML.php");

class iDealMollie extends GatewayFramework implements GatewayModule {          
	
	var $secure = false;
	var $url = 'http://www.mollie.nl/xml/ideal/';

	function __construct () {
		// global $Shopp,$wp;
		// $this->settings = $Shopp->Settings->get('iDealMollie');
		// $this->settings['merchant_email'] = $Shopp->Settings->get('merchant_email');
		// $this->settings['base_operations'] = $Shopp->Settings->get('base_operations');
		// 
		// $loginproc = (isset($_POST['process-login']) 
		// 	&& $_POST['process-login'] != 'false')?$_POST['process-login']:false;
		// 
		// if (isset($_POST['checkout']) && 
		// 	$_POST['checkout'] == "process" && 
		// 	!$loginproc) $this->checkout();
		// 
		// // Don't do anything with Mollie.nl reports
		// if (isset($_GET['idealreport'])) die('1');
		// 
		// if (isset($_GET['transaction_id']) 
		// 	&& !isset($_GET['idealreport'])) 
		// 		$_POST['checkout'] = "confirmed";
		// 	
		// return true;
	}
	
	function actions () { 
		
	}
	
	function checkout () {
		global $Shopp;
		if (empty($_POST['checkout'])) return false;
		
		// Save checkout data
		$Order = $Shopp->Cart->data->Order;
		
		if (isset($_POST['data'])) $Order->data = $_POST['data'];
		if (empty($Order->Customer))
			$Order->Customer = new Customer();
		$Order->Customer->updates($_POST);

		if (isset($_POST['confirm-password']))
			$Order->Customer->confirm_password = $_POST['confirm-password'];

		if (empty($Order->Billing))
			$Order->Billing = new Billing();
		$Order->Billing->updates($_POST['billing']);

		if (empty($Order->Shipping))
			$Order->Shipping = new Shipping();
			
		if ($_POST['shipping']) $Order->Shipping->updates($_POST['shipping']);
		if (!empty($_POST['shipmethod'])) $Order->Shipping->method = $_POST['shipmethod'];
		else $Order->Shipping->method = key($Shopp->Cart->data->ShipCosts);

		// Override posted shipping updates with billing address
		if ($_POST['sameshipaddress'] == "on")
			$Order->Shipping->updates($Order->Billing,
				array("_datatypes","_table","_key","_lists","id","created","modified"));

		$estimatedTotal = $Shopp->Cart->data->Totals->total;
		$Shopp->Cart->updated();
		$Shopp->Cart->totals();
		
		if ($Shopp->Cart->orderisfree())
			return ($_POST['checkout'] = 'confirmed');
			
		$_ = array();
		
		$_['partnerid']				= $this->settings['account'];

		// Options
		$_['a'] 					= "fetch"; // specify fetch mode
		$_['returnurl']				= add_query_arg('shopp_xco','iDealMollie/iDealMollie',$Shopp->link('confirm-order'));
		$_['reporturl']				= add_query_arg(array('shopp_xco'=>'iDealMollie/iDealMollie','idealreport'=>1),$Shopp->link('catalog'));

		// Line Items
		$description = array();
		foreach($Shopp->Cart->contents as $i => $Item) 
			$description[] = $Item->quantity."x ".$Item->name.((!empty($Item->optionlabel))?' '.$Item->optionlabel:'');

		// Transaction
		$_['bank_id']				= $_POST['idealmollie-bank'];
		$_['amount']				= round(floatvalue($Shopp->Cart->data->Totals->total)*100);
		$_['description']			= join(", ",$description);
				
		$this->transaction = $this->encode($_);
		$this->send();

		if (empty($this->Response)) return false;
		if ($this->error()) return false;
		
		$url = $this->Response->getElementContent('URL');
		if (!empty($url)) shopp_redirect($url);
		
		return false;
	}
	
	function process () {
		global $Shopp;
		if (empty($_GET['transaction_id'])) return false;
		
		$_['a'] 					= "check"; // specify check mode
		$_['partnerid']				= $this->settings['account'];
		$_['transaction_id']		= $_GET['transaction_id'];
		if ($this->settings['testmode'] == "on")
			$_['testmode'] = 'true';

		if (!$Shopp->Cart->validorder()) {
			new ShoppError(__('There is not enough customer information to process the order.','Shopp'),'invalid_order',SHOPP_TRXN_ERR);
			shopp_redirect($Shopp->link('cart'));
		}
		
		// Check for unique transaction id
		$Purchase = new Purchase($_['transaction_id'],'transactionid');
		if(!empty($Purchase->id)){
			if(SHOPP_DEBUG) new ShoppError(__('Order validation failed. Received duplicate transaction id: ','Shopp').$_['transaction_id'], 'duplicate_order',SHOPP_TRXN_ERR);
			shopp_redirect($Shopp->link('cart'));
		}

		// Try up to 3 times
		for ($i = 3; $i > 0; $i--) {
			$this->transaction = $this->encode($_);
			$this->send();

			if (empty($this->Response)) return false;
			if ($this->error()) return false;
			$payment = $this->Response->getElementContent('payed');
		 	if ($payment == "true") break;
			
		}
		
		if ($payment == "false") {
			new ShoppError(__('Payment could not be confirmed, this order cannot be processed.','Shopp'),'ideal_mollie_transaction_error',SHOPP_TRXN_ERR);
			shopp_redirect($Shopp->link('cart'));
		}

		$Order = $Shopp->Cart->data->Order;
		$Order->Totals = $Shopp->Cart->data->Totals;
		$Order->Items = $Shopp->Cart->contents;
		$Order->Cart = $Shopp->Cart->session;
	
		$Order->Customer->save();
	
		$Order->Billing->customer = $Order->Customer->id;
		$Order->Billing->cardtype = "iDeal Mollie";
		$Order->Billing->save();
	
		$Order->Shipping->customer = $Order->Customer->id;
		$Order->Shipping->save();
		
		$Purchase = new Purchase();
		$Purchase->customer = $Order->Customer->id;
		$Purchase->billing = $Order->Billing->id;
		$Purchase->shipping = $Order->Shipping->id;
		$Purchase->copydata($Order->Customer);
		$Purchase->copydata($Order->Billing);
		$Purchase->copydata($Order->Shipping,'ship');
		$Purchase->copydata($Order->Totals);
		$Purchase->freight = $Order->Totals->shipping;
		$Purchase->gateway = "iDeal Mollie";
		$Purchase->transactionid = $_GET['transaction_id'];
		$Purchase->txnstatus = "CHARGED";
		$Purchase->ip = $Shopp->Cart->ip;
		$Purchase->save();

		foreach($Shopp->Cart->contents as $Item) {
			$Purchased = new Purchased();
			$Purchased->copydata($Item);
			$Purchased->purchase = $Purchase->id;
			if (!empty($Purchased->download)) $Purchased->keygen();
			$Purchased->save();
			if ($Item->inventory) $Item->unstock();
		}

		return $Purchase;
	}
	
	function error () {
		if (empty($this->Response)) return false;
		$code = $this->Response->getElement('errorcode');
		$message = $this->Response->getElementContent('message');
		if (!$code) return false;
		
		return new ShoppError($message,'ideal_mollie_transaction_error',SHOPP_TRXN_ERR,
			array('code'=>$code));
	}
		
	function send () {
		$connection = curl_init();
		curl_setopt($connection,CURLOPT_URL,$this->gateway_url);
		curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0); 
		curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0); 
		curl_setopt($connection, CURLOPT_NOPROGRESS, 1); 
		curl_setopt($connection, CURLOPT_VERBOSE, 1); 
		curl_setopt($connection, CURLOPT_FOLLOWLOCATION,0); 
		curl_setopt($connection, CURLOPT_POST, 1); 
		curl_setopt($connection, CURLOPT_POSTFIELDS, $this->transaction); 
		curl_setopt($connection, CURLOPT_TIMEOUT, 60); 
		curl_setopt($connection, CURLOPT_USERAGENT, SHOPP_GATEWAY_USERAGENT); 
		curl_setopt($connection, CURLOPT_REFERER, "https://".$_SERVER['SERVER_NAME']); 
		curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);

		$buffer = curl_exec($connection);   
		if (curl_errno($connection))
			new ShoppError(curl_error($connection),'ideal_mollie_connection',SHOPP_COMM_ERR);
		curl_close($connection);

		$this->Response = new XMLdata($buffer);
		return $this->Response;
	}
	
	function encode ($data) {
		$query = "";
		foreach($data as $key => $value) {
			if (is_array($value)) {
				foreach($value as $item) {
					if (strlen($query) > 0) $query .= "&";
					$query .= "$key=".urlencode($item);
				}
			} else {
				if (strlen($query) > 0) $query .= "&";
				$query .= "$key=".urlencode($value);
			}
		}
		return $query;
	}
	
	function tag ($property,$options=array()) {
		global $Shopp;

		if (!isset($Shopp->Cart->data->iDealMollie))
			$Shopp->Cart->data->iDealMollie = new stdClass();
			
		switch ($property) {
			case "button":
				$args = array();
				$args['shopp_xco'] = 'iDealMollie/iDealMollie';
				$url = add_query_arg($args,$Shopp->link('checkout'));				
				$result .= '<p class="idealmollie"><a href="'.$url.'"><img src="'.SHOPP_PLUGINURI.'/gateways/iDealMollie/ideal.gif'.'" alt="iDeal" width="57" height="51" /></a></p>';
				return $result;
		}
	}
	
	function billing ($options) {
		global $Shopp;

		$_ = array();
		$_['a'] = "banklist";
	
		if ($this->settings['testmode'] == "on")
			$_['testmode'] = 'true';
			
		$this->transaction = $this->encode($_);
		$Response = $this->send();

		$result = '';
		if ($banks = $Response->getElement('bank')) {
			$result .= '<li>';
			$result .= '<h3 class="mast" for="idealmollie-bank">'.__('iDeal Payment','Shopp').'</h3>';
			$result .= '<span><select name="idealmollie-bank" id="idealmollie-bank">';
			foreach ($banks as $bank) {
				if (isset($bank['CHILDREN'])) {
					$bank_id = $bank['CHILDREN']['bank_id']['CONTENT'];
					$bank_name = $bank['CHILDREN']['bank_name']['CONTENT'];
				} else {
					$bank_id = $bank['bank_id']['CONTENT'];
					$bank_name = $bank['bank_name']['CONTENT'];
				}
				$result .= '<option value="'.$bank_id.'">'.$bank_name.'</option>';

			}
			$result .= '</select>';
			$result .= '<label for="idealmollie-bank">'.__('iDeal Bank','Shopp').'</label></span>';
			$result .= '</li>';
		}
		return $result;
	}
	
	function settings () {
		$this->ui->text(0,array(
			'name' => 'account',
			'value' => $this->settings['account'],
			'size' => 30,
			'label' => __('Enter your iDeal Mollie account ID.','Shopp')
		));
		
		$this->ui->checkbox(0,array(
			'name' => 'testmode',
			'label' => __('Enable test mode','Shopp'),
			'checked' => $this->settings['testmode']
		));
	}
	
} // END class iDealMollie

?>