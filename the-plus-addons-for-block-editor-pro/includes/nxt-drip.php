<?php
/**
 * TPGB Pro Plugin.
 *
 * @package TPGBP
 * @since 4.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! class_exists( 'Nexter_DripIntegration' ) ){
    class Nexter_DripIntegration {
        private $account_id;
        private $api_key;

        public function __construct($account_id, $api_key) {
            $this->account_id = $account_id;
            $this->api_key = $api_key;
        }

        public function addSubscriber($subscriber_data) {
            $endpoint = "https://api.getdrip.com/v2/{$this->account_id}/subscribers";
            $response = wp_remote_post($endpoint, [
                'method'    => 'POST',
                'headers'   => [
                    'Authorization' => 'Basic ' . base64_encode("{$this->api_key}:"),
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

            if ($response_code === 200 || $response_code === 201) {
                return $response_body_decoded;
            }

            return new WP_Error('drip_error', 'Failed to add subscriber to Drip. Response: ' . $response_body);
        }
    }
}