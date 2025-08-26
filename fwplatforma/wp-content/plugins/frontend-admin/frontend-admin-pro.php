<?php

/**
 * Plugin Name: Frontend Admin Pro
 * Plugin URI: https://wordpress.org/plugins/frontend-admin/
 * Description: This awesome plugin allows you to easily display admin forms to the frontend of your site so your clients can easily edit content on their own from the frontend.
 * Version:     3.15.3
 * Author:      Shabti Kaplan
 * Author URI:  https://www.dynamiapps.com/
 * Text Domain: frontend-admin
 * Domain Path: /languages/
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'feap_fs' ) ) {
	function feap_fs() {
		return false;
	}
}
if ( ! class_exists( 'Front_End_Admin_Pro' ) ) {
	if ( ! defined( 'FEA_VERSION' ) ) {
		define( 'FEA_VERSION', '3.15.3.1' );
		define( 'FEA_PATH', __FILE__ );
		define( 'FEA_NAME', plugin_basename( __FILE__ ) );
		define( 'FEA_URL', plugin_dir_url( __FILE__ ) );
		define( 'FEA_DIR', __DIR__ );
		define( 'FEA_TITLE', 'Frontend Admin' );
		define( 'FEA_PREFIX', 'frontend_admin' );
		define( 'FEA_NS', 'acf-frontend-form-element' );
		define( 'FEA_PRO', 'https://www.dynamiapps.com/frontend-admin/#pricing' );
		define( 'FEA_PRE', 'fea' );
	}
	/**
	 * Main Frontend Admin Class
	 *
	 * The main class that initiates and runs the plugin.
	 *
	 * @since 1.0.0
	 */
	final class Front_End_Admin_Pro {

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 */
		public function __construct() {
			global $fea_instance;
			if ( isset( $fea_instance ) ) {
				return;
			}		
			include_once 'pro/plugin.php';
			global $fea_pro_instance;
			$fea_pro_instance = new \Frontend_Admin\Pro_Features();

			include_once 'main/plugin.php';
			global $fea_instance;
			$fea_instance = new \Frontend_Admin\Plugin( [
				'pro_version' => true,
				'requires_acf' => false,
			] );
			
		}
		
	}
	new Front_End_Admin_Pro();

}