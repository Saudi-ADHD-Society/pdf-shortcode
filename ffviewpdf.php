<?php
/*
Plugin Name: Formidable Views PDF
Plugin URI: https://github.com/jvarn/ff-views-pdf
Description: Export Formidable Forms Views to PDF with a Shortcode
Version: 0.2.0
Author: Jeremy Varnham
Author URI: https://abuyasmeen.com/
*/

namespace jvarn\FFVIEWPDF;

// Prevent Direct Access
if ( ! defined( 'ABSPATH' ) ) { 
	wp_die(); 
}

/**
 * loads dependencies 
 *
 */
if ( ! class_exists( 'Mpdf' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php');
}

/**
 * Class FFVIEWPDF
 *
 * @package jvarn\ffviewpdf
 */
class FFVIEWPDF {
	
	public $viewid, $type;

	public function __construct() {
		//add_action( 'init', array( $this, 'process_form') );
		add_action( 'template_redirect', array( $this, 'process_form') );
		add_shortcode( 'ffviewpdf', array( $this, 'insert_form') );
	}
	
	public function process_form() {
		if ( $this->is_form_submitted() ) {
			
			$this->set_args( $_POST['viewid'],  $_POST['type'] );
			
			$html = $this->content_type( $this->type );
						
			$mpdf = new \Mpdf\Mpdf( 
				[
				'mode' => 'utf-8',
				'orientation' => 'L'
				]
			);
			
			$mpdf->SetDirectionality('rtl');
			$mpdf->autoScriptToLang = true;
			$mpdf->autoLangToFont = true;
			$mpdf->baseScript = 1;
			//$mpdf->autoArabic = true; // what does this add?
			
			$stylesheet = file_get_contents( plugin_dir_path( __FILE__ ) . 'style.css');

			$mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);
			$mpdf->WriteHTML($html,\Mpdf\HTMLParserMode::HTML_BODY);
						
			$mpdf->Output("filename.pdf",\Mpdf\Output\Destination::DOWNLOAD);
	    }
	}
	
	public function download_form() {
		$html = '<form method="post" action="?action=download-ffviewpdf" enctype="multipart/form-data">
		 <input type="hidden" name="action" value="download-ffviewpdf">
		 <input type="hidden" name="viewid" value="'. $this->viewid .'">
		 <input type="hidden" name="type" value="'. $this->type .'">
		 <input type="submit" value="Download PDF">';
		$html .= wp_nonce_field( 'ffviewpdf_form', 'ffviewpdf_form_nonce', true, false );
		$html .= '</form>';
		
		return $html;
	}

	public function insert_form( $atts, $content="" ) {
		extract( \shortcode_atts( 
			array( 
				'viewid' => $this->viewid,
				'type'	 => $this->type,
				), $atts ) );
		$this->set_args( $viewid, $type );

		if ( $this->is_form_submitted() ) {	
			// this works to save to web root or specified path
			//$content = $mpdf->Output("filename.pdf",\Mpdf\Output\Destination::FILE);

		} else {
			return $this->download_form();
		}
	}
	
	private function set_args( $viewid, $type ) {
		$this->viewid = $viewid;
		$this->type = $type;
	}
	
	private function content_type( $type ) {
		switch ( $type ) {
		    case "page":
				return get_the_content();
		        break;
		    case "view":
				if ( function_exists( 'load_formidable_forms' ) ) { // to-do: function check for Views
					return \FrmViewsDisplaysController::get_shortcode( array( 'id' => $this->viewid ) );
				} else {
					return "Formidable Forms not found";
				}
		        break;
		    case "html":
				return "html";
		        break;
		}
	}
	
	private function is_form_submitted() {
		if ( $_SERVER["REQUEST_METHOD"] == "POST" && isset( $_REQUEST['action'] ) && 'download-ffviewpdf' === $_REQUEST['action'] ) {
			if ( $this->check_nonce() === false ) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
	
	private function check_nonce() {
		if ( !isset( $_POST['ffviewpdf_form_nonce'] ) || !wp_verify_nonce( $_POST['ffviewpdf_form_nonce'], 'ffviewpdf_form' ) ) {
			return false;
		}
	}
	
	
}
$ffviewpdf = new FFVIEWPDF();