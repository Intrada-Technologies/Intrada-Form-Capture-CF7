<?php

/**
 * Plugin Name:       Intrada Contact Form 7 Form Capture
 * Plugin URI:        https://github.com/Intrada-Technologies/Intrada-Form-Capture-CF7/
 * Description:       Captures Contact Form 7 submissions and sends them to a custom endpoint.
 * Version:           1.0.5
 * Author:            Intrada Technologies
 * Author URI:        https://intradatech.com/
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       cf7-intrada-form-capture
 * Requires Plugins:  contact-form-7
 * Update URI:        https://raw.githubusercontent.com/Intrada-Technologies/Intrada-Form-Capture-CF7/main/info.json
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Load plugin text domain for translations
 */
function icf7_intrada_form_capture_load_textdomain()
{
  load_plugin_textdomain('icf7-intrada-form-capture', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'icf7_intrada_form_capture_load_textdomain');


function icf7_intrada_form_capture_init()
{
  $settings_file = plugin_dir_path(__FILE__) . 'includes/class-icf7-settings-integration.php';
  $capture_file  = plugin_dir_path(__FILE__) . 'includes/class-icf7-form-capture.php';
  $update_file   = plugin_dir_path(__FILE__) . 'includes/update.php';

  if (file_exists($update_file)) {
    require_once $update_file;
    new icf7_intrada_form_capture_plugin_update();
  } else {
    error_log('Intrada Form Capture: Missing update file.');
  }


  // Check if required files exist
  if (!file_exists($settings_file)) {
    error_log('Intrada Form Capture: Missing settings integration file.');
    return;
  }
  if (!file_exists($capture_file)) {
    error_log('Intrada Form Capture: Missing form capture file.');
    return;
  }

  require_once $settings_file;
  require_once $capture_file;

  // Check if classes exist before instantiating
  if (!class_exists('icf7_Settings_Integration')) {
    error_log('Intrada Form Capture: icf7_Settings_Integration class not found.');
    return;
  }
  if (!class_exists('icf7_Form_Capture')) {
    error_log('Intrada Form Capture: icf7_Form_Capture class not found.');
    return;
  }

  try {
    $icf7_settings_integration = new icf7_Settings_Integration();
    $icf7_settings_integration->icf7_register();

    $icf7_form_capture = new icf7_Form_Capture();
  } catch (Exception $e) {
    error_log('Intrada Form Capture: Exception - ' . $e->getMessage());
  }
}
add_action('init', 'icf7_intrada_form_capture_init');


/**
 * Add custom links to the plugin row on the plugins page.
 */
function intrada_cf7_add_plugin_links() {
    $plugin_basename = plugin_basename( __FILE__ );

    // Add "Settings" link to the main action links
    add_filter( 'plugin_action_links_' . $plugin_basename, 'intrada_cf7_add_settings_action_link' );

    // Add "View details" link to the meta links
    add_filter( 'plugin_row_meta', 'intrada_cf7_add_details_meta_link', 10, 2 );
}
add_action( 'admin_init', 'intrada_cf7_add_plugin_links' );

/**
 * Adds the "Settings" link.
 */
function intrada_cf7_add_settings_action_link( $links ) {
    $settings_link = sprintf(
        '<a href="admin.php?page=icf7-intrada-form-capture-settings">%s</a>',
        esc_html__( 'Settings', 'cf7-intrada-form-capture' )
    );
    array_unshift( $links, $settings_link );
    return $links;
}

/**
 * Adds the "View details" link.
 */
function intrada_cf7_add_details_meta_link( $links, $file ) {
    // Ensure this is your plugin
    if ( plugin_basename( __FILE__ ) === $file ) {
        // Use your Text Domain for the slug
        $plugin_slug = 'intrada-cf7-form-capture';
        $details_url = add_query_arg(
            [
                'tab'       => 'plugin-information',
                'plugin'    => $plugin_slug,
                'TB_iframe' => 'true',
                'width'     => '772',
                'height'    => '550',
            ],
            admin_url( 'plugin-install.php' )
        );

        $links[] = sprintf(
            '<a href="%s" class="thickbox" title="%s">%s</a>',
            esc_url( $details_url ),
            esc_attr__( 'More information about the Intrada CF7 Capture Integration plugin', 'cf7-intrada-form-capture' ),
            esc_html__( 'View details', 'cf7-intrada-form-capture' )
        );
    }
    return $links;
}
