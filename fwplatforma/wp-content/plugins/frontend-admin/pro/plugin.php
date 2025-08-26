<?php
namespace Frontend_Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! class_exists( 'Pro_Features' ) ) :

	class Pro_Features {

		public $using_freemius = '';

		public function submit_actions_settings( $fields ) {
			$fields[] = array(
				'key'               => 'ajax_submit',
				'label'             => __( 'No Page Reload', 'acf-frontend-form-element' ),
				'type'              => 'true_false',
				'instructions'      => '',
				'required'          => 0,
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'redirect',
							'operator' => '==',
							'value'    => 'current',
						),
					),
				),
				'message'           => '',
				'ui'                => 1,
				'ui_on_text'        => '',
				'ui_off_text'       => '',
			);
			$fields[] = array(
				'key'               => 'redirect_action',
				'label'             => __( 'After Reload', 'acf-frontend-form-element' ),
				'type'              => 'select',
				'instructions'      => '',
				'required'          => 0,
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'choices'           => array(
					'clear' => __( 'Clear Form', 'acf-frontend-form-element' ),
					'edit'  => __( 'Edit Form', 'acf-frontend-form-element' ),
				),
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'redirect',
							'operator' => '==',
							'value'    => 'current',
						),
						array(
							'field'    => 'ajax_submit',
							'operator' => '==',
							'value'    => '1',
						),
					),
				),
				'allow_null'        => 0,
				'multiple'          => 0,
				'ui'                => 0,
				'return_format'     => 'value',
				'ajax'              => 0,
				'placeholder'       => '',
			);

			$remote_actions = array();
			$action_layouts = array();

			global $fea_instance;
			if( ! empty( $fea_instance->remote_actions ) ){
				foreach ( $fea_instance->remote_actions as $name => $action ) {
					$sub_fields       = array(
						array(
							'key'               => 'action_id',
							'label'             => __( 'Action Name', 'acf-frontend-form-element' ),
							'name'              => 'action_id',
							'type'              => 'text',
							'instructions'      => __( 'Give this action an identifier', 'acf-frontend-form-element' ),
							'default_value'     => $action->get_label(),
							'conditional_logic' => 0,
							'placeholder'       => __( 'Action Name', 'acf-frontend-form-element' ),
							'maxlength'         => '100',
						),
					);
					$sub_fields       = array_merge( $sub_fields, $action->action_options() );
					$layouts[ $name ] = array(
						'key'        => $name,
						'name'       => $name,
						'label'      => $action->get_label(),
						'display'    => 'block',
						'sub_fields' => $sub_fields,
						'min'        => '',
						'max'        => '',
					);
				}

				$default = array();
				global $form;
				if ( ! empty( $form['emails'] ) ) {
					foreach ( $form['emails'] as $email ) {
						$row                        = $email;
						$row['fea_block_structure'] = 'email';
						$row['action_id']           = $email['email_id'];
						$default[]                  = $row;
					}
				}
				if ( ! empty( $form['webhooks'] ) ) {
					foreach ( $form['webhooks'] as $webhook ) {
						$row                        = $webhook;
						$row['fea_block_structure'] = 'webhook';
						$row['action_id']           = $webhook['webhook_id'];
						$default[]                  = $row;
					}
				}

				$fields[] = array(
					'key'                         => 'submit_actions',
					'label'                       => __( 'Submit Actions', 'acf-frontend-form-element' ),
					'type'                        => 'frontend_blocks',
					'instructions'                => '',
					'required'                    => 0,
					'conditional_logic'           => 0,
					'wrapper'                     => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'block_labels'                => array(
						'remove'    => __( 'Remove Action', 'acf-frontend-form-element' ),
						'add'       => __( 'Add Action', 'acf-frontend-form-element' ),
						'duplicate' => __( 'Duplicate Action', 'acf-frontend-form-element' ),
						'collapse'  => __( 'Click to Toggle', 'acf-frontend-form-element' ),
						'button'    => __( 'Add Action', 'acf-frontend-form-element' ),
						'no_value'  => __( 'Click the button below to add your first action', 'acf-frontend-form-element' ),
					),
					'frontend_admin_display_mode' => 'edit',
					'only_front'                  => 0,
					'default_value'               => $default,
					'blocks'                      => $layouts,
					'min'                         => '',
					'max'                         => '',
				);
			}

			return $fields;
		}
		public function permissions_settings( $fields ) {
			if ( empty( $fields[2]['sub_fields'] ) ) {
				return $fields;
			}
			$fields[2]['sub_fields'] = array_merge(
				$fields[2]['sub_fields'],
				array(
					array(
						'key'               => 'allowed_submits',
						'label'             => __( 'Allowed Submissions', 'acf-frontend-form-element' ),
						'type'              => 'number',
						'instructions'      => __( 'Limit the amount of times this form can be submitted', 'acf-frontend-form-element' ),
						'conditional_logic' => 0,
						'min'               => 1,
						'wrapper'           => array(
							'width' => '30',
							'class' => '',
							'id'    => '',
						),
						'conditional_logic' => array(
							array(
								array(
									'field'    => 'who_can_see',
									'operator' => '==',
									'value'    => 'logged_in',
								),
							),
						),
					),
					array(
						'key'               => 'limit_reached',
						'label'             => __( 'No Permissions Message', 'acf-frontend-form-element' ),
						'type'              => 'select',
						'instructions'      => '',
						'required'          => 0,
						'choices'           => array(
							'show_nothing'   => __( 'None', 'acf-frontend-form-element' ),
							'show_message'   => __( 'Message', 'acf-frontend-form-element' ),
							'custom_content' => __( 'Custom Content', 'acf-frontend-form-element' ),
						),
						'conditional_logic' => array(
							array(
								array(
									'field'    => 'allowed_submits',
									'operator' => '!=empty',
								),
								array(
									'field'    => 'who_can_see',
									'operator' => '==',
									'value'    => 'logged_in',
								),
							),
						),
					),
					array(
						'key'               => 'limit_reached_message',
						'label'             => '',
						'type'              => 'textarea',
						'instructions'      => '',
						'required'          => 0,
						'rows'              => 3,
						'placeholder'       => __( 'You have submitted this form the maximum amount of times allowed', 'acf-frontend-form-element' ),
						'default_value'     => __( 'You have submitted this form the maximum amount of times allowed', 'acf-frontend-form-element' ),
						'conditional_logic' => array(
							array(
								array(
									'field'    => 'limit_reached',
									'operator' => '==',
									'value'    => 'show_message',
								),
								array(
									'field'    => 'allowed_submits',
									'operator' => '!=empty',
								),
								array(
									'field'    => 'who_can_see',
									'operator' => '==',
									'value'    => 'logged_in',
								),
							),

						),
					),
					array(
						'key'               => 'limit_reached_content',
						'label'             => '',
						'type'              => 'wysiwyg',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => array(
							array(
								array(
									'field'    => 'limit_reached',
									'operator' => '==',
									'value'    => 'custom_content',
								),
								array(
									'field'    => 'allowed_submits',
									'operator' => '!=empty',
								),
								array(
									'field'    => 'who_can_see',
									'operator' => '==',
									'value'    => 'logged_in',
								),
							),
						),
					),
				)
			);

			return $fields;
		}

		public function scripts() {
			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';

			wp_register_style( 'fea-public-pro', FEA_URL . 'pro/assets/css/frontend-admin-pro' . $min . '.css', array(), FEA_VERSION );
			wp_register_script( 'fea-public-pro', FEA_URL . 'pro/assets/js/frontend-admin-pro' . $min . '.js', array( 'fea-public' ), FEA_VERSION, true );

			wp_enqueue_style( 'fea-public-pro' );
			wp_enqueue_script( 'fea-public-pro' );

		}

		public function field_types( $field_types ) {
			if( ! is_array( $field_types ) ) return $field_types;

			$path = __DIR__ . "/forms/fields/";
			$field_types['pro-form'] = [
				'path' => $path,
				'form-step',
				'frontend-blocks',
				'signature'
			];
			$field_types['options'] = array(
				'path' => $path,
				'site-title',
				'site-tagline',
				'site-logo',
				'site-favicon',
			);
			$field_types['mailchimp'] = array(
				'path' => $path,
				'mailchimp-email',
				'mailchimp-first-name',
				'mailchimp-last-name',
				'mailchimp-status',
			);
			if ( class_exists( 'woocommerce' ) ) {
				$field_types['product'] = array(
					'path' => $path,
					'product-to-edit',
					'product-title',
					'product-description',
					'product-short-description',
					'product-slug',
					'product-status',
					'product-author',
					'product-date',
					'product-sku',
					'shipping-attributes',
					'product-weight',
					'product-height',
					'product-length',
					'product-width',
					'product-shipping-class',
					'is-downloadable',
					'downloadable-files',
					'download-limit',
					'download-expiry',
					'product-price',
					'product-sale-price',
					'product-menu-order',
					'main-image',
					'product-images',
					'product-tax-class',
					'product-tax-status',
					'product-attributes',
					'external-url',
					'button-text',
					'product-types',
					'is-virtual',
					'product-variations',
					'multiple-selection',
					'product-linked',
					'product-grouped',
					'product-upsells',
					'product-cross-sells',
					'stock-status',
					'allow-backorders',
					'stock-quantity',
					'low-stock-threshold',
					'manage-stock',
					'sold-individually',
					'product-enable-reviews',
					'product-purchase-note',
					'delete-product',
				);
			}

			return $field_types;
		}
		public function form_types( $form_types ) {
				if ( class_exists( 'woocommerce' ) ) {
				$form_types = array_merge(
				   $form_types,
				   array(
					   __( 'Product', 'acf-frontend-form-element' ) => array(
						   'new_product'       => __( 'New Product Form', 'acf-frontend-form-element' ),
						   'edit_product'      => __( 'Edit Product Form', 'acf-frontend-form-element' ),
						   'duplicate_product' => __( 'Duplicate Product Form', 'acf-frontend-form-element' ),
						   'delete_product'    => __( 'Delete Product Button', 'acf-frontend-form-element' ),
						   'status_product'    => __( 'Post Status Button', 'acf-frontend-form-element' ),
					   ),
				   )
			   );
			}
			$form_types['edit_options'] = __( 'Edit Options Form', 'acf-frontend-form-element' );
			return $form_types;
		}

		function pro_tabs( $tabs ) {

			// $this->tabs['pdf'] = 'PDF Generator';
			$tabs['payments'] = 'Payments';

			return $tabs;
		}

		function license_tab( $tabs ) {

			// $this->tabs['pdf'] = 'PDF Generator';
			$tabs['license'] = 'License';

			return $tabs;
		}

		
		public function license_page() {
			add_submenu_page(  'fea-settings', __( 'License', 'acf-frontend-form-element' ), __( 'License', 'acf-frontend-form-element' ), 'manage_options',  admin_url( '?page=fea-settings&tab=license' ), '', 10 );
	   }


		public function get_settings_fields( $field_keys ) {
			$license     = get_option( 'fea_main_license_key' );
			$is_valid    = get_option( 'fea_main_license_valid' );
			$instruction = '';

			if ( $is_valid ) {
				$instruction = sprintf( __( 'License activated successfully', 'acf-frontend-form-element' ), $is_valid );
			} else {
				if ( $license ) {
					$instruction = __( 'Your license is not valid', 'acf-frontend-form-element' );
				} else {
					$instruction = __( 'Enter your license key to activate pro features', 'acf-frontend-form-element' );
				}
			}

			$local_fields = array(
				'fea_main_license_key' => array(
					'label'         => __( 'License Key', 'acf-frontend-form-element' ),
					'type'          => 'text',
					'instructions'  => $instruction,
					'required'      => 0,
					'default_value' => $license,
					'wrapper'       => array(
						'width' => '50.1',
						'class' => '',
						'id'    => '',
					),
				),
			);

			return $local_fields;
		}

		public function pro_form_tabs( $tabs ) {
			if ( class_exists( 'woocommerce' ) ) {
				$tabs['product'] = __( 'Product', 'acf-frontend-form-element' );
			}
			$tabs['steps'] = __( 'Multi Step', 'acf-frontend-form-element' );
			return $tabs;
		}

		public function included_files(){
			if ( class_exists( 'woocommerce' ) ) {
				include_once 'forms/actions/product.php';
			}
			include_once 'forms/actions/email.php';
			include_once 'forms/actions/mailchimp.php';
			include_once 'forms/actions/webhook.php';
			include_once 'addon-installer.php';		
		}

		public function init(){

			add_action( 'frontend_admin/forms/enqueue_scripts', array( $this, 'scripts' ) );
			
			add_filter( 'frontend_admin/field_types', array( $this, 'field_types' ) );

			add_filter( 'frontend_admin/admin_tabs', array( $this, 'pro_tabs' ), 15 );
		
			add_filter( 'frontend_admin/forms/form_types', array( $this, 'form_types' ) );
			add_filter( 'frontend_admin/forms/settings_tabs', array( $this, 'pro_form_tabs' ) );
			add_filter( 'frontend_admin/forms/settings/permissions', array( $this, 'permissions_settings' ) );
			add_filter( 'frontend_admin/forms/settings/submit_actions', array( $this, 'submit_actions_settings' ) );
			add_action( 'frontend_admin/forms/included_files', array( $this, 'included_files' ) );


			// elementor
			require_once( 'elementor/main.php' );
		}


		public function is_license_active() {

			if ( function_exists( 'fea_freemius' ) ) {
				return fea_freemius()->is__premium_only();
			}

			return get_option( 'fea_main_license_valid' );
		}

		public function plugin_updater() {
			require_once 'license.php';

			// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
			$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
			if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
				return;
			}

			// retrieve our license key from the DB
			$license_key = trim( get_option( 'fea_main_license_key' ) );

			if ( $this->is_license_active() ) {
				require_once 'updater.php';
				// setup the updater
				$updater = new \Frontend_Admin\Plugin_Updater(
					'https://dynamiapps.com/wp-json/paddlepress-api/v1/update',
					__FILE__,
					array(
						'version'      => FEA_VERSION,   // current version number
						'license_key'  => $license_key,             // license key (used get_option above to retrieve from DB)
						'license_url'  => home_url(),             // license domain
						'download_tag' => 'frontend-admin-pro',            // download tag slug
						'beta'         => false,
					)
				);
			}

		}

		function include_acf_plugin() {
			if ( class_exists( 'ACF' ) ) return;

			// Define path and URL to the ACF plugin.
			define( 'FEA_ACF_PATH', FEA_DIR . '/includes/acf/' );
			define( 'FEA_ACF_URL', FEA_URL . '/includes/acf/' );
			
			// Include the ACF plugin.
			include_once FEA_ACF_PATH . 'acf.php';

			// Customize the url setting to fix incorrect asset URLs.
			add_filter(
				'acf/settings/url',
				function( $url ) {
					return FEA_ACF_URL;
				}
			);

		}		

		public function after_theme_setup() {
			global $fea_instance;
			if( $fea_instance ){
				$fea_instance->pro_features = $this;
			}
		
		}

		public function __construct( $data = false ) {
			if( empty( $data['using_freemius'] ) ){
				add_action( 'init', array( $this, 'plugin_updater' ) );
				add_filter( 'frontend_admin/admin_tabs', array( $this, 'license_tab' ), 16 );
				add_filter( 'frontend_admin/license_fields', array( $this, 'get_settings_fields' ) );
				add_action( 'admin_menu', array( $this, 'license_page' ), 20 );
			}else{
				$this->using_freemius = true;
			}
			add_action( 'after_setup_theme', array( $this, 'include_acf_plugin' ), 10 );
			add_action( 'after_setup_theme', array( $this, 'after_theme_setup' ), 12 );
			
			if ( ! $this->is_license_active() ) {
				return;
			}
			
			$this->init();
			do_action( 'front_end_admin_pro_loaded' );
		}
	}
endif;
