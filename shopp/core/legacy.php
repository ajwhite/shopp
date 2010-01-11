<?php
/**
 * legacy.php
 * A library of functions for older version of PHP and WordPress
 *
 * @author Jonathan Davis
 * @version 1.0
 * @copyright Ingenesis Limited, November 18, 2009
 * @license GNU GPL version 3 (or later) {@see license.txt}
 * @package shopp
 **/

if( !function_exists('esc_url') ) {
	/**
	 * Checks and cleans a URL
	 *
	 * A number of characters are removed from the URL. If the URL is for displaying
	 * (the default behaviour) amperstands are also replaced. The 'esc_url' filter
	 * is applied to the returned cleaned URL.
	 *
	 * @since WordPress 2.8.0+
	 * 
	 * @uses esc_url()
	 * @uses wp_kses_bad_protocol() To only permit protocols in the URL set
	 *		via $protocols or the common ones set in the function.
	 *
	 * @param string $url The URL to be cleaned.
	 * @param array $protocols Optional. An array of acceptable protocols.
	 *		Defaults to 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet' if not set.
	 * @return string The cleaned $url after the 'cleaned_url' filter is applied.
	 */
	function esc_url( $url, $protocols = null ) {
		return clean_url( $url, $protocols, 'display' );
	}
}

if (!function_exists('json_encode')) {
	/**
	 * Builds JSON {@link http://www.json.org/} formatted strings from PHP data structures
	 *
	 * @author Jonathan Davis
	 * @since PHP 5.2.0+
	 *
	 * @param mixed $a PHP data structure
	 * @return string JSON encoded string
	 **/
	function json_encode ($a = false) {
		if (is_null($a)) return 'null';
		if ($a === false) return 'false';
		if ($a === true) return 'true';
		if (is_scalar($a)) {
			if (is_float($a)) {
				// Always use "." for floats.
				return floatval(str_replace(",", ".", strval($a)));
			}

			if (is_string($a)) {
				static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
				return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
			} else return $a;
		}

		$isList = true;
		for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
			if (key($a) !== $i) {
				$isList = false;
				break;
			}
		}

		$result = array();
		if ($isList) {
			foreach ($a as $v) $result[] = json_encode($v);
			return '[' . join(',', $result) . ']';
		} else {
			foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
			return '{' . join(',', $result) . '}';
		}
	}
}

if(!function_exists('scandir')) {
	/**
	 * Lists files and directories inside the specified path
	 *
	 * @author Jonathan Davis
	 * @since PHP 5.0+
	 * 
	 * @param string $dir Directory path to scan
	 * @param int $sortorder The sort order of the file listing (0=alphabetic, 1=reversed)
	 * @return array|boolean The list of files or false if not available
	 **/
	function scandir($dir, $sortorder = 0) {
		if(is_dir($dir) && $dirlist = @opendir($dir)) {
			$files = array();
			while(($file = readdir($dirlist)) !== false) $files[] = $file;
			closedir($dirlist);
			($sortorder == 0) ? asort($files) : rsort($files);
			return $files;
		} else return false;
	}
}

if (!function_exists('attribute_escape_deep')) {
	/**
	* @todo	Replace with esc_attrs in functions.php
	**/
	function attribute_escape_deep($value) {
		 $value = is_array($value) ?
			 array_map('attribute_escape_deep', $value) :
			 attribute_escape($value);
		 return $value;
	}
}

if (!function_exists('property_exists')) {
	/**
	 * Checks an object for a declared property
	 * 
	 * @author Jonathan Davis
	 * @since PHP 5.1.0+
	 * 
	 * @param object $Object The object to inspect
	 * @param string $property The name of the property to look for
	 * @return boolean True if the property exists, false otherwise
	 **/
	function property_exists($object, $property) {
		return array_key_exists($property, get_object_vars($object));
	}
}

if ( !function_exists('sys_get_temp_dir')) {
	/**
	 * Determines the temporary directory for the local system
	 *
	 * @author Jonathan Davis
	 * @since PHP 5.2.1+
	 * 
	 * @return string The path to the system temp directory
	 **/
	function sys_get_temp_dir() {
		if (!empty($_ENV['TMP'])) return realpath($_ENV['TMP']);
		if (!empty($_ENV['TMPDIR'])) return realpath( $_ENV['TMPDIR']);
		if (!empty($_ENV['TEMP'])) return realpath( $_ENV['TEMP']);
		$tempfile = tempnam(uniqid(rand(),TRUE),'');
		if (file_exists($tempfile)) {
			unlink($tempfile);
			return realpath(dirname($tempfile));
		}
	}
}


?>