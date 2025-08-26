<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'product_short_description' ) ) :

	class product_short_description extends post_excerpt {



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
			$this->name     = 'product_short_description';
			$this->label    = __( 'Short Description', 'acf-frontend-form-element' );
			$this->category = __( 'Product', 'acf-frontend-form-element' );
			$this->defaults = array(
				'default_value' => '',
				'new_lines'     => '',
				'maxlength'     => '',
				'placeholder'   => '',
				'rows'          => '',
			);
			add_filter( 'acf/update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 3 );

		}

	}



endif;


