<?php

if( ! class_exists('acf_field_mailchimp_email') ) :

class acf_field_mailchimp_email extends acf_field_email {
	
	
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
		$this->name = 'mailchimp_email';
		$this->label = __("Mailchimp Email",FEA_NS);
        $this->category = __( 'Mailchimp', FEA_NS );
		$this->defaults = array(
            'default_value'	=> '',
			'placeholder'	=> '',
			'prepend'		=> '',
			'append'		=> ''
		);     
        add_filter( 'frontend_admin/add_to_record/'.$this->name, array( $this, 'add_to_record' ), 10, 3 );

    }

    function add_to_record( $record, $group, $field ){
        if( empty( $record['mailchimp']['email'] ) ){
            $record['mailchimp']['email'] = $group.':'.$field['name'];
        }
        return $record;
    }
    
   

    public function validate_value( $is_valid, $value, $field, $input ){        
        if( $field['required'] == 0 && $value == '' ){
            return $is_valid;
        }

        if ( ! is_email( $value ) ){
            return sprintf( __( '%s is not a valid email address', FEA_NS ),  $value );
        }else{
            list($name, $domain) = explode('@', $value);
            if( ! checkdnsrr( $domain, 'MX') ){
                return sprintf( __( '%s is not a valid domain', FEA_NS ),  $domain );
            }
        }
        
        return $is_valid;
    }

    function prepare_field( $field ){
        $field['type'] = 'email';
        return $field;
    }

}

// initialize
acf_register_field_type( 'acf_field_mailchimp_email' );

endif;
	
?>