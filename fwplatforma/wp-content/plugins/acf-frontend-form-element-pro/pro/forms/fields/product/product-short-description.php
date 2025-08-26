<?php

if( ! class_exists('acf_field_product_short_description') ) :

class acf_field_product_short_description extends acf_field_post_excerpt {
	
	
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
		$this->name = 'product_short_description';
		$this->label = __("Short Description",FEA_NS);
        $this->category = __( "Product", FEA_NS );
		$this->defaults = array(
			'default_value'	=> '',
			'new_lines'		=> '',
			'maxlength'		=> '',
			'placeholder'	=> '',
			'rows'			=> ''
		);
        add_filter( 'acf/update_value/type=' . $this->name,  [ $this, 'pre_update_value'], 9, 3 );      
		
	}

}

// initialize
acf_register_field_type( 'acf_field_product_short_description' );

endif;
	
?>