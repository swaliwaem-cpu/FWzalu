<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'mailchimp_email' ) ) :

	class mailchimp_email extends email {



		  /*
		  *  initialize
		  *
		  *  This function will setup the field type data
		  *
		  *  @type      function
		  *  @date      5/03/2014
		  *  @since      5.0.0
		  *
		  *  @param      n/a
		  *  @return      n/a
		  */

		function initialize() {
			// vars
			$this->name     = 'mailchimp_email';
			$this->label    = __( 'Mailchimp Email', 'acf-frontend-form-element' );
			$this->category = __( 'Mailchimp', 'acf-frontend-form-element' );
			$this->defaults = array(
				'default_value' => '',
				'placeholder'   => '',
				'prepend'       => '',
				'append'        => '',
			);
			add_filter( 'frontend_admin/add_to_record/' . $this->name, array( $this, 'add_to_record' ), 10, 3 );

		}

		function add_to_record( $record, $group, $field ) {
			if ( empty( $record['mailchimp']['email'] ) ) {
				$record['mailchimp']['email'] = $group . ':' . $field['name'];
			}
			return $record;
		}


		function prepare_field( $field ) {
			$field['type'] = 'email';
			return $field;
		}

	}



endif;


