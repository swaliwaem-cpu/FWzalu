<?php

if( ! class_exists('acf_field_product_menu_order') ) :

class acf_field_product_menu_order extends acf_field_menu_order {
	
	
	/*
	*  initialize
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function initialize() {
		
		// vars
		$this->name = 'product_menu_order';
		$this->label = __("Menu Order",FEA_NS);
		$this->category = __( 'Advanced Product Options', FEA_NS );
		$this->defaults = array(
			'default_value'	=> '',
			'min'			=> '0',
			'max'			=> '',
			'step'			=> '',
			'placeholder'	=> '',
			'prepend'		=> '',
			'append'		=> ''
		);
	}
}

// initialize
acf_register_field_type( 'acf_field_product_menu_order' );

endif; // class_exists check

?>