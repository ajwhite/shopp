<?php

/**
* CartDevAPITests - cart dev api test suite
*/
class CartDevAPITests extends ShoppTestCase {
	function test_shopp_add_cart_product () {
		$Product = shopp_product('1/10 Carat Diamond Journey Heart Pendant in Yellow Gold', 'name');
		shopp_add_cart_product($Product->id, 2);
		$items = shopp_cart_items();
		$this->AssertEquals(1, count($items));

		$item = reset($items);

		$this->AssertEquals('1/10 Carat Diamond Journey Heart Pendant in Yellow Gold', $item->name);
		$this->AssertEquals('110-carat-diamond-journey-heart-pendant-in-yellow-gold', $item->slug);
		$this->AssertEquals(2, $item->quantity);
		$this->AssertEquals(44, $item->unitprice);
		$this->AssertEquals(88, $item->totald);
		$this->AssertEquals(88, $item->total);

		$Totals = ShoppOrder()->Cart->Totals;

		$this->AssertEquals(88, $Totals->subtotal);
		$this->AssertEquals(3, $Totals->shipping);
		$this->AssertEquals(91, $Totals->total);

		$Product = shopp_product('Aion', 'name');
		shopp_add_cart_product($Product->id, 1);

		$items = shopp_cart_items();
		$this->AssertEquals(2, count($items));

		$item = $items[1];

		$this->AssertEquals('Aion', $item->name);
		$this->AssertEquals('aion', $item->slug);
		$this->AssertEquals(1, $item->quantity);
		$this->AssertEquals(49.82, $item->unitprice);
		$this->AssertEquals(49.82, $item->totald);
		$this->AssertEquals(49.82, $item->total);

		$Totals = ShoppOrder()->Cart->Totals;

		$this->AssertEquals(88+49.82, $Totals->subtotal);
		$this->AssertEquals(3, $Totals->shipping);
		$this->AssertEquals(91+49.82, $Totals->total);
	}

	// this test will fail if the above shopp_add_cart_product test does not run
	function test_shopp_cart_item () {
		$item = shopp_cart_item(0);
		$this->AssertEquals('1/10 Carat Diamond Journey Heart Pendant in Yellow Gold', $item->name);
		$this->AssertEquals('110-carat-diamond-journey-heart-pendant-in-yellow-gold', $item->slug);
		$this->AssertEquals(2, $item->quantity);
		$this->AssertEquals(44, $item->unitprice);
		$this->AssertEquals(88, $item->totald);
		$this->AssertEquals(88, $item->total);

		$item = shopp_cart_item('recent-cartitem');
		$this->AssertEquals('Aion', $item->name);
		$this->AssertEquals('aion', $item->slug);
		$this->AssertEquals(1, $item->quantity);
		$this->AssertEquals(49.82, $item->unitprice);
		$this->AssertEquals(49.82, $item->totald);
		$this->AssertEquals(49.82, $item->total);

	}

	// this test will fail if the above shopp_add_cart_product test does not run
	function test_shopp_rmv_cart_item () {
		shopp_rmv_cart_item(1);

		$items = shopp_cart_items();
		$this->AssertEquals(1, count($items));

		$item = reset($items);

		$this->AssertEquals('1/10 Carat Diamond Journey Heart Pendant in Yellow Gold', $item->name);
		$this->AssertEquals('110-carat-diamond-journey-heart-pendant-in-yellow-gold', $item->slug);
		$this->AssertEquals(2, $item->quantity);
		$this->AssertEquals(44, $item->unitprice);
		$this->AssertEquals(88, $item->totald);
		$this->AssertEquals(88, $item->total);

		$Totals = ShoppOrder()->Cart->Totals;

		$this->AssertEquals(88, $Totals->subtotal);
		$this->AssertEquals(3, $Totals->shipping);
		$this->AssertEquals(91, $Totals->total);

		shopp_rmv_cart_item(0);
		$Totals = ShoppOrder()->Cart->Totals;

		$this->AssertEquals(0, $Totals->subtotal);
		$this->AssertEquals(false, $Totals->shipping);
		$this->AssertEquals(0, $Totals->total);

	}

	function test_shopp_add_cart_variant () {
		$Product = shopp_product('Code Is Poetry T-Shirt', 'name');
		$Variant = reset($Product->prices);
		$Variant = next($Product->prices);

		shopp_add_cart_variant ( $Variant->id, 1 );

		$item = shopp_cart_item('recent-cartitem');

		$this->AssertEquals('Code Is Poetry T-Shirt', $item->name);
		$this->AssertEquals('Small', $item->option->label);
		$this->AssertEquals(1, $item->quantity);
		$this->AssertEquals(9.01, $item->unitprice);
		$this->AssertEquals(9.01, $item->total);

	}
}

?>