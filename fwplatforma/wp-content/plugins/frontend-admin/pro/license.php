<?php
namespace Frontend_Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! class_exists( 'Pro_License' ) ) :

	class Pro_License {


		private $api_url = '';

		/**
		 * Check license key when user submits it.
		 *
		 * @param array $_transient_data Update array build by WordPress.
		 *
		 * @return array Modified update array with custom plugin data.
		 * @uses   api_request()
		 */
		public function check_license( $form ) {
			if ( $form['hidden_fields']['admin_page'] == 'license' ) {
				$key = $form['record']['fields']['admin_options']['fea_main_license_key']['_input'];

				$is_valid = 0;

				if ( $key ) {
					$api_params = array(
						'action'      => 'activate',
						'license_key' => $key,
						'license_url' => home_url(),
					);
					$request    = wp_remote_post(
						$this->api_url,
						array(
							'timeout'   => 15,
							'sslverify' => $this->verify_ssl(),
							'body'      => $api_params,
						)
					);

					if ( ! is_wp_error( $request ) ) {
						$request = json_decode( wp_remote_retrieve_body( $request ) );

						if ( isset( $request->license_status ) && $request->license_status == 'valid' ) {
							$is_valid = 1;
						}
					}
				}

				update_option( 'fea_main_license_valid', $is_valid );

			}

		}

		/**
		 * Returns if the SSL of the store should be verified.
		 *
		 * @return bool
		 * @since  1.0
		 */
		private function verify_ssl() {
			 return (bool) apply_filters( 'paddlepress_api_request_verify_ssl', true, $this );
		}
		public function __construct( $_api_url ) {
			$this->api_url = $_api_url;

			
			add_action( 'frontend_admin/save_admin_options', array( $this, 'check_license' ), 10, 1 );

		}
	}
	new Pro_License(
		'https://dynamiapps.com/wp-json/paddlepress-api/v1/license'
	);


endif;
