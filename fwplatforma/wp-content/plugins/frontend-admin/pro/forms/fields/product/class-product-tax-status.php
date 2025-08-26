<?php
namespace Frontend_Admin\Field_Types;

if( class_exists ( 'select' ) ) :

	class product_tax_status extends select {



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
			$this->name     = 'product_tax_status';
			$this->label    = __( 'Tax Status', 'acf-frontend-form-element' );
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

			add_filter( 'acf/load_field/type=select', array( $this, 'tax_status_field' ) );
			add_filter( 'acf/update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 3 );
		}

		function tax_status_field( $field ) {
			if ( ! empty( $field['custom_tax_status'] ) ) {
				$field['type'] = 'product_tax_status';
			}
			return $field;
		}

		function prepare_field( $field ) {
			$field['choices'] = array(
				'taxable'  => __( 'Taxable', 'woocommerce' ),
				'shipping' => __( 'Shipping only', 'woocommerce' ),
				'none'     => _x( 'None', 'Tax status', 'woocommerce' ),
			);

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
				  $field['value'] = $product->get_tax_status( 'edit' );
			}

			return $field;
		}

		function pre_update_value( $value, $post_id, $field ) {
			if ( empty( $post_id ) || ! is_numeric( $post_id ) ) {
				return null;
			}

			$product = wc_get_product( $post_id );

			if ( $product ) {
				  $product->set_tax_status( $value );
				  $product->save();
			}
			return null;

		}
		function update_value( $value, $post_id, $field ) {
			   return null;
		}

	}



endif; // class_exists check


