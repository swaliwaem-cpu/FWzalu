<?php

if( ! class_exists('acf_field_shipping_attributes') ) :

class acf_field_shipping_attributes extends acf_field_number {
	
	
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
        $this->public = false;
        $this->attr = false;
	}


    function prepare_field( $field ){
        if( isset( $GLOBALS['form_fields'] ) ){
            $fields = $GLOBALS['form_fields'];    

            $simple = array(
                array(
                    'field' => $fields['product_types'],
                    'operator' => '==',
                    'value' => 'simple',
                ),
            );
            if( isset( $fields['is_virtual'] ) ){
                $simple[] = array(
                    'field' => $fields['is_virtual'],
                    'operator' => '==',
                    'value' => '0',
                );
            }
            $variation = array(
                array(
                    'field' => $fields['product_types'],
                    'operator' => '==',
                    'value' => 'variable',
                ),
            );

            $field['conditional_logic'] = array(
                $simple,
                $variation,
            );
        }

        $field['type'] = 'number';

        return $field;
    }
    function load_value( $value, $post_id = false, $field = false ){
        $value = get_post_meta( $post_id, '_'.$this->attr, true );
        return $value;
    }
    

  function load_field( $field ){
      $field['name'] = $field['type'];
      return $field;
}
  function pre_update_value( $value, $post_id = false, $field = false ){
        update_metadata( 'post', $post_id, '_'.$this->attr, $value );
        return null;
    }

    function update_value( $value, $post_id = false, $field = false ){
        return null;
    }
    
}

// initialize
acf_register_field_type( 'acf_field_shipping_attributes' );

endif; // class_exists check

?>