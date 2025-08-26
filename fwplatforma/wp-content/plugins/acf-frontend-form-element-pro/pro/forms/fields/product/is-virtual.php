<?php

if( ! class_exists('acf_field_is_virtual') ) :

class acf_field_is_virtual extends acf_field_true_false {
	
	
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
		$this->name = 'is_virtual';
		$this->label = __( 'Virtual','acf' );
		$this->category = __( 'Product Type', FEA_NS );
		$this->defaults = array(
			'default_value'	=> 0,
			'message'		=> '',
			'ui'			=> 1,
			'ui_on_text'	=> '',
			'ui_off_text'	=> '',
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
            ),
        );
    }
        return $field;
    }

    public function load_value( $value, $post_id = false, $field = false ){
        if( get_post_meta( $post_id, '_virtual', true ) == 'yes' ){
            $value = true;
        }else{
            $value = false;
        }
        return $value;
    }

    function load_field( $field ){
      $field['name'] = $field['type'];
      return $field;
}
function pre_update_value( $value, $post_id = false, $field = false ){
        if( $value == 1 ){
            update_metadata( 'post', $post_id, '_virtual', 'yes' );
        }else{
            update_metadata( 'post', $post_id, '_virtual', 'no' );
        }
        return null;
    }

    public function update_value( $value, $post_id = false, $field = false ){
        return null;
    }		
	
}

// initialize
acf_register_field_type( 'acf_field_is_virtual' );

endif; // class_exists check

?>