<?php
/**
 * Encryption Class.
 *
 * @package jvarn\pdf-shortcode
 */

namespace Jvarn\PdfShortcode;

/**
 * No direct access
 */
if ( ! defined( 'ABSPATH' ) ) {
	wp_die();
}

/**
 * Class Encryption
 *
 * These are sample values. CHANGE in production!
 */
class Encryption {
	
	private static $key;
	private static $method  = 'AES-256-CBC';
	private static $options = 0;
	private static $iv;
		
	/**
	 * Makes keys that are unique to current site.
	 *
	 * These are unique, but not cryptographically secure.
	 */
	private static function make_keys() {
		self::$key = hash("sha256", ABSPATH );
		self::$iv  = substr( hash("md5", ABSPATH ), 0, 16);
	}

	 public static function encrypt( $string ) {
		self::make_keys();
	    return openssl_encrypt( $string, self::$method, self::$key, self::$options, self::$iv );
	}
	
	 public static function decrypt( $encrypted ) {	
		self::make_keys();
	    return openssl_decrypt( $encrypted, self::$method, self::$key, self::$options, self::$iv );
	}
}
