<?php
/**
 * CheckoutAPITests
 *
 * @author Jonathan Davis
 * @version 1.0
 * @copyright Ingenesis Limited, 20 October, 2009
 * @package shopp
 **/
class CheckoutAPITests extends ShoppTestCase {

	public function test_checkout_url () {
		global $Shopp;
		$gateway = $Shopp->Settings->get('payment_gateway');
		$Shopp->Settings->registry['payment_gateway'] = "PayPalPro/PayPalPro.php";
		ob_start();
		shopp('checkout','url');
		$actual = ob_get_contents();
		ob_end_clean();
		$this->assertEquals('https://shopptest/store/checkout/',$actual);

		$Shopp->Settings->registry['payment_gateway'] = "TestMode/TestMode.php";
		ob_start();
		shopp('checkout','url');
		$actual = ob_get_contents();
		ob_end_clean();
		$this->assertEquals('http://shopptest/store/checkout/',$actual);
		$Shopp->Settings->registry['payment_gateway'] = $gateway;
	}

	function test_checkout_function () {
		ob_start();
		shopp('checkout','function');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'script',
			'attributes' => array('type' => 'text/javascript')
		);
		$this->assertTag($expected,$actual,'',true);

		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'hidden','name' => 'checkout')
		);
		$this->assertTag($expected,$actual,'',true);
		
		$this->assertValidMarkup($actual);
	}
	
	function test_checkout_error () {
		new ShoppError('Test Error');
		ob_start();
		shopp('checkout','error');
		$actual = ob_get_contents();
		ob_end_clean();
		$this->assertEquals('Test Error',$actual);
	}
	
	function test_checkout_cartsummary () {
		global $Shopp;
		$Shopp->Order->Cart->clear();
	
		$Product = new Product(81); $Price = false;
		$Shopp->Order->Cart->add(1,$Product,$Price,false);
		$Shopp->Order->Cart->totals();

		ob_start();
		shopp('checkout','cart-summary');
		$actual = ob_get_contents();
		ob_end_clean();
		$this->assertTrue(!empty($actual));
		$this->assertValidMarkup($actual);
	}

	function test_checkout_notloggedin () {
		global $Shopp;
		$Shopp->Settings->registry['account_system'] = 'wordpress';
		$this->assertTrue(shopp('checkout','notloggedin'));
	}
	
	function test_checkout_loggedin () {
		global $Shopp;
		$this->assertFalse(shopp('checkout','loggedin'));
		$Account = new Customer();
		$Shopp->Order->Cart->loggedin($Account);
		$this->assertTrue(shopp('checkout','loggedin'));
	}
	
	function test_checkout_accountlogin () {
		ob_start();
		shopp('checkout','account-login');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'account-login','id' => 'account-login')
		);
		$this->assertTag($expected,$actual,'',true);
		
		$this->assertValidMarkup($actual);
	}

	function test_checkout_passwordlogin () {
		ob_start();
		shopp('checkout','password-login');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'password','name' => 'password-login','id' => 'password-login')
		);
		$this->assertTag($expected,$actual,'',true);
		
		$this->assertValidMarkup($actual);
	}

	function test_checkout_loginbutton () {
		ob_start();
		shopp('checkout','login-button');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'hidden','name' => 'process-login','id' => 'process-login','value' => 'false')
		);
		$this->assertTag($expected,$actual,'',true);

		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'submit','name' => 'submit-login','id' => 'submit-login')
		);
		$this->assertTag($expected,$actual,'',true);
		
		$this->assertValidMarkup($actual);
	}

	function test_checkout_firstname () {
		ob_start();
		shopp('checkout','firstname');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'firstname','id' => 'firstname')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}
	
	function test_checkout_lastname () {
		ob_start();
		shopp('checkout','lastname');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'lastname','id' => 'lastname')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_email () {
		ob_start();
		shopp('checkout','email');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'email','id' => 'email')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_loginname () {
		ob_start();
		shopp('checkout','loginname');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'login','id' => 'login')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}
	
	function test_checkout_password () {
		ob_start();
		shopp('checkout','password');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'password','name' => 'password','id' => 'password')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_confirmpassword () {
		ob_start();
		shopp('checkout','confirm-password');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'password','name' => 'confirm-password','id' => 'confirm-password')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_phone () {
		ob_start();
		shopp('checkout','phone');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'phone','id' => 'phone')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}
	
	function test_checkout_company () {
		ob_start();
		shopp('checkout','company');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'company','id' => 'company')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_customerinfo () {
		ob_start();
		shopp('checkout','customer-info','type=text&name=Test');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'info[Test]','id' => 'customer-info-Test')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}
	
	function test_checkout_shipping () {
		$this->assertTrue(shopp('checkout','shipping'));
	}
	
	function test_checkout_shipping_address () {
		ob_start();
		shopp('checkout','shipping-address');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'shipping[address]','id' => 'shipping-address')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_shipping_xaddress () {
		ob_start();
		shopp('checkout','shipping-xaddress');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'shipping[xaddress]','id' => 'shipping-xaddress')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}
	
	function test_checkout_shipping_city () {
		ob_start();
		shopp('checkout','shipping-city');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'shipping[city]','id' => 'shipping-city')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_shipping_state () {
		ob_start();
		shopp('checkout','shipping-state');
		$actual = ob_get_contents();
		ob_end_clean();

		$expected = array(
			'tag' => 'select',
			'attributes' => array('name' => 'shipping[state]','id' => 'shipping-state')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);

		ob_start();
		shopp('checkout','shipping-state','type=text');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'shipping[state]','id' => 'shipping-state')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}
	
	function test_checkout_shipping_postcode () {
		ob_start();
		shopp('checkout','shipping-postcode');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'shipping[postcode]','id' => 'shipping-postcode')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_shipping_country () {
		ob_start();
		shopp('checkout','shipping-country');
		$actual = ob_get_contents();
		ob_end_clean();

		$expected = array(
			'tag' => 'select',
			'attributes' => array('name' => 'shipping[country]','id' => 'shipping-country')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_sameshippingaddress () {
		ob_start();
		shopp('checkout','same-shipping-address');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'checkbox','name' => 'sameshipaddress','id' => 'same-shipping')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_billingrequired () {
		$this->assertTrue(shopp('checkout','billing-required'));
	}

	function test_checkout_billing_address () {
		ob_start();
		shopp('checkout','billing-address');
		$actual = ob_get_contents();
		ob_end_clean();

		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'billing[address]','id' => 'billing-address')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_billing_xaddress () {
		ob_start();
		shopp('checkout','billing-xaddress');
		$actual = ob_get_contents();
		ob_end_clean();

		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'billing[xaddress]','id' => 'billing-xaddress')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_billing_city () {
		ob_start();
		shopp('checkout','billing-city');
		$actual = ob_get_contents();
		ob_end_clean();

		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'billing[city]','id' => 'billing-city')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_billing_state () {
		ob_start();
		shopp('checkout','billing-state');
		$actual = ob_get_contents();
		ob_end_clean();

		$expected = array(
			'tag' => 'select',
			'attributes' => array('name' => 'billing[state]','id' => 'billing-state')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);

		ob_start();
		shopp('checkout','billing-state','type=text');
		$actual = ob_get_contents();
		ob_end_clean();

		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'billing[state]','id' => 'billing-state')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_billing_postcode () {
		ob_start();
		shopp('checkout','billing-postcode');
		$actual = ob_get_contents();
		ob_end_clean();

		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'billing[postcode]','id' => 'billing-postcode')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_billing_country () {
		ob_start();
		shopp('checkout','billing-country');
		$actual = ob_get_contents();
		ob_end_clean();

		$expected = array(
			'tag' => 'select',
			'attributes' => array('name' => 'billing[country]','id' => 'billing-country')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_billing_card () {
		ob_start();
		shopp('checkout','billing-card');
		$actual = ob_get_contents();
		ob_end_clean();

		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'billing[card]','id' => 'billing-card')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_billing_cardtype () {
		ob_start();
		shopp('checkout','billing-cardtype');
		$actual = ob_get_contents();
		ob_end_clean();

		$expected = array(
			'tag' => 'select',
			'attributes' => array('name' => 'billing[cardtype]','id' => 'billing-cardtype')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_billing_cardexpires_mm () {
		ob_start();
		shopp('checkout','billing-cardexpires-mm');
		$actual = ob_get_contents();
		ob_end_clean();

		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'billing[cardexpires-mm]','id' => 'billing-cardexpires-mm')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_billing_cardexpires_yy () {
		ob_start();
		shopp('checkout','billing-cardexpires-yy');
		$actual = ob_get_contents();
		ob_end_clean();

		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'billing[cardexpires-yy]','id' => 'billing-cardexpires-yy')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}
	
	function test_checkout_billing_cardholder () {
		ob_start();
		shopp('checkout','billing-cardholder');
		$actual = ob_get_contents();
		ob_end_clean();

		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'billing[cardholder]','id' => 'billing-cardholder')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}
	
	function test_checkout_billing_cvv () {
		ob_start();
		shopp('checkout','billing-cvv');
		$actual = ob_get_contents();
		ob_end_clean();

		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'billing[cvv]','id' => 'billing-cvv')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_billing_xco () { /* TODO */ }

	function test_checkout_orderdata () {
		ob_start();
		shopp('checkout','order-data','type=text&name=Test');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'text','name' => 'data[Test]','id' => 'order-data-Test')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_submit () {
		ob_start();
		shopp('checkout','submit');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'submit','name' => 'process','id' => 'checkout-button')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_confirmbutton () {
		ob_start();
		shopp('checkout','confirm-button');
		$actual = ob_get_contents();
		ob_end_clean();
		
		$expected = array(
			'tag' => 'input',
			'attributes' => array('type' => 'submit','name' => 'confirmed','id' => 'confirm-button')
		);
		$this->assertTag($expected,$actual,'',true);
		$this->assertValidMarkup($actual);
	}

	function test_checkout_localpayment () {
		$this->assertTrue(shopp('checkout','localpayment'));
	}

	function test_checkout_xcobuttons () {	/* TODO */ }
	
	

} // end CheckoutAPITests class

?>