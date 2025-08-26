<?php

if( ! class_exists('acf_field_button_text') ) :

class acf_field_button_text extends acf_field_text {
	
	
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
		$this->name = 'button_text';
		$this->label = __( "Button Text",FEA_NS );
		$this->category = __( 'External/Affiliate Product', FEA_NS );
		$this->defaults = array(
			'default_value'	=> '',
			'maxlength'		=> '',
			'placeholder'	=> __( 'Buy product', FEA_NS ),
            'instructions'  => __( 'This text will be shown on the button linking to the external product.', 'woocommerce' ),
			'prepend'		=> '',
			'append'		=> ''
		);
		
    }

	function prepare_field( $field ){
		if( isset( $GLOBALS['form_fields'] ) ){
            $fields = $GLOBALS['form_fields'];

			$field['conditional_logic'] = array(
				array(
					array(
						'field' => $fields['product_types'],
						'operator' => '==',
						'value' => 'external',
					),
				),
			);
		}
		return $field;
	}

    public function load_value( $value, $post_id = false, $field = false ){
        if( get_post_type( $post_id ) !== 'product' ) return $value;
        
        $product = wc_get_product( $post_id );

        if ( $product->is_type( 'external' ) ) return $product->get_button_text();
    }

    public function update_value( $value, $post_id = false, $field = false ){
        if( get_post_type( $post_id ) !== 'product' ) return $value;
        
        $product = wc_get_product( $post_id );
        $product->set_button_text( $value );
        $product->save();

        return null;
    }

    function render_field( $field ){
        $field['type'] = 'text';
        parent::render_field( $field );
    }

   
}

// initialize
acf_register_field_type( 'acf_field_button_text' );

endif;
	
?>