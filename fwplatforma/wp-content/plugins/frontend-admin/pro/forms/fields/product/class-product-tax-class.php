<?php
namespace Frontend_Admin\Field_Types;

if( class_exists ( 'select' ) ) :

	class product_tax_class extends select {

		  /*
		  *  __construct
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
			$this->name     = 'product_tax_class';
			$this->label    = __( 'Tax Class', 'acf-frontend-form-element' );
			$this->public   = false;
			$this->defaults = array(
				'multiple'      => 0,
				'allow_null'    => 0,
				'choices'       => array(),
				'default_value' => '',
				'ui'            => 0,
				'ajax'          => 0,
				'placeholder'   => '',
				'return_format' => 'value',
			);

			add_filter( 'acf/load_field/type=select', array( $this, 'tax_class_field' ) );
			add_filter( 'acf/update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 3 );
		}

		function tax_class_field( $field ) {
			if ( ! empty( $field['custom_tax_class'] ) ) {
				$field['type'] = 'product_tax_class';
			}
			return $field;
		}


		function prepare_field( $field ) {
			$field['choices'] = wc_get_product_tax_class_options();

			if ( isset( $GLOBALS['form_fields'] ) ) {
				$fields                     = $GLOBALS['form_fields'];
				$field['conditional_logic'] = array(
					array(
						array(
							'field'    => $fields['product_types'],
							'operator' => '!=',
							'value'    => 'grouped',
						),
					),
				);
			}

			$product = feadmin_get_product_object();

			if ( $product ) {
				  $field['value'] = $product->get_tax_class( 'edit' );
			}

			return $field;
		}


		function pre_update_value( $value, $post_id, $field ) {
			if ( empty( $post_id ) || ! is_numeric( $post_id ) ) {
				return null;
			}

			  $product = wc_get_product( $post_id );

			if ( $product ) {
				$product->set_tax_class( $value );
				$product->save();
			}
			return null;

		}
		function update_value( $value, $post_id, $field ) {
			   return null;
		}

	}



endif; // class_exists check


