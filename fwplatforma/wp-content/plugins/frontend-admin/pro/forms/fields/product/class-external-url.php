<?php
namespace Frontend_Admin\Field_Types;

if( class_exists ( 'url' ) ) :

	class external_url extends url {



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
			$this->name     = 'external_url';
			$this->label    = __( 'Product URL', 'acf-frontend-form-element' );
			$this->category = __( 'External/Affiliate Product', 'acf-frontend-form-element' );
			$this->defaults = array(
				'default_value' => '',
				'placeholder'   => __( 'https://', 'acf-frontend-form-element' ),
				'instructions'  => __( 'Enter the external URL to the product.', 'woocommerce' ),
			);

		}

		function prepare_field( $field ) {
			if ( isset( $GLOBALS['form_fields'] ) ) {
				$fields                     = $GLOBALS['form_fields'];
				$field['conditional_logic'] = array(
					array(
						array(
							'field'    => $fields['product_types'],
							'operator' => '==',
							'value'    => 'external',
						),
					),
				);
			}
			return $field;

		}

		function load_value( $value, $post_id = false, $field = false ) {
			if ( get_post_type( $post_id ) !== 'product' ) {
				return $value;
			}

			$product = wc_get_product( $post_id );

			if ( $product->is_type( 'external' ) ) {
				return $product->get_product_url();
			}
		}

		public function update_value( $value, $post_id = false, $field = false ) {
			if ( get_post_type( $post_id ) !== 'product' ) {
				return $value;
			}

			$product = wc_get_product( $post_id );

			$product->set_product_url( $value );
			$product->save();

			return null;
		}

		function render_field( $field ) {
			$field['type'] = 'url';
			parent::render_field( $field );
		}


	}



endif;


