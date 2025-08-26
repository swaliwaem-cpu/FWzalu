<?php

if( ! class_exists('acf_field_site_tagline') ) :

class acf_field_site_tagline extends acf_field_text {
	
	
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
		$this->name = 'site_tagline';
		$this->label = __("Site Tagline",FEA_NS);
        $this->category = __( 'Site', FEA_NS );
		$this->defaults = array(
			'default_value'	=> '',
			'maxlength'		=> '',
			'placeholder'	=> '',
			'prepend'		=> '',
			'append'		=> ''
		);
        add_filter( 'acf/load_field/type=text',  [ $this, 'load_site_tagline_field'] );
        add_filter( 'acf/update_value/type=' . $this->name,  [ $this, 'pre_update_value'], 9, 3 );      
		
	}
    
    function load_site_tagline_field( $field ){
        if( ! empty( $field['custom_site_tagline'] ) ){
            $field['type'] = 'site_tagline';
        }
        return $field;
    }

    public function load_value( $value, $post_id = false, $field = false ){
        $value = get_option( 'blogdescription' );
        return $value;
    }

    function load_field( $field ){
      $field['name'] = $field['type'];
      return $field;
}
function pre_update_value( $value, $post_id = false, $field = false ){
        update_option( 'blogdescription', $value );
        return $value;
    }

    public function update_value( $value, $post_id = false, $field = false ){
        return null;
    }

    function render_field( $field ){
        $field['type'] = 'text';
        parent::render_field( $field );

    }

}

// initialize
acf_register_field_type( 'acf_field_site_tagline' );

endif;
	
?>