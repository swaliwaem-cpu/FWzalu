<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'product_menu_order' ) ) :

	class product_menu_order extends menu_order {



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
			$this->name     = 'product_menu_order';
			$this->label    = __( 'Menu Order', 'acf-frontend-form-element' );
			$this->category = __( 'Advanced Product Options', 'acf-frontend-form-element' );
			$this->defaults = array(
				'default_value' => '',
				'min'           => '0',
				'max'           => '',
				'step'          => '',
				'placeholder'   => '',
				'prepend'       => '',
				'append'        => '',
			);
		}
	}



endif; // class_exists check


