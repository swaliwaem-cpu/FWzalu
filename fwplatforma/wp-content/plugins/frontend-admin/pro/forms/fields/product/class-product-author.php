<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'product_author' ) ) :

	class product_author extends post_author {



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
			$this->name     = 'product_author';
			$this->label    = __( 'Author', 'acf-frontend-form-element' );
			$this->category = __( 'Product', 'acf-frontend-form-element' );
			$this->defaults = array(
				'data_name'     => 'author',
				'role'          => '',
				'multiple'      => 0,
				'allow_null'    => 0,
				'return_format' => 'array',
			);

			add_filter( 'acf/update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 3 );

		}

	}



endif;


