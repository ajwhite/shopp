<?php
/**
* tests for api/collection.php
*/
class CollectionDevAPITests extends ShoppTestCase
{

	function test_shopp_add_product_category () {
		global $test_shopp_add_product_category;

		$this->AssertFalse( ! $parent = shopp_add_product_category('Unit Test Category Parent','Product Category for Unit Testing') );
		$this->AssertFalse( ! $child = shopp_add_product_category('Unit Test Category Child', 'Product Category for Unit Testing', $parent) );
		$this->AssertFalse( ! $grandchild = shopp_add_product_category('Unit Test Category Grand-Child', 'Product Category for Unit Testing', $child) );
		$this->AssertFalse( ! $secondgrandchild = shopp_add_product_category('Unit Test Category 2nd Grand-Child', 'Product Category for Unit Testing', $child) );
		$this->AssertFalse( ! $ggrandchild = shopp_add_product_category('Unit Test Category Great Grand-Child', 'Product Category for Unit Testing', $secondgrandchild) );

		// check hierarchy
		$hierarchy = _get_term_hierarchy(ProductCategory::$taxonomy);
		$this->AssertTrue(in_array($parent, array_keys($hierarchy)));
		$this->AssertTrue(in_array($child, $hierarchy[$parent]));

		$this->AssertTrue(in_array($child, array_keys($hierarchy)));
		$this->AssertTrue(in_array($grandchild, $hierarchy[$child]));
		$this->AssertTrue(in_array($secondgrandchild, $hierarchy[$child]));

		$this->AssertTrue(in_array($secondgrandchild, array_keys($hierarchy)));
		$this->AssertTrue(in_array($ggrandchild, $hierarchy[$secondgrandchild]));

		$test_shopp_add_product_category = array($parent, $child, $grandchild, $secondgrandchild, $ggrandchild);
		foreach ( $test_shopp_add_product_category as $cat ) {
			$this->AssertTrue( is_a($Cat = shopp_product_category($cat), 'ProductCategory'));
			$this->AssertTrue( $Cat->id == $cat );
		}

	}

	function test_shopp_rmv_product_category () {
		global $test_shopp_add_product_category;
		foreach ( $test_shopp_add_product_category as $destroy ) {
			$this->AssertTrue( shopp_rmv_product_category($destroy) );
			$this->AssertFalse( shopp_product_category($destroy) );
		}
	}

	function test_shopp_product_categories() {
		$cats = shopp_product_categories();
		foreach ( $cats as $index => $Cat ) {
			$this->AssertTrue( is_a($Cat, 'ProductCategory'));
			$this->AssertEquals( $Cat->id, $index );
		}

		$cats = shopp_product_categories(array('index'=>'slug'));
		foreach ( $cats as $index => $Cat ) {
			$this->AssertTrue( is_a($Cat, 'ProductCategory'));
			$this->AssertEquals( $Cat->slug, $index );
		}

		$cats = shopp_product_categories(array('index'=>'name'));
		foreach ( $cats as $index => $Cat ) {
			$this->AssertTrue( is_a($Cat, 'ProductCategory'));
			$this->AssertEquals( $Cat->name, $index );
		}

		$cats = shopp_product_categories(array('load'=>array(), 'hide_empty'=>true));
		foreach ( $cats as $index => $Cat ) {
			$this->AssertTrue( is_a($Cat, 'ProductCategory'));
			if ( $Cat->count ) $this->AssertTrue( ! empty($Cat->products) );
			else $this->AssertTrue( empty($Cat->products) );
		}

	}

	function test_shopp_product_tag() {
		$Tag = shopp_product_tag('action');
		$this->AssertEquals($Tag->name, 'action');
		$this->AssertEquals($Tag->slug, 'action');
		$this->AssertTrue( ! empty($Tag->products) );

		$id = $Tag->id;
		$Tag = shopp_product_tag($id);

		$this->AssertEquals($Tag->name, 'action');
		$this->AssertEquals($Tag->slug, 'action');
		$this->AssertTrue( ! empty($Tag->products) );
	}

	function test_shopp_product_term () {
		$Tag = shopp_product_tag('action');
		$Term = shopp_product_term($Tag->id, ProductTag::$taxonomy);
		$this->AssertEquals($Term->name, 'action');
		$this->AssertEquals($Term->slug, 'action');
		$this->AssertTrue( ! empty($Term->products) );

		shopp_register_taxonomy('product_term_test');

		$Product = shopp_add_product(array('name'=>'shopp_product_term_test', 'publish'=>array('flag'=>true)));
		$term = shopp_add_product_term('shopp_product_term_test1', 'shopp_product_term_test');
		shopp_product_add_terms ( $Product->id, $terms = array($term), 'shopp_product_term_test' );

		$Term = shopp_product_term($term, 'shopp_product_term_test');
		$this->AssertTrue(is_a($Term, 'ProductTaxonomy'));
		$this->AssertEquals('shopp_product_term_test', $Term->taxonomy);
		$this->AssertEquals(1, count($Term->products));
		$this->AssertEquals('shopp_product_term_test', reset($Term->products)->name);
	}

	function test_shopp_product_tags() {
		$tags = shopp_product_tags();
		foreach ( $tags as $index => $ProductTag ) {
			$this->AssertTrue( is_a($ProductTag, 'ProductTag'));
			$this->AssertEquals( $ProductTag->id, $index );
		}

		$tags = shopp_product_tags(array('index'=>'slug'));
		foreach ( $tags as $index => $ProductTag ) {
			$this->AssertTrue( is_a($ProductTag, 'ProductTag'));
			$this->AssertEquals( $ProductTag->slug, $index );
		}

		$tags = shopp_product_tags(array('index'=>'name'));
		foreach ( $tags as $index => $ProductTag ) {
			$this->AssertTrue( is_a($ProductTag, 'ProductTag'));
			$this->AssertEquals( $ProductTag->name, $index );
		}

		$tags = shopp_product_tags(array('load'=>array(), 'hide_empty'=>true));
		foreach ( $tags as $index => $ProductTag ) {
			$this->AssertTrue( is_a($ProductTag, 'ProductTag'));
			$this->AssertTrue( ! empty($ProductTag->products) );
		}

	}
}
?>