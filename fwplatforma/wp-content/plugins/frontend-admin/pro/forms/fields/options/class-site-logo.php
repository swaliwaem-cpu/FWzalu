<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'site_logo' ) ) :

	class site_logo extends upload_image {



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
			$this->name     = 'site_logo';
			$this->label    = __( 'Site Logo', 'acf-frontend-form-element' );
			$this->category = __( 'Site', 'acf-frontend-form-element' );
			$this->defaults = array(
				'return_format' => 'array',
				'preview_size'  => 'medium',
				'library'       => 'all',
				'min_width'     => 0,
				'min_height'    => 0,
				'min_size'      => 0,
				'max_width'     => 0,
				'max_height'    => 0,
				'max_size'      => 0,
				'mime_types'    => '',
				'no_file_text'  => __( 'No Image selected', 'acf-frontend-form-element' ),
			);

			add_filter( 'acf/load_field/type=image', array( $this, 'load_site_logo_field' ) );
			add_filter( 'acf/update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 3 );
		}

		function load_site_logo_field( $field ) {
			if ( ! empty( $field['custom_site_logo'] ) ) {
				$field['type'] = 'site_logo';
			}
			return $field;
		}

		public function load_value( $value, $post_id = false, $field = false ) {
			$value = get_theme_mod( 'custom_logo' );

			/*
			 if( ! $value ){
			  $value = get_option( 'fea_custom_logo' );
			} */

			return $value;
		}

		function load_field( $field ) {
			 $field['name'] = $field['type'];
			return $field;
		}
		function pre_update_value( $value, $post_id = false, $field = false ) {
			   // update_option( 'fea_custom_logo', $value );
			set_theme_mod( 'custom_logo', $value );
			  return null;
		}

		public function update_value( $value, $post_id = false, $field = false ) {
			return null;
		}

		public function render_field_settings( $field ) {
			acf_render_field_setting(
				$field,
				array(
					'label'       => __( 'Button Text' ),
					'name'        => 'button_text',
					'type'        => 'text',
					'placeholder' => __( 'Add Image', 'acf-frontend-form-element' ),
				)
			);

		}

	}



endif;


