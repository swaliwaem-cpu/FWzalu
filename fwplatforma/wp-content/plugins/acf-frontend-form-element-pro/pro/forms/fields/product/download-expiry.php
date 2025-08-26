<?php

if( ! class_exists('acf_field_download_expiry') ) :

class acf_field_download_expiry extends acf_field_number {
	
	
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
		$this->name = 'download_expiry';
		$this->label = __("Download Expiry",FEA_NS);
        $this->category = 'Downloadable Product';
		$this->defaults = array(
			'default_value'	=> '',
			'min'			=> '0',
			'max'			=> '',
			'step'			=> '',
			'placeholder'	=> __('Never','woocommerce'),
		);
        add_filter( 'acf/update_value/type=' . $this->name,  [ $this, 'pre_update_value'], 9, 3 );      
	}


    function prepare_field( $field ){
        if( isset( $GLOBALS['form_fields'] ) ){
            $fields = $GLOBALS['form_fields'];    
        $field['conditional_logic'] = array(
            array(
                array(
                    'field' => $fields['product_types'],
                    'operator' => '==',
                    'value' => 'simple',
                ),
                array(
                    'field' => $fields['is_downloadable'],
                    'operator' => '==',
                    'value' => '1',
                ),
            ),
        );
    }
        $field['placeholder'] = __('Never','woocommerce');
        if( $field['min'] < 0 || $field['min'] == '' ) $field['min'] = 0;

        $field['type'] = 'number';

        return $field;
    }
    function load_value( $value, $post_id = false, $field = false ){
        $value = get_post_meta( $post_id, '_download_expiry', true );
        if( $value == '-1' ) $value = '';
        return $value;
    }
    

  function load_field( $field ){
      $field['name'] = $field['type'];
      return $field;
}
  function pre_update_value( $value, $post_id = false, $field = false ){
        if( $value == '' ) $value = '-1';
        update_metadata( 'post', $post_id, '_download_expiry', $value );
        return null;
    }

    function update_value( $value, $post_id = false, $field = false ){
        return null;
    }
    
}

// initialize
acf_register_field_type( 'acf_field_download_expiry' );

endif; // class_exists check

?>