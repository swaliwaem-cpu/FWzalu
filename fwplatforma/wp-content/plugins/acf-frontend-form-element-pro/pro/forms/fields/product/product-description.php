<?php

if( ! class_exists('acf_field_product_description') ) :

class acf_field_product_description extends acf_field_post_content {
	
	
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
		$this->name = 'product_description';
		$this->label = __("Description",FEA_NS);
        $this->category = __( "Product", FEA_NS );
		$this->defaults = array(
            'field_type'    => 'wysiwyg',
			'tabs'			=> 'all',
			'toolbar'		=> 'full',
			'media_upload' 	=> 1,
			'default_value'	=> '',
			'delay'			=> 0,
			'new_lines'		=> '',
			'maxlength'		=> '',
			'placeholder'	=> '',
			'rows'			=> ''
		);
        add_filter( 'acf/update_value/type=' . $this->name,  [ $this, 'pre_update_value'], 9, 3 );      
		
	}


}

// initialize
acf_register_field_type( 'acf_field_product_description' );

endif;
	
?>