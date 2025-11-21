<?php
/**
 * Nexter Block Pro Plugin.
 *
 * @package TPGBP
 * @since 4.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'nxt_activecampaign_action_callback' ) ) {

    class nxt_activecampaign_action_callback {
        private $api_key;
        private $api_url;

        public function __construct($api_key, $api_url) {
            $this->api_key = $this->validate_api_key($api_key);
            $this->api_url = rtrim($this->validate_api_url($api_url), '/') . '/api/3/';
        }

        private function validate_api_key($api_key) {
            if (!preg_match('/^[a-zA-Z0-9]+$/', $api_key)) {
                throw new Exception('Invalid API key format.');
            }
            return sanitize_text_field($api_key);
        }

        private function validate_api_url($api_url) {
            if (!filter_var($api_url, FILTER_VALIDATE_URL)) {
                throw new Exception('Invalid API URL format.');
            }
            return esc_url_raw($api_url);
        }

        public function create_subscriber($subscriber_data) {
            $endpoint = $this->api_url . 'contacts';
            $response = wp_remote_post($endpoint, [
                'method'    => 'POST',
                'headers'   => [
                    'Api-Token'     => $this->api_key,
                    'Content-Type'  => 'application/json',
                ],
                'body'      => json_encode($subscriber_data),
            ]);

            if (is_wp_error($response)) {
                return $response; 
            }

            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            $response_body_decoded = json_decode($response_body, true);

            if ($response_code === 201 && isset($response_body_decoded['contact']) && isset($response_body_decoded['contact']['id'])) {
                return $response_body_decoded; 
            }

            return new WP_Error('activecampaign_error', 'Failed to add contact to ActiveCampaign. Response: ' . $response_body);
        }
    }
}
