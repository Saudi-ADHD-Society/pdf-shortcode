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
 */
class Encryption {

	/**
	 * Encryption salt.
	 *
	 * @var string
	 */
	private static $key;

	/**
	 * Encryption method.
	 *
	 * @var string
	 */
	private static $method = 'AES-256-CBC';

	/**
	 * Encryption options.
	 *
	 * @var string
	 */
	private static $options = 0;

	/**
	 * Encryption iv.
	 *
	 * @var string
	 */
	private static $iv;

	/**
	 * Makes keys that are unique to current site.
	 *
	 * These are unique, but not cryptographically secure.
	 */
	private static function make_keys() {
		self::$key = hash( 'sha256', ABSPATH );
		self::$iv  = substr( hash( 'md5', ABSPATH ), 0, 16 );
	}

	/**
	 * Encrypts the given string.
	 *
	 * @param string $string the string to be encrypted.
	 */
	public static function encrypt( $string ) {
		self::make_keys();
		return openssl_encrypt( $string, self::$method, self::$key, self::$options, self::$iv );
	}

	/**
	 * Decrypts the given encrypted string.
	 *
	 * @param string $encrypted the string to be decrypted.
	 */
	public static function decrypt( $encrypted ) {
		self::make_keys();
		return openssl_decrypt( $encrypted, self::$method, self::$key, self::$options, self::$iv );
	}
}
