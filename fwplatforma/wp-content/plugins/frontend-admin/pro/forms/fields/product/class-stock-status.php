<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'stock_status' ) ) :

	class stock_status extends Field_Base {



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
			$this->name     = 'stock_status';
			$this->label    = __( 'Stock Status', 'acf-frontend-form-element' );
			$this->category = __( 'Product Inventory', 'acf-frontend-form-element' );
			$this->defaults = array(
				'multiple'      => 0,
				'allow_null'    => 0,
				'choices'       => array(),
				'default_value' => '',
				'ui'            => 0,
				'ajax'          => 0,
				'placeholder'   => '',
				'return_format' => 'value',
				'field_type'    => 'radio',
				'layout'        => 'vertical',
				'other_choice'  => 0,
			);
			add_filter( 'acf/load_field/type=select', array( $this, 'load_stock_status_field' ), 2 );
			add_filter( 'acf/update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 3 );

		}

		function load_stock_status_field( $field ) {
			if ( ! empty( $field['custom_stock_status'] ) ) {
				$field['type'] = 'stock_status';
			}
			return $field;
		}



		function prepare_field( $field ) {
			if ( ! $field['choices'] ) {
				$field['choices'] = array(
					'instock'     => __( 'In stock', 'woocommerce' ),
					'outofstock'  => __( 'Out of stock', 'woocommerce' ),
					'onbackorder' => __( 'On backorder', 'woocommerce' ),
				);
			}

			if ( isset( $GLOBALS['form_fields'] ) ) {
				$fields                     = $GLOBALS['form_fields'];
				$field['conditional_logic'] = array(
					array(
						array(
							'field'    => $fields['product_types'],
							'operator' => '==',
							'value'    => 'simple',
						),
						array(
							'field'    => $fields['manage_stock'],
							'operator' => '!=',
							'value'    => '1',
						),
					),
				);
			}
			return $field;
		}

		public function load_value( $value, $post_id = false, $field = false ) {
			$value = get_post_meta( $post_id, '_stock_status', true );
			return $value;
		}

		function load_field( $field ) {
			 $field['name'] = $field['type'];
			return $field;
		}
		function pre_update_value( $value, $post_id = false, $field = false ) {
			 update_metadata( 'post', $post_id, '_stock_status', $value );
			return null;
		}

		public function update_value( $value, $post_id = false, $field = false ) {
			return null;
		}

		function render_field( $field ) {
			$field['type'] = $field['field_type'];
			acf_render_field( $field );

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
			  // field_type
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Appearance', 'acf-frontend-form-element' ),
					'instructions' => __( 'Select the appearance of this field', 'acf-frontend-form-element' ),
					'type'         => 'select',
					'name'         => 'field_type',
					'optgroup'     => true,
					'choices'      => array(
						'radio'  => __( 'Radio Buttons', 'acf-frontend-form-element' ),
						'select' => _x( 'Select', 'noun', 'acf-frontend-form-element' ),
					),
				)
			);
		}

	}



endif;


