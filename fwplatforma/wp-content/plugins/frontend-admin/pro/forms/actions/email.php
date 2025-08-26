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

if ( ! class_exists( 'SendEmail' ) ) :

	class SendEmail extends ActionBase {


		public $site_domain = '';

		public function get_name() {
			return 'email';
		}

		public function get_label() {
			return __( 'Email', 'acf-frontend-form-element' );
		}

		public function admin_fields() {
			return array(
				array(
					'key'               => 'emails',
					'label'             => __( 'Emails', 'acf-frontend-form-element' ),
					'type'              => 'list_items',
					'instructions'      => '',
					'required'          => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'collapsed'         => 'email_id',
					'collapsable'       => true,
					'min'               => '',
					'max'               => '',
					'layout'            => 'block',
					'button_label'      => __( 'Add Email', 'acf-frontend-form-element' ),
					'remove_label'      => __( 'Remove Email', 'acf-frontend-form-element' ),
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'more_actions',
								'operator' => '==contains',
								'value'    => $this->get_name(),
							),
						),
					),
					'sub_fields'        => $this->action_options(),
				),
			);
		}

		public function action_options() {
			$fields = array(
				array(
					'key'     => 'email_to_message',
					'label'   => __( 'Email Addresses', 'acf-frontend-form-element' ),
					'name'    => 'recipient_custom',
					'type'    => 'message',
					'message' => __( 'Separate emails with commas. <br> To display the names and addresses, use the following format: Name&lt;address&gt;.', 'acf-frontend-form-element' ),
				),
				array(
					'key'                   => 'email_to',
					'label'                 => __( 'To', 'acf-frontend-form-element' ),
					'name'                  => 'email_to',
					'type'                  => 'text',
					'dynamic_value_choices' => 1,
					'instructions'          => '',
					'required'              => 0,
					'default_value'         => '[user:display_name]<[user:email]>',
					'placeholder'           => '',
					'prepend'               => '',
					'append'                => '',
					'maxlength'             => '',
				),
				array(
					'key'                   => 'email_to_cc',
					'label'                 => __( 'CC', 'acf-frontend-form-element' ),
					'name'                  => 'email_to_cc',
					'type'                  => 'text',
					'dynamic_value_choices' => 1,
					'instructions'          => '',
					'required'              => 0,
					'default_value'         => '',
					'placeholder'           => '',
					'prepend'               => '',
					'append'                => '',
					'maxlength'             => '',
				),
				array(
					'key'                   => 'email_to_bcc',
					'label'                 => __( 'BCC', 'acf-frontend-form-element' ),
					'name'                  => 'email_to_bcc',
					'type'                  => 'text',
					'dynamic_value_choices' => 1,
					'instructions'          => '',
					'required'              => 0,
					'default_value'         => '',
					'placeholder'           => '',
					'prepend'               => '',
					'append'                => '',
					'maxlength'             => '',
				),
				array(
					'key'                   => 'email_from',
					'label'                 => __( 'From Email', 'acf-frontend-form-element' ),
					'name'                  => 'from',
					'type'                  => 'text',
					'dynamic_value_choices' => 1,
					'instructions'          => '',
					'required'              => 0,
					'default_value'         => get_bloginfo( 'name' ) . '<' . get_bloginfo( 'admin_email' ) . '>',
					'placeholder'           => '',
					'prepend'               => '',
					'append'                => '',
					'maxlength'             => '',
				),
				array(
					'key'                   => 'email_reply_to',
					'label'                 => __( 'Reply To Email', 'acf-frontend-form-element' ),
					'name'                  => 'reply_to',
					'type'                  => 'text',
					'dynamic_value_choices' => 1,
					'default_value'         => 'noreply@' . feadmin_get_site_domain(),
					'instructions'          => '',
					'required'              => 0,
					'placeholder'           => '',
					'prepend'               => '',
					'append'                => '',
					'maxlength'             => '',
				),
				array(
					'key'                   => 'email_subject',
					'label'                 => __( 'Subject', 'acf-frontend-form-element' ),
					'name'                  => 'subject',
					'type'                  => 'text',
					'dynamic_value_choices' => 1,
					'instructions'          => '',
					'required'              => 0,
					'conditional_logic'     => 0,
					'wrapper'               => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'default_value'         => __( 'New Form Submission', 'acf-frontend-form-element' ),
					'placeholder'           => '',
					'prepend'               => '',
					'append'                => '',
					'maxlength'             => '',
				),
				array(
					'key'                   => 'email_content',
					'label'                 => __( 'Content', 'acf-frontend-form-element' ),
					'type'                  => 'wysiwyg',
					'dynamic_value_choices' => 1,
					'default_value'         => '[all_fields]',
					'instructions'          => '',
					'required'              => 0,
					'conditional_logic'     => 0,
					'wrapper'               => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'tabs'                  => 'all',
					'toolbar'               => 'full',
					'media_upload'          => 1,
				),
				array(
					'key'           => 'email_content_type',
					'label'         => __( 'Send As', 'acf-frontend-form-element' ),
					'type'          => 'select',
					'instructions'  => '',
					'required'      => 0,
					'default_value' => 'html',
					'choices'       => array(
						'html'  => __( 'HTML', 'acf-frontend-form-element' ),
						'plain' => __( 'Plain', 'acf-frontend-form-element' ),
					),
					'allow_null'    => 0,
					'multiple'      => 0,
					'ui'            => 0,
					'return_format' => 'value',
					'ajax'          => 0,
					'placeholder'   => '',
				),
			);

			$fields = apply_filters( 'frontend_admin/action_settings/type=' . $this->get_name(), $fields );

			return $fields;
		}

		public function register_settings_section( $widget ) {
			$site_domain = feadmin_get_site_domain();

			$repeater = new \Elementor\Repeater();

			$widget->start_controls_section(
				'section_email',
				array(
					'label'     => $this->get_label(),
					'tab'       => Controls_Manager::TAB_CONTENT,
					'condition' => array(
						'more_actions'       => $this->get_name(),
						'admin_forms_select' => '',
					),
				)
			);

			$widget->add_control(
				'steps_emails_message',
				array(
					'show_label' => false,
					'type'       => Controls_Manager::RAW_HTML,
					'raw'        => 'In each step, enter the email names you want to be sent upon completing that step.',
					'separator'  => 'after',
					'condition'  => array(
						'multi' => 'true',
					),
				)
			);
			$repeater->add_control(
				'email_id',
				array(
					'label'       => __( 'Email Name', 'acf-frontend-form-element' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => __( 'Email Name', 'acf-frontend-form-element' ),
					'default'     => __( 'Email Name', 'acf-frontend-form-element' ),
					'label_block' => true,
					'description' => __( 'Give this email an identifier', 'acf-frontend-form-element' ),
					'render_type' => 'none',
				)
			);

			$repeater->start_controls_tabs( 'tabs_to_emails' );

			$repeater->start_controls_tab(
				'tab_to_email',
				array(
					'label' => __( 'To', 'acf-frontend-form-element' ),
				)
			);

			$repeater->add_control(
				'email_to',
				array(
					'type'        => Controls_Manager::TEXTAREA,
					'label_block' => true,
					'show_label'  => false,
					'default'     => get_option( 'admin_email' ),
					'placeholder' => get_option( 'admin_email' ),
					'description' => __( 'Separate emails with commas. <br> To display the names and addresses, use the following format: Name&lt;address&gt;.', 'acf-frontend-form-element' ),
					'render_type' => 'none',
				)
			);

			$repeater->end_controls_tab();

			$repeater->start_controls_tab(
				'tab_to_cc_email',
				array(
					'label' => __( 'Cc', 'acf-frontend-form-element' ),
				)
			);

			$repeater->add_control(
				'email_to_cc',
				array(
					'type'        => Controls_Manager::TEXTAREA,
					'label_block' => true,
					'show_label'  => false,
					'default'     => '',
					'description' => __( 'Separate emails with commas. <br> To display the names and addresses, use the following format: Name&lt;address&gt;.', 'acf-frontend-form-element' ),
					'render_type' => 'none',
				)
			);

			$repeater->end_controls_tab();

			$repeater->start_controls_tab(
				'tab_to_bcc_email',
				array(
					'label' => __( 'Bcc', 'acf-frontend-form-element' ),
				)
			);

			$repeater->add_control(
				'email_to_bcc',
				array(
					'type'        => Controls_Manager::TEXTAREA,
					'label_block' => true,
					'show_label'  => false,
					'default'     => '',
					'description' => __( 'Separate emails with commas. <br> To display the names and addresses, use the following format: Name&lt;address&gt;.', 'acf-frontend-form-element' ),
					'render_type' => 'none',
				)
			);

			$repeater->end_controls_tab();

			$repeater->end_controls_tabs();

			$repeater->start_controls_tabs( 'tabs_from_emails' );

			$repeater->start_controls_tab(
				'tab_from_email',
				array(
					'label' => __( 'From', 'acf-frontend-form-element' ),
				)
			);

			$repeater->add_control(
				'email_from',
				array(
					'label'       => __( 'From Email', 'acf-frontend-form-element' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'default'     => get_bloginfo( 'name' ) . '<' . get_bloginfo( 'admin_email' ) . '>',
					'render_type' => 'none',
				)
			);

			$repeater->end_controls_tab();

			$repeater->start_controls_tab(
				'tab_reply_to_email',
				array(
					'label' => __( 'Reply-To', 'acf-frontend-form-element' ),
				)
			);

			$repeater->add_control(
				'email_reply_to',
				array(
					'label'       => __( 'Reply-To Email', 'acf-frontend-form-element' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'render_type' => 'none',
					'default'     => 'noreply@' . feadmin_get_site_domain(),
				)
			);

			$repeater->end_controls_tab();

			$repeater->end_controls_tabs();

			$repeater->add_control(
				'email_subject',
				array(
					'label'       => __( 'Subject', 'acf-frontend-form-element' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => __( 'New message from', 'acf-frontend-form-element' ) . ' [user:username]',
					'label_block' => true,
					'render_type' => 'none',
				)
			);

			$repeater->add_control(
				'email_content',
				array(
					'label'       => __( 'Message', 'acf-frontend-form-element' ),
					'type'        => Controls_Manager::WYSIWYG,
					'placeholder' => __( 'Hello, this is', 'acf-frontend-form-element' ) . ' [user:username]',
					'label_block' => true,
					'render_type' => 'none',
				)
			);

			$repeater->add_control(
				'email_content_type',
				array(
					'label'       => __( 'Send As', 'acf-frontend-form-element' ),
					'type'        => Controls_Manager::SELECT,
					'default'     => 'html',
					'render_type' => 'none',
					'options'     => array(
						'html'  => __( 'HTML', 'acf-frontend-form-element' ),
						'plain' => __( 'Plain', 'acf-frontend-form-element' ),
					),
					'render_type' => 'none',
				)
			);

			$widget->add_control(
				'emails_to_send',
				array(
					'label'       => __( 'Emails', 'acf-frontend-form-element' ),
					'type'        => Controls_Manager::REPEATER,
					'fields'      => $repeater->get_controls(),
					'title_field' => '{{{ email_id }}}',
				)
			);

			$widget->end_controls_section();
		}

		public function run( $form ) {
			if ( ! empty( $form['emails'] ) ) {
				$emails = $form['emails'];
			} else {
				if ( empty( $emails ) && ! empty( $form['submit_actions'] ) ) {
					$actions = $form['submit_actions'];
					if ( $actions ) {
						   $emails = array();
						foreach ( $actions as $action ) {
							if ( $action['fea_block_structure'] == 'email' ) {
								   $emails[] = $action;
							}
						}
					}
				}
			}

			if ( empty( $emails ) ) {
				return;
			}

			foreach ( $emails as $email ) {
				$send_email = true;

				$send_html  = 'plain' !== $email['email_content_type'];
				$line_break = $send_html ? '<br>' : "\n";

				$fields = array(
					'email_to'       => get_option( 'admin_email' ),
					'email_to_cc'    => '',
					'email_to_bcc'   => '',
					'email_from'     => get_bloginfo( 'name' ) . '<' . get_bloginfo( 'admin_email' ) . '>',
					'email_reply_to' => 'noreply@' . feadmin_get_site_domain(),
					/* translators: %s: Site title. */
					'email_subject'  => sprintf( __( 'New form submission from "%s"', 'acf-frontend-form-element' ), get_bloginfo( 'name' ) ),
					'email_content'  => __( 'A form has been filled out on your site', 'acf-frontend-form-element' ),

				);

				foreach ( $fields as $key => $default ) {
					$setting = trim( $email[ $key ] );
					$setting = fea_instance()->dynamic_values->get_dynamic_values( $setting, $form );
					if ( ! empty( $setting ) ) {
						   $fields[ $key ] = $setting;
					}
				}

				if ( ! empty( $email_meta ) ) {
					$fields['email_content'] .= $line_break . '---' . $line_break . $line_break . $email_meta;
				}

				$headers   = array(
					sprintf( 'From: %s' . "\r\n", $fields['email_from'] ),
				);
				$headers[] = sprintf( 'Reply-To: %s' . "\r\n", $fields['email_reply_to'] );

				if ( $send_html ) {
					$headers[] = 'Content-Type: text/html; charset=UTF-8' . "\r\n";
				}

				if ( ! empty( $fields['email_to_cc'] ) ) {
					$headers[] = 'Cc: ' . $fields['email_to_cc'] . "\r\n";
				}
				if ( ! empty( $fields['email_to_bcc'] ) ) {
					$headers[] = 'Bcc: ' . $fields['email_to_bcc'] . "\r\n";
				}

				/**
				 * Email headers.
				 *
				 * Filters the additional headers sent when the form send an email.
				 *
				 * @since 1.0.0
				 *
				 * @param string|array $headers Additional headers.
				 */
				$headers = apply_filters( 'frontend_admin/wp_mail_headers', $headers );

				/**
				 * Email attachemnts.
				 *
				 * Filters the additional headers sent when the form send an email.
				 *
				 * @since 1.0.0
				 *
				 * @param string|array $headers Additional headers.
				 */
				$attachments = apply_filters( 'frontend_admin/wp_mail_attachments', array(), $email, $form );

				/**
				 * Email content.
				 *
				 * Filters the content of the email sent by the form.
				 *
				 * @since 1.0.0
				 *
				 * @param string $email_content Email content.
				 */
				$fields['email_content'] = apply_filters( 'frontend_admin/wp_mail_message', $fields['email_content'] );

				$email_sent = wp_mail( $fields['email_to'], $fields['email_subject'], $fields['email_content'], $headers, $attachments );

				/**
				 * Mail sent.
				 *
				 * Fires when an email was sent successfully.
				 *
				 * @since 1.0.0
				 *
				 * @param $email      array of settings of this email.
				 * @param $form       array of form settings
				 */
				do_action( 'frontend_admin/mail_sent', $email, $form, $attachments );
			}

		}

	}
	fea_instance()->remote_actions['email'] = new SendEmail();

endif;
