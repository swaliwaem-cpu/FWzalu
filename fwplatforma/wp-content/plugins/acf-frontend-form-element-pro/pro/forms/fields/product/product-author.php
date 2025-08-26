<?php

if( ! class_exists('acf_field_product_author') ) :

class acf_field_product_author extends acf_field_post_author {
	
	
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
		$this->name = 'product_author';
		$this->label = __("Author",FEA_NS);
        $this->category = __( "Product", FEA_NS );
		$this->defaults = array(
            'data_name'     => 'author',
			'role' 			=> '',
			'multiple' 		=> 0,
			'allow_null' 	=> 0,
			'return_format'	=> 'array',
		);
      
        add_filter( 'acf/update_value/type=' . $this->name,  [ $this, 'pre_update_value'], 9, 3 );      

	}

}

// initialize
acf_register_field_type( 'acf_field_product_author' );

endif;
	
?>