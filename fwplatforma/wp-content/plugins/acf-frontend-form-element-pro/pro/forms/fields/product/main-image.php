<?php

if( ! class_exists('acf_field_main_image') ) :

class acf_field_main_image extends acf_field_featured_image {
	
	
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
		$this->name = 'main_image';
		$this->label = __("Main Image",FEA_NS);
        $this->category = __( "Product", FEA_NS );
        $this->defaults = array(
			'return_format'	=> 'array',
			'preview_size'	=> 'medium',
			'library'		=> 'all',
			'min_width'		=> 0,
			'min_height'	=> 0,
			'min_size'		=> 0,
			'max_width'		=> 0,
			'max_height'	=> 0,
			'max_size'		=> 0,
			'mime_types'	=> '',
			'no_file_text'  => __( 'No Image selected', FEA_NS ),
		);
	     
		add_filter( 'acf/update_value/type=' . $this->name,  [ $this, 'pre_update_value'], 9, 3 );        

	}

	
}

// initialize
acf_register_field_type( 'acf_field_main_image' );

endif;
	
?>