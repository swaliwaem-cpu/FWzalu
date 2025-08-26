<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'product_sku' ) ) :

	class product_sku extends text {



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
			$this->name     = 'product_sku';
			$this->label    = __( 'SKU', 'acf-frontend-form-element' );
			$this->category = __( 'Product Inventory', 'acf-frontend-form-element' );
			$this->defaults = array(
				'default_value' => '',
				'maxlength'     => '',
				'placeholder'   => '',
				'prepend'       => '',
				'append'        => '',
			);
			add_filter( 'acf/load_field/type=text', array( $this, 'load_product_sku_field' ) );
			add_filter( 'acf/update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 3 );
		}

		function load_product_sku_field( $field ) {
			if ( ! empty( $field['custom_sku'] ) ) {
				$field['type'] = 'product_sku';
			}
			return $field;
		}

		public function load_value( $value, $product_id = false, $field = false ) {
			if ( $product_id && is_numeric( $product_id ) ) {
				$value = get_post_meta( $product_id, '_sku', true );
			}
			return $value;
		}

		function load_field( $field ) {
			 $field['name'] = $field['type'];
			return $field;
		}
		function pre_update_value( $value, $product_id = false, $field = false ) {
			if ( $product_id && is_numeric( $product_id ) ) {
				update_metadata( 'post', $product_id, '_sku', $value );
			}
			return null;
		}

		public function update_value( $value, $product_id = false, $field = false ) {
			return null;
		}

		public function prepare_field( $field ) {
			$field['type'] = 'text';
			// return
			return $field;
		}


	}



endif;


