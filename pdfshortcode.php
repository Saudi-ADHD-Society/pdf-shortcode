<?php
/**
 * Plugin Name: PDF Shortcode
 * Plugin URI: https://github.com/jvarn/ff-views-pdf
 * Description: Inserts a button to save WordPress to PDF, including Pages and Formidable Forms Views.
 * Version: 0.4.1
 * Author: Jeremy Varnham
 * Author URI: https://abuyasmeen.com/
 *
 * @package jvarn\pdfshortcode
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
function ffviewpdf_load_textdomain() {
	load_plugin_textdomain( 'ffviewpdf', false, WP_FFVIEW_PDF_DIR . '/languages' );
}
add_action( 'init', 'ffviewpdf_load_textdomain' );

/**
 * Load dependencies.
 */
if ( ! class_exists( 'Mpdf' ) ) {
	require_once WP_FFVIEW_PDF_PATH . 'vendor/autoload.php';
	require_once WP_FFVIEW_PDF_PATH . 'classes/class-ffviewpdf.php';
	require_once WP_FFVIEW_PDF_PATH . 'classes/class-ffviewpdf-admin.php';
}
