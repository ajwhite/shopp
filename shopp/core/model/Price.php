<?php
/**
 * Price.php
 *
 * Product price objects
 *
 * @author Jonathan Davis
 * @version 1.0
 * @copyright Ingenesis Limited, 28 March, 2008
 * @license GNU GPL version 3 (or later) {@see license.txt}
 * @package shopp
 * @since 1.0
 * @subpackage products
 **/
class Price extends DatabaseObject {

	static $table = "price";

	function __construct ($id=false,$key=false) {
		$this->init(self::$table);
		if ($this->load($id,$key))
			$this->load_download();

		// Recalculate promo price from applied promotional discounts
		add_action('shopp_price_updates',array(&$this,'discounts'));
	}

	/**
	 * Loads a product download attached to the price object
	 *
	 * @author Jonathan Davis
	 * @since 1.0
	 *
	 * @return boolean
	 **/
	function load_download () {
		if ($this->type != "Download") return false;
		$this->download = new ProductDownload(array(
			'parent' => $this->id,
			'context' => 'price',
			'type' => 'download'
			));

		if (empty($this->download->id)) return false;
		return true;
	}

	/**
	 * Attaches a product download asset to the price object
	 *
	 * @author Jonathan Davis
	 * @since 1.1
	 *
	 * @return boolean
	 **/
	function attach_download ($id) {
		if (!$id) return false;

		$Download = new ProductDownload($id);
		$Download->parent = $this->id;
		$Download->save();

		do_action('attach_product_download',$id,$this->id);

		return true;
	}

	/**
	 * Updates price record with provided data
	 *
	 * @author Jonathan Davis
	 * @since 1.1
	 *
	 * @param array $data An associative array of key/value data
	 * @param array $ignores A list of properties to ignore updating
	 * @return void
	 **/
	function updates($data,$ignores = array()) {
		parent::updates($data,$ignores);
		do_action('shopp_price_updates');
	}

	/**
	 * Calculates promotional discounts applied to the price record
	 *
	 * @author Jonathan Davis
	 * @since 1.1
	 *
	 * @return void
	 **/
	function discounts () {

		if ('on' == $this->sale) $this->promoprice = floatvalue($this->saleprice);
		else $this->promoprice = floatvalue($this->price);
		if (empty($this->discounts)) return;

		$db =& DB::get();
		$promo_table = DatabaseObject::tablename(Promotion::$table);
		$query = "SELECT type,SUM(discount) AS amount FROM $promo_table WHERE 0 < FIND_IN_SET(id,'$this->discounts') AND discount > 0 AND status='enabled' GROUP BY type ORDER BY type DESC";
		$discounts = $db->query($query,AS_ARRAY);
		if (empty($discounts)) return;

		// Apply discounts
		$a = $p = 0;
		foreach ($discounts as $discount) {
			switch ($discount->type) {
				case 'Amount Off': $a += $discount->amount; break;
				case 'Percentage Off': $p += $discount->amount; break;
			}
		}

		if ($a > 0) $this->promoprice -= $a; // Take amounts off first (to reduce merchant percentage discount burden)
		if ($p > 0)	$this->promoprice -= ($this->promoprice * ($p/100));
	}

	/**
	 * Returns structured product price line type values and labels
	 *
	 * Used for building selection UIs in the editors
	 *
	 * @author Jonathan Davis
	 * @since 1.2
	 *
	 * @return array
	 **/
	static function types () {
		 return array(
			array('value'=>'Shipped','label'=>__('Shipped','Shopp')),
			array('value'=>'Virtual','label'=>__('Virtual','Shopp')),
			array('value'=>'Download','label'=>__('Download','Shopp')),
			array('value'=>'Donation','label'=>__('Donation','Shopp')),
			array('value'=>'Subscription','label'=>__('Subscription','Shopp')),
			array('value'=>'Membership','label'=>__('Membership','Shopp')),
			array('value'=>'N/A','label'=>__('Disabled','Shopp')),
		);
	}

	/**
	 * Returns structured subscription period values and labels
	 *
	 * Used for building selector UIs in the editors. The structure
	 * is organized with plural labels first array[0] and singular
	 * labels are second array[1].
	 *
	 * @author Jonathan Davis
	 * @since 1.2
	 *
	 * @return array
	 **/
	static function periods () {
		return array(
			array(
				array('value'=>'d','label'=>__('days','Shopp')),
				array('value'=>'w','label'=>__('weeks','Shopp')),
				array('value'=>'m','label'=>__('months','Shopp')),
				array('value'=>'y','label'=>__('years','Shopp')),

			),
			array(
				array('value'=>'d','label'=>__('day','Shopp')),
				array('value'=>'w','label'=>__('week','Shopp')),
				array('value'=>'m','label'=>__('month','Shopp')),
				array('value'=>'y','label'=>__('year','Shopp')),
			)
		);
	}

} // END class Price

?>