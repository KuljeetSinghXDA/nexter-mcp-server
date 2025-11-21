<?php
/**
 * TPGB Pro Plugin.
 *
 * @package TPGBP
 * @since 4.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Nexter_MailerLiteIntegration' ) ) {
    class Nexter_MailerLiteIntegration {
        private $api_key;
        private $api_url;

        public function __construct($api_key, $api_url = 'https://connect.mailerlite.com/api/subscribers') {
            $this->api_key = $api_key;
            $this->api_url = $api_url;
        }

        public function addSubscriber($subscriber_data) {
            if (empty($subscriber_data['email']) || empty($subscriber_data['name'])) {
                return new WP_Error('mailerlite_error', 'Email and name are required fields.');
            }

            $data_to_send = [
                'email' => $subscriber_data['email'],
                'fields' => [
                    'name' => $subscriber_data['name']
                ],
                'groups' => $subscriber_data['groups'] ?? []
            ];

            $response = wp_remote_post($this->api_url, [
                'method'    => 'POST',
                'headers'   => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->api_key
                ],
                'body'      => json_encode($data_to_send),
            ]);

            if (is_wp_error($response)) {
                return new WP_Error('mailerlite_error', 'Failed to add subscriber to MailerLite. Error: ' . $response->get_error_message());
            }

            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);

            $response_body_decoded = json_decode($response_body, true);

            if ($response_code === 200 || $response_code === 201) {
                if (isset($response_body_decoded['data']['id'])) {
                    return $response_body_decoded['data']; 
                }
                return new WP_Error('mailerlite_error', 'Failed to add subscriber. Check response details: ' . $response_body);
            }

            return new WP_Error('mailerlite_error', 'Failed to add subscriber to MailerLite. Response: ' . $response_body);
        }
    }
}