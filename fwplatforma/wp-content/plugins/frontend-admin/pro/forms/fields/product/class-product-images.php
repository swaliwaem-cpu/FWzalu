<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'product_images' ) ) :

	class product_images extends upload_files {



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
			$this->name     = 'product_images';
			$this->label    = __( 'Images', 'acf-frontend-form-element' );
			$this->category = __( 'Product', 'acf-frontend-form-element' );
			$this->defaults = array(
				'library'     => 'all',
				'min'         => 0,
				'max'         => 0,
				'min_width'   => 0,
				'min_height'  => 0,
				'min_size'    => 0,
				'max_width'   => 0,
				'max_height'  => 0,
				'max_size'    => 0,
				'mime_types'  => '',
				'insert'      => 'append',
				'button_text' => __( 'Add Images', 'acf-frontend-form-element' ),
			);

			add_filter( 'acf/load_field/type=gallery', array( $this, 'load_main_images_field' ) );
			add_filter( 'acf/update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 3 );

		}


		function load_main_images_field( $field ) {
			if ( ! empty( $field['custom_product_gallery'] ) ) {
				$field['type'] = 'product_images';
			}
			return $field;
		}

		public function load_value( $value, $post_id = false, $field = false ) {
			if ( $post_id && is_numeric( $post_id ) ) {
				$value = explode( ',', get_post_meta( $post_id, '_product_image_gallery', true ) );
			}
			return $value;
		}

		function load_field( $field ) {
			 $field['name'] = $field['type'];
			return $field;
		}
		function pre_update_value( $value, $post_id = false, $field = false ) {
			if ( $post_id && is_numeric( $post_id ) ) {
				$product_images = $value;
				if ( is_array( $product_images ) ) {
					  $product_images = implode( ',', $product_images );
				}
				 update_metadata( 'post', $post_id, '_product_image_gallery', $product_images );
			}
			return null;
		}

		public function update_value( $value, $post_id = false, $field = false ) {
			return null;
		}


	}



endif;


