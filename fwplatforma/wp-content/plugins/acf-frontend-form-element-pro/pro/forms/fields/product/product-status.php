<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acf_field_product_status')):

class acf_field_product_status extends acf_field_post_status{
    
    function initialize(){
        
        $this->name = 'product_status';
        $this->label = __('Product Status', FEA_NS);
        $this->category = __( "Product", FEA_NS );
        $this->defaults = array(
            'product_status'           => array(),
            'field_type'            => 'checkbox',            'choices'               => array(),
            'default_value'         => '',
            'ui'                    => 0,
            'ajax'                  => 0,
            'placeholder'           => '',
            'search_placeholder'    => '',
            'layout'                => '',
            'toggle'                => 0,
            'allow_custom'          => 0,
            'return_format'         => 'object',
            'post_status'           => array( 'publish', 'draft', 'pending','private' ),
        );

        add_filter( 'acf/update_value/type=' . $this->name,  [ $this, 'pre_update_value'], 9, 3 );          
    }

}

// initialize
acf_register_field_type('acf_field_product_status');

endif;