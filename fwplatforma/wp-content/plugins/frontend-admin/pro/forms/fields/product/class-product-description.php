<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'product_description' ) ) :

	class product_description extends post_content {



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
			$this->name     = 'product_description';
			$this->label    = __( 'Description', 'acf-frontend-form-element' );
			$this->category = __( 'Product', 'acf-frontend-form-element' );
			$this->defaults = array(
				'field_type'    => 'wysiwyg',
				'tabs'          => 'all',
				'toolbar'       => 'full',
				'media_upload'  => 1,
				'default_value' => '',
				'delay'         => 0,
				'new_lines'     => '',
				'maxlength'     => '',
				'placeholder'   => '',
				'rows'          => '',
			);
			add_filter( 'acf/update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 3 );

		}


	}



endif;


