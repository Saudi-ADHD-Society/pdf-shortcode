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

	/**
	 * Plugin name.
	 *
	 * @var string
	 */
	private $plugin_name = 'PDF Shortcode';

	/**
	 * Plugin name with hyphen.
	 *
	 * @var string
	 */
	private $plugin_dash = 'pdf-shortcode';

	/**
	 * Plugin name with underscore.
	 *
	 * @var string
	 */
	private $plugin_underscore = 'pdf_shortcode';

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

		echo '<div class="wrap">';
		echo '<h1>' . esc_html( get_admin_page_title() ) . '</h1>';
		echo '<form action="options.php" method="post">';

		\settings_fields( 'pdfshortcode' );
		\do_settings_sections( 'pdfshortcode' );
		\submit_button( 'Save Settings' );

		echo '</form></div>';

	}

	/**
	 * Custom options and settings.
	 *
	 * @todo add translation array to fields array
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
					'label_for'                => 'pdfshortcode_field_' . $value,
					'class'                    => 'pdfshortcode_row',
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
	 * @param array $args Args.
	 */
	public function field_direction_callback( $args ) {
		$options  = \get_option( 'pdfshortcode_options' );
		$issetltr = isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'ltr', false ) ) : ( '' );
		$issetrtl = isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'rtl', false ) ) : ( '' );

		$select  = '<select id="' . esc_attr( $args['label_for'] ) . '"';
		$select .= 'data-custom="' . esc_attr( $args['pdfshortcode_custom_data'] ) . '"';
		$select .= 'name="pdfshortcode_options[' . esc_attr( $args['label_for'] ) . ']">';

		$select .= '<option value="ltr" ' . $issetltr . '>';
		$select .= esc_html__( 'Left to Right', 'pdfshortcode' );
		$select .= '</option>';

		$select .= '<option value="rtl"' . $issetrtl . '>';
		$select .= esc_html__( 'Right to Left', 'pdfshortcode' );
		$select .= '</option>';

		$select .= '</select>';

		echo $select;
	}

	/**
	 * Orientation field callback functions.
	 *
	 * @param array $args Args.
	 */
	public function field_orientation_callback( $args ) {
		$options  = \get_option( 'pdfshortcode_options' );
		$issetp = isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'P', false ) ) : ( '' );
		$issetl = isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'L', false ) ) : ( '' );

		$select  = '<select id="' . esc_attr( $args['label_for'] ) . '"';
		$select .= 'data-custom="' . esc_attr( $args['pdfshortcode_custom_data'] ) . '"';
		$select .= 'name="pdfshortcode_options[' . esc_attr( $args['label_for'] ) . ']">';

		$select .= '<option value="P" ' . $issetp . '>';
		$select .= esc_html__( 'Portrait', 'pdfshortcode' );
		$select .= '</option>';

		$select .= '<option value="L"' . $issetl . '>';
		$select .= esc_html__( 'Landscape', 'pdfshortcode' );
		$select .= '</option>';

		$select .= '</select>';

		echo $select;
	}

	/**
	 * Filename field callback functions.
	 *
	 * @param array $args Args.
	 */
	public function field_filename_callback( $args ) {
		$options = \get_option( 'pdfshortcode_options' );
		$isset   = isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'P', false ) ) : ( '' );

		$input  = '<input type="text" id="' . esc_attr( $args['label_for'] ) . '"';
		$input .= 'data-custom="' . esc_attr( $args['pdfshortcode_custom_data'] ) . '"';
		$input .= 'name="pdfshortcode_options[' . esc_attr( $args['label_for'] ) . ']" ';

		$input .= 'value="' . $isset . '">';

		echo $input;
	}

	/**
	 * ScriptToLang field callback functions.
	 *
	 * @param array $args Args.
	 */
	public function field_scripttolang_callback( $args ) {
		$options = \get_option( 'pdfshortcode_options' );
		$checked = checked( isset( $options[ $args['label_for'] ] ), true, false );

		$checkbox  = '<input type="checkbox" id="' . esc_attr( $args['label_for'] ) . '"';
		$checkbox .= 'data-custom="' . esc_attr( $args['pdfshortcode_custom_data'] ) . '"';
		$checkbox .= 'name="pdfshortcode_options[' . esc_attr( $args['label_for'] ) . ']" ';
		$checkbox .= 'value="1" ' . $checked . '">';

		$checkbox .= '<label for="' . esc_attr( $args['label_for'] ) . '">' . esc_html__( 'Auto Script to Lang', 'pdfshortcode' ) . '</label>';

		echo $checkbox;
	}

	/**
	 * LangToFont field callback functions.
	 *
	 * @param array $args Args.
	 */
	public function field_langtofont_callback( $args ) {
		$options = \get_option( 'pdfshortcode_options' );
		$checked = checked( isset( $options[ $args['label_for'] ] ), true, false );

		$checkbox  = '<input type="checkbox" id="' . esc_attr( $args['label_for'] ) . '"';
		$checkbox .= 'data-custom="' . esc_attr( $args['pdfshortcode_custom_data'] ) . '"';
		$checkbox .= 'name="pdfshortcode_options[' . esc_attr( $args['label_for'] ) . ']" ';
		$checkbox .= 'value="1" ' . $checked . '">';

		$checkbox .= '<label for="' . esc_attr( $args['label_for'] ) . '">' . esc_html__( 'Auto Lang to Font', 'pdfshortcode' ) . '</label>';

		echo $checkbox;
	}

}
