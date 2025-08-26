<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'site_favicon' ) ) :

	class site_favicon extends upload_image {



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
			$this->name     = 'site_favicon';
			$this->label    = __( 'Site Favicon', 'acf-frontend-form-element' );
			$this->category = __( 'Site', 'acf-frontend-form-element' );
			$this->defaults = array(
				'return_format' => 'array',
				'preview_size'  => 'medium',
				'library'       => 'all',
				'min_width'     => 512,
				'min_height'    => 512,
				'min_size'      => 0,
				'max_width'     => 0,
				'max_height'    => 0,
				'max_size'      => 0,
				'mime_types'    => '',
				'no_file_text'  => __( 'No Image selected', 'acf-frontend-form-element' ),
			);

			add_filter( 'acf/update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 3 );
			// add_action('wp_head', [$this, 'site_favicon'] );
		}

		function site_favicon() {
			$favicon = get_option( 'site_icon' );

			if ( $favicon ) { ?>
				  <link rel="shortcut icon" href="<?php echo esc_url( wp_get_attachment_image_src( $favicon ) ); ?>" >
			<?php }

		}

		public function load_value( $value, $post_id = false, $field = false ) {
			$value = get_option( 'site_icon' );

			return $value;
		}

		function load_field( $field ) {
			 $field['name'] = $field['type'];
			return $field;
		}
		function pre_update_value( $value, $post_id = false, $field = false ) {
			 update_option( 'site_icon', $value );
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

?>
