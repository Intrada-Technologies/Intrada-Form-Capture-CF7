<?php
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

// Uninstall the plugin and clean up settings
if (defined('WP_UNINSTALL_PLUGIN')) {
  // Delete plugin options
  delete_option('icf7_form_capture_settings');
  delete_option('icf7_intrada_webhook_url');
  delete_option('icf7_intrada_site_id');
  delete_option('icf7_intrada_site_secret');
  delete_option('icf7_intrada_api_key');
}