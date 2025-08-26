<?php

if( ! class_exists('acf_field_product_weight') ) :

class acf_field_product_weight extends acf_field_shipping_attributes {
	
	
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
		$this->name = 'product_weight';
		$this->label = __( "Weight",FEA_NS );
		$this->category = __( 'Product Shipping', FEA_NS );
        $this->attr = 'weight';
		$this->defaults = array(
			'default_value'	=> '',
			'min'			=> '0',
			'max'			=> '',
			'step'			=> '0.01',
			'placeholder'	=> '',
			'prepend'		=> '',
			'append'		=> ''
		);
        add_filter( 'acf/update_value/type=' . $this->name,  [ $this, 'pre_update_value'], 9, 3 );      
	}

    
}

// initialize
acf_register_field_type( 'acf_field_product_weight' );

endif; // class_exists check

?>