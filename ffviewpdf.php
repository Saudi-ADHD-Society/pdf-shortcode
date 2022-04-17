<?php
/**
 * Plugin Name: Formidable Views PDF
 * Plugin URI: https://github.com/jvarn/ff-views-pdf
 * Description: Export WordPress content including Formidable Forms Views to PDF with a Shortcode.
 * Version: 0.3.3
 * Author: Jeremy Varnham
 * Author URI: https://abuyasmeen.com/
 *
 * @package jvarn\ffviewpdf
 */

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

/**
 * Load plugin textdomain.
 *
 * @todo generate pot file.
 */
function ffviewpdf_load_textdomain() {
	load_plugin_textdomain( 'ffviewpdf', false, WP_FFVIEW_PDF_PATH . '/languages' );
}

/**
 * Load dependencies.
 */
if ( ! class_exists( 'Mpdf' ) ) {
	require_once WP_FFVIEW_PDF_PATH . 'vendor/autoload.php';
	require_once WP_FFVIEW_PDF_PATH . 'classes/class-ffviewpdf.php';
}
