<?php
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

// using this hook: elementor_pro/forms/new_record we need to post to the endpoint saved in the settings
/**
 * Class icf7_Form_Capture
 *
 * Handles the capture of Contact Form 7 submissions and sends them to a custom endpoint.
 */
class icf7_Form_Capture
{
  // get the settings from the database
  private $settings;
  public function __construct()
  {
    // Load settings from the database
    $this->settings = get_option('icf7_form_capture_settings', []);

    // Hook into Contact Form 7's form submission action
    add_action('wpcf7_submit', [$this, 'capture_form_submission'], 10, 2);
  }

  /**
   * Capture the form submission and send it to the custom endpoint.
   *
   * @param \WPCF7_ContactForm $form The form instance.
   * @param \WPCF7_Submission $submission The form submission instance.
   */
  public function capture_form_submission($contact_form, $result)
  {

    $form_name = $contact_form->title;
    $fields = $contact_form->scan_form_tags();

    $submission = WPCF7_Submission::get_instance();
    $posted_data = $submission->get_posted_data();

    $field_lookup = [];
    foreach ($fields as $field) {
      $field_lookup[$field->raw_name] = $field;
    }

    $data = [];
    foreach ($posted_data as $key => $value) {
      $field = isset($field_lookup[$key]) ? $field_lookup[$key] : null;

      $data[$key] = [
        'id' => $key,
        'type' => $field ? $field->type : 'text', // Default to 'text' if type is not found
        'title' => $field ? $field->name : ucfirst(str_replace('-', ' ', $key)), // Use field name or convert key to a readable format
        'value' => $value,
        'raw_value' => $value,
        'required' => $field ? $field->is_required() : false, // Check if the field is required
      ];
    }

    $data['form_name'] = $form_name; // Add form name to the data


    // Reload settings from the database to ensure the latest values
    $settings = get_option('icf7_form_capture_settings', []);

    $webhook_url = get_option('icf7_intrada_webhook_url', '');
    $site_id = get_option('icf7_intrada_site_id', '');
    $site_secret = get_option('icf7_intrada_site_secret', '');
    $api_key = get_option('icf7_intrada_api_key', '');




    // // Check if the webhook URL is set
    if (empty($webhook_url)) {
      error_log('Contact Form 7 Capture: Webhook URL is not set.');
      return;
    }


    // // Send the data to the endpoint
    wp_remote_post($webhook_url, [
      'method' => 'POST',
      'body' => json_encode($data),
      'headers' => [
        'Content-Type' => 'application/json',
        'X-Site-ID' => $site_id,
        'X-Site-Secret' => $site_secret,
        'X-API-Key' => $api_key,
      ],
    ]);
  }
}
