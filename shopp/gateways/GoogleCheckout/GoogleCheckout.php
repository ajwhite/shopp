<?php
/**
 * Google Checkout
 * @class GoogleCheckout
 *
 * @author Jonathan Davis
 * @version 1.0
 * @copyright Ingenesis Limited, 19 August, 2008
 * @package Shopp
 **/

require_once(SHOPP_PATH."/core/model/XMLdata.php");

class GoogleCheckout {
	var $urls = array();
	var $settings = array();
	var $Response = false;

	function GoogleCheckout () {
		global $Shopp;
		
		$this->urls['schema'] = 'http://checkout.google.com/schema/2';
		
		$this->urls['checkout'] = array(
			'live' => 'https://%s:%s@checkout.google.com/api/checkout/v2/merchantCheckout/Merchant/%s',
			'test' => 'https://%s:%s@sandbox.google.com/checkout/api/checkout/v2/merchantCheckout/Merchant/%s'
			);
			
		$this->urls['order'] = array(
			'live' => 'https://%s:%s@checkout.google.com/api/checkout/v2/request/Merchant/%s',
			'test' => 'https://%s:%s@sandbox.google.com/checkout/api/checkout/v2/request/Merchant/%s'
			);
			
		$this->urls['button'] = array(
			'live' => 'http://checkout.google.com/buttons/checkout.gif',
			'test' => 'http://sandbox.google.com/checkout/buttons/checkout.gif'
			);
		
		$this->settings = $Shopp->Settings->get('GoogleCheckout');
		$this->settings['merchant_email'] = $Shopp->Settings->get('merchant_email');
		$this->settings['location'] = "en_US";
		$base = $Shopp->Settings->get('base_operations');
		if ($base['country'] == "UK") $this->settings['location'] = "en_GB";
		
		return true;
	}
	
	function checkout () {
		global $Shopp;
		
		$this->transaction = $this->buildCheckoutRequest($Shopp->Cart);
		$Response = $this->send($this->urls['checkout']);
		
		if (!empty($Response)) {
			$redirect = false;
			$redirect = $Response->getElementContent('redirect-url');
			if ($redirect) {
				// Empty cart on successful sending the order to Google
				$Shopp->Cart->unload();
				session_destroy();

				// Start new cart session, just in case they come back for more
				$Shopp->Cart = new Cart();
				session_start();
				
				header("Location: $redirect");
				exit();
			}
			echo "An error occured";
			exit();
		}
			
		return false;	
	}
	
	function process () {
		if ($this->authentication()) {			
			
			// Read incoming request data
			$data = trim(file_get_contents('php://input'));

			// Handle notifications
			$XML = new XMLdata($data);
			$type = key($XML->data);
			$serial = $XML->getElementAttr($type,'serial-number');
			
			switch($type) {
				case "new-order-notification": $this->order($XML); break;
				case "risk-information-notification": $this->risk($XML); break;
				case "order-state-change-notification": $this->state($XML); break;
				case "charge-amount-notification":			// Not implemented
				case "refund-amount-notification":			// Not implemented
				case "chargeback-amount-notification":		// Not implemented
				case "authorization-amount-notification":	// Not implemented
					break;
			}
			// Send acknowledgement
			$this->acknowledge($serial);	
		}
		exit();
	}
	
	/**
	 * authcode()
	 * Build a hash code for the merchant id and merchant key */
	function authcode ($id,$key) {
		return sha1($id.$key);
	}
	
	/**
	 * authentication()
	 * Authenticate an incoming request */
	function authentication () {
		if (isset($_GET['merc'])) $merc = $_GET['merc'];

		if (!empty($this->settings['id']) && !empty($this->settings['key']) 
				&& $_GET['merc'] == $this->authcode($this->settings['id'],$this->settings['key']));
		 	return true;
		
		header('HTTP/1.0 401 Unauthorized');
		die("<h1>401 Unauthorized Access</h1>");
		exit();
	}
	
	/**
	 * acknowledge()
	 * Sends an acknowledgement message back to Google to confirm the notification
	 * was received and processed */
	function acknowledge ($serial) {
		header('HTTP/1.0 200 OK');
		$_ = array('<?xml version="1.0" encoding="utf-8"?>'."\n");
		$_[] .= '<notification-acknowledgement xmlns="'.$this->urls['schema'].'" serial-number="'.$serial.'" />';
		echo join("\n",$_);
	}
		
	function buildCheckoutRequest ($Cart) {
		$_ = array('<?xml version="1.0" encoding="utf-8"?>'."\n");
		$_[] = '<checkout-shopping-cart xmlns="'.$this->urls['schema'].'">';
			
			// Build the cart
			$_[] = '<shopping-cart>';
				$_[] = '<items>';
				foreach($Cart->contents as $i => $Item) {
					$_[] = '<item>';
					$_[] = '<item-name>'.htmlspecialchars($Item->name).htmlspecialchars((!empty($Item->optionlabel))?' ('.$Item->optionlabel.')':'').'</item-name>';
					$_[] = '<item-description>'.htmlspecialchars($Item->description).'</item-description>';
					$_[] = '<unit-price currency="USD">'.$Item->unitprice.'</unit-price>';
					$_[] = '<quantity>'.$Item->quantity.'</quantity>';
					if (!empty($Item->sku)) $_[] = '<merchant-item-id>'.$Item->sku.'</merchant-item-id>';
					$_[] = '</item>';
				}
				$_[] = '</items>';
			$_[] = '</shopping-cart>';
			
			// Build the flow support request
			$_[] = '<checkout-flow-support>';
			$_[] = '<merchant-checkout-flow-support />';
			$_[] = '</checkout-flow-support>';
			
			
		$_[] = '</checkout-shopping-cart>';
		return join("\n",$_);
	}
	
	
	/**
	 * order()
	 * Handles new order notifications from Google */
	function order ($XML) {
		global $Shopp;
		$db = DB::get();

		$buyer = $XML->getElement('buyer-billing-address');
		$buyer = $buyer['CHILDREN'];
		$Customer = new Customer();
		
		$name = $XML->getElement('structured-name');
		$Customer->firstname = $buyer['structured-name']['CHILDREN']['first-name']['CONTENT'];
		$Customer->lastname = $buyer['structured-name']['CHILDREN']['last-name']['CONTENT'];
		if (empty($name)) {
			$name = $buyer['contact-name']['CONTENT'];
			$names = split(" ",$name);
			$Customer->firstname = $names[0];
			$Customer->lastname = $names[count($names)-1];
		}
		
		$Customer->email = $buyer['email']['CONTENT'];
		$Customer->phone = $buyer['phone']['CONTENT'];
		$Customer->save();

		$Billing = new Billing();
		$Billing->customer = $Customer->id;
		$Billing->address = $buyer['address1']['CONTENT'];
		$Billing->xaddress = $buyer['address2']['CONTENT'];
		$Billing->city = $buyer['city']['CONTENT'];
		$Billing->state = $buyer['region']['CONTENT'];
		$Billing->country = $buyer['country-code']['CONTENT'];
		$Billing->postcode = $buyer['postal-code']['CONTENT'];
		$Billing->save();
		
		$shipto = $XML->getElement('buyer-shipping-address');
		$shipto = $shipto['CHILDREN'];
		$Shipping = new Shipping();
		$Shipping->customer = $Customer->id;
		$Shipping->address = $shipto['address1']['CONTENT'];
		$Shipping->xaddress = $shipto['address2']['CONTENT'];
		$Shipping->city = $shipto['city']['CONTENT'];
		$Shipping->state = $shipto['region']['CONTENT'];
		$Shipping->country = $shipto['country-code']['CONTENT'];
		$Shipping->postcode = $shipto['postal-code']['CONTENT'];
		$Shipping->save();

		$Purchase = new Purchase();
		$Purchase->customer = $Customer->id;
		$Purchase->billing = $Billing->id;
		$Purchase->shipping = $Shipping->id;
		$Purchase->copydata($Customer);
		$Purchase->copydata($Billing);
		$Purchase->copydata($Shipping,'ship');
		$Purchase->freight = $XML->getElementContent('shipping-cost');
		$Purchase->tax = $XML->getElementContent('total-tax');
		$Purchase->total = $XML->getElementContent('order-total');
		$Purchase->subtotal = $Purchase->total-$Purchase->frieght-$Purchase->tax;
		$Purchase->gateway = "Google Checkout";
		$Purchase->transactionid = $XML->getElementContent('google-order-number');
		$Purchase->transtatus = $XML->getElementContent('financial-order-state');
		$Purchase->save();

		$items = $XML->getElement('item');
		if (key($items) == "CHILDREN") $items = array($items);
		foreach ($items as $item) {
			$item = $item['CHILDREN'];
			$Purchased = new Purchased();
			$Purchased->purchase = $Purchase->id;
			$Purchased->product = $item['merchant-product-id']['CONTENT'];
			$Purchased->price = $item['merchant-price-id']['CONTENT'];
			$Purchased->download = $item['merchant-download-id']['CONTENT'];
			if (!empty($Purchased->download)) $Purchased->keygen();
			$Purchased->name = addslashes($item['item-name']['CONTENT']);
			$Purchased->description = addslashes($item['item-description']['CONTENT']);
			$Purchased->sku = $item['merchant-item-id']['CONTENT'];
			$Purchased->quantity = $item['quantity']['CONTENT'];
			$Purchased->unitprice = $item['unit-price']['CONTENT'];
			$Purchased->total = $Purchased->quantity*$Purchased->unitprice;
			$Purchased->save();
		}
		
	}
	
	function risk ($XML) {
 		$id = $XML->getElementContent('google-order-number');
		$Purchase = new Purchase($id,'transactionid');
		$Purchase->ip = $XML->getElementContent('ip-address');
		$Purchase->card = $XML->getElementContent('partial-cc-number');
		$Purchase->save();
	}
	
	function state ($XML) {
 		$id = $XML->getElementContent('google-order-number');
		$state = $XML->getElementContent('new-financial-order-state');
		$Purchase = new Purchase($id,'transactionid');
		$Purchase->transtatus = $state;
		$Purchase->save();
		
		if (strtoupper($state) == "CHARGEABLE" && $this->settings['autocharge'] == "on") {
			$_ = array('<?xml version="1.0" encoding="utf-8"?>'."\n");
			$_[] = '<charge-order xmlns="'.$this->urls['schema'].'" google-order-number="'.$id.'">';
			$_[] = '<amount currency="USD">'.$Purchase->total.'</amount>';
			$_[] = '</charge-order>';
			$this->transaction = join("\n",$_);
			$Reponse = $this->send($this->urls['order']);
			exit();
		}
	}
	
	function send ($url) {
		$connection = curl_init();

		$type = "live";
		if ($this->settings['testmode'] == "on") $type = "test";
		$url = sprintf($url[$type],$this->settings['id'],$this->settings['key'],$this->settings['id']);

		curl_setopt($connection, CURLOPT_URL,$url);
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
		curl_close($connection);

		$Response = new XMLdata($buffer);
		return $Response;
	}
		
	function tag ($property,$options=array()) {
		global $Shopp;
		switch ($property) {
			case "button": 
				$type = "live";
				if ($this->settings['testmode'] == "on") $type = "test";
				$buttonuri = $this->urls['button'][$type];
				$buttonuri .= '?merchant_id='.$this->settings['id'];
				$buttonuri .= '&'.$this->settings['button'];
				$buttonuri .= '&style='.$this->settings['buttonstyle'];
				$buttonuri .= '&variant=text';
				$buttonuri .= '&loc='.$this->settings['location'];
				if (SHOPP_PERMALINKS) $url = $Shopp->link('checkout')."?shopp_xco=GoogleCheckout/GoogleCheckout";
				else $url = $Shopp->link('checkout')."&shopp_xco=GoogleCheckout/GoogleCheckout";
				return '<p class="submit"><a href="'.$url.'"><img src="'.$buttonuri.'" alt="Checkout with Google Checkout" /></a></p>';
		}
	}
	
	
	function settings () {
		global $Shopp;
		$buttons = array("w=160&h=43"=>"Small (160x43)","w=168&h=44"=>"Medium (168x44)","w=180&h=46"=>"Large (180x46)");
		$styles = array("white"=>"On White Background","trans"=>"With Transparent Background");
		
		?>
		<p><input type="text" name="settings[GoogleCheckout][id]" id="googlecheckout-id" size="18" value="<?php echo $this->settings['id']; ?>"/><br />
		Enter your Google Checkout merchant ID.</p>
		<p><input type="text" name="settings[GoogleCheckout][key]" id="googlecheckout-key" size="24" value="<?php echo $this->settings['key']; ?>" /><br />
		Enter your Google Checkout merchant key.</p>
		
		<?php if (!empty($this->settings['apiurl'])): ?>
		<p><input type="text" name="settings[GoogleCheckout][apiurl]" id="googlecheckout-apiurl" size="48" value="<?php echo $this->settings['apiurl']; ?>" readonly="readonly" class="select" /><br />
		<strong>Copy this URL to your Google Checkout integration settings API callback URL.</p>
		<?php endif;?>

		<p><select name="settings[GoogleCheckout][button]">
			<?php echo menuoptions($buttons,$this->settings['button'],true); ?>
			</select>
			<select name="settings[GoogleCheckout][buttonstyle]">
				<?php echo menuoptions($styles,$this->settings['buttonstyle'],true); ?>
				</select><br />Select the preferred size and style of the Google Checkout button.</p>
				<p><label for="googlecheckout-autocharge"><input type="hidden" name="settings[GoogleCheckout][autocharge]" value="off" /><input type="checkbox" name="settings[GoogleCheckout][autocharge]" id="googlecheckout-autocharge" size="48" value="on"<?php echo ($this->settings['autocharge'])?' checked="checked"':''; ?> /> Automatically charge orders</label></p>
		<p><label for="googlecheckout-testmode"><input type="hidden" name="settings[GoogleCheckout][testmode]" value="off" /><input type="checkbox" name="settings[GoogleCheckout][testmode]" id="googlecheckout-testmode" size="48" value="on"<?php echo ($this->settings['testmode'])?' checked="checked"':''; ?> /> Enable test mode</label></p>
		<?php
	}

} // end GoogleCheckout class

?>