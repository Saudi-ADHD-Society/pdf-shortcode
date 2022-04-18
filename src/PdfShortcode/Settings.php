<?php
/**
 * Settings Class.
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
 * Class Settings
 */
class Settings {
	
	private $plugin_name = 'PDF Shortcode',
			$plugin_dash = 'pdf-shortcode',
			$plugin_underscore = 'pdf_shortcode';

	/**
	 * Construct.
	 */
	public function __construct() {
		\add_action( 'admin_menu', array( $this, 'add_submenu_page' ), 9 );
		\add_action( 'admin_init', array( $this, 'settings_init' ) );
	}

	/**
	 * Add submenu page.
	 */
	public function add_submenu_page() {
		\add_submenu_page( 'options-general.php', $this->plugin_name, $this->plugin_name, 'manage_options', $this->plugin_dash . '-options', array( $this, 'submenu_callback' ) );
	}

	/**
	 * Submenu callback.
	 */
	public function submenu_callback() {
		// check user capabilities.
		if ( ! \current_user_can( 'manage_options' ) ) {
			return;
		}

		// check if the user have submitted the settings.
		if ( isset( $_GET['settings-updated'] ) ) {
			// add settings saved message with the class of "updated".
			\add_settings_error( 'pdfshortcode_messages', 'pdfshortcode_message', __( 'Settings Saved', 'pdfshortcode' ), 'updated' );
		}

		// show error/update messages.
		settings_errors( 'pdfshortcode_messages' );

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				\settings_fields( 'pdfshortcode' );
				\do_settings_sections( 'pdfshortcode' );
				\submit_button( 'Save Settings' );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Custom options and settings.
	 */
	public function settings_init() {
		// Register a new setting for "pdfshortcode" page.
		\register_setting( 'pdfshortcode', 'pdfshortcode_options' );

		\add_settings_section(
			'pdfshortcode_section_defaults',
			__( 'PDF Settings', 'pdfshortcode' ),
			array( $this, 'section_defaults_callback' ),
			'pdfshortcode'
		);

		$fields = array(
			'Direction'           => 'direction',
			'Orientation'         => 'orientation',
			'Filename'            => 'filename',
			'Auto Script to Lang' => 'scripttolang',
			'Auto Lang to Font'   => 'langtofont',
		);

		foreach ( $fields as $key => $value ) {
			\add_settings_field(
				'pdfshortcode_field_' . $value,
				__( $key, 'pdfshortcode' ),
				array( $this, 'field_' . $value . '_callback' ),
				'pdfshortcode',
				'pdfshortcode_section_defaults',
				array(
					'label_for'             => 'pdfshortcode_field_' . $value,
					'class'                 => 'pdfshortcode_row',
					'pdfshortcode_custom_data' => 'custom',
				)
			);
		}
	}

	/**
	 * Options section callback function.
	 *
	 * @param array $args The settings array, defining title, id, callback.
	 */
	public function section_defaults_callback( $args ) {
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Change the default settings for PDF generation.', 'pdfshortcode' ); ?></p>
		<?php
	}

	/**
	 * Direction field callback functions.
	 *
	 * @param array $args
	 */
	public function field_direction_callback( $args ) {
		$options = \get_option( 'pdfshortcode_options' );
		?>
		<select
				id="<?php echo esc_attr( $args['label_for'] ); ?>"
				data-custom="<?php echo esc_attr( $args['pdfshortcode_custom_data'] ); ?>"
				name="pdfshortcode_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
			<option value="ltr" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'ltr', false ) ) : ( '' ); ?>>
				<?php esc_html_e( 'Left to Right', 'pdfshortcode' ); ?>
			</option>
			<option value="rtl" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'rtl', false ) ) : ( '' ); ?>>
				<?php esc_html_e( 'Right to Left', 'pdfshortcode' ); ?>
			</option>
		</select>
		<?php
	}

	/**
	 * Orientation field callback functions.
	 *
	 * @param array $args
	 */
	public function field_orientation_callback( $args ) {
		$options = \get_option( 'pdfshortcode_options' );
		?>
		<select
				id="<?php echo esc_attr( $args['label_for'] ); ?>"
				data-custom="<?php echo esc_attr( $args['pdfshortcode_custom_data'] ); ?>"
				name="pdfshortcode_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
			<option value="P" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'P', false ) ) : ( '' ); ?>>
				<?php esc_html_e( 'Portrait', 'pdfshortcode' ); ?>
			</option>
			<option value="L" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'L', false ) ) : ( '' ); ?>>
				<?php esc_html_e( 'Landscape', 'pdfshortcode' ); ?>
			</option>
		</select>
		<?php
	}

	/**
	 * Filename field callback functions.
	 *
	 * @param array $args
	 */
	public function field_filename_callback( $args ) {
		$options = \get_option( 'pdfshortcode_options' );
		?>
		<input type="text"
				id="<?php echo esc_attr( $args['label_for'] ); ?>"
				data-custom="<?php echo esc_attr( $args['pdfshortcode_custom_data'] ); ?>"
				name="pdfshortcode_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
				value="<?php echo isset( $options[ $args['label_for'] ] ) ? esc_attr( $options[ $args['label_for'] ] ) : ''; ?>">
		<?php
	}

	/**
	 * ScriptToLang field callback functions.
	 *
	 * @param array $args
	 */
	public function field_scripttolang_callback( $args ) {
		$options = \get_option( 'pdfshortcode_options' );

		?>
		<input type="checkbox"
				id="<?php echo esc_attr( $args['label_for'] ); ?>"
				data-custom="<?php echo esc_attr( $args['pdfshortcode_custom_data'] ); ?>"
				name="pdfshortcode_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
				value=1<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
		<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><?php esc_html_e( 'Auto Script to Lang', 'pdfshortcode' ); ?></label>
		<?php
	}

	/**
	 * LangToFont field callback functions.
	 *
	 * @param array $args
	 */
	public function field_langtofont_callback( $args ) {
		$options = \get_option( 'pdfshortcode_options' );
		?>
		<input type="checkbox"
				id="<?php echo esc_attr( $args['label_for'] ); ?>"
				data-custom="<?php echo esc_attr( $args['pdfshortcode_custom_data'] ); ?>"
				name="pdfshortcode_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
				value=1<?php checked( isset( $options[ $args['label_for'] ] ) ); ?>>
		<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><?php esc_html_e( 'Auto Lang to Font', 'pdfshortcode' ); ?></label>
		<?php
	}

}