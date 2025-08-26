<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'product_title' ) ) :

	class product_title extends post_title {



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
			$this->name     = 'product_title';
			$this->label    = __( 'Title', 'acf-frontend-form-element' );
			$this->category = __( 'Product', 'acf-frontend-form-element' );
			$this->defaults = array(
				'default_value' => '',
				'maxlength'     => '',
				'placeholder'   => '',
				'prepend'       => '',
				'append'        => '',
			);

		}


		  /*
		*  render_field_settings()
		*
		*  Create extra options for your field. This is rendered when editing a field.
		*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
		*
		*  @type      action
		*  @since      3.6
		*  @date      23/01/13
		*
		*  @param      $field      - an array holding all the field's data
		*/

		function render_field_settings( $field ) {
			text::render_field_settings( $field );
			  // default_value
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Product Slug' ),
					'instructions' => 'Save value as product slug.',
					'name'         => 'custom_slug',
					'type'         => 'true_false',
					'ui'           => 1,
				)
			);
		}

	}



endif;


