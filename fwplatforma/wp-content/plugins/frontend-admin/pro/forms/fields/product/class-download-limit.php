<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'download_limit' ) ) :

	class download_limit extends number {



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
			$this->name     = 'download_limit';
			$this->label    = __( 'Download Limit', 'acf-frontend-form-element' );
			$this->category = 'Downloadable Product';
			$this->defaults = array(
				'default_value' => '',
				'min'           => '0',
				'max'           => '',
				'step'          => '',
				'placeholder'   => __( 'Unlimited', 'woocommerce' ),
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
						array(
							'field'    => $fields['is_downloadable'],
							'operator' => '==',
							'value'    => '1',
						),
					),
				);
			}
			$field['placeholder'] = __( 'Unlimited', 'woocommerce' );
			if ( $field['min'] < 0 || $field['min'] == '' ) {
				$field['min'] = 0;
			}
			$field['type'] = 'number';

			return $field;
		}
		function load_value( $value, $post_id = false, $field = false ) {
			$value = get_post_meta( $post_id, '_download_limit', true );
			if ( $value == '-1' ) {
				$value = '';
			}
			return $value;
		}


		function load_field( $field ) {
			 $field['name'] = $field['type'];
			return $field;
		}
		function pre_update_value( $value, $post_id = false, $field = false ) {
			if ( $value == '' ) {
				$value = '-1';
			}
			update_metadata( 'post', $post_id, '_download_limit', $value );
			return null;
		}

		function update_value( $value, $post_id = false, $field = false ) {
			 return null;
		}


	}



endif; // class_exists check


