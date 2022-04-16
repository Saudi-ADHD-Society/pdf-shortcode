<?php
/**
 * FFVIEWPDF Class.
 *
 * @package jvarn\ffviewpdf\ffviewpdf
 */

namespace jvarn\FFVIEWPDF;

// Prevent Direct Access.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die();
}

/**
 * Class FFVIEWPDF
 *
 * @package jvarn\ffviewpdf
 */
class FFVIEWPDF {

	/**
	 * [$default_args default args]
	 *
	 * @var array
	 */
	private $default_args = array(
		'viewid'              => 1,
		'type'                => 'view',
		'encoding'            => 'utf-8',
		'orientation'         => 'P',
		'direction'           => 'ltr',
		'filename'            => 'filename.pdf',
		'auto_script_to_lang' => true,
		'auto_lang_to_font'   => true,
	);

	/**
	 * $input_args args entered using shortcode
	 *
	 * @var array
	 */
	protected $input_args = array();

	/**
	 * $output_args input args merged with defaults
	 *
	 * @var array
	 */
	private $output_args = array();

	/**
	 * $is_form_submitted has the form been submitted?
	 *
	 * @var boolean
	 */
	protected $is_form_submitted = false;

	/**
	 * Construct.
	 */
	public function __construct() {
		$this->do_actions();
		$this->is_form_submitted();
	}

	/**
	 * WordPress hooks.
	 */
	private function do_actions() {
		add_shortcode( 'ffviewpdf', array( $this, 'make_shortcode' ) );
		add_action( 'template_redirect', array( $this, 'process_form' ) ); // or use init.
	}

	/**
	 * Shortcode function.
	 *
	 * @param  array  $atts    shortcode args.
	 * @param  string $content shorcode content.
	 * @return array           content for output to screen by shortcode.
	 */
	public function make_shortcode( $atts, $content = null ) {
		$this->input_args = $atts; // to-do: process $content as html.
		$this->set_args();

		if ( ! $this->is_form_submitted ) {
			return $this->insert_form();
		}
	}

	/**
	 * Sets the args before and after form submission.
	 */
	private function set_args() {
		if ( $this->is_form_submitted ) {
			$this->output_args = $this->process_post_args();
		} else {
			$this->output_args = $this->process_input_args();
		}
	}

	/**
	 * Processes input args from shortcode.
	 *
	 * @return array input args merged with defaults.
	 */
	private function process_input_args() {
		$args = \shortcode_atts( // same as array merge ...
			$this->default_args,
			$this->input_args,
			'ffviewpdf', // ... but has hook
		);
		return $args;
	}

	/**
	 * Processes args after form submission.
	 *
	 * @return array args ready for output
	 */
	protected function process_post_args() {
		foreach ( $this->default_args as $key => $value ) {
			if ( isset( $_POST[ $key ] ) ) {
				$args[ $key ] = \sanitize_key( $_POST[ $key ] );
			}
		}
		// $args = $this->merge_args( $args ); // do I need to do this?
		return $args;
	}

	/* protected function merge_args( $array ) {
		$args = array_merge( $array, $this->default_args );
		return $args;
	} */

	/**
	 * Displays a button to trigger the PDF download.
	 *
	 * @return string html form
	 */
	public function insert_form() {
		$html = '<form method="post" action="?action=download-ffviewpdf" enctype="multipart/form-data">
		 <input type="hidden" name="action" value="download-ffviewpdf">';

		foreach ( $this->defaults as $key => $value ) {
			$html .= '<input type="hidden" name="' . $this->output_args[ $key ] . '" value="' . $this->output_args[ $key ] . '">';
		}

		$html .= '<input type="submit" value="Download PDF">';
		$html .= \wp_nonce_field( 'ffviewpdf_form', 'ffviewpdf_form_nonce', true, false );
		$html .= '</form>';

		return $html;
	}

	/**
	 * Outputs the generated PDF.
	 *
	 * @return void
	 */
	public function process_form() {
		if ( $this->is_form_submitted ) {
			$this->set_args(); // to-do: process $content as html.
			$html = $this->get_pdf_content( $this->output_args['type'] );

			$mpdf_args['mode']        = $this->output_args['encoding'];
			$mpdf_args['orientation'] = $this->output_args['orientation'];

			$mpdf = new \Mpdf\Mpdf( $mpdf_args );

			$mpdf->SetDirectionality( $this->output_args['direction'] );

			$mpdf->autoScriptToLang = $this->output_args['auto_script_to_lang'];
			$mpdf->autoLangToFont   = $this->output_args['auto_lang_to_font'];
			$mpdf->baseScript       = 1;

			$stylesheet = \file_get_contents( WP_FFVIEW_PDF_PATH . 'style.css' );

			$mpdf->WriteHTML( $stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS );
			$mpdf->WriteHTML( $html, \Mpdf\HTMLParserMode::HTML_BODY );

			$mpdf->Output( $this->output_args['filename'], \Mpdf\Output\Destination::DOWNLOAD );
		}
	}

	/**
	 * Gets the content to be inserted into the PDF.
	 *
	 * @param  string $type the type of content (page, view, html).
	 * @return string the content
	 */
	private function get_pdf_content( $type ) {
		switch ( $type ) {
			case 'page':
				return \get_the_content();
			case 'view':
				if ( function_exists( 'load_formidable_forms' ) ) { // to-do: function check for Views.
					return \FrmViewsDisplaysController::get_shortcode( array( 'id' => $this->output_args['viewid'] ) );
				} else {
					return __( 'Formidable Forms not found.', 'ffviewpdf_textdomain' );
				}
			case 'html':
				return 'html'; // to-do.
		}
	}

	/**
	 * Checks if the form has been submitted.
	 */
	private function is_form_submitted() {
		if ( isset( $_POST['action'] ) && 'download-ffviewpdf' === \sanitize_key( $_POST['action'] ) ) {
			if ( $this->check_nonce() === false ) {
				$this->is_form_submitted = false;
			} else {
				$this->is_form_submitted = true;
			}
		} else {
			$this->is_form_submitted = false;
		}
	}

	/**
	 * Checks the form nonce to make sure it hasn't been tampered with
	 *
	 * @return boolean false if nonce check fails
	 */
	private function check_nonce() {
		if ( ! isset( $_POST['ffviewpdf_form_nonce'] ) || ! \wp_verify_nonce( \sanitize_key( $_POST['ffviewpdf_form_nonce'] ), 'ffviewpdf_form' ) ) {
			return false;
		}
	}


}
$ffviewpdf = new FFVIEWPDF();