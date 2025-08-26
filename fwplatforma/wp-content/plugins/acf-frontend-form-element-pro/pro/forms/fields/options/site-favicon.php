<?php

if( ! class_exists('acf_field_site_favicon') ) :

class acf_field_site_favicon extends acf_field_upload_image {
	
	
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
		$this->name = 'site_favicon';
		$this->label = __("Site Favicon",FEA_NS);
        $this->category = __( 'Site', FEA_NS );
        $this->defaults = array(
			'return_format'	=> 'array',
			'preview_size'	=> 'medium',
			'library'		=> 'all',
			'min_width'		=> 512,
			'min_height'	=> 512,
			'min_size'		=> 0,
			'max_width'		=> 0,
			'max_height'	=> 0,
			'max_size'		=> 0,
			'mime_types'	=> '',
			'no_file_text'  => __( 'No Image selected', FEA_NS ),
		);
		
		add_filter( 'acf/update_value/type=' . $this->name,  [$this, 'pre_update_value'], 9, 3 );       
		//add_action('wp_head', [$this, 'site_favicon'] );

	}

	function site_favicon(){
        $favicon = get_option( 'site_icon' );

		if( $favicon ){ ?>
			<link rel="shortcut icon" href="<?php echo wp_get_attachment_image_src( $favicon ); ?>" >
		<?php }

	}
    
    public function load_value( $value, $post_id = false, $field = false ){
        $value = get_option( 'site_icon' );

        return $value;
    }

    function load_field( $field ){
      $field['name'] = $field['type'];
      return $field;
}
function pre_update_value( $value, $post_id = false, $field = false ){
        update_option( 'site_icon', $value );
		return null;
	}

	public function update_value( $value, $post_id = false, $field = false ){
		return null;
	}

	public function render_field_settings( $field ) {
		acf_render_field_setting( $field, array(
			'label'			=> __('Button Text'),
			'name'			=> 'button_text',
			'type'			=> 'text',
			'placeholder'	=> __( 'Add Image', FEA_NS ),
		) );

	}

}

// initialize
acf_register_field_type( 'acf_field_site_favicon' );

endif;
	
?>