<?php
/**
* ProductDevAPITests - tests for the product dev api
*/
class ProductDevAPITests extends ShoppTestCase
{

	function test_shopp_add_product () {
		$Product = shopp_product("St. John's Bay® Color Block Windbreaker", 'name');
		if ( ! empty($Product->id) ) $Product->delete();

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

		$this->AssertEquals('St. John\'s Bay® Color Block Windbreaker', $Product->name);
		$this->AssertEquals('This water-repellent windbreaker offers lightweight protection on those gusty days.',$Product->summary);
		$this->AssertEquals('on', $Product->variants);
		$this->AssertEquals('on', $Product->addons);
		$this->AssertEquals('on', $Product->featured);
		$this->AssertEquals('on', $Product->sale);
		$this->AssertEquals(19.99, $Product->maxprice);
		$this->AssertEquals(0.00, $Product->minprice);
		$this->AssertEquals('on', $Product->packaging);
		$this->AssertEquals('a:2:{s:1:"v";a:2:{i:1;a:3:{s:2:"id";i:1;s:4:"name";s:4:"Size";s:7:"options";a:9:{i:1;a:3:{s:2:"id";i:1;s:4:"name";s:6:"medium";s:6:"linked";s:3:"off";}i:2;a:3:{s:2:"id";i:2;s:4:"name";s:5:"large";s:6:"linked";s:3:"off";}i:3;a:3:{s:2:"id";i:3;s:4:"name";s:7:"x-large";s:6:"linked";s:3:"off";}i:4;a:3:{s:2:"id";i:4;s:4:"name";s:5:"small";s:6:"linked";s:3:"off";}i:5;a:3:{s:2:"id";i:5;s:4:"name";s:8:"xx-large";s:6:"linked";s:3:"off";}i:6;a:3:{s:2:"id";i:6;s:4:"name";s:10:"large-tall";s:6:"linked";s:3:"off";}i:7;a:3:{s:2:"id";i:7;s:4:"name";s:12:"x-large tall";s:6:"linked";s:3:"off";}i:8;a:3:{s:2:"id";i:8;s:4:"name";s:13:"2x-large tall";s:6:"linked";s:3:"off";}i:9;a:3:{s:2:"id";i:9;s:4:"name";s:8:"2x-large";s:6:"linked";s:3:"off";}}}i:2;a:3:{s:2:"id";i:2;s:4:"name";s:5:"Color";s:7:"options";a:5:{i:10;a:3:{s:2:"id";i:10;s:4:"name";s:18:"Black/Grey Colorbi";s:6:"linked";s:3:"off";}i:11;a:3:{s:2:"id";i:11;s:4:"name";s:15:"Navy Baby Solid";s:6:"linked";s:3:"off";}i:12;a:3:{s:2:"id";i:12;s:4:"name";s:18:"Red/Iron Colorbloc";s:6:"linked";s:3:"off";}i:13;a:3:{s:2:"id";i:13;s:4:"name";s:10:"Iron Solid";s:6:"linked";s:3:"off";}i:14;a:3:{s:2:"id";i:14;s:4:"name";s:17:"Dark Avocado Soil";s:6:"linked";s:3:"off";}}}}s:1:"a";a:1:{i:1;a:3:{s:2:"id";i:1;s:4:"name";s:7:"Special";s:7:"options";a:1:{i:1;a:3:{s:2:"id";i:1;s:4:"name";s:11:"Embroidered";s:6:"linked";s:3:"off";}}}}}',
							serialize($Product->options));
		$this->AssertEquals(47, count($Product->prices));

		$counts = array('product'=>0,'addon'=>0,'variation'=>0);
		$Variant = $Addon = false;
		foreach ( $Product->prices as $index => $Price ) {
			$counts[$Price->context]++;
			if ( 7001 == $Price->optionkey ) $Addon = &$Product->prices[$index];
			if ( 79754 == $Price->optionkey ) $Variant = &$Product->prices[$index];
		}

		$this->AssertEquals(45, $counts['variation']);
		$this->AssertEquals(1, $counts['addon']);
		$this->AssertEquals(1, $counts['product']);

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

	function test_shopp_add_product_1386 () {
		$Product = shopp_add_product( array(
		    'name' => 'This is a book',
		    'publish' => array(
		        'flag' => true,
		        'publishtime' => array(
		            'month' => 10,
		            'day' => 6,
		            'year' => 2011,
		            'hour' => 12,
		            'minute' => 02,
		            'meridian' => 'AM'
		        )
		    ),
		    'categories' => array(),
		    'tags' => array(),
		    'terms' => array(),
		    'description' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit...',
		    'summary' => '',
		    'specs' => array(
		        'Author' => 'Bourne, Jason'
		    ),
		    'single' => array(
		        'type' => 'Shipped',
		        'taxed' => true,
		        'price' => 19.00,
		        'sale' => array(
		            'flag' => true,
		            'price' => 14.99
		        ),
		        'shipping' => array(
		            'flag' => true,
		            'fee' => 0,
		         ),
		        'inventory' => array(
		            'flag' => true,
		            'stock' => 1,
		            'sku' => '237469'
		        )
		    ),
		    'featured' => false,
		    'packaging' => false,
		    'processing' => array(
		        'flag' => false
		    )
		));
		$this->AssertTrue( (bool) $Product );
		$this->AssertEquals( 1, count($Product->prices) );
	}

	function test_shopp_add_product_1525 () {
		$args = array(
			'name' => 'Beugel',
			'publish' => array('flag'=>true),
			'single'=>array(
				'type'=>'Shipped',
				'price'=>'0.00',
				'taxed'=>true,
				'inventory'=>array(
					'flag'=>true,
					'stock'=>'3',
					'sku'=>'BE_0001_NI_11,5'
				)
			)
		);

		$Product = shopp_add_product($args);
		$this->AssertEquals( 1, count($Product->prices) );

		$args = array(
			'name' => 'Beugel',
			'publish' => array('flag'=>true),
			'single'=>array(
				'type'=>'Shipped',
				'price'=>'3.25',
				'taxed'=>true,
				'inventory'=>array(
					'flag'=>true,
					'stock'=>'0',
					'sku'=>'BE_0002_NI_10'
				)
			)
		);

		$Product = shopp_add_product($args);
		$this->AssertEquals( 1, count($Product->prices) );

		$args = array(
			'name' => 'Beugel',
			'publish' => array('flag'=>true),
			'single'=>array(
				'type'=>'Shipped',
				'price'=>'2.00',
				'taxed'=>true,
				'inventory'=>array(
					'flag'=>true,
					'stock'=>'5',
					'sku'=>'BE_0003_GO_14'
				)
			)
		);

		$Product = shopp_add_product($args);
		$this->AssertEquals( 1, count($Product->prices) );

	}

	function test_shopp_product () {
		$Product = shopp_product( '1/10 Carat Diamond Journey Heart Pendant in Yellow Gold', 'name' );
		$this->AssertEquals(1, count($Product->prices));
		$Price = reset($Product->prices);
		$this->AssertEquals('product', $Price->context);
		$this->AssertEquals('Shipped', $Price->type);
		$this->AssertEquals(49, $Price->price);
		$this->AssertEquals(44, $Price->saleprice);
		$this->AssertEquals(44, $Price->promoprice);
		$this->AssertEquals(0.5, $Price->weight);
		$this->AssertEquals('on', $Price->tax);
		$this->AssertEquals('on', $Price->shipping);
		$this->AssertEquals('on', $Price->sale);
		$this->AssertEquals('off', $Price->inventory);
	}

	function test_shopp_product_publish () {
		$Product = shopp_product( '1/10 Carat Diamond Journey Heart Pendant in Yellow Gold', 'name' );
		shopp_product_publish ( $Product->id, false );
		$Product = shopp_product( '1/10 Carat Diamond Journey Heart Pendant in Yellow Gold', 'name' );

		$this->AssertEquals('draft', $Product->status);

		shopp_product_publish ( $Product->id, true, mktime( 12, 0, 0, 12, 1, 2011) );
		$Product = shopp_product( '1/10 Carat Diamond Journey Heart Pendant in Yellow Gold', 'name' );
		$this->AssertEquals('future', $Product->status);
		$this->AssertEquals($Product->publish, mktime( 12, 0, 0, 12, 1, 2011));

		shopp_product_publish ( $Product->id, true );
		$Product = shopp_product( '1/10 Carat Diamond Journey Heart Pendant in Yellow Gold', 'name' );
		$this->AssertEquals('publish', $Product->status);
		$this->assertTrue(time() >= $Product->publish);
	}

	function test_shopp_product_specs () {
		$Product = shopp_product('Her Personalized Heart Class Ring', 'name');
		$specs = shopp_product_specs( $Product->id );
		$this->assertTrue(in_array('Model No.', array_keys($specs)));
		$this->assertTrue(in_array('Gender', array_keys($specs)));
		$this->AssertEquals(116, $specs['Model No.']->value);
		$this->AssertEquals('Women', $specs['Gender']->value);
	}

	function test_shopp_product_variants () {
		$Product = shopp_product('Bones - Season 4', 'name');
		$variations = shopp_product_variants($Product->id);

		$this->assertEquals(2, count($variations));
		$Price = reset($variations);
		$this->assertEquals('Widescreen', $Price->label);
		$this->assertEquals(77011, $Price->optionkey);
		$this->assertEquals(59.98, $Price->price);
		$this->assertEquals(34.86, $Price->saleprice);
		$this->assertEquals(34.86, $Price->promoprice);

		$Price = next($variations);
		$this->assertEquals('Full-Screen', $Price->label);
		$this->assertEquals(84012, $Price->optionkey);
		$this->assertEquals(59.98, $Price->price);
		$this->assertEquals(34.86, $Price->saleprice);
		$this->assertEquals(34.86, $Price->promoprice);

	}

	function test_shopp_product_addons () {
		$Product = shopp_product("St. John's Bay® Color Block Windbreaker", 'name');

		$addons = shopp_product_addons($Product->id);
		$testing = array (
	        'product' => $Product->id,
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
		$Product = shopp_product("St. John's Bay® Color Block Windbreaker", 'name');

		$product = $Product->id;
		$Price = shopp_product_variant(array( 'product' => $product, 'option' => array('Size'=>'medium', 'Color'=>'Navy Baby Solid')), 'variant');
		$this->AssertEquals(79754, $Price->optionkey);
		$this->AssertEquals('medium, Navy Baby Solid', $Price->label);
		$this->AssertEquals('variation', $Price->context);

		$Price = shopp_product_variant($Price->id); // test load by id
		$this->AssertEquals(79754, $Price->optionkey);
		$this->AssertEquals('medium, Navy Baby Solid', $Price->label);
		$this->AssertEquals('variation', $Price->context);

		$Price = shopp_product_variant(array( 'product' => $product, 'option' => array('Special' => 'Embroidered') ), 'addon' );
		$this->AssertEquals(7001, $Price->optionkey);
		$this->AssertEquals('Embroidered', $Price->label);
		$this->AssertEquals('addon', $Price->context);

		$Price = shopp_product_variant(array( 'product' => 31), 'product');
		$this->AssertEquals(42, $Price->id);
		$this->AssertEquals('Price & Delivery', $Price->label);
		$this->AssertEquals('product', $Price->context);
	}

	function test_shopp_product_addon () {
		$Product = shopp_product("St. John's Bay® Color Block Windbreaker", 'name');
		$product = $Product->id;
		$Price = shopp_product_addon(array( 'product' => $product, 'option' => array('Special' => 'Embroidered') ) );
		$this->AssertEquals(7001, $Price->optionkey);
		$this->AssertEquals('Embroidered', $Price->label);
		$this->AssertEquals('addon', $Price->context);

		$id = $Price->id;
		unset($Price);
		$Price = shopp_product_addon($id); // test load by id
		$this->AssertEquals(7001, $Price->optionkey);
		$this->AssertEquals('Embroidered', $Price->label);
		$this->AssertEquals('addon', $Price->context);

	}

	function test_shopp_product_variant_options () {
		$Product = shopp_product("St. John's Bay® Color Block Windbreaker", 'name');
		$product = $Product->id;

		$options = shopp_product_variant_options($product);
		$this->AssertEquals('a:2:{s:4:"Size";a:9:{i:0;s:6:"medium";i:1;s:5:"large";i:2;s:7:"x-large";i:3;s:5:"small";i:4;s:8:"xx-large";i:5;s:10:"large-tall";i:6;s:12:"x-large tall";i:7;s:13:"2x-large tall";i:8;s:8:"2x-large";}s:5:"Color";a:5:{i:0;s:18:"Black/Grey Colorbi";i:1;s:15:"Navy Baby Solid";i:2;s:18:"Red/Iron Colorbloc";i:3;s:10:"Iron Solid";i:4;s:17:"Dark Avocado Soil";}}',
		serialize($options));
	}

	function test_shopp_product_addon_options () {
		$Product = shopp_product("St. John's Bay® Color Block Windbreaker", 'name');
		$product = $Product->id;

		$addon_options = shopp_product_addon_options ( $product );
		$this->AssertEquals('a:1:{s:7:"Special";a:1:{i:0;s:11:"Embroidered";}}', serialize($addon_options));
	}

	function test_shopp_product_add_categories () {
		$Product = shopp_product("St. John's Bay® Color Block Windbreaker", 'name');
		$product = $Product->id;

		$category = shopp_add_product_category ( 'Jackets', "Men's Jackets", 5 );
		$this->assertTrue(shopp_product_add_categories($product, array($category)));

		$Product = shopp_product($product);

		$this->assertTrue(isset($Product->categories[$category]));
		$this->AssertEquals('jackets', $Product->categories[$category]->slug);
		$this->AssertEquals('Jackets', $Product->categories[$category]->name);
		$this->AssertEquals("Men's Jackets", $Product->categories[$category]->description);
	}

	function test_shopp_product_add_tags () {
		$Product = shopp_product("St. John's Bay® Color Block Windbreaker", 'name');
		$product = $Product->id;

		$tag = shopp_add_product_tag ( 'Waterproof' );
		$tag2 = shopp_add_product_tag ( 'Fashionable' );
		$this->AssertTrue( shopp_product_add_tags($product, array($tag, 'Fashionable')) );

		$Product = shopp_product($product);
		$this->AssertEquals('Waterproof', $Product->tags[$tag]->name);
		$this->AssertEquals('Fashionable', $Product->tags[$tag2]->name);
	}

	function test_shopp_product_set_specs () {
		$Product = shopp_product("St. John's Bay® Color Block Windbreaker", 'name');
		$product = $Product->id;

		shopp_product_rmv_spec($product, 'pockets');
		shopp_product_rmv_spec($product, 'drawstring');
		shopp_product_rmv_spec($product, 'washable');

		$Product = shopp_product($product);

		$this->assertTrue(! isset($Product->specs) || empty($Product->specs));

		$specs = array('pockets'=>2, 'drawstring'=>'yes','washable'=>'yes');
		shopp_product_set_specs ( $product, $specs);

		$Specs = shopp_product_specs($product);

		$this->AssertEquals(2, $Specs['pockets']->value);
		$this->AssertEquals('yes', $Specs['drawstring']->value);
		$this->AssertEquals('yes', $Specs['washable']->value);
	}

	function test_shopp_product_add_terms () {
		$Product = shopp_product("St. John's Bay® Color Block Windbreaker", 'name');
		$product = $Product->id;

		shopp_register_taxonomy('brand', array(
	        'hierarchical' => true
	    ));

		$term = shopp_add_product_term("Domestic Brands", 'shopp_brand');
		$term1 = shopp_add_product_term("St. John's Bay", 'shopp_brand', $term);

		shopp_product_add_terms($product, array($term,$term1), 'shopp_brand');
		$Product = shopp_product($product);

		$this->AssertEquals("Domestic Brands", $Product->shopp_brands[$term]->name);
		$this->AssertEquals("domestic-brands", $Product->shopp_brands[$term]->slug);

		$this->AssertEquals("St. John's Bay", $Product->shopp_brands[$term1]->name);
		$this->AssertEquals("st-johns-bay", $Product->shopp_brands[$term1]->slug);
		$this->AssertEquals($term, $Product->shopp_brands[$term1]->parent);
	}

	function test_shopp_product_add_terms_1485 () {
		$pid = shopp_product("St. John's Bay® Color Block Windbreaker", 'name')->id;

		$args = array(
			'type'			=> 'post',
			'child_of'		=> 0,
			'parent'		=> '',
			'orderby'		=> 'name',
			'order'			=> 'ASC',
			'hide_empty'	=> 0,
			'hierarchical'	=> 0,
			'exclude'		=> '',
			'include'		=> '',
			'number'		=> '',
			'taxonomy'		=> 'shopp_category',
			'pad_counts'	=> false
		);
		$categories = get_categories($args);

		$terms = array();
		foreach ( $categories as $category ) {
			$terms[] = $category->term_id;
		}

		$this->AssertTrue(shopp_product_add_terms($pid, $terms, 'shopp_category', false));
		$expected = array();
		foreach ( shopp_product("St. John's Bay® Color Block Windbreaker", 'name')->categories as $category ) {
			$expected[] = $category->id;
		}

		$compare = array_diff($terms, $expected);
		$this->AssertTrue( empty( $compare ) );
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