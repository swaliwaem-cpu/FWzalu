<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'main_image' ) ) :

	class main_image extends featured_image {



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
			$this->name     = 'main_image';
			$this->label    = __( 'Main Image', 'acf-frontend-form-element' );
			$this->category = __( 'Product', 'acf-frontend-form-element' );
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

			add_filter( 'acf/update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 3 );

		}


	}



endif;


