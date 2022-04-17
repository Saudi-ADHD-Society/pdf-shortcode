<?php
/**
 * FfViewPdf Class.
 *
 * @package jvarn\ffviewpdf
 */

namespace jvarn\FfViewPdf;

/**
 * No direct access
 */
if ( ! defined( 'ABSPATH' ) ) {
	wp_die();
}

/**
 * Class FfViewPdf
 */

namespace jvarn\FfViewPdf;

class FfViewPdf {

	/**
	 * [$default_args default args]
	 *
	 * @var array
	 * @todo change 'viewid' to 'id' so it can be used with page as well.
	 */
	private $default_args = array(
		'viewid'              => 1,
		'type'                => 'view',
		'encoding'            => 'utf-8',
		'orientation'         => 'P',
		'direction'           => 'ltr',
		'filename'            => 'filename',
		'auto_script_to_lang' => null,
		'auto_lang_to_font'   => null,
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
	protected $output_args = array();

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
	 * @param  string $content shortcode content.
	 * @return array           content for output to screen by shortcode
	 * @todo   process $content as html (see get_pdf_content()).
	 */
	public function make_shortcode( $atts, $content = null ) {
		$this->input_args = $atts;
		$this->set_args();

		if ( ! $this->is_form_submitted ) {
			return $this->insert_form();
		}
	}
	
	/**
	 * Gets the settings from the backend.
	 *
	 * @see class-ffviewpdf-admin.php Admin settings
	 */
	protected function get_default_settings() {
		if ( \get_option( 'ffviewpdf_options' ) ) {
			$options = \get_option( 'ffviewpdf_options' );
			$args = array(
				'orientation'         => $options['ffviewpdf_field_orientation'],
				'direction'           => $options['ffviewpdf_field_direction'],
				'filename'            => $options['ffviewpdf_field_filename'],
				'auto_script_to_lang' => $options['ffviewpdf_field_scripttolang'],
				'auto_lang_to_font'   => $options['ffviewpdf_field_langtofont'],
			);
			$merged = \shortcode_atts(
				$this->default_args,
				$args,
				'ffviewpdf-saved-settings',
			);

			$this->default_args = $merged;
		}
	}

	/**
	 * Sets the args before and after form submission.
	 */
	private function set_args() {
		$this->get_default_settings();
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
			'ffviewpdf-default-settings', // ... but has hook
		);
		return $args;
	}

	/**
	 * Processes args after form submission.
	 *
	 * @return array args ready for output
	 * @todo   test if array merge is necessary.
	 */
	protected function process_post_args() {
		foreach ( $this->default_args as $key => $value ) {
			if ( isset( $_POST[ $key ] ) ) {
				$args[ $key ] = \sanitize_key( $_POST[ $key ] );
			}
		}
		return $args;
	}

	/**
	 * Displays a button to trigger the PDF download.
	 *
	 * @return string html form
	 * @todo add key to nonce field to make unique ID if more than one ffviewpdf shortcode is used on the same page.
	 */
	public function insert_form() {
		$html = '<form method="post" action="?action=download-ffviewpdf" enctype="multipart/form-data">
		 <input type="hidden" name="action" value="download-ffviewpdf">';

		foreach ( $this->default_args as $key => $value ) {
			$html .= '<input type="hidden" name="' . $key . '" value="' . $this->output_args[ $key ] . '">';
		}

		$html .= '<input type="submit" value="' . \__( 'Download PDF', 'ffviewpdf' ) . '">';
		$html .= \wp_nonce_field( 'ffviewpdf_form', 'ffviewpdf_form_nonce', true, false );
		$html .= '</form>';

		return $html;
	}

	/**
	 * Outputs the generated PDF.
	 *
	 * Must be public so it can be called by Wordpress add_action().
	 *
	 * @return void
	 * @see https://mpdf.github.io/ mPdf Manual
	 */
	public function process_form() {
		if ( $this->is_form_submitted ) {
			$this->set_args();
			$html = $this->get_pdf_content( $this->output_args['type'] );

			$mpdf_args['mode']        = $this->output_args['encoding'];
			$mpdf_args['orientation'] = $this->output_args['orientation'];

			$mpdf = new \Mpdf\Mpdf( $mpdf_args );

			$mpdf->SetDirectionality( $this->output_args['direction'] );

			$mpdf->autoScriptToLang = ( 1 == $this->output_args['auto_script_to_lang'] ) ? true : false;
			$mpdf->autoLangToFont   = ( 1 == $this->output_args['auto_lang_to_font'] ) ? true : false;
			$mpdf->baseScript       = 1;

			$stylesheet = \file_get_contents( WP_FFVIEW_PDF_PATH . 'style.css' );

			$mpdf->WriteHTML( $stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS );
			$mpdf->WriteHTML( $html, \Mpdf\HTMLParserMode::HTML_BODY );

			$mpdf->Output( $this->output_args['filename'] . '.pdf', \Mpdf\Output\Destination::DOWNLOAD );
		}
	}

	/**
	 * Gets the content to be inserted into the PDF.
	 *
	 * Description of types:
	 * 'view' requires Formidable Forms Pro with Visual Views addon
	 * 'page' can be used with any WordPress installation
	 * 'html' parses content between shortcode opening and closing tags.
	 *
	 * @param  string $type the type of content.
	 * @return string html
	 * @todo   for view do function check for Views addon instead of FF.
	 * @todo   do html case using shortcode $content.
	 * @todo   add define_type() method.
	 */
	private function get_pdf_content( $type ) {
		switch ( $type ) {
			case 'page':
				return \get_the_content();
			case 'view':
				if ( function_exists( 'load_formidable_forms' ) ) {
					return \FrmViewsDisplaysController::get_shortcode( array( 'id' => $this->output_args['viewid'] ) );
				} else {
					return \__( 'Formidable Forms not found.', 'ffviewpdf' );
				}
			case 'html':
				return 'html';
		}
	}

	/**
	 * Checks if the form has been submitted.
	 */
	protected function is_form_submitted() {
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
	protected function check_nonce() {
		if ( ! isset( $_POST['ffviewpdf_form_nonce'] ) || ! \wp_verify_nonce( \sanitize_key( $_POST['ffviewpdf_form_nonce'] ), 'ffviewpdf_form' ) ) {
			return false;
		}
	}


}
$ffviewpdf = new FfViewPdf();
