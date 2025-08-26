<?php

if( ! class_exists('acf_field_product_slug') ) :

class acf_field_product_slug extends acf_field_text {
	
	
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
		$this->name = 'product_slug';
		$this->label = __("Slug",FEA_NS);
        $this->category = __( "Product", FEA_NS );
		$this->defaults = array(
			'default_value'	=> '',
			'maxlength'		=> '',
			'placeholder'	=> '',
			'prepend'		=> '',
			'append'		=> ''
		);
        add_filter( 'acf/load_field/type=text',  [ $this, 'load_product_slug_field'] );
        add_filter( 'acf/update_value/type=' . $this->name,  [ $this, 'pre_update_value'], 9, 3 );   
	}
    
    function load_product_slug_field( $field ){
        if( ! empty( $field['custom_slug'] ) ){
            $field['type'] = 'product_slug';
        }
        return $field;
    }

    function load_field( $field ){
        $field['name'] = $field['type'];
        if( isset( $field['wrapper']['class'] ) ){ 
            $field['wrapper']['class'] .= ' post-slug-field';
        }else{
            $field['wrapper']['class'] = 'post-slug-field';
        }
        return $field;
    }

    public function load_value( $value, $product_id = false, $field = false ){
        if( $product_id && is_numeric( $product_id ) ){  
            $edit_product = get_post( $product_id );
            $value = $edit_product->post_name == 'auto-draft' ? '' : $edit_product->post_name;
        }
        return $value;
    }

function pre_update_value( $value, $product_id = false, $field = false ){
        if( $product_id && is_numeric( $product_id ) ){  
            $product_to_edit = [
                'ID' => $product_id,
            ];
            $product_to_edit['post_name'] = sanitize_text_field( $value );
            remove_action( 'acf/save_post', '_acf_do_save_post' );
            wp_update_post( $product_to_edit );
            add_action( 'acf/save_post', '_acf_do_save_post' );
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
acf_register_field_type( 'acf_field_product_slug' );

endif;
	
?>