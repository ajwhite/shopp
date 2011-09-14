<?php
/**
* ProductDevAPITests - tests for the product dev api
*/
class ProductDevAPITests extends ShoppTestCase
{

	function test_shopp_add_product () {
		$data = array(
			'name' => "St. John's Bay® Color Block Windbreaker",
			'publish' => array( 'flag' => true,
								'publishtime' => array('month' => 12,
								'day' => 25,
								'year' => 2011,
								'hour' => 0,
								'minute' => 0,
								'meridian' => 'AM')
			 					),
			'description' => "This water-repellent windbreaker offers lightweight protection on those gusty days.

			hood with drawstring
			zip front
			2 inner pockets
			on-seam pockets
			contrast side panels
			elastic cuffs
			side elastic on bottom
			contrast mesh lining
			polyester microfiber
			polyester mesh lining
			washable
			imported",
			'summary' => "This water-repellent windbreaker offers lightweight protection on those gusty days.",
			'featured' => true,
			'categories'=> array('terms' => array(5)),
			'tags'=>array('terms'=>array('action')),
			'specs'=>array('pockets'=>2, 'drawstring'=>'yes','washable'=>'yes'),
			'variants'=>array(
				'menu' => array(
					'Size' => array('medium','large','x-large','small','xx-large','large-tall','x-large tall','2x-large tall','2x-large'),
					'Color' => array('Black/Grey Colorbi', 'Navy Baby Solid','Red/Iron Colorbloc','Iron Solid','Dark Avocado Soil')
				)
			),
			'addons'=> array(
				'menu' => array('Special' => array('Embroidered'))
			),
			'packaging' => true
			// 'processing' => array( 'flag' => true, 'min' => array('interval'=>3,'period'=>'d'), 'max' => array('interval'=>5,'period'=>'d'))  // order processing adds from 3 to 5 days. (not implemented yet)
		);

		$data['variants'][] = array(
			'option' => array('Size'=>'medium', 'Color' => 'Navy Baby Solid'),
			'type' => 'Shipped',
			'price' => 40.00,
			'sale' => array('flag'=>true, 'price' => 19.99),
			'shipping' => array('flag'=>true, 'fee'=>1.50, 'weight'=>1.1, 'length'=>10.0, 'width'=>10.0, 'height'=>2.0),
			'inventory'=>array('flag'=>true, 'stock'=>10, 'sku'=>'WINDBREAKER1')
		);
		$data['addons'][] = array(
			'option' => array('Special'=>'Embroidered'),
			'type' => 'Shipped',
			'price' => 10.00
		);

		$Product = shopp_add_product($data);

		// Load fresh for testing
		$Product = new Product(130);
		$Product->load_data();

		$this->AssertEquals(130, $Product->id);
		$this->AssertEquals('St. John\'s Bay® Color Block Windbreaker',$Product->name);
		$this->AssertEquals('This water-repellent windbreaker offers lightweight protection on those gusty days.',$Product->summary);
		$this->AssertEquals('on', $Product->featured);
		$this->AssertEquals('on', $Product->sale);
		$this->AssertEquals(40.0, $Product->maxprice);
		$this->AssertEquals(0.0, $Product->minprice);
		$this->AssertEquals('on', $Product->packaging);
		$this->AssertEquals('a:2:{s:1:"v";a:2:{i:1;a:3:{s:2:"id";i:1;s:4:"name";s:4:"Size";s:7:"options";a:9:{i:1;a:3:{s:2:"id";i:1;s:4:"name";s:6:"medium";s:6:"linked";s:3:"off";}i:2;a:3:{s:2:"id";i:2;s:4:"name";s:5:"large";s:6:"linked";s:3:"off";}i:3;a:3:{s:2:"id";i:3;s:4:"name";s:7:"x-large";s:6:"linked";s:3:"off";}i:4;a:3:{s:2:"id";i:4;s:4:"name";s:5:"small";s:6:"linked";s:3:"off";}i:5;a:3:{s:2:"id";i:5;s:4:"name";s:8:"xx-large";s:6:"linked";s:3:"off";}i:6;a:3:{s:2:"id";i:6;s:4:"name";s:10:"large-tall";s:6:"linked";s:3:"off";}i:7;a:3:{s:2:"id";i:7;s:4:"name";s:12:"x-large tall";s:6:"linked";s:3:"off";}i:8;a:3:{s:2:"id";i:8;s:4:"name";s:13:"2x-large tall";s:6:"linked";s:3:"off";}i:9;a:3:{s:2:"id";i:9;s:4:"name";s:8:"2x-large";s:6:"linked";s:3:"off";}}}i:2;a:3:{s:2:"id";i:2;s:4:"name";s:5:"Color";s:7:"options";a:5:{i:10;a:3:{s:2:"id";i:10;s:4:"name";s:18:"Black/Grey Colorbi";s:6:"linked";s:3:"off";}i:11;a:3:{s:2:"id";i:11;s:4:"name";s:15:"Navy Baby Solid";s:6:"linked";s:3:"off";}i:12;a:3:{s:2:"id";i:12;s:4:"name";s:18:"Red/Iron Colorbloc";s:6:"linked";s:3:"off";}i:13;a:3:{s:2:"id";i:13;s:4:"name";s:10:"Iron Solid";s:6:"linked";s:3:"off";}i:14;a:3:{s:2:"id";i:14;s:4:"name";s:17:"Dark Avocado Soil";s:6:"linked";s:3:"off";}}}}s:1:"a";a:1:{i:1;a:3:{s:2:"id";i:1;s:4:"name";s:7:"Special";s:7:"options";a:1:{i:1;a:3:{s:2:"id";i:1;s:4:"name";s:11:"Embroidered";s:6:"linked";s:3:"off";}}}}}',
							serialize($Product->options));
		$this->AssertEquals(46, count($Product->prices));

		$counts = array('product'=>0,'addon'=>0,'variation'=>0);
		$Variant = $Addon = false;
		foreach ( $Product->prices as $index => $Price ) {
			$counts[$Price->context]++;
			if ( 7001 == $Price->optionkey ) $Addon = &$Product->prices[$index];
			if ( 79754 == $Price->optionkey ) $Variant = &$Product->prices[$index];
		}

		$this->AssertEquals(45, $counts['variation']);
		$this->AssertEquals(1, $counts['addon']);
		$this->AssertEquals(0, $counts['product']);

		// Variant assertions
		$this->AssertEquals('1,11',$Variant->options);
		$this->AssertEquals('medium, Navy Baby Solid', $Variant->label);
		$this->AssertEquals('Shipped', $Variant->type);
		$this->AssertEquals('variation', $Variant->context);
		$this->AssertEquals('on', $Variant->sale);
		$this->AssertEquals(40.00, $Variant->price);
		$this->AssertEquals(19.99, $Variant->promoprice);
		$this->AssertEquals(19.99, $Variant->saleprice);
		$this->AssertEquals('on', $Variant->tax);
		$this->AssertEquals('on', $Variant->shipping);
		$this->AssertEquals('a:4:{s:6:"weight";d:1.1000000000000001;s:6:"height";d:2;s:5:"width";d:10;s:6:"length";d:10;}',serialize($Variant->dimensions));
		$this->AssertEquals(1.5, $Variant->shipfee);
		$this->AssertEquals('on', $Variant->inventory);
		$this->AssertEquals(10, $Variant->stock);
		$this->AssertEquals(10, $Variant->stocked);
		$this->AssertEquals('WINDBREAKER1', $Variant->sku);

		$this->AssertEquals('1',$Addon->options);
		$this->AssertEquals('Embroidered', $Addon->label);
		$this->AssertEquals('Shipped', $Addon->type);
		$this->AssertEquals('addon', $Addon->context);
		$this->AssertEquals('off', $Addon->sale);
		$this->AssertEquals(10, $Addon->price);
		$this->AssertEquals('on', $Addon->tax);
		$this->AssertEquals('on', $Addon->shipping);
		$this->AssertEquals(0, $Addon->shipfee);
		$this->AssertEquals('off', $Addon->inventory);

	}

	function test_shopp_product () {
		$Product = shopp_product( 107 );

		$this->AssertEquals(107, $Product->id);
		$this->AssertEquals(
			'a:1:{i:0;O:8:"stdClass":29:{s:2:"id";s:3:"190";s:7:"product";s:3:"107";s:7:"options";s:0:"";s:9:"optionkey";s:1:"0";s:5:"label";s:16:"Price & Delivery";s:7:"context";s:7:"product";s:4:"type";s:7:"Shipped";s:3:"sku";s:0:"";s:5:"price";d:49;s:9:"saleprice";d:44;s:6:"weight";s:8:"0.500000";s:7:"shipfee";d:0;s:5:"stock";s:1:"0";s:9:"inventory";s:3:"off";s:4:"sale";s:2:"on";s:8:"shipping";s:2:"on";s:3:"tax";s:2:"on";s:8:"donation";a:2:{s:3:"var";s:3:"off";s:3:"min";s:3:"off";}s:9:"sortorder";s:1:"1";s:7:"created";s:19:"2009-10-13 15:05:56";s:8:"modified";s:19:"2009-10-13 15:05:56";s:10:"dimensions";a:0:{}s:10:"promoprice";d:44;s:4:"cost";s:8:"0.000000";s:7:"stocked";s:1:"0";s:9:"discounts";s:0:"";s:12:"freeshipping";b:0;s:9:"isstocked";b:0;s:6:"onsale";b:1;}}',
			serialize($Product->prices)
		);
	}

	function test_shopp_product_publish () {
		shopp_product_publish ( 107, false );
		$Product = shopp_product( 107 );
		$this->AssertEquals('draft', $Product->status);

		shopp_product_publish ( 107, true, mktime( 12, 0, 0, 12, 1, 2011) );
		$Product = shopp_product( 107 );
		$this->AssertEquals('future', $Product->status);
		$this->AssertEquals($Product->publish, mktime( 12, 0, 0, 12, 1, 2011));

		shopp_product_publish ( 107, true );
		$Product = shopp_product( 107 );
		$this->AssertEquals('publish', $Product->status);
		$this->assertTrue(time() >= $Product->publish);
	}

	function test_shopp_product_specs () {
		$specs = shopp_product_specs( 121 );
		$this->assertTrue(in_array('Model No.', array_keys($specs)));
		$this->assertTrue(in_array('Gender', array_keys($specs)));
		$this->AssertEquals(116, $specs['Model No.']->value);
		$this->AssertEquals('Women', $specs['Gender']->value);
	}

	function test_shopp_product_variants () {
		$variations = shopp_product_variants(70);
		$expected = 'a:2:{i:0;O:8:"stdClass":29:{s:2:"id";s:3:"119";s:7:"product";s:2:"70";s:7:"options";s:2:"11";s:9:"optionkey";s:5:"77011";s:5:"label";s:10:"Widescreen";s:7:"context";s:9:"variation";s:4:"type";s:7:"Shipped";s:3:"sku";s:0:"";s:5:"price";d:59.979999999999997;s:9:"saleprice";d:34.860000999999997;s:6:"weight";s:8:"1.000000";s:7:"shipfee";d:0;s:5:"stock";s:1:"0";s:9:"inventory";s:3:"off";s:4:"sale";s:2:"on";s:8:"shipping";s:2:"on";s:3:"tax";s:2:"on";s:8:"donation";a:2:{s:3:"var";s:3:"off";s:3:"min";s:3:"off";}s:9:"sortorder";s:1:"2";s:7:"created";s:19:"2009-10-13 14:05:04";s:8:"modified";s:19:"2009-10-13 14:12:03";s:10:"dimensions";a:0:{}s:10:"promoprice";d:34.860000999999997;s:4:"cost";s:8:"0.000000";s:7:"stocked";s:1:"0";s:9:"discounts";s:0:"";s:12:"freeshipping";b:0;s:9:"isstocked";b:0;s:6:"onsale";b:1;}i:1;O:8:"stdClass":29:{s:2:"id";s:3:"120";s:7:"product";s:2:"70";s:7:"options";s:2:"12";s:9:"optionkey";s:5:"84012";s:5:"label";s:11:"Full-Screen";s:7:"context";s:9:"variation";s:4:"type";s:7:"Shipped";s:3:"sku";s:0:"";s:5:"price";d:59.979999999999997;s:9:"saleprice";d:34.860000999999997;s:6:"weight";s:8:"1.000000";s:7:"shipfee";d:0;s:5:"stock";s:1:"0";s:9:"inventory";s:3:"off";s:4:"sale";s:2:"on";s:8:"shipping";s:2:"on";s:3:"tax";s:2:"on";s:8:"donation";a:2:{s:3:"var";s:3:"off";s:3:"min";s:3:"off";}s:9:"sortorder";s:1:"3";s:7:"created";s:19:"2009-10-13 14:05:04";s:8:"modified";s:19:"2009-10-13 14:12:03";s:10:"dimensions";a:0:{}s:10:"promoprice";d:34.860000999999997;s:4:"cost";s:8:"0.000000";s:7:"stocked";s:1:"0";s:9:"discounts";s:0:"";s:12:"freeshipping";b:0;s:9:"isstocked";b:0;s:6:"onsale";b:1;}}';

		$actual = serialize($variations);
		$this->AssertEquals($expected,$actual);
	}

	function test_shopp_product_addons () {
		$addons = shopp_product_addons(130);
		$testing = array (
		'id' => 302,
	        'product' => 130,
	        'options' => 1,
	        'optionkey' => 7001,
	        'label' => 'Embroidered',
	        'context' => 'addon',
	        'type' => 'Shipped',
	        'price' => 10
		);
		foreach ( $testing as $key => $value ) {
			$this->AssertEquals($addons[0]->$key, $value);
		}
	}

	function test_shopp_product_variant () {
		$Price = shopp_product_variant(array( 'product' => 130, 'option' => array('Size'=>'medium', 'Color'=>'Navy Baby Solid')), 'variant');
		$this->AssertEquals(79754, $Price->optionkey);
		$this->AssertEquals('medium, Navy Baby Solid', $Price->label);
		$this->AssertEquals('variation', $Price->context);

		$Price = shopp_product_variant(array( 'product' => 130, 'option' => array('Special' => 'Embroidered') ), 'addon' );
		$this->AssertEquals(7001, $Price->optionkey);
		$this->AssertEquals('Embroidered', $Price->label);
		$this->AssertEquals('addon', $Price->context);

		$Price = shopp_product_variant(array( 'product' => 31), 'product');
		$this->AssertEquals(42, $Price->id);
		$this->AssertEquals('Price & Delivery', $Price->label);
		$this->AssertEquals('product', $Price->context);

		$Price = shopp_product_variant(258);
		$this->AssertEquals(79754, $Price->optionkey);
		$this->AssertEquals('medium, Navy Baby Solid', $Price->label);
		$this->AssertEquals('variation', $Price->context);

	}

	function test_shopp_product_addon () {
		$Price = shopp_product_addon(array( 'product' => 130, 'option' => array('Special' => 'Embroidered') ) );
		$this->AssertEquals(7001, $Price->optionkey);
		$this->AssertEquals('Embroidered', $Price->label);
		$this->AssertEquals('addon', $Price->context);

		unset($Price);
		$Price = shopp_product_addon(302);
		$this->AssertEquals(7001, $Price->optionkey);
		$this->AssertEquals('Embroidered', $Price->label);
		$this->AssertEquals('addon', $Price->context);

	}

	function test_shopp_product_variant_options () {
		$options = shopp_product_variant_options(130);
		$this->AssertEquals('a:2:{s:4:"Size";a:9:{i:0;s:6:"medium";i:1;s:5:"large";i:2;s:7:"x-large";i:3;s:5:"small";i:4;s:8:"xx-large";i:5;s:10:"large-tall";i:6;s:12:"x-large tall";i:7;s:13:"2x-large tall";i:8;s:8:"2x-large";}s:5:"Color";a:5:{i:0;s:18:"Black/Grey Colorbi";i:1;s:15:"Navy Baby Solid";i:2;s:18:"Red/Iron Colorbloc";i:3;s:10:"Iron Solid";i:4;s:17:"Dark Avocado Soil";}}',
		serialize($options));
	}

	function test_shopp_product_addon_options () {
		$addon_options = shopp_product_addon_options ( 130 );
		$this->AssertEquals('a:1:{s:7:"Special";a:1:{i:0;s:11:"Embroidered";}}', serialize($addon_options));
	}

	function test_shopp_product_add_categories () {
		$category = shopp_add_product_category ( 'Jackets', "Men's Jackets", 5 );
		$this->assertTrue(shopp_product_add_categories(130, array($category)));
		$this->AssertEquals(62, $category);

		$Product = shopp_product(130);

		$this->assertTrue(isset($Product->categories[62]));
		$this->AssertEquals('jackets', $Product->categories[62]->slug);
		$this->AssertEquals('Jackets', $Product->categories[62]->name);
		$this->AssertEquals("Men's Jackets", $Product->categories[62]->description);
	}

	function test_shopp_product_add_tags () {
		$tag = shopp_add_product_tag ( 'Waterproof' );
		$tag2 = shopp_add_product_tag ( 'Fashionable' );
		$this->AssertTrue( shopp_product_add_tags(130, array($tag, 'Fashionable')) );

		$Product = shopp_product(130);
		$this->AssertEquals('Waterproof', $Product->tags[$tag]->name);
		$this->AssertEquals('Fashionable', $Product->tags[$tag2]->name);
	}

	function test_shopp_product_set_specs () {
		shopp_product_rmv_spec(130, 'pockets');
		shopp_product_rmv_spec(130, 'drawstring');
		shopp_product_rmv_spec(130, 'washable');

		$Product = shopp_product(130);

		$this->assertTrue(! isset($Product->specs) || empty($Product->specs));

		$specs = array('pockets'=>2, 'drawstring'=>'yes','washable'=>'yes');
		shopp_product_set_specs ( 130, $specs);

		$Specs = shopp_product_specs(130);

		$this->AssertEquals(2, $Specs['pockets']->value);
		$this->AssertEquals('yes', $Specs['drawstring']->value);
		$this->AssertEquals('yes', $Specs['washable']->value);
	}

	function test_shopp_product_add_terms () {
		shopp_register_taxonomy('brand', array(
	        'hierarchical' => true
	    ));

		$term = shopp_add_product_term("Domestic Brands", 'shopp_brand');
		$term1 = shopp_add_product_term("St. John's Bay", 'shopp_brand', $term);

		shopp_product_add_terms(130, array($term,$term1), 'shopp_brand');
		$Product = shopp_product(130);

		$this->AssertEquals("Domestic Brands", $Product->shopp_brands[$term]->name);
		$this->AssertEquals("domestic-brands", $Product->shopp_brands[$term]->slug);

		$this->AssertEquals("St. John's Bay", $Product->shopp_brands[$term1]->name);
		$this->AssertEquals("st-johns-bay", $Product->shopp_brands[$term1]->slug);
		$this->AssertEquals($term, $Product->shopp_brands[$term1]->parent);
	}

	function test_shopp_product_set_variant () {
		global $lastpid;
		// Create new product for subscription
		$data = array(
			'name' => "Site Subscription",
			'publish' => array( 'flag' => true ),
			'description' =>
				"Subscription to our site.\n".
				"Off monthly and annual.",
			'summary' => "Subscription to our site.",
			'featured' => true,
			'variants'=>array(
				'menu' => array(
					'Access' => array('Standard','Premium','Donate'),
					'Billing' => array('One-Time','Monthly', 'Annual')
				)
			)
		);

		$Product = shopp_add_product($data);
		$pid = $Product->id;

		$StandardMonthly = shopp_product_variant(array('product' => $pid, 'option' => array('Access'=>'Standard', 'Billing'=>'Monthly')), 'variant');
		$standard_monthly = array(
			'type' => 'Subscription',
			'price' => 15.99,
			'sale' => array('flag'=>true, 'price'=>9.99),
			'subscription' => array(
				'trial' => array(
					'price' => 4.99,
					'cycle' => array(
						'interval' => 30,
						'period' => 'd'
					)
				),
				'billcycle' => array(
					'cycles' => 12,
					'cycle' => array(
						'interval' => 1,
						'period' => 'm'
					)
				)
			)
		);
		$this->AssertTrue(shopp_product_set_variant($StandardMonthly->id, $standard_monthly));

		$StandardAnnual = shopp_product_variant(array('product' => $pid, 'option' => array('Access'=>'Standard', 'Billing'=>'Annual')), 'variant');
		$standard_annual = array(
			'type' => 'Subscription',
			'price' => 149.99,
			'sale' => array('flag'=>true, 'price'=>99.99),
			'subscription' => array(
				'billcycle' => array(
					'cycles' => 12,
					'cycle' => array(
						'interval' => 0,
						'period' => 'y'
					)
				)
			)
		);
		$this->AssertTrue(shopp_product_set_variant($StandardAnnual->id, $standard_annual));


		$PremiumMonthly = shopp_product_variant(array('product' => $pid, 'option' => array('Access'=>'Premium', 'Billing'=>'Monthly')), 'variant');
		$premium_monthly = array(
			'type' => 'Subscription',
			'price' => 25.99,
			'sale' => array('flag'=>true, 'price'=>19.99),
			'subscription' => array(
				'trial' => array(
					'price' => 14.99,
					'cycle' => array(
						'interval' => 30,
						'period' => 'd'
					)
				),
				'billcycle' => array(
					'cycles' => 12,
					'cycle' => array(
						'interval' => 1,
						'period' => 'm'
					)
				)
			)
		);
		$this->AssertTrue(shopp_product_set_variant($PremiumMonthly->id, $premium_monthly));

		$PremiumAnnual = shopp_product_variant(array('product' => $pid, 'option' => array('Access'=>'Premium', 'Billing'=>'Annual')), 'variant');
		$premium_annual = array(
			'type' => 'Subscription',
			'price' => 269.99,
			'sale' => array('flag'=>true, 'price'=>219.99),
			'subscription' => array(
				'billcycle' => array(
					'cycles' => 12,
					'cycle' => array(
						'interval' => 0,
						'period' => 'y'
					)
				)
			)
		);
		$this->AssertTrue(shopp_product_set_variant($PremiumAnnual->id, $premium_annual));

		$StandardMonthly = shopp_product_variant($StandardMonthly->id);
		$PremiumMonthly = shopp_product_variant($PremiumMonthly->id);
		$StandardAnnual = shopp_product_variant($StandardAnnual->id);
		$PremiumAnnual = shopp_product_variant($PremiumAnnual->id);

		$this->AssertEquals("on", $StandardMonthly->recurring['trial']);
		$this->AssertEquals(4.99, $StandardMonthly->recurring['trialprice']);
		$this->AssertEquals(30, $StandardMonthly->recurring['trialint']);
		$this->AssertEquals('d', $StandardMonthly->recurring['trialperiod']);
		$this->AssertEquals(12, $StandardMonthly->recurring['cycles']);
		$this->AssertEquals(1, $StandardMonthly->recurring['interval']);
		$this->AssertEquals('m', $StandardMonthly->recurring['period']);

		$this->AssertEquals("on", $PremiumMonthly->recurring['trial']);
		$this->AssertEquals(14.99, $PremiumMonthly->recurring['trialprice']);
		$this->AssertEquals(30, $PremiumMonthly->recurring['trialint']);
		$this->AssertEquals('d', $PremiumMonthly->recurring['trialperiod']);
		$this->AssertEquals(12, $PremiumMonthly->recurring['cycles']);
		$this->AssertEquals(1, $PremiumMonthly->recurring['interval']);
		$this->AssertEquals('m', $PremiumMonthly->recurring['period']);

		$this->AssertEquals(12, $StandardAnnual->recurring['cycles']);
		$this->AssertEquals(0, $StandardAnnual->recurring['interval']);
		$this->AssertEquals('y', $StandardAnnual->recurring['period']);

		$this->AssertEquals(12, $PremiumAnnual->recurring['cycles']);
		$this->AssertEquals(0, $PremiumAnnual->recurring['interval']);
		$this->AssertEquals('y', $PremiumAnnual->recurring['period']);

		$DonateOnetime = shopp_product_variant(array('product' => $pid, 'option' => array('Access'=>'Donate', 'Billing'=>'One-Time')), 'variant');
		$donate_onetime = array(
			'type' => 'Donation',
			'price' => 10.00,
			'donation' => array(
				'variable'=> true,
				'minimum' => true
			)
		);

		$this->AssertTrue(shopp_product_set_variant($DonateOnetime->id, $donate_onetime));
		$DonateOnetime = shopp_product_variant(array('product' => $pid, 'option' => array('Access'=>'Donate', 'Billing'=>'One-Time')), 'variant');

		$this->AssertEquals('on', $DonateOnetime->donation['var']);
		$this->AssertEquals('on', $DonateOnetime->donation['min']);
		$lastpid = $pid;
	}

	function test_shopp_product_variant_set_subscription () {
		global $lastpid;
		$pid = $lastpid;
		$PremiumAnnual = shopp_product_variant(array('product' => $pid, 'option' => array('Access'=>'Premium', 'Billing'=>'Annual')), 'variant');
		$settings =
		array(
			// free 7 day trial
			'trial' => array(
				'price' => 0.00,
				'cycle' => array(
					'interval' => 7,
					'period' => 'd'
				)
				),
			'billcycle' =>
			array(
				'cycles' => 0,		// 0 for forever, int number of cycles to repeat the billing
				'cycle' =>
				array (
					'interval' => 12, // how many units of the period before the next billing cycle (day,week,month,year)
					'period' => 'm'  // d for days, w for weeks, m for months, y for years
				)
			)
		);
		shopp_product_variant_set_subscription ( $PremiumAnnual->id, $settings );
		$test = shopp_product_variant(array('product' => $pid, 'option' => array('Access'=>'Premium', 'Billing'=>'Annual')), 'variant');
		$this->AssertEquals('on', $test->recurring['trial']);
		$this->AssertEquals(0, $test->recurring['trialprice']);
		$this->AssertEquals(7, $test->recurring['trialint']);
		$this->AssertEquals('d', $test->recurring['trialperiod']);
		$this->AssertEquals(0, $test->recurring['cycles']);
		$this->AssertEquals(12, $test->recurring['interval']);
		$this->AssertEquals('m', $test->recurring['period']);

	}

	function test_shopp_product_set_addon_options () {
		$data = array(
			'name' => "Motorcycle",
			'publish' => array( 'flag' => true ),
			'description' =>
				"Testing shopp_product_set_addon_options"
		);

		$Product = shopp_add_product($data);

		$options = array(
			'Accessories' => array('Helmet', 'Decals', 'Plate Mount'),
			'Apparel' => array('T-Shirt', 'Chaps')
		);

		shopp_product_set_addon_options ( $Product->id, $options, 'save' );

		$Helmet = shopp_product_variant(array('product'=>$Product->id, 'option'=>array('Accessories'=>'Helmet')), 'addon');
		$this->AssertEquals('Helmet', $Helmet->label);
		$this->AssertEquals(1, $Helmet->options);
		$this->AssertEquals(7001, $Helmet->optionkey);
		$this->AssertEquals('addon', $Helmet->context);

		$Decals = shopp_product_variant(array('product'=>$Product->id, 'option'=>array('Accessories'=>'Decals')), 'addon');
		$this->AssertEquals('Decals', $Decals->label);
		$this->AssertEquals(2, $Decals->options);
		$this->AssertEquals(14002, $Decals->optionkey);
		$this->AssertEquals('addon', $Decals->context);

		$PlateMount = shopp_product_variant(array('product'=>$Product->id, 'option'=>array('Accessories'=>'Plate Mount')), 'addon');
		$this->AssertEquals('Plate Mount', $PlateMount->label);
		$this->AssertEquals(3, $PlateMount->options);
		$this->AssertEquals(21003, $PlateMount->optionkey);
		$this->AssertEquals('addon', $PlateMount->context);

		$TShirt = shopp_product_variant(array('product'=>$Product->id, 'option'=>array('Apparel'=>'T-Shirt')), 'addon');
		$this->AssertEquals('T-Shirt', $TShirt->label);
		$this->AssertEquals(4, $TShirt->options);
		$this->AssertEquals(28004, $TShirt->optionkey);
		$this->AssertEquals('addon', $TShirt->context);

		$Chaps = shopp_product_variant(array('product'=>$Product->id, 'option'=>array('Apparel'=>'Chaps')), 'addon');
		$this->AssertEquals('Chaps', $Chaps->label);
		$this->AssertEquals(5, $Chaps->options);
		$this->AssertEquals(35005, $Chaps->optionkey);
		$this->AssertEquals('addon', $Chaps->context);
	}

	function test_shopp_product_variant_set_type() {
		$data = array(
			'name' => "Mixed Type Product",
			'single' => array(),
			'publish' => array( 'flag' => true ),
			'description' =>
				"Testing shopp_product_variant_set_type"
		);

		$Product = shopp_add_product($data);

		$Pricetag = shopp_product_variant( array( 'product'=>$Product->id ), 'product' );

		// set the product type to Download
		shopp_product_variant_set_type($Pricetag->id, 'Download', 'product');

		$options = array(
			'Bonus' => array('Call from Artist', 'Magazine Subscription')
		);

		shopp_product_set_addon_options ( $Product->id, $options, 'save' );
		$Call = shopp_product_variant(array('product'=>$Product->id, 'option' => array('Bonus'=>'Call from Artist')), 'addon');
		shopp_product_variant_set_type($Call->id, 'Virtual', 'addon');
		$Mag = shopp_product_variant(array('product'=>$Product->id, 'option' => array('Bonus'=>'Magazine Subscription')), 'addon');
		shopp_product_variant_set_type($Mag->id, 'Subscription', 'addon');

		$Product = shopp_product($Product->id);
		foreach ( $Product->prices as $Price ) {
			switch ( $Price->optionkey ) {
				case 0:
					$this->AssertEquals('product', $Price->context);
					$this->AssertEquals('Download', $Price->type);
					break;
				case 7001:
					$this->AssertEquals('addon', $Price->context);
					$this->AssertEquals('Virtual', $Price->type);
					break;
				case 14002:
					$this->AssertEquals('addon', $Price->context);
					$this->AssertEquals('Subscription', $Price->type);
					break;
				default:
					$this->AssertTrue(false);
			}
		}
	}

	function test_shopp_product_variant_set_taxed() {

	}
}
?>