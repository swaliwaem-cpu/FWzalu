<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'product_upsells' ) ) :

	class product_upsells extends product_linked {



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
			$this->name     = 'product_upsells';
			$this->label    = __( 'Upsells', 'acf-frontend-form-element' );
			$this->category = __( 'Linked Products', 'acf-frontend-form-element' );
			$this->defaults = array(
				'post_type'       => array( 'product' ),
				'taxonomy'        => array(),
				'exclude_current' => 1,
				'min'             => 0,
				'max'             => 0,
				'filters'         => array( 'search', 'taxonomy' ),
				'elements'        => array(),
				'return_format'   => 'object',
				'add_edit_post'   => 0,
				'add_post_button' => __( 'Add Product', 'acf-frontend-form-element' ),
				'form_width'      => 600,
			);

			add_filter( 'acf/update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 3 );
		}

		function load_value( $value, $post_id, $field ) {
			$product = wc_get_product( $post_id );

			if ( $product ) {
				  $value = $product->get_upsell_ids( 'edit' );
			}

			return $value;
		}

		function pre_update_value( $value, $post_id, $field ) {
			if ( empty( $post_id ) || ! is_numeric( $post_id ) ) {
				return null;
			}

			$product = wc_get_product( $post_id );

			if ( $product ) {
				  $product->set_upsell_ids( $value );
				  $product->save();
			}
			return null;

		}
		function update_value( $value, $post_id, $field ) {
			   return null;
		}


	}



endif; // class_exists check


