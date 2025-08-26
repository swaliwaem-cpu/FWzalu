<?php

if( ! class_exists('acf_field_mailchimp_first_name') ) :

class acf_field_mailchimp_first_name extends acf_field_text {
	
	
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
		$this->name = 'mailchimp_first_name';
		$this->label = __("Mailchimp First Name",FEA_NS);
        $this->category = __( 'Mailchimp', FEA_NS );
		$this->defaults = array(
			'default_value'	=> '',
			'maxlength'		=> '',
			'placeholder'	=> '',
			'prepend'		=> '',
			'append'		=> ''
		); 

        add_filter( 'frontend_admin/add_to_record/'.$this->name, array( $this, 'add_to_record' ), 10, 3 );

    }

    function add_to_record( $record, $group, $field ){
        if( empty( $record['mailchimp']['first_name'] ) ){
            $record['mailchimp']['first_name'] = $group.':'.$field['name'];
        }
        return $record;
    }
    

    function prepare_field( $field ){
        $field['type'] = 'text';
        return $field;
    }

   
}

// initialize
acf_register_field_type( 'acf_field_mailchimp_first_name' );

endif;
	
?>