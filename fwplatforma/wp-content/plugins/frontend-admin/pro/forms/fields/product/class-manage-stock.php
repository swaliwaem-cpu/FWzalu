<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'manage_stock' ) ) :

	class manage_stock extends true_false {



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
			$this->name     = 'manage_stock';
			$this->label    = __( 'Manage Stock', 'acf-frontend-form-element' );
			$this->category = __( 'Product Inventory', 'acf-frontend-form-element' );
			$this->defaults = array(
				'default_value' => 0,
				'message'       => '',
				'ui'            => 1,
				'ui_on_text'    => '',
				'ui_off_text'   => '',
			);
			add_filter( 'acf/load_field/type=select', array( $this, 'load_manage_stock_field' ), 2 );
			add_filter( 'acf/update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 3 );

		}

		function load_manage_stock_field( $field ) {
			if ( ! empty( $field['custom_manage_stock'] ) ) {
				$field['type'] = 'manage_stock';
			}
			return $field;
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
			$field['ui'] = 1;
			return $field;
		}

		public function load_value( $value, $post_id = false, $field = false ) {
			if ( get_post_meta( $post_id, '_manage_stock', true ) == 'yes' ) {
				$value = true;
			} else {
				$value = false;
			}
			return $value;
		}

		function load_field( $field ) {
			 $field['name'] = $field['type'];
			return $field;
		}
		function pre_update_value( $value, $post_id = false, $field = false ) {
			if ( $value == 1 ) {
				update_metadata( 'post', $post_id, '_manage_stock', 'yes' );
			} else {
				update_metadata( 'post', $post_id, '_manage_stock', 'no' );
			}
			return null;
		}

		public function update_value( $value, $post_id = false, $field = false ) {
			return null;
		}

	}



endif; // class_exists check


