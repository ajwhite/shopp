<?php
/**
 * Address.php
 *
 * Provides foundational address data management framework
 *
 * @author Jonathan Davis
 * @version 1.0
 * @copyright Ingenesis Limited, February 21, 2011
 * @license GNU GPL version 3 (or later) {@see license.txt}
 * @package shopp
 * @since 1.2
 * @subpackage shopp
 **/

/**
 * Address
 *
 * @author Jonathan Davis
 * @since 1.1
 * @package shopp
 **/
class Address extends DatabaseObject {
	static $table = "address";

	/**
	 * Address constructor
	 *
	 * @author Jonathan Davis
	 *
	 * @return void
	 **/
	function __construct ($id=false,$key=false) {
		$this->init(self::$table);
		$this->load($id,$key);
	}

	/**
	 * Determines the domestic area name from a U.S. ZIP code or
	 * Canadian postal code.
	 *
	 * @author Jonathan Davis
	 * @since 1.1
	 * @version 1.2
	 *
	 * @return string
	 **/
	function postmap () {
		if (empty($this->postcode) || empty($this->country)) return false;

		$postcode = $this->postcode;
		$patterns = Lookup::postcode_patterns();

		if (!isset($patterns[$this->country]) || empty($patterns[$this->country])) return false;

		$pattern = $patterns[$this->country];
		if (!preg_match("/$pattern/",$postcode)) return false;

		do_action_ref_array('shopp_map_'.strtolower($this->country).'_postcode',array(&$Address));
	}

} // END class Address


/**
 * BillingAddress class
 *
 * Billing Address
 *
 * @author Jonathan Davis
 * @version 1.2
 * @copyright Ingenesis Limited, 21 February, 2011
 * @package address
 **/

class BillingAddress extends Address {

	var $type = 'billing';

	/**
	 * Billing constructor
	 *
	 * @author Jonathan Davis
	 *
	 * @param int $id The ID of the record
	 * @param string $key The column name for the specified ID
	 * @return void
	 **/
	function __construct ($id=false,$key='customer') {
		$this->init(self::$table);
		$this->load(array($key => $id,'type' => 'billing'));
		$this->type = 'billing';
	}

	function exportcolumns () {
		$prefix = "b.";
		return array(
			$prefix.'address' => __('Billing Street Address','Shopp'),
			$prefix.'xaddress' => __('Billing Street Address 2','Shopp'),
			$prefix.'city' => __('Billing City','Shopp'),
			$prefix.'state' => __('Billing State/Province','Shopp'),
			$prefix.'country' => __('Billing Country','Shopp'),
			$prefix.'postcode' => __('Billing Postal Code','Shopp'),
			);
	}

} // end BillingAddress class

/**
 * ShippingAddress class
 *
 * The shipping address manager
 *
 * @author Jonathan Davis
 * @version 1.2
 * @copyright Ingenesis Limited, 21 February, 2011
 * @package address
 **/
class ShippingAddress extends Address {

	var $type = 'shipping';
	var $method = false;
	var $residential = "on";

	/**
	 * Shipping constructor
	 *
	 * @author Jonathan Davis
	 *
	 * @param int $id The ID of the record
	 * @param string $key The column name for the specified ID
	 * @return void
	 **/
	function __construct ($id=false,$key='customer') {
		$this->init(self::$table);
		$this->load(array($key => $id,'type' => 'shipping'));
		$this->type = 'shipping';
	}

	/**
	 * Registry of supported export fields
	 *
	 * @author Jonathan Davis
	 * @since 1.1
	 *
	 * @return array
	 **/
	function exportcolumns () {
		$prefix = "s.";
		return array(
			$prefix.'address' => __('Shipping Street Address','Shopp'),
			$prefix.'xaddress' => __('Shipping Street Address 2','Shopp'),
			$prefix.'city' => __('Shipping City','Shopp'),
			$prefix.'state' => __('Shipping State/Province','Shopp'),
			$prefix.'country' => __('Shipping Country','Shopp'),
			$prefix.'postcode' => __('Shipping Postal Code','Shopp'),
		);
	}

	/**
	 * Sets the shipping address location for calculating
	 * shipping estimates.
	 *
	 * @author Jonathan Davis
	 * @since 1.1
	 *
	 * @return void Description...
	 **/
	function destination ($data=false) {
		global $Shopp;

		$base = shopp_setting('base_operations');
		$countries = Lookup::countries();
		$regions = Lookup::regions();

		if ($data) $this->updates($data);

		// Update state if postcode changes for tax updates
		if (isset($this->postcode))	$this->postmap();

		if (empty($this->country))
			$this->country = $base['country'];

		$this->region = false;
		if (isset($regions[$countries[$this->country]['region']]))
			$this->region = $regions[$countries[$this->country]['region']];

	}


} // END class ShippingAddress

class PostcodeMapping {

	static function uszip (&$Address) {
		PostcodeMapping::prefixcode(substr($Address->postcode,0,3),&$Address);
	}

	static function capost (&$Address) {
		PostcodeMapping::prefixcode($Address->postcode{0},&$Address);
	}

	/**
	 * Lookup country state/province by postal code prefix
	 *
	 * @author Jonathan Davis
	 * @since 1.2
	 *
	 * @param string $prefix The postal code prefix
	 * @return void
	 **/
	static function prefixcode ($prefix,&$Address) {
		$postcodes = Lookup::postcodes();
		if (!isset($postcodes[$Address->country])) return;

		$prefix = substr($Address->postcode,0,3);
		$state = isset($postcodes[$prefix])?$postcodes[$prefix]:false;
		if (!$state) return;

		if (empty($this->state)) $this->state = $state;
	}

}

add_action('shopp_map_us_postcode',array('PostcodeMapping','uszip'));
add_action('shopp_map_usaf_postcode',array('PostcodeMapping','uszip'));
add_action('shopp_map_usat_postcode',array('PostcodeMapping','uszip'));
add_action('shopp_map_ca_postcode',array('PostcodeMapping','capost'));

?>