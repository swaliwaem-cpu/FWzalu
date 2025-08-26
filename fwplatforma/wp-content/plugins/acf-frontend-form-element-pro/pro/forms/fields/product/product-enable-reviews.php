<?php

if( ! class_exists('acf_field_product_enable_reviews') ) :

class acf_field_product_enable_reviews extends acf_field_allow_comments {
	
	
	/*
	*  __construct
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
		$this->name = 'product_enable_reviews';
		$this->label = __( 'Enable Reviews',FEA_NS );
		$this->category = __( 'Advanced Product Options', FEA_NS );
		$this->defaults = array(
			'default_value'	=> 0,
			'message'		=> '',
			'ui'			=> 1,
			'ui_on_text'	=> '',
			'ui_off_text'	=> '',
		);
        add_filter( 'acf/update_value/type=' . $this->name,  [ $this, 'pre_update_value'], 9, 3 );      	
	}

}

// initialize
acf_register_field_type( 'acf_field_product_enable_reviews' );

endif; // class_exists check

?>