<?php
namespace Frontend_Admin\Field_Types;

if( class_exists ( 'select' ) ) :

	class product_shipping_class extends select {



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
			$this->name     = 'product_shipping_class';
			$this->label    = __( 'Shipping Class', 'acf-frontend-form-element' );
			$this->category = __( 'Product Shipping', 'acf-frontend-form-element' );
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

			add_filter( 'acf/update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 3 );
		}

		function prepare_field( $field ) {
			if ( isset( $GLOBALS['form_fields'] ) ) {
				$fields = $GLOBALS['form_fields'];

				$simple = array(
					array(
						'field'    => $fields['product_types'],
						'operator' => '==',
						'value'    => 'simple',
					),
				);
				if ( isset( $fields['is_virtual'] ) ) {
					$simple[] = array(
						'field'    => $fields['is_virtual'],
						'operator' => '==',
						'value'    => '0',
					);
				}
				$variation = array(
					array(
						'field'    => $fields['product_types'],
						'operator' => '==',
						'value'    => 'variable',
					),
				);

				$field['conditional_logic'] = array(
					$simple,
					$variation,
				);

			}

			$product = feadmin_get_product_object();

			if ( $product ) {
				  $field['value'] = $product->get_shipping_class_id( 'edit' );
			}

			return $field;
		}

		function render_field( $field ) {
			 $args = array(
				 'taxonomy'         => 'product_shipping_class',
				 'hide_empty'       => 0,
				 'show_option_none' => __( 'No shipping class', 'woocommerce' ),
				 'name'             => $field['name'],
				 'id'               => 'product_shipping_class',
				 'selected'         => $field['value'],
				 'class'            => 'select short',
				 'orderby'          => 'name',
			 );
			  wp_dropdown_categories( $args );
		}

		function pre_update_value( $value, $post_id, $field ) {
			if ( empty( $post_id ) || ! is_numeric( $post_id ) ) {
				return null;
			}

			  $product = wc_get_product( $post_id );

			if ( $product ) {
				$product->set_shipping_class_id( $value );
				$product->save();
			}
			return null;

		}
		function update_value( $value, $post_id, $field ) {
			   return null;
		}

	}



endif; // class_exists check


