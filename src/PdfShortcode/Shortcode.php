<?php
/**
 * Shortcode Class.
 *
 * @package jvarn\pdf-shortcode
 */

namespace Jvarn\PdfShortcode;

use Jvarn\PdfShortcode\Encryption as Encryption;

/**
 * No direct access
 */
if ( ! defined( 'ABSPATH' ) ) {
	wp_die();
}

/**
 * Class Shortcode
 */
class Shortcode {

	/**
	 * Default args.
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
		'filename'            => 'download',
		'auto_script_to_lang' => '',
		'auto_lang_to_font'   => '',
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
		add_shortcode( 'wp2pdf', array( $this, 'make_shortcode' ) );
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
	 * @todo Why isn't filename getting merged from default when not set in settings or shortcode?
	 */
	protected function get_default_settings() {
		if ( \get_option( 'pdfshortcode_options' ) ) {
			$options = \get_option( 'pdfshortcode_options' );

			// Cludge.
			if ( null == $options['pdfshortcode_field_filename'] ) {
				$options['pdfshortcode_field_filename'] = $this->default_args['filename'];
			}

			$args    = array(
				'orientation'         => $options['pdfshortcode_field_orientation'],
				'direction'           => $options['pdfshortcode_field_direction'],
				'filename'            => $options['pdfshortcode_field_filename'],
				'auto_script_to_lang' => $options['pdfshortcode_field_scripttolang'],
				'auto_lang_to_font'   => $options['pdfshortcode_field_langtofont'],
			);

			$merged  = \shortcode_atts(
				$this->default_args,
				$args,
				'pdfshortcode-saved-settings',
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
			'pdfshortcode-default-settings', // ... but has hook
		);
		return $args;
	}

	/**
	 * Processes args after form submission.
	 *
	 * @return array args ready for output
	 * @todo   why is this being called twice?
	 */
	protected function process_post_args() {
		$options = $this->decrypt_args( $_POST['options'] );
		foreach ( $this->default_args as $key => $value ) {
			if ( isset( $options[ $key ] ) ) {
				$args[ $key ] = $options[ $key ];
			}
		}

		return $args;
	}

	/**
	 * Hash args
	 *
	 * Implodes args array into string and makes hash.
	 *
	 * @param array $args Shortcode Args.
	 * @return string hash
	 */
	protected function hash_args( $args ) {
		$args_string = http_build_query( $args, '', ',' );
		$args_string = sanitize_key( $args_string );
		return wp_hash( $args_string );
	}

	/**
	 * Encrypt Args
	 *
	 * Encrypts and implodes args array into string.
	 *
	 * @param array $args_array Shortcode Args.
	 * @return string encrypted string
	 */
	protected function encrypt_args( $args_array ) {
		$args_string     = http_build_query( $args_array, '', ',' );
		$args_string_enc = Encryption::encrypt( $args_string );
		return $args_string_enc;
	}

	/**
	 * Decrypt Args
	 *
	 * Decrypts and explodes previously imploded string.
	 *
	 * @param string $args_string_enc Encoded string.
	 * @return array Shortcode Args array.
	 */
	protected function decrypt_args( $args_string_enc ) {
		$args_string = Encryption::decrypt( $args_string_enc );
		$args        = explode( ',', $args_string );
		foreach ( $args as $arg ) {
			$item              = explode( '=', $arg );
			$array[ $item[0] ] = $item[1];
		}
		return $array;
	}

	/**
	 * Displays a button to trigger the PDF download.
	 *
	 * @return string html form
	 * @todo add key to nonce field to make unique ID if more than one pdfshortcode shortcode is used on the same page.
	 */
	public function insert_form() {
		$hash_args = $this->hash_args( $this->output_args );
		$html      = '<form method="post" action="?action=download-pdfshortcode" enctype="multipart/form-data">';
		$html     .= '<input type="hidden" name="action" value="download-pdfshortcode">';
		$html     .= '<input type="hidden" name="pdfshortcode_form_check" value="' . $hash_args . '">';

		// For debugging.
		/*foreach ( $this->default_args as $key => $value ) {
			$html .= '<input type="hidden" name="' . $key . '" value="' . $this->output_args[ $key ] . '">';
		}*/

		$html .= '<input type="hidden" name="options" value="' . $this->encrypt_args( $this->output_args ) . '">';

		$html .= '<input type="submit" value="' . \__( 'Download PDF', 'pdfshortcode' ) . '">';
		$html .= \wp_nonce_field( 'pdfshortcode_form', 'pdfshortcode_form_nonce', true, false );
		$html .= '</form>';

		return $html;
	}

	/**
	 * Outputs the generated PDF.
	 *
	 * Must be public so it can be called by WordPress add_action().
	 *
	 * @return void
	 * @see https://mpdf.github.io/ mPdf Manual
	 */
	public function process_form() {
		if ( $this->is_form_submitted ) {
			$this->set_args();
			if ( $this->check_hash() ) {
				$html = $this->get_pdf_content( $this->output_args['type'] );

						/*$args_string = http_build_query( $this->output_args, '', ',' );
						$html .= $args_string;*/

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
	 * @todo   do html case using shortcode $content.
	 * @todo   add define_type() method.
	 */
	private function get_pdf_content( $type ) {
		switch ( $type ) {
			case 'page':
				return \get_the_content();
			case 'view':
				if ( function_exists( 'load_formidable_views' ) ) {
					return \FrmViewsDisplaysController::get_shortcode( array( 'id' => $this->output_args['viewid'] ) );
				} else {
					return \__( 'Formidable Forms Visual Views not found.', 'pdfshortcode' );
				}
			case 'html':
				return 'html';
		}
	}

	/**
	 * Checks if the form has been submitted.
	 */
	protected function is_form_submitted() {
		if ( isset( $_POST['action'] ) && 'download-pdfshortcode' === \sanitize_key( $_POST['action'] ) ) {
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
		if ( ! isset( $_POST['pdfshortcode_form_nonce'] ) || ! \wp_verify_nonce( \sanitize_key( $_POST['pdfshortcode_form_nonce'] ), 'pdfshortcode_form' ) ) {
			return false;
		}
	}

	/**
	 * Checks the form nonce to make sure it hasn't been tampered with
	 *
	 * @return boolean false if nonce check fails
	 */
	protected function check_hash() {
		if ( $this->hash_args( $this->output_args ) === sanitize_key( $_POST['pdfshortcode_form_check'] ) ) {
			return true;
		}
	}

}
