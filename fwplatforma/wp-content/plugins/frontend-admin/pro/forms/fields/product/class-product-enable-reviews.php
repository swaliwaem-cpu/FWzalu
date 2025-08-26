<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'product_enable_reviews' ) ) :

	class product_enable_reviews extends allow_comments {



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
			$this->name     = 'product_enable_reviews';
			$this->label    = __( 'Enable Reviews', 'acf-frontend-form-element' );
			$this->category = __( 'Advanced Product Options', 'acf-frontend-form-element' );
			$this->defaults = array(
				'default_value' => 0,
				'message'       => '',
				'ui'            => 1,
				'ui_on_text'    => '',
				'ui_off_text'   => '',
			);
			add_filter( 'acf/update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 3 );
		}

	}



endif; // class_exists check


