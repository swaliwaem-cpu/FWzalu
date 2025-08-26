<?php

if( ! class_exists('acf_field_product_sku') ) :

class acf_field_product_sku extends acf_field_text {
	
	
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
		$this->name = 'product_sku';
		$this->label = __( "SKU", FEA_NS );
		$this->category = __( 'Product Inventory', FEA_NS );
        $this->defaults = array(
			'default_value'	=> '',
			'maxlength'		=> '',
			'placeholder'	=> '',
			'prepend'		=> '',
			'append'		=> ''
		);
        add_filter( 'acf/load_field/type=text',  [ $this, 'load_product_sku_field'] );
        add_filter( 'acf/update_value/type=' . $this->name,  [ $this, 'pre_update_value'], 9, 3 );   
	}
    
    function load_product_sku_field( $field ){
        if( ! empty( $field['custom_sku'] ) ){
            $field['type'] = 'product_sku';
        }
        return $field;
    }

    public function load_value( $value, $product_id = false, $field = false ){
        if( $product_id && is_numeric( $product_id ) ){  
            $value = get_post_meta( $product_id, '_sku', true );
        }
        return $value;
    }

    function load_field( $field ){
      $field['name'] = $field['type'];
      return $field;
}
function pre_update_value( $value, $product_id = false, $field = false ){
        if( $product_id && is_numeric( $product_id ) ){  
            update_metadata( 'post', $product_id, '_sku', $value );
        }
        return null;
    }

    public function update_value( $value, $product_id = false, $field = false ){
        return null;
    }

    function render_field( $field ){
        $field['type'] = 'text';
        parent::render_field( $field );
    }

   
}

// initialize
acf_register_field_type( 'acf_field_product_sku' );

endif;
	
?>