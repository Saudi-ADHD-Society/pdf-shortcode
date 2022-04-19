<?php
/**
 * Defaults Class.
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
 * Class Defaults
 */
class Defaults {

	/**
	 * Default args.
	 *
	 * @var array
	 * @todo change 'viewid' to 'id' so it can be used with page as well.
	 */
	public static $args = array(
		'viewid'              => 1,
		'type'                => 'view',
		'encoding'            => 'utf-8',
		'orientation'         => 'P',
		'direction'           => 'ltr',
		'filename'            => 'download',
		'auto_script_to_lang' => '',
		'auto_lang_to_font'   => '',
	);

}
