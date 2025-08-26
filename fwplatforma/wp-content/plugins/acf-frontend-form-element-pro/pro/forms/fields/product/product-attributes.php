<?php

if( ! class_exists('acf_field_product_attributes') ) :

class acf_field_product_attributes extends acf_field {
	
	
	/*
	*  initialize
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2020
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function initialize() {		
		// vars
		$this->name = 'product_attributes';
		$this->label = __("Product Attributes",FEA_NS);
		$this->category = __( 'Product Attributes', FEA_NS );
		$this->defaults = array(
			'layouts' 		=> [], 
			'min'			=> '',
			'max'			=> '',
			'button_label'  => __( 'Add Attribute', FEA_NS ),
			'save_text'     => __( 'Save Changes', FEA_NS ),
			'no_value_msg'  => '',
			'fields_settings' => array(
				'name' => array(
					'id' => __( 'Name', FEA_NS ),
					'field_label_hide' => 0,
					'label' => __( 'Name', FEA_NS ),
					'placeholder' => '',
					'instructions' => '',
				),
				'locations' => array(
					'id' => __( 'Locations', FEA_NS ),
					'field_label_hide' => 1,
					'label' => __( 'Locations', FEA_NS ),
					'choices' => array(
						'products_page' => __( 'Visible on the product page', FEA_NS ),
						'for_variations' => __( 'Used for variations', FEA_NS ),
					),
					'instructions' => '',
				),
				'custom_terms' => array(
					'id' => __( 'Custom Terms', FEA_NS ),
					'field_label_hide' => 0,
					'label' => __( 'Value(s)', FEA_NS ),
					'instructions' => '',
					'button_label' => __( 'Add Value', FEA_NS ),
				),
				'terms' => array(
					'id' => __( 'Global Terms', FEA_NS ),
					'field_label_hide' => 0,
					'label' => __( 'Terms', FEA_NS ),
					'instructions' => '',
					'button_label' => __( 'Add Value', FEA_NS ),
				),
			),
		);
	
		// actions		
		add_action( 'wp_ajax_frontend_admin/fields/attributes/save_attributes', array( $this, 'ajax_save_attributes', ) );
		add_action( 'wp_ajax_nopriv_frontend_admin/fields/attributes/save_attributes', array( $this, 'ajax_save_attributes' ) );	
		
		// filters
		$this->add_filter('acf/prepare_field_for_export',	array( $this, 'prepare_any_field_for_export' ) );
		$this->add_filter('acf/clone_field', 				array( $this, 'clone_any_field' ), 10, 2 );
		$this->add_filter('acf/validate_field',					array( $this, 'validate_any_field' ) );
		
		
		// field filters
		$this->add_field_filter('acf/get_sub_field', 			array($this, 'get_sub_field'), 10, 3);
		$this->add_field_filter('acf/prepare_field_for_export', array($this, 'prepare_field_for_export'));
		$this->add_field_filter('acf/prepare_field_for_import', array($this, 'prepare_field_for_import'));
		
	}
	
	
	/*
	*  input_admin_enqueue_scripts
	*
	*  description
	*
	*  @type	function
	*  @date	16/12/2020
	*  @since	5.3.2
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function input_admin_enqueue_scripts() {
		
		// localize
		acf_localize_text(array(
			
			// identifiers
		   	'layout'													=> __('attribute', FEA_NS),
			'layouts'													=> __('attributes', FEA_NS),
			
			// min / max
			'This field requires at least {min} {label} {identifier}'	=> __('This field requires at least {min} {label} {identifier}', FEA_NS),
			'This field has a limit of {max} {label} {identifier}'		=> __('This field has a limit of {max} {label} {identifier}', FEA_NS),
			
			// popup badge
			'{available} {label} {identifier} available (max {max})'	=> __('{available} {label} {identifier} available (max {max})', FEA_NS),
			'{required} {label} {identifier} required (min {min})'		=> __('{required} {label} {identifier} required (min {min})', FEA_NS),
			
			// field settings
			'Flexible Content requires at least 1 attribute'				=> __('Flexible Content requires at least 1 attribute', FEA_NS)
	   	));
	}
	
	function ajax_save_attributes(){
		$args = acf_parse_args( $_POST, array(
			'_acf_product'	 	=> 0,
			'_acf_form' 		=> '',
			'attributes' 		=> '',
			'variations' 		=> '',
			'product_types'		=> '',
		));
				
		// validate
		if( !acf_verify_nonce('fea_form') ) wp_send_json_error( __( 'Invalid Nonce', FEA_NS ) );		
		
		$field = acf_get_field( $args['attributes'] );

		if( ! $field ){
			wp_send_json_error( __( 'Invalid Field', FEA_NS ) );
		}

		$product_info = $args['acff']['woo_product'];

		if( $args['_acf_product'] == 'add_product' ){
			$type = $product_info[$product_info['types']];
			$classname = WC_Product_Factory::get_classname_from_product_type( $type );
			$product = new $classname();
			$product->set_status( 'auto-draft' );			
			$product_id = $product->save();
			$auto_draft = true;
		}else{
			$product_id = wp_kses( $args['_acf_product'], 'strip' );
			$auto_draft = false;
		}

		$this->update_value( $product_info[$field['key']], $product_id, $field );
		$form = json_decode( acf_decrypt( $args['_acf_form'] ), true );
		$form['product_id'] = $product_id;
		$form['save_to_product'] = 'edit_product';
		$GLOBALS['admin_form'] = $form; 
		$GLOBALS['form_fields'] = array( 'product_types' => $args['product_types'] ); 

		$return = array( 'product_id' => $product_id, 'auto_draft' => $auto_draft, 'form_data' => acf_encrypt( json_encode( $form ) ) );

		if( $args['variations'] ){
			ob_start();
			
			$variations_field = acf_get_field( $args['variations'] );
			$variations_field['prefix'] = 'acff[woo_product]';
			if( !isset( $variations_field['value'] ) || $variations_field['value'] === null ) {
				$variations_field['value'] = acf_get_value( $product_id, $variations_field );
			} 
			fea_instance()->form_display->render_field_wrap( $variations_field );
			$return['variations'] = ob_get_contents();
			ob_end_clean();
		}

		wp_send_json_success( $return );
				
		// failure
		wp_send_json_error();
	}
	
	/*
	*  get_valid_layout
	*
	*  This function will fill in the missing keys to create a valid layout
	*
	*  @type	function
	*  @date	3/10/13
	*  @since	1.1.0
	*
	*  @param	$layout (array)
	*  @return	$layout (array)
	*/
	
	function get_valid_layout( $layout = array() ) {
		
		// parse
		$layout = wp_parse_args($layout, array(
			'key'			=> uniqid('layout_'),
			'name'			=> '',
			'label'			=> '',
			'display'		=> 'block',
			'sub_fields'	=> array(),
			'min'			=> '',
			'max'			=> '',
		));
		
		
		// return
		return $layout;
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


		$field['layouts'] = array(
			'custom_attributes' => array(
				'key' => 'custom_attributes',
				'name' => 'custom_product_attributes',
				'label' => 'Custom Product Attributes',
				'display' => 'block',
				'sub_fields' => array(),
				'min' => '',
				'max' => '',
			),
		);

		$attribute_taxonomies  = wc_get_attribute_taxonomies();

		if ( $attribute_taxonomies ) {
			foreach ( $attribute_taxonomies as $tax ) {
				$field['layouts']['layout_' . $tax->attribute_name ] = array(
					'key' => 'layout_' . $tax->attribute_name,
					'name' => $tax->attribute_name,
					'label' => $tax->attribute_label,
					'display' => 'block',
					'sub_fields' => array(),
					'min' => '',
					'max' => '1',
				);
			}
		}

		
		// loop through layouts, sub fields and swap out the field key with the real field
		foreach( array_keys($field['layouts']) as $i ) {
			
			// extract layout
			$layout = acf_extract_var( $field['layouts'], $i );			
			
			// validate layout
			$layout = $this->get_valid_layout( $layout );			
					
			$layout['sub_fields'] = $this->get_attribute_fields( $i, $field );
														
			// append back to layouts
			$field['layouts'][ $i ] = $layout;
			
		}
		
		
		// return
		return $field;
	}
	
	
	/*
	*  get_sub_field
	*
	*  This function will return a specific sub field
	*
	*  @type	function
	*  @date	29/09/2016
	*  @since	5.4.0
	*
	*  @param	$sub_field 
	*  @param	$selector (string)
	*  @param	$field (array)
	*  @return	$post_id (int)
	*/
	function get_sub_field( $sub_field, $id, $field ) {
		
		// Get active layout.
		$active = get_row_layout();
		
		// Loop over layouts.
		if( $field['layouts'] ) {
			foreach( $field['layouts'] as $layout ) {
				
				// Restict to active layout if within a have_rows() loop.
				if( $active && $active !== $layout['name'] ) {
					continue;
				}
				
				// Check sub fields.
				if( $layout['sub_fields'] ) {
					$sub_field = acf_search_fields( $id, $layout['sub_fields'] );
					if( $sub_field ) {
						break;
					}
				}
			}
		}
				
		// return
		return $sub_field;
	}
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field( $field ) {
	
		// button label
		if( $field['button_label'] === '' ) $field['button_label'] = __('Add Attribute', FEA_NS);
		if( $field['save_text'] === '' ) $field['save_text'] = __('Save Changes', FEA_NS);
		
		
		// sort layouts into names
		$layouts = array();
		
		foreach( $field['layouts'] as $k => $layout ) {
		
			$layouts[ $layout['name'] ] = $layout;
			
		}
		
		
		// vars
		$div = array(
			'class'    => 'acf-frontend-blocks',
			'data-min'	=> $field['min'],
			'data-max'	=> $field['max']
		);
		
		// empty
		if( empty($field['value']) ) {
			$div['class'] .= ' -empty';
		}
		
		
		// no value message
		if( $field['no_value_msg'] == '' ){
			$no_value_message = __('Click the "%s" button below to add attributes to your product',FEA_NS);
			$field['no_value_msg'] = sprintf( $no_value_message, $field['button_label'] );
		}

?>
<div <?php acf_esc_attr_e( $div ); ?>>
	
	<?php acf_hidden_input(array( 'name' => $field['name'] )); ?>
	
	<div class="no-value-message">
		<?php echo $field['no_value_msg']; ?>
	</div>
	
	<?php
		$clones_class = feadmin_edit_mode() && empty($field['value']) ? 'clone_list' : 'clones';
	?>
	<div class="<?php echo $clones_class; ?>">
		<?php foreach( $layouts as $layout ): ?>
			<?php $this->render_layout( $field, $layout, 'acfcloneindex', array() ); ?>
		<?php endforeach; ?>
	</div>
	
	<div class="values">
		<?php 
		if( !empty($field['value']) ): 

			foreach( $field['value'] as $i => $value ):
				
				// validate
				if( empty($layouts[ $value['woo_attrs'] ]) ) continue;
				
				// render
				$this->render_layout( $field, $layouts[ $value['woo_attrs'] ], $i, $value );
				
			endforeach;
			
		endif; ?>
	</div>
	
	<div class="acf-actions">
		<a class="acf-button button button-primary add-attrs" href="#" data-name="add-layout"><?php echo $field['button_label']; ?></a>
		<a class="acf-button button button-primary save-changes" href="#" data-name="save-changes"><?php echo $field['save_text']; ?></a>
	</div>
	
	<script type="text-html" class="tmpl-popup"><?php 
		?><ul><?php foreach( $layouts as $layout ): 
			
			$atts = array(
				'href'			=> '#',
				'data-layout'	=> $layout['name'],
				'data-min' 		=> $layout['min'],
				'data-max' 		=> $layout['max'],
			);
			
			?><li><a <?php acf_esc_attr_e( $atts ); ?>><?php echo $layout['label']; ?></a></li><?php 
		
		endforeach; ?></ul>
	</script>
	
</div>
<?php
		
	}
		
	function get_attribute_fields( $layout, $field ){
		$settings = $field['fields_settings'];
		
		$common_settings = array(
			'ID' => 0,
			'prefix' => 'acf',
			'parent' => $field['key'],
		);

		$sub_fields = [];
			if( $layout == 'custom_attributes' ){
				$sub_fields = array(
					 array_merge( array(
						'field_label_hide' => $settings['name']['field_label_hide'],
						'label' => $settings['name']['label'],
						'name' => 'name',
						'_name' => 'name',
						'parent_layout' => $layout,
						'instructions' => $settings['name']['instructions'],
						'key' => 'name',
						'type' => 'text',
						'required' => 1,
						'start_column' => '25',
						'wrapper' => [
							'width' => '',
							'class' => 'pa-custom-name',
							'id' => '',
						],
						'default_value' => '',
						'placeholder' => $settings['name']['placeholder'],
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					), $common_settings ),
					array_merge( array(
						'field_label_hide' => $settings['locations']['field_label_hide'],
						'label' => $settings['locations']['label'],
						'name' => 'locations',
						'_name' => 'locations',
						'key' => 'locations',
						'_name' => 'name',
						'parent_layout' => $layout,
						'type' => 'checkbox',
						'instructions' => $settings['locations']['instructions'],
						'required' => 0,
						'wrapper' => [
							'width' => '',
							'class' => '',
							'id' => '',
						],
						'choices' => $settings['locations']['choices'],
						'allow_custom' => 0,
						'default_value' => array(
						),
						'layout' => 'vertical',
						'toggle' => 0,
						'return_format' => 'value',
						'save_custom' => 0,
					), $common_settings ),
					array_merge( array(
						'field_label_hide' => $settings['custom_terms']['field_label_hide'],
						'label' => $settings['custom_terms']['label'],
						'name' => 'terms',
						'_name' => 'terms',
						'key' => 'terms',
						'parent_layout' => $layout,
						'type' => 'custom_terms',
						'choices' => array(),
						'instructions' => $settings['custom_terms']['instructions'],
						'required' => 0,
						'end_column' => 1,
						'wrapper' => [
							'width' => '75',
							'class' => '',
							'id' => '',
						],
						'allow_custom' => 1,
						'default_value' => array(),
						'layout' => 'horizontal',
						'toggle' => 0,
						'return_format' => 'value',
						'save_custom' => 0,
					), $common_settings ),
				);
			}else{
				$taxonomy = explode( 'layout_', $layout )[1];
				$sub_fields = array(
					array_merge( array(
						'field_label_hide' => $settings['locations']['field_label_hide'],
						'label' => $settings['locations']['label'],
						'name' => 'locations',
						'key' => $taxonomy . '_locations',
						'type' => 'checkbox',
						'instructions' => $settings['locations']['instructions'],
						'_name' => 'locations',
						'parent_layout' => $layout,
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '25',
							'class' => '',
							'id' => '',
						),
						'choices' => $settings['locations']['choices'],
						'allow_custom' => 0,
						'default_value' => array(
						),
						'layout' => 'vertical',
						'toggle' => 0,
						'return_format' => 'value',
						'save_custom' => 0,
					), $common_settings ),
					array_merge( array(
						'field_label_hide' => $settings['terms']['field_label_hide'],
						'label' => $settings['terms']['label'],
						'name' => 'terms',
						'_name' => 'terms',
						'parent_layout' => $layout,
						'key' => $taxonomy . '_terms',
						'type' => 'related_terms',
						'instructions' => $settings['terms']['instructions'],
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '75',
							'class' => '',
							'id' => '',
						),
						'taxonomy' => 'pa_' . $taxonomy,
						'field_type' => 'multi_select',
						'allow_null' => 0,
						'add_term' => 1,
						'save_terms' => 0,
						'load_terms' => 0,
						'return_format' => 'object',
						'multiple' => 0,
					), $common_settings ),
				);
			}
			return $sub_fields;		
		
	} 

/* 
	public function validate_sub_fields() {
		if( ! isset( $_POST['_acf_element_id'] ) ) return;
		$form_id = $_POST['_acf_element_id'];

		if( isset( $_POST['acff']['woo_product'][ 'acfef_' .$form_id. '_attributes' ] ) ){
			$field = acf_get_field( 'acfef_' .$form_id. '_attributes' );

			if( $field ){
				$layouts = $field['layouts'];
				foreach( array_keys( $layouts ) as $i ){
					$sub_fields = $this->get_attribute_fields( $i, $field );
					
					// register field if isset in $_POST
					foreach( $sub_fields as $sub_field ) {
						
						acf_add_local_field($sub_field);
						
					}
				}
			}
		}
		
		
	} */

	/*
	*  render_layout
	*
	*  description
	*
	*  @type	function
	*  @date	19/11/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function render_layout( $field, $layout, $i, $value ) {
		
		// vars
		$order = 0;
		$el = 'div';
		$sub_fields = $layout['sub_fields'];
		$id = ( $i === 'acfcloneindex' ) ? 'acfcloneindex' : "row-$i";
		$prefix = $field['name'] . '[' . $id .  ']';
		
		
		// div
		$div = array(
			'class'			=> 'layout',
			'data-id'		=> $id,
			'data-layout'	=> $layout['name']
		);
		
		
		// clone
		if( is_numeric($i) ) {
			
			$order = $i + 1;
			
		} else {
			
			$div['class'] .= ' acf-clone';
			
		}
		
		
		// display
		if( $layout['display'] == 'table' ) {
			
			$el = 'td';
			
		}
		
		
		// title
		$title = $this->get_layout_title( $field, $layout, $i, $value );
		
		
		// remove row
		reset_rows();
		
?>
<div <?php echo acf_esc_attr($div); ?>>
			
	<?php acf_hidden_input(array( 'name' => $prefix.'[woo_attrs]', 'value' => $layout['name'] )); ?>
	
	<div class="acf-frontend-blocks-layout-handle" title="<?php _e('Drag to reorder',FEA_NS); ?>" data-name="collapse-layout"><?php echo $title; ?></div>
	
	<div class="acf-frontend-blocks-layout-controls">
		<a class="acf-icon -plus small light acf-js-tooltip" href="#" data-name="add-layout" title="<?php _e('Add attribute',FEA_NS); ?>"></a>
		<?php if( $layout['name'] == 'custom_product_attributes' ):?>
			<a class="acf-icon -duplicate small light acf-js-tooltip" href="#" data-name="duplicate-layout" title="<?php _e('Duplicate attribute',FEA_NS); ?>"></a>
		<?php endif; ?> 
		<a class="acf-icon -minus small light acf-js-tooltip" href="#" data-name="remove-layout" title="<?php _e('Remove attribute',FEA_NS); ?>"></a>
		<a class="acf-icon -collapse small -clear acf-js-tooltip" href="#" data-name="collapse-layout" title="<?php _e('Click to toggle',FEA_NS); ?>"></a>
	</div>
	
<?php if( !empty($sub_fields) ): ?>
	
	<?php if( $layout['display'] == 'table' ): ?>
	<table class="acf-table">
		
		<thead>
			<tr>
				<?php foreach( $sub_fields as $sub_field ): 					

					// prepare field (allow sub fields to be removed)
					$sub_field = acf_prepare_field($sub_field);
					
					
					// bail ealry if no field
					if( !$sub_field ) continue;
					
					
					// vars
					$atts = array();
					$atts['class'] = 'acf-th';
					$atts['data-name'] = $sub_field['_name'];
					$atts['data-type'] = $sub_field['type'];
					$atts['data-key'] = $sub_field['key'];
					
					
					// Add custom width
					if( $sub_field['wrapper']['width'] ) {
					
						$atts['data-width'] = $sub_field['wrapper']['width'];
						$atts['style'] = 'width: ' . $sub_field['wrapper']['width'] . '%;';
						
					}
					
					?>
					<th <?php echo acf_esc_attr( $atts ); ?>>
						<?php echo acf_get_field_label( $sub_field ); ?>
						<?php if( $sub_field['instructions'] ): ?>
							<p class="description"><?php echo $sub_field['instructions']; ?></p>
						<?php endif; ?>
					</th>
					
				<?php endforeach; ?> 
			</tr>
		</thead>
		
		<tbody>
			<tr class="acf-row">
	<?php else: ?>
	<div class="acf-fields <?php if($layout['display'] == 'row'): ?>-left<?php endif; ?>">
	<?php endif; ?>
	
		<?php
			
		// loop though sub fields
		foreach( $sub_fields as $sub_field ) {		

			// add value
			if( isset($value[ $sub_field['key'] ]) ) {
				
				// this is a normal value
				$sub_field['value'] = $value[ $sub_field['key'] ];
				
			} elseif( isset($sub_field['default_value']) ) {
				
				// no value, but this sub field has a default value
				$sub_field['value'] = $sub_field['default_value'];
				
			}
			
			
			// update prefix to allow for nested values
			$sub_field['prefix'] = $prefix;
			
			
			// render input
			fea_instance()->form_display->render_field_wrap( $sub_field, $el );
		
		}
		
		?>
			
	<?php if( $layout['display'] == 'table' ): ?>
			</tr>
		</tbody>
	</table>
	<?php else: ?>
	</div>
	<?php endif; ?>

<?php endif; ?>

</div>
<?php
		
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
		
		fea_instance()->form_display->render_field_setting( $field, array(
			'label' => __( 'Fields', FEA_NS ),
			'name' => 'fields_settings',
			'type' => 'list_items',
			'show_add' => false,
			'collapsed'	=> 'label',
			'show_remove' => false,
			'show_order' => 'ids',
			'maintain_order' => 1,
			'save_names' => 1,
			'layout' => 'block',
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
					'type' => 'text',
					'wrapper' => array(
						'width' => 70,
					),
				),
				array(
					'key' => 'field_label_hide',
					'name' => 'field_label_hide',
					'label' => __( 'Hide Field Label', FEA_NS ),
					'type' => 'true_false',
					'ui' => 1,
					'wrapper' => array(
						'width' => 30,
					),
				),
				array(
					'key' => 'placeholder',
					'name' => 'placeholder',
					'label' => __( 'Placeholder', FEA_NS ),
					'type' => 'text',
					'row_conditions' => array(
						'name' => 'name', 
					),
				),  
				array(
					'key' => 'choices',
					'name' => 'choices',
					'label' => __( 'Options', FEA_NS ),
					'type' => 'group',
					'row_conditions' => array(
						'name' => 'locations', 
					),
					'sub_fields' => array(
						array(
							'key' => 'products_page',
							'name' => 'products_page',
							'label' => __( 'Visible on the product page', FEA_NS ),
							'type' => 'text',
							'wrapper' => array(
								'width' => '50',
							),
						),  
						array(
							'key' => 'for_variations',
							'name' => 'for_variations',
							'label' => __( 'Used for variations', FEA_NS ),
							'type' => 'text',
							'wrapper' => array(
								'width' => '50',
							),
						),  
					),
				),  
				array(
					'key' => 'instructions',
					'name' => 'instructions',
					'label' => __( 'Instructions', FEA_NS ),
					'type' => 'textarea',
				),  
			),
		) );
		
		
		// min
		acf_render_field_setting( $field, array(
			'label'			=> __(' Add Button Label',FEA_NS),
			'instructions'	=> '',
			'type'			=> 'text',
			'name'			=> 'button_label',
		));

		acf_render_field_setting( $field, array(
			'label'			=> __('Save Button Label',FEA_NS),
			'instructions'	=> '',
			'type'			=> 'text',
			'name'			=> 'save_text',
		));		
		
		// min
		acf_render_field_setting( $field, array(
			'label'			=> __('Minimum Attributes',FEA_NS),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'min',
		));
		
		
		// max
		acf_render_field_setting( $field, array(
			'label'			=> __('Maximum Attributes',FEA_NS),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'max',
		));
				
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

			foreach( $product->get_attributes() as $attr_name => $attr ){
				$tax = false;

				$locations = array();
				if( $attr['visible'] ){
					$locations[] = 'products_page'; 
				}
				if( $attr['variation'] ){
					$locations[] = 'for_variations';
				}

				if( get_taxonomy( $attr_name ) ) $tax = true; 
				if( $tax ){
					$layout = explode( 'pa_', $attr['name'] )[1];
					$rows[$i]['woo_attrs'] = $layout;
					$rows[$i][$layout. '_terms'] = $attr['options'];					
					$rows[$i][$layout. '_locations'] = $locations;					
				}else{
					$rows[$i]['woo_attrs'] = 'custom_product_attributes';
					$rows[$i]['name'] = $attr['name'];					
					$rows[$i]['terms'] = $attr['options'];		
					$rows[$i]['locations'] = $locations;						
				}

				$i++;
			}
		} 

		// return
		return $rows;
		
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/
	
	function format_value( $value, $post_id, $field ) {
		
		// bail early if no value
		if( empty($value) || empty($field['layouts']) ) {
			
			return false;
			
		}
		
		
		// sort layouts into names
		$layouts = array();
		foreach( $field['layouts'] as $k => $layout ) {
		
			$layouts[ $layout['name'] ] = $layout['sub_fields'];
			
		}
		
		
		// loop over rows
		foreach( array_keys($value) as $i ) {
			
			// get layout name
			$l = $value[ $i ]['woo_attrs'];
			
			
			// bail early if layout deosnt exist
			if( empty($layouts[ $l ]) ) continue;
			
			
			// get layout
			$layout = $layouts[ $l ];
			
			
			// loop through sub fields
			foreach( array_keys($layout) as $j ) {
				
				// get sub field
				$sub_field = $layout[ $j ];
				
				
				// bail ealry if no name (tab)
				if( acf_is_empty($sub_field['name']) ) continue;
				
				
				// extract value
				$sub_value = acf_extract_var( $value[ $i ], $sub_field['key'] );
				
				
				// update $sub_field name
				$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";
					
				
				// format value
				$sub_value = acf_format_value( $sub_value, $post_id, $sub_field );
				
				
				// append to $row
				$value[ $i ][ $sub_field['_name'] ] = $sub_value;
				
			}
			
		}
		
		
		// return
		return $value;
	}
	
	
	/*
	*  validate_value
	*
	*  description
	*
	*  @type	function
	*  @date	11/02/2020
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function validate_value( $valid, $value, $field, $input ){
		
		// vars
		$count = 0;
		
		
		// check if is value (may be empty string)
		if( is_array($value) ) {
			
			// remove acfcloneindex
			if( isset($value['acfcloneindex']) ) {
				unset($value['acfcloneindex']);
			}
			
			// count
			$count = count($value);
		}
		
		
		// validate required
		if( $field['required'] && !$count ) {
			$valid = false;
		}
		
		
		// validate min
		$min = (int) $field['min'];
		if( $min && $count < $min ) {
			
			// vars
			$error = __('This field requires at least {min} {label} {identifier}', FEA_NS);
			$identifier = _n('layout', 'layouts', $min);
			
 			// replace
 			$error = str_replace('{min}', $min, $error);
 			$error = str_replace('{label}', '', $error);
 			$error = str_replace('{identifier}', $identifier, $error);
 			
 			// return
			return $error;
		}
		
		
		// find layouts
		$layouts = array();
		foreach( array_keys($field['layouts']) as $i ) {
			
			// vars
			$layout = $field['layouts'][ $i ];
			
			// add count
			$layout['count'] = 0;
			
			// append
			$layouts[ $layout['name'] ] = $layout;
		}
		
		
		// validate value
		if( $count ) {
			
			// loop rows
			foreach( $value as $i => $row ) {	
				
				// get layout
				$l = $row['woo_attrs'];
				
				// bail if layout doesn't exist
				if( !isset($layouts[ $l ]) ) {
					continue;
				}
				
				// increase count
				$layouts[ $l ]['count']++;
				
				// bail if no sub fields
				if( empty($layouts[ $l ]['sub_fields']) ) {
					continue;
				}
				
				// loop sub fields
				foreach( $layouts[ $l ]['sub_fields'] as $sub_field ) {
					
					// get sub field key
					$k = $sub_field['key'];
					
					// bail if no value
					if( !isset($value[ $i ][ $k ]) ) {
						continue;
					}
					
					// validate
					acf_validate_value( $value[ $i ][ $k ], $sub_field, "{$input}[{$i}][{$k}]" );
				}
				// end loop sub fields
				
			}
			// end loop rows
		}
		
		
		// validate layouts
		foreach( $layouts as $layout ) {
			
			// validate min / max
			$min = (int) $layout['min'];
			$count = $layout['count'];
			$label = $layout['label'];
			
			if( $min && $count < $min ) {
				
				// vars
				$error = __('This field requires at least {min} {label} {identifier}', FEA_NS);
				$identifier = _n('layout', 'layouts', $min);
				
	 			// replace
	 			$error = str_replace('{min}', $min, $error);
	 			$error = str_replace('{label}', '"' . $label . '"', $error);
	 			$error = str_replace('{identifier}', $identifier, $error);
	 			
	 			// return
				return $error;				
			}
		}
		
		
		// return
		return $valid;
	}
	
	
	/*
	*  get_layout
	*
	*  This function will return a specific layout by name from a field
	*
	*  @type	function
	*  @date	15/2/17
	*  @since	5.5.8
	*
	*  @param	$name (string)
	*  @param	$field (array)
	*  @return	(array)
	*/
	
	function get_layout( $name, $field ) {
		
		// bail early if no layouts
		if( !isset($field['layouts']) ) return false;
		
		
		// loop
		foreach( $field['layouts'] as $layout ) {
			
			// match
			if( $layout['name'] === $name ) return $layout;
			
		}
		
		
		// return
		return false;
		
	}
	
	
	/*
	*  delete_row
	*
	*  This function will delete a value row
	*
	*  @type	function
	*  @date	15/2/17
	*  @since	5.5.8
	*
	*  @param	$i (int)
	*  @param	$field (array)
	*  @param	$post_id (mixed)
	*  @return	(boolean)
	*/
	
	function delete_row( $i, $field, $post_id ) {
		
		// vars
		$value = acf_get_metadata( $post_id, $field['name'] );
		
		
		// bail early if no value
		if( !is_array($value) || !isset($value[ $i ]) ) return false;
		
		
		// get layout
		$layout = $this->get_layout($value[ $i ], $field);
		
		
		// bail early if no layout
		if( !$layout || empty($layout['sub_fields']) ) return false;
		
		
		// loop
		foreach( $layout['sub_fields'] as $sub_field ) {
			
			// modify name for delete
			$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";
			
			
			// delete value
			acf_delete_value( $post_id, $sub_field );
			
		}
		
		
		// return
		return true;
		
	}
	
	
	/*
	*  update_row
	*
	*  This function will update a value row
	*
	*  @type	function
	*  @date	15/2/17
	*  @since	5.5.8
	*
	*  @param	$i (int)
	*  @param	$field (array)
	*  @param	$post_id (mixed)
	*  @return	(boolean)
	*/
	
	function update_row( $row, $i, $field, $post_id ) {
		
		// bail early if no layout reference
		if( !is_array($row) || !isset($row['woo_attrs']) ) return false;
		
		
		// get layout
		$layout = $this->get_layout($row['woo_attrs'], $field);
		
		
		// bail early if no layout
		if( !$layout || empty($layout['sub_fields']) ) return false;
		
		
		// loop
		foreach( $layout['sub_fields'] as $sub_field ) {
			
			// value
			$value = null;
			

			// find value (key)
			if( isset($row[ $sub_field['key'] ]) ) {
				
				$value = $row[ $sub_field['key'] ];
			
			// find value (name)	
			} elseif( isset($row[ $sub_field['name'] ]) ) {
				
				$value = $row[ $sub_field['name'] ];
				
			// value does not exist	
			} else {
				
				continue;
				
			}
			
			
			// modify name for save
			$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";
								
			
			// update field
			acf_update_value( $value, $post_id, $sub_field );
				
		}
		
		// return
		return true;
		
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
		$attrs = array();

		// bail early if no layouts
		if( empty($field['layouts']) ) return $value;
				
		// update
		if( !empty($value) ) {
			
			$i = 0;
			
			// remove acfcloneindex
			if( isset($value['acfcloneindex']) ) {
			
				unset($value['acfcloneindex']);
				
			}
			
			
			// loop through rows
			foreach( $value as $row ) {
				$attribute = new WC_Product_Attribute();

				$locations = array();
				if( $row['woo_attrs'] == 'custom_product_attributes' ) {
					$attr_name = $row['name'];
					$attr_id = 0;
					$attr_options = $row['terms'];
					$locations = $row['locations'];
				}else{
					$attr_name = $row['woo_attrs'];
					$attr_id = wc_attribute_taxonomy_id_by_name( 'pa_' .$attr_name );
					$attr_options = $row[$attr_name.'_terms'];
					if( is_array( $attr_options ) )	$attr_options = array_map('intval', $attr_options );
					$locations = $row[$attr_name.'_locations'];
				}

				if( $locations ){
					if( in_array( 'for_variations', $locations ) ){
						$attribute->set_variation( 1 );
					}
					if( in_array( 'products_page', $locations ) ){
						$attribute->set_visible( 1 );
					}
				}
				$attribute->set_position( $i );
				$attribute->set_name( 'pa_' .$attr_name );
				$attribute->set_id( $attr_id );

				if( !empty( $attr_options ) ) $attribute->set_options( $attr_options );
				
				$attrs[] = $attribute;

				$i++;
			}

		}
		$product = wc_get_product( $post_id );

		if( $product ){
			$product->set_attributes( $attrs );
			$product->save();
		}		
		
		// return
		return;
		
	}
	
	/*
	*  delete_value
	*
	*  description
	*
	*  @type	function
	*  @date	1/07/2020
	*  @since	5.2.3
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
		

	function delete_value( $post_id, $key, $field ) {
		
		// vars
		$old_value = acf_get_metadata( $post_id, $field['name'] );
		$old_value = is_array($old_value) ? $old_value : array();
		
		
		// bail early if no rows or no sub fields
		if( empty($old_value) ) return;
				
		
		// loop
		foreach( array_keys($old_value) as $i ) {
				
			$this->delete_row( $i, $field, $post_id );
			
		}
			
	}
	
	
	/*
	*  update_field()
	*
	*  This filter is appied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the field group ID (post_type = acf)
	*
	*  @return	$field - the modified field
	*/

	function update_field( $field ) {
		
		// loop
		if( !empty($field['layouts']) ) {
			
			foreach( $field['layouts'] as &$layout ) {
		
				unset($layout['sub_fields']);
				
			}
			
		}
				
		// return		
		return $field;
	}
	
	
	/*
	*  delete_field
	*
	*  description
	*
	*  @type	function
	*  @date	4/04/2020
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function delete_field( $field ) {
		
		if( !empty($field['layouts']) ) {
			
			// loop through layouts
			foreach( $field['layouts'] as $layout ) {
				
				// loop through sub fields
				if( !empty($layout['sub_fields']) ) {
				
					foreach( $layout['sub_fields'] as $sub_field ) {
					
						acf_delete_field( $sub_field['ID'] );
						
					}
					// foreach
					
				}
				// if
				
			}
			// foreach
			
		}
		// if
		
	}
	
	
	/*
	*  duplicate_field()
	*
	*  This filter is appied to the $field before it is duplicated and saved to the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$field - the modified field
	*/
	
	function duplicate_field( $field ) {
		
		// vars
		$sub_fields = array();
		
		
		if( !empty($field['layouts']) ) {
			
			// loop through layouts
			foreach( $field['layouts'] as $layout ) {
				
				// extract sub fields
				$extra = acf_extract_var( $layout, 'sub_fields' );
				
				
				// merge
				if( !empty($extra) ) {
					
					$sub_fields = array_merge($sub_fields, $extra);
					
				}
				
			}
			// foreach
			
		}
		// if
		
		
		// save field to get ID
		$field = acf_update_field( $field );
		
		
		// duplicate sub fields
		acf_duplicate_fields( $sub_fields, $field['ID'] );
		
		
		// return		
		return $field;
		
	}
	
	
	
	function get_layout_title( $field, $layout, $i, $value ) {
		
		// vars
		$rows = array();
		$rows[ $i ] = $value;
		
		
		// add loop
		acf_add_loop(array(
			'selector'	=> $field['name'],
			'name'		=> $field['name'],
			'value'		=> $rows,
			'field'		=> $field,
			'i'			=> $i,
			'post_id'	=> 0,
		));
		
		
		// vars
		$title = $layout['label'];
		
		if( $layout['key'] == 'custom_attributes' ){
			$title = '<span class="attr_name">' .get_sub_field('name'). '</span>'; 
		}
		
		// remove loop
		acf_remove_loop();
		
		
		// prepend order
		$order = is_numeric($i) ? $i+1 : 0;
		$title = '<span class="acf-frontend-blocks-layout-order">' . $order . '</span>' . $title;
		
		
		// return
		return $title;
		
	}
	
	
	/*
	*  clone_any_field
	*
	*  This function will update clone field settings based on the origional field
	*
	*  @type	function
	*  @date	28/06/2016
	*  @since	5.3.8
	*
	*  @param	$clone (array)
	*  @param	$field (array)
	*  @return	$clone
	*/
	
	function clone_any_field( $field, $clone_field ) {
		
		// remove parent_layout
		// - allows a sub field to be rendered as a normal field
		unset($field['parent_layout']);
		
		
		// attempt to merger parent_layout
		if( isset($clone_field['parent_layout']) ) {
			
			$field['parent_layout'] = $clone_field['parent_layout'];
			
		}
		
		
		// return
		return $field;
		
	}
	
	
	/*
	*  prepare_field_for_export
	*
	*  description
	*
	*  @type	function
	*  @date	11/03/2020
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function prepare_field_for_export( $field ) {
		
		// loop
		if( !empty($field['layouts']) ) {
			
			foreach( $field['layouts'] as &$layout ) {
		
				$layout['sub_fields'] = acf_prepare_fields_for_export( $layout['sub_fields'] );
				
			}
			
		}
		
		
		// return
		return $field;
		
	}
	
	function prepare_any_field_for_export( $field ) {
		
		// remove parent_layout
		unset( $field['parent_layout'] );
		
		
		// return
		return $field;
		
	}
	
	
	/*
	*  prepare_field_for_import
	*
	*  description
	*
	*  @type	function
	*  @date	11/03/2020
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function prepare_field_for_import( $field ) {
		
		// Bail early if no layouts
		if( empty($field['layouts']) ) {
			return $field;
		}
		
		// Storage for extracted fields.
		$extra = array();
		
		// Loop over layouts.
		foreach( $field['layouts'] as &$layout ) {
			
			// Ensure layout is valid.
			$layout = $this->get_valid_layout( $layout );
			
			// Extract sub fields.
			$sub_fields = acf_extract_var( $layout, 'sub_fields' );
			
			// Modify and append sub fields to $extra.
			if( $sub_fields ) {
				foreach( $sub_fields as $i => $sub_field ) {					

					// Update atttibutes
					$sub_field['parent'] = $field['key'];
					$sub_field['parent_layout'] = $layout['key'];
					$sub_field['menu_order'] = $i;
					
					// Append to extra.
					$extra[] = $sub_field;
				}
			}
		}
		
		// Merge extra sub fields.
		if( $extra ) {
			array_unshift($extra, $field);
			return $extra;
		}
		
		// Return field.
		return $field;
	}
	
	
	/*
	*  validate_any_field
	*
	*  This function will add compatibility for the 'column_width' setting
	*
	*  @type	function
	*  @date	30/1/17
	*  @since	5.5.6
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function validate_any_field( $field ) {
		
		// width has changed
		if( isset($field['column_width']) ) {
			
			$field['wrapper']['width'] = acf_extract_var($field, 'column_width');
			
		}
		
		
		// return
		return $field;
		
	}
	
	
	/*
	*  translate_field
	*
	*  This function will translate field settings
	*
	*  @type	function
	*  @date	8/03/2016
	*  @since	5.3.2
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function translate_field( $field ) {
		
		// translate
		$field['button_label'] = acf_translate( $field['button_label'] );
		
		
		// loop
		if( !empty($field['layouts']) ) {
			
			foreach( $field['layouts'] as &$layout ) {
		
				$layout['label'] = acf_translate($layout['label']);
				
			}
			
		}
		
		
		// return
		return $field;
		
	}
	
}



// initialize
acf_register_field_type( 'acf_field_product_attributes' );

endif; // class_exists check


?>
