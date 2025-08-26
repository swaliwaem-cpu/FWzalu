<?php

if( ! class_exists('acf_field_low_stock_threshold') ) :

class acf_field_low_stock_threshold extends acf_field_number {
	
	
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
		$this->name = 'low_stock_threshold';
		$this->label = __( "Low Stock Threshold",FEA_NS );
		$this->category = __( 'Product Inventory', FEA_NS );
		$this->defaults = array(
			'default_value'	=> '',
			'min'			=> '0',
			'max'			=> '',
			'step'			=> '',
			'placeholder'	=> '',
			'prepend'		=> '',
			'append'		=> ''
		);
        add_filter( 'acf/load_field/type=number',  [ $this, 'load_low_stock_threshold_field'], 2 );
        add_filter( 'acf/update_value/type=' . $this->name,  [ $this, 'pre_update_value'], 9, 3 );      
	}

    function load_low_stock_threshold_field( $field ){
        if( ! empty( $field['custom_low_stock'] ) ){
            $field['type'] = 'low_stock_threshold';
        }
        return $field;
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
                        'field' => $fields['manage_stock'],
                        'operator' => '==',
                        'value' => '1',
                    )
                ),
                array(
                    array(
                        'field' => $fields['product_types'],
                        'operator' => '==',
                        'value' => 'variable',
                    ),
                    array(
                        'field' => $fields['manage_stock'],
                        'operator' => '==',
                        'value' => '1',
                    )
                ),
            );
        }
        $field['type'] = 'number';

        return $field;
    }

    public function load_value( $value, $post_id = false, $field = false ){
        $value = get_post_meta( $post_id, '_low_stock_amount', true );
        return $value;
    }

    function load_field( $field ){
      $field['name'] = $field['type'];
      return $field;
}
function pre_update_value( $value, $post_id = false, $field = false ){
        update_metadata( 'post', $post_id, '_low_stock_amount', $value );
        return null;
    }

    public function update_value( $value, $post_id = false, $field = false ){
        return null;
    }

}

// initialize
acf_register_field_type( 'acf_field_low_stock_threshold' );

endif; // class_exists check

?>