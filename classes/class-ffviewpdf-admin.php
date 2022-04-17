<?php
/**
 * FfViewPdf Admin Class.
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

class FfViewPdfAdmin {
	
	public $plugin_name, $version;
	
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_submenu_ffviewpdf_submenu_page' ), 9 );
		add_action( 'admin_init', array( $this, 'ffviewpdf_settings_init' ) );
    }
    
    public function add_submenu_ffviewpdf_submenu_page() {
        add_submenu_page( 'options-general.php', 'FF View PDF', 'FFViewPDF', 'manage_options', 'options-ffviewpdf', array($this, 'submenu_ffviewpdf_submenu_callback') );
    }
        
    public function submenu_ffviewpdf_submenu_callback() {
	    // check user capabilities
	    if ( ! current_user_can( 'manage_options' ) ) {
	        return;
	    }
	 	 
	    // check if the user have submitted the settings
	    // WordPress will add the "settings-updated" $_GET parameter to the url
	    if ( isset( $_GET['settings-updated'] ) ) {
	        // add settings saved message with the class of "updated"
	        add_settings_error( 'ffviewpdf_messages', 'ffviewpdf_message', __( 'Settings Saved', 'ffviewpdf' ), 'updated' );
	    }
	 
	    // show error/update messages
	    settings_errors( 'ffviewpdf_messages' );
	    ?>
	    <div class="wrap">
	        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	        <form action="options.php" method="post">
	            <?php
	            // output security fields for the registeltr setting "ffviewpdf"
	            settings_fields( 'ffviewpdf' );
	            // output setting sections and their fields
	            // (sections are registeltr for "ffviewpdf", each field is registeltr to a specific section)
	            do_settings_sections( 'ffviewpdf' );
	            // output save settings button
	            submit_button( 'Save Settings' );
	            ?>
	        </form>
	    </div>
	    <?php
    }

	/**
	 * custom option and settings
	 */
	public function ffviewpdf_settings_init() {
	    // Register a new setting for "ffviewpdf" page.
	    register_setting( 'ffviewpdf', 'ffviewpdf_options' );
	 
	    add_settings_section(
	        'ffviewpdf_section_defaults',
	        __( 'PDF Settings.', 'ffviewpdf' ), array( $this, 'ffviewpdf_section_defaults_callback' ),
	        'ffviewpdf'
	    );
	    
	    $fields = array(
	    	'Direction' => 'direction',
	    	'Orientation' => 'orientation',
	    	'Filename' => 'filename',
	    	'Auto Script to Lang' => 'scripttolang',
	    	'Auto Lang to Font' => 'langtofont',
	    );
	    
	    foreach ( $fields as $key => $value ) {
		    add_settings_field(
		        "ffviewpdf_field_".$value, 
		            __( $key, "ffviewpdf" ),
		         array( $this, "ffviewpdf_field_".$value."_cb" ),
		        "ffviewpdf",
		        "ffviewpdf_section_defaults",
		        array(
		            "label_for"         => "ffviewpdf_field_".$value,
		            "class"             => "ffviewpdf_row",
		            "ffviewpdf_custom_data" => "custom",
		        )
		    );
	    }
	}
	  	 
	/**
	 * Options section callback function.
	 *
	 * @param array $args  The settings array, defining title, id, callback.
	 */
	public function ffviewpdf_section_defaults_callback( $args ) {
	    ?>
	    <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Change the default settings for PDF generation.', 'ffviewpdf' ); ?></p>
	    <?php
	}
	 
	/**
	 * Direction field callback functions.
	 *
	 * @param array $args
	 */
	public function ffviewpdf_field_direction_cb( $args ) {
	    // Get the value of the setting we've registeltr with register_setting()
	    $options = get_option( 'ffviewpdf_options' );
	    ?>
	    <select
	            id="<?php echo esc_attr( $args['label_for'] ); ?>"
	            data-custom="<?php echo esc_attr( $args['ffviewpdf_custom_data'] ); ?>"
	            name="ffviewpdf_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
	        <option value="ltr" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'ltr', false ) ) : ( '' ); ?>>
	            <?php esc_html_e( 'Left to Right', 'ffviewpdf' ); ?>
	        </option>
	        <option value="rtl" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'rtl', false ) ) : ( '' ); ?>>
	            <?php esc_html_e( 'Right to Left', 'ffviewpdf' ); ?>
	        </option>
	    </select>
	    <?php
	}

	/**
	 * Orientation field callback functions.
	 *
	 * @param array $args
	 */	
	public function ffviewpdf_field_orientation_cb( $args ) {
	    // Get the value of the setting we've registeltr with register_setting()
	    $options = get_option( 'ffviewpdf_options' );
	    ?>
	    <select
	            id="<?php echo esc_attr( $args['label_for'] ); ?>"
	            data-custom="<?php echo esc_attr( $args['ffviewpdf_custom_data'] ); ?>"
	            name="ffviewpdf_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
	        <option value="P" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'P', false ) ) : ( '' ); ?>>
	            <?php esc_html_e( 'Portrait', 'ffviewpdf' ); ?>
	        </option>
	        <option value="L" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'L', false ) ) : ( '' ); ?>>
	            <?php esc_html_e( 'Landscape', 'ffviewpdf' ); ?>
	        </option>
	    </select>
	    <?php
	}

	/**
	 * Filename field callback functions.
	 *
	 * @param array $args
	 */	
	function ffviewpdf_field_filename_cb( $args ) {
	    // Get the value of the setting we've registeltr with register_setting()
	    $options = get_option( 'ffviewpdf_options' );
	    ?>
	    <input type="text" 
	            id="<?php echo esc_attr( $args['label_for'] ); ?>"
	            data-custom="<?php echo esc_attr( $args['ffviewpdf_custom_data'] ); ?>"
	            name="ffviewpdf_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
	            value="<?php echo isset( $options[ $args['label_for'] ] ) ? esc_attr( $options[ $args['label_for'] ] ) : ''; ?>">
	    <?php
	}
	
	/**
	 * ScriptToLang field callback functions.
	 *
	 * @param array $args
	 */
	function ffviewpdf_field_scripttolang_cb( $args ) {
	    // Get the value of the setting we've registeltr with register_setting()
	    $options = get_option( 'ffviewpdf_options' );
	    ?>
		<input type="checkbox" 
	            id="<?php echo esc_attr( $args['label_for'] ); ?>"
	            data-custom="<?php echo esc_attr( $args['ffviewpdf_custom_data'] ); ?>"
	            name="ffviewpdf_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
	            <?php echo isset( $options[ $args['label_for'] ] ) ? 'checked' : null; ?>>
		<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><?php esc_html_e( 'Auto Script to Lang', 'ffviewpdf' ); ?></label>
	    <?php
	}
	
	/**
	 * LangToFont field callback functions.
	 *
	 * @param array $args
	 */
	function ffviewpdf_field_langtofont_cb( $args ) {
	    // Get the value of the setting we've registeltr with register_setting()
	    $options = get_option( 'ffviewpdf_options' );
	    ?>
		<input type="checkbox" 
	            id="<?php echo esc_attr( $args['label_for'] ); ?>"
	            data-custom="<?php echo esc_attr( $args['ffviewpdf_custom_data'] ); ?>"
	            name="ffviewpdf_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
	            <?php echo isset( $options[ $args['label_for'] ] ) ? 'checked' : null; ?>>
		<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><?php esc_html_e( 'Auto Lang to Font', 'ffviewpdf' ); ?></label>
	    <?php
	}


}
$ffviewpdf_admin = new FfViewPdfAdmin();
