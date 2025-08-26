<?php

if( ! class_exists('acf_field_delete_product') ) :

class acf_field_delete_product extends acf_field_delete_object {
	
	
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
		$this->name = 'delete_product';
		$this->label = __("Delete Product",FEA_NS);
		$this->category = __( 'Product', FEA_NS );
		$this->object = 'product';
		$this->defaults = array(
			'button_text' 	=> __( 'Delete', FEA_NS ),
			'confirmation_text' => __( 'Are you sure you want to delete this product?', FEA_NS ),
            'field_label_hide'  => 1,
			'force_delete' => 0,
			'redirect' => 'current',
			'delete_message' => __( 'Your product has been deleted' ),
		);
		
	}
	
	
}


// initialize
acf_register_field_type( 'acf_field_delete_product' );

endif; // class_exists check

?>