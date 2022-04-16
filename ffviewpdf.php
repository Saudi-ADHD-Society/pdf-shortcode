<?php
/**
 * Plugin Name: Formidable Views PDF
 * Plugin URI: https://github.com/jvarn/ff-views-pdf
 * Description: Export Formidable Forms Views to PDF with a Shortcode
 * Version: 0.3.0
 * Author: Jeremy Varnham
 * Author URI: https://abuyasmeen.com/
 *
 * @package jvarn\ffviewpdf
 */

namespace jvarn\FFVIEWPDF;

// Prevent Direct Access.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die();
}

define( 'WP_FFVIEW_PDF_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Load plugin textdomain.
 */
function ffviewpdf_load_textdomain() {
	load_plugin_textdomain( 'ffviewpdf_textdomain', false, WP_FFVIEW_PDF_PATH . '/languages' );
}

/**
 * Load dependencies.
 */
if ( ! class_exists( 'Mpdf' ) ) {
	require_once WP_FFVIEW_PDF_PATH . 'vendor/autoload.php';
	require_once WP_FFVIEW_PDF_PATH . 'classes/class-ffviewphp.php';
}
