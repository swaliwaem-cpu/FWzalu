<?php
namespace Frontend_Admin\Actions;

use Frontend_Admin\Plugin;
use Frontend_Admin;
use Frontend_Admin\Classes\ActionBase;
use Frontend_Admin\Forms\Actions;
use Elementor\Controls_Manager;


if ( ! defined( 'ABSPATH' ) ) {
	  exit; // Exit if accessed directly.
}
if ( ! class_exists( 'SendWebhook' ) ) :

	class SendWebhook extends ActionBase {


		  public $site_domain = '';

		public function get_name() {
			return 'webhook';
		}

		public function get_label() {
			 return __( 'Webhook', 'acf-frontend-form-element' );
		}

		public function action_options() {
			return array(
				array(
					'key'               => 'webhook_url',
					'label'             => __( 'Webhook URL', 'acf-frontend-form-element' ),
					'name'              => 'webhook_url',
					'type'              => 'text',
					'instructions'      => __( 'Enter the integration URL that will receive the form\'s submitted data.', 'acf-frontend-form-element' ),
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '70',
						'class' => '',
						'id'    => '',
					),
					'placeholder'       => 'https://your-webhook-url.com?key=',
					'prepend'           => '',
					'append'            => '',
					'maxlength'         => '',
				),
			);
		}

		public function register_settings_section( $widget ) {
			  $site_domain = feadmin_get_site_domain();

			  $repeater = new \Elementor\Repeater();

			$widget->start_controls_section(
				'section_webhook',
				array(
					'label'     => $this->get_label(),
					'tab'       => Controls_Manager::TAB_CONTENT,
					'condition' => array(
						'more_actions'       => $this->get_name(),
						'admin_forms_select' => '',
					),
				)
			);

			$repeater->add_control(
				'webhook_id',
				array(
					'label'       => __( 'Webhook Name', 'acf-frontend-form-element' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => __( 'Webhook Name', 'acf-frontend-form-element' ),
					'label_block' => true,
					'description' => __( 'Give this webhook an identifier', 'acf-frontend-form-element' ),
					'render_type' => 'none',
				)
			);

			$repeater->add_control(
				'webhook_url',
				array(
					'label'       => __( 'Webhook URL', 'acf-frontend-form-element' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => 'https://your-webhook-url.com?key=',
					'label_block' => true,
					'separator'   => 'before',
					'description' => __( 'Enter the integration URL that will receive the form\'s submitted data.', 'acf-frontend-form-element' ),
					'render_type' => 'none',
				)
			);

			$widget->add_control(
				'webhooks_to_send',
				array(
					'label'       => __( 'Webhooks', 'acf-frontend-form-element' ),
					'type'        => Controls_Manager::REPEATER,
					'fields'      => $repeater->get_controls(),
					'title_field' => '{{{ webhook_id }}}',
					'render_type' => 'none',
				)
			);

			  $widget->end_controls_section();
		}

		public function run( $form ) {
			if ( ! empty( $form['webhooks'] ) ) {
				$webhooks = $form['webhooks'];
			} else {
				if ( ! empty( $form['submit_actions'] ) ) {
					  $actions = $form['submit_actions'];
					if ( $actions ) {
						$webhooks = array();
						foreach ( $actions as $action ) {
							if ( $action['fea_block_structure'] == 'webhook' ) {
								$webhooks[] = $action;
							}
						}
					}
				}
			}

			if ( empty( $webhooks ) ) {
				return;
			}

			$record = $form['record'];

			foreach ( $webhooks as $webhook ) {
				if ( empty( $webhook['webhook_url'] ) || ! filter_var( $webhook['webhook_url'], FILTER_SANITIZE_URL ) ) {
					continue;
				}

				/**
				 * Forms webhook request arguments.
				 *
				 * Filters the request arguments delivered by the form webhook when executing
				 * an ajax request.
				 *
				 * @since 1.0.0
				 *
				 * @param array    $record   The submission's recorded data sent through the webhook .
				 */
				$data     = array(
					'method'      => 'POST',
					'body'        => $record,
					'headers'     => array(
						'Content-Type' => 'application/json',
					),
					'timeout'     => 60,
					'redirection' => 5,
					'blocking'    => true,
					'httpversion' => '1.0',
					'sslverify'   => false,
					'data_format' => 'body',
				);
				$data     = apply_filters( 'frontend_admin/forms/webhooks/request_data', $data );
				$response = wp_remote_post( $webhook['webhook_url'], $data );

				/**
				 * Form webhook response.
				 *
				 * Fires when the webhook response is retrieved.
				 *
				 * @since 1.0.0
				 *
				 * @param \WP_Error|array $response The response or WP_Error on failure.
				 * @param array     $record   An instance of the form record.
				 */
				do_action( 'frontend_admin/forms/webhooks/response', $response, $record );

				if ( is_wp_error( $response ) ) {
					  $form['action_errors'][] = sprint_f( __( 'Webhook Failed. Error message: %s', 'acf-frontend-form-element' ), $response->get_error_message() );
				} else {
					  $form['action_success'] = __( 'Webhook Failed', 'acf-frontend-form-element' );
				}
			}

		}

	}
	fea_instance()->remote_actions['webhook'] = new SendWebhook();

endif;
