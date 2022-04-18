<?php
/**
 * Plugin Name: PDF Shortcode
 * Plugin URI: https://github.com/jvarn/ff-views-pdf
 * Description: Inserts a button to save WordPress to PDF, including Pages and Formidable Forms Views.
 * Version: 0.5.3
 * Author: Jeremy Varnham
 * Author URI: https://abuyasmeen.com/
 *
 * @package jvarn\pdf-shortcode
 */

namespace Jvarn;

/**
 * No direct access.
 */
if ( ! defined( 'ABSPATH' ) ) {
	wp_die();
}

/**
 * Define plugin constants.
 */
define( 'WP_FFVIEW_PDF_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_FFVIEW_PDF_DIR', dirname( plugin_basename( __FILE__ ) ) );

/**
 * Load plugin textdomain.
 *
 * @todo generate pot file.
 */
function load_textdomain() {
	load_plugin_textdomain( 'pdfshortcode', false, WP_FFVIEW_PDF_DIR . '/languages' );
}
add_action( 'init', 'Jvarn\load_textdomain' );

/**
 * Composer autoloader.
 */
require_once WP_FFVIEW_PDF_PATH . 'vendor/autoload.php';

$pdf_shortcode = new PdfShortcode\Shortcode();
$pdf_shortcode_settings = new PdfShortcode\Settings();

/*if ( class_exists('Jvarn\PdfShortcode\Shortcode') ) {
	echo '<script>console.log("Shortcode Class Exists");</script>';
}
if ( class_exists('Jvarn\PdfShortcode\Settings') ) {
	echo '<script>console.log("Settings Class Exists");</script>';
}*/
