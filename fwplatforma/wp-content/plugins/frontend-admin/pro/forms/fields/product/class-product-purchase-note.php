<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'product_purchase_note' ) ) :

	class product_purchase_note extends textarea {



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
			$this->name     = 'product_purchase_note';
			$this->label    = __( 'Purchase Note', 'acf-frontend-form-element' );
			$this->category = __( 'Advanced Product Options', 'acf-frontend-form-element' );
			$this->defaults = array(
				'default_value' => '',
				'new_lines'     => '',
				'maxlength'     => '',
				'placeholder'   => '',
				'rows'          => '',
			);
			add_filter( 'acf/update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 3 );

		}

		function prepare_field( $field ) {
			if ( isset( $GLOBALS['form_fields'] ) ) {
				$fields                     = $GLOBALS['form_fields'];
				$field['conditional_logic'] = array(
					array(
						array(
							'field'    => $fields['product_types'],
							'operator' => '==',
							'value'    => 'simple',
						),
					),
					array(
						array(
							'field'    => $fields['product_types'],
							'operator' => '==',
							'value'    => 'variable',
						),
					),
				);
			}
			$field['type'] = 'textarea';
			return $field;
		}

		function load_value( $value, $post_id = false, $field = false ) {
			$value = get_post_meta( $post_id, '_purchase_note', true );
			return $value;
		}


		function load_field( $field ) {
			 $field['name'] = $field['type'];
			return $field;
		}
		function pre_update_value( $value, $post_id = false, $field = false ) {
			   update_metadata( 'post', $post_id, '_purchase_note', $value );
			  return null;
		}


		public function update_value( $value, $post_id = false, $field = false ) {
			return null;
		}

	}



endif;


