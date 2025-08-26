<?php

if( ! class_exists('acf_field_downloadable_files') ) :

class acf_field_downloadable_files extends acf_field_list_items {
	
	
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
		$this->name = 'downloadable_files';
		$this->label = __("Downloadable Files",'woocommerce');
		$this->category = 'Downloadable Product';
		$this->defaults = array(
			'min'			=> 0,
			'max'			=> 0,
			'layout' 		=> 'table',
			'button_label'	=> __( 'Add File', 'woocommerce' ),
			'duplicate_label'	=> __( 'Duplicate File', FEA_NS ),
			'remove_label'	=> __( 'Remove File', FEA_NS ),
			'no_value_msg'  => '',
			'no_attrs_msg'  => '',
			'collapsed'		=> '',
			'sub_fields' 	=> array(),
			'row_data'		=> array(
				array(
					'class' => 'row-download-id',
					'name' => 'download_id',
					'value' => 'id',
				),
			),
			'fields_settings' => array(
				'name' => array(
					'placeholder' => __( 'Name', FEA_NS ),
					'label' => __( 'Name', FEA_NS ),
					'id' => __( 'Name', FEA_NS ),
					'width' => '25',
					'name' => 'name',
				),
				'file' => array(
					'placeholder' => 'https://my-file-url.com',
					'label' => __( 'File URL', FEA_NS ),
					'id' => __( 'File URL', FEA_NS ),
					'width' => '60',
					'name' => 'file',
				),
				'upload' => array(
					'placeholder' => __( 'Upload', FEA_NS ),
					'label' => __( 'Upload', FEA_NS ),
					'id' => __( 'Upload', FEA_NS ),
					'width' => '15',
					'name' => 'upload',
				),
			),
		);		

		
		// field filters
		$this->add_field_filter('acf/prepare_field_for_export', array($this, 'prepare_field_for_export'));
		$this->add_field_filter('acf/prepare_field_for_import', array($this, 'prepare_field_for_import'));

		// filters
		$this->add_filter('acf/validate_field',	array($this, 'validate_any_field'));
		
	}

		/*
	*  load_field()
	*
	*  This filter is appied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$field - the field array holding all the field options
	*/
	
	function load_field( $field ) {
		
		if( empty( $field['fields_settings'] ) ){
			$field['fields_settings'] = $this->defaults['fields_settings'];
		}

		$settings = $field['fields_settings'];
		// min/max
		$field['min'] = (int) $field['min'];
		$field['max'] = (int) $field['max'];

		$field['sub_fields'] = array(
			array( //file name
				'prefix' => 'acf',
				'type' => 'text',
				'key' => 'name',
				'name' => 'name',
				'_name' => 'name',
				'label' => $settings['name']['label'],
				'placeholder' => $settings['name']['placeholder'],
				'field_label_on' => 1,
				'instructions' => '',
				'required' => 0,
				'disabled' => 0,
				'prepend' => '',
				'append' => '',
				'wrapper' => array(
					'width' => $settings['name']['width'],
					'class' => '',
					'id' => '',
				)
			),
			array( // upload url
				'prefix' => 'acf',
				'type' => 'url',
				'key' => 'file',
				'name' => 'file',
				'_name' => 'file',
				'label' => $settings['file']['label'],
				'placeholder' => $settings['file']['placeholder'],
				'field_label_on' => 1,
				'instructions' => '',
				'required' => 0,
				'disabled' => 0,
				'prepend' => '',
				'append' => '',
				'wrapper' => array(
					'width' => $settings['file']['width'],
					'class' => '',
					'id' => '',
				)
			),
		);

		// vars
		$uploader = acf_get_setting('uploader');		
		// enqueue
		if( $uploader == 'wp' ) {
			$field['sub_fields'][] = array( //upload button
				'prefix' => 'acf',
				'type' => 'url_upload',
				'key' => 'upload',
				'name' => 'upload',
				'_name' => 'upload',
				'label' => $settings['upload']['label'],
				'button_text' => $settings['upload']['placeholder'],
				'no_file_text' => '',
				'no_save' => 1,
				'destination' => 'file',
				'field_label_on' => 0,
				'instructions' => '',
				'required' => 0,
				'disabled' => 0,
				'hidden' => 0,
				'prepend' => '',
				'append' => '',
				'wrapper' => array(
					'width' => $settings['upload']['width'],
					'class' => '',
					'id' => '',
				)
			);
		}
		
		// return
		return $field;

	}

	function prepare_field( $field ) {

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
		$field['collapsed'] = false;
		$field['button_label'] = __( 'Add File', 'woocommerce' );
		$field['duplicate_label'] = __( 'Duplicate File', FEA_NS );
		$field['remove_label'] = __( 'Remove File', FEA_NS );
		
		// return
		return $field;

	}

		/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/
	
	function load_value( $value, $post_id, $field ) {

		$rows = array();

		if( get_post_type( $post_id ) == 'product' ){
			$product = wc_get_product( $post_id );
			$i = 0;

			if ( ! $product->is_downloadable() ) return array();

			$i = 0;
			foreach ( $product->get_downloads() as $file_id => $file ) { $i++;

				$rows[$i]['id'] = $file_id;
				$rows[$i]['name'] = $file['name'];
				$rows[$i]['file'] = $file['file'];
				
			}


		}
			
		// return
		return $rows;
		
	}

	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the $post_id of which the value will be saved
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field ) {		
		// bail early if no sub fields
		if( get_post_type( $post_id ) != 'product' ) return null;
		
		$product = wc_get_product( $post_id );
		if( ! $product->is_downloadable() ) return null;

		$downloads = array();

		foreach ( $value as $row ) {
			if ( ! isset( $row['name'], $row['file'] ) ) {
				continue;
			}
			$download = new WC_Product_Download();
			$download->set_id( ! empty( $row['download_id'] ) ? $row['download_id'] : wp_generate_uuid4() );
			$download->set_name( $row['name'] ? $row['name'] : wc_get_filename_from_url( $row['file'] ) );
			$download->set_file( apply_filters( 'woocommerce_file_download_path', $row['file'], $product, $row['download_id'] ) );
			$downloads[] = $download;
		}
		$product->set_downloads( $downloads );
		
		$product->save();

		// return
		return null;
	}

		/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function render_field_settings( $field ) {
	
		// min
		acf_render_field_setting( $field, array(
			'label'			=> __('Minimum Files',FEA_NS),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'min',
			'placeholder'	=> '0',
		));
		
		
		// max
		acf_render_field_setting( $field, array(
			'label'			=> __('Maximum Files',FEA_NS),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'max',
			'placeholder'	=> '0',
		));			
		// button_label
		acf_render_field_setting( $field, array(
			'label'			=> __('Add File Label',FEA_NS),
			'instructions'	=> '',
			'type'			=> 'text',
			'name'			=> 'button_label',
			'placeholder'	=> __('Add File',FEA_NS)
		));
		// button_label
		acf_render_field_setting( $field, array(
			'label'			=> __('Remove File Label',FEA_NS),
			'instructions'	=> '',
			'type'			=> 'text',
			'name'			=> 'remove_label',
			'placeholder'	=> __('Remove File',FEA_NS)
		));
		//$input_prefix = 'acf_fields[' . $field['ID'] . ']';
		//$input_prefix = 'steps[' . $field['step'] . '][' . $field['ID'] . ']';
		
		fea_instance()->form_display->render_field_setting( $field, array(
			'label' => __( 'Fields', FEA_NS ),
			'name' => 'fields_settings',
			'type' => 'list_items',
			//'prefix' => $input_prefix,
			'show_add' => false,
			'show_remove' => false,
			'show_order' => 'ids',
			'save_names' => 1,
			'maintain_order' => 1,
			'layout' => 'table',
			'sub_fields' => array(
				array(
					'key' => 'id',
					'name' => 'id',
					'type' => 'text',
					'frontend_admin_display_mode' => 'hidden'
				),
				array(
					'key' => 'label',
					'name' => 'label',
					'label' => __( 'Label', FEA_NS ),
					'type' => 'text'
				),
				array(
					'key' => 'placeholder',
					'name' => 'placeholder',
					'label' => __( 'Placeholder', FEA_NS ),
					'type' => 'text'
				),  
				array(
					'key' => 'width',
					'name' => 'width',
					'label' => __( 'Width', FEA_NS ),
					'prepend' => '%',
					'type' => 'number'
				),  
			),
		) );
		
	}

}


// initialize
acf_register_field_type( 'acf_field_downloadable_files' );

endif; // class_exists check

?>
