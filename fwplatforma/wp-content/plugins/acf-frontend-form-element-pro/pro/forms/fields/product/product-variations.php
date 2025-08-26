<?php

if( ! class_exists('acf_field_product_variations') ) :

class acf_field_product_variations extends acf_field {
	
	
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
		
		$variable_fields = array( 
			'description','image','price','sale_price','sku','stock_status','manage_stock','stock_quantity','allow_backorders'
		);
		$default_fields_settings = array();
		foreach( $variable_fields as $field_type ){
			$field_label = ucwords( str_replace( "_"," ",$field_type ) );
			$default_fields_settings[] = [
				'type' => $field_type,
				'field_label_on' => 'true',
				'required' => '',
				'label' => __( $field_label, FEA_NS ),
				'instructions' => '',
			];
		}

		// vars
		$this->name = 'product_variations';
		$this->label = __("Product Variations",FEA_NS);
		$this->category = __( 'Product Attributes', FEA_NS );
		$this->defaults = array(
			'min'			=> 0,
			'max'			=> 0,
			'layout' 		=> 'block',
			'button_label'	=> __( 'Add Variation', FEA_NS ),
			'save_text'	    => __( 'Save Changes', FEA_NS ),
			'no_value_msg'  => '',
			'no_attrs_msg'  => '',
			'collapsed'		=> '',
			'sub_fields' 	=> array(),
			'fields_settings' => $default_fields_settings,
		);		

		add_action( 'wp_ajax_frontend_admin/fields/variations/add_variation', array( $this, 'ajax_add_variation' ) );
		add_action( 'wp_ajax_nopriv_frontend_admin/fields/variations/add_variation', array( $this, 'ajax_add_variation' ) );	
		add_action( 'wp_ajax_frontend_admin/fields/variations/save_variations', array( $this, 'ajax_save_variations' ) );
		add_action( 'wp_ajax_nopriv_frontend_admin/fields/variations/save_variations', array( $this, 'ajax_save_variations' ) );	
		add_action( 'wp_ajax_frontend_admin/fields/variations/remove_variation', array( $this, 'ajax_remove_variation' ) );
		add_action( 'wp_ajax_nopriv_frontend_admin/fields/variations/remove_variation', array( $this, 'ajax_remove_variation' ) );	
		
		// field filters
		$this->add_field_filter('acf/prepare_field_for_export', array($this, 'prepare_field_for_export'));
		$this->add_field_filter('acf/prepare_field_for_import', array($this, 'prepare_field_for_import'));

		// filters
		//$this->add_filter('acf/validate_field',	array($this, 'validate_any_field'));
		
	}
	
	
	/*
	*  input_admin_enqueue_scripts
	*
	*  description
	*
	*  @type	function
	*  @date	16/12/2015
	*  @since	5.3.2
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function input_admin_enqueue_scripts() {
		
		// localize
		acf_localize_text(array(
		   	'Minimum rows reached ({min} rows)'	=> __('Minimum rows reached ({min} rows)', FEA_NS),
			'Maximum rows reached ({max} rows)'	=> __('Maximum rows reached ({max} rows)', FEA_NS),
	   	));
	}

	function ajax_save_variations(){
		$args = acf_parse_args( $_POST, array(
			'_acf_product'	 	=> 0,
			'variations' 		=> '',
		));
				
		// validate
		if( !acf_verify_nonce('fea_form') ) wp_send_json_error( __( 'Invalid Nonce', FEA_NS ) );	
		
		$field = acf_get_field( $args['variations'] );

		if( ! $field ){
			wp_send_json_error( __( 'Invalid Field', FEA_NS ) );
		}

		$product_id = wp_kses( $args['_acf_product'], 'strip' );
		$product = wc_get_product( $product_id );
		if( $product && $product->get_type() != 'variable' ){
			$classname = WC_Product_Factory::get_product_classname( $product_id, 'variable' );
			$product = new $classname( $product_id );
			$product->save();
		}		

		$this->update_value( $args['acff']['woo_product'][$field['key']], $product_id, $field );
	
		wp_send_json_success( array( 'product_id' => $product_id ) );
				
		// failure
		wp_send_json_error();
	}
	
	function ajax_add_variation(){
		$args = acf_parse_args( $_POST, array(
			'parent_id' 	=> 0,
			'field_key'		=> '',
			'nonce'			=> '',
		));
				
		// validate
		if( ! wp_verify_nonce($args['nonce'], 'acf_nonce') ) {
		
			wp_send_json_error( __( 'Invalid Nonce', FEA_NS ) );
			
		}

		$product_id = $args['parent_id'];
		$product = wc_get_product( $product_id );
		if( $product && $product->get_type() != 'variable' ){
			$classname = WC_Product_Factory::get_product_classname( $product_id, 'variable' );
			$product = new $classname( $product_id );
			$product->save();
		}		

		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $product_id );
		$variation_id = $variation->save();

		// success
		if( $variation_id ) {
			$product->save();
			wp_send_json_success( array( 'variation_id' => $variation_id ) );			
		}
				
		// failure
		wp_send_json_error();
	}

	function ajax_remove_variation(){
		$args = acf_parse_args( $_POST, array(
			'variation_id' 	=> 0,
			'field_key'		=> '',
			'nonce'			=> '',
		));
				
		// validate
		if( ! wp_verify_nonce($args['nonce'], 'acf_nonce') ) {
		
			wp_send_json_error();
			
		}
		
		if ( 'product_variation' === get_post_type( $args['variation_id'] ) ) {
			$variation = wc_get_product( $args['variation_id'] );
			$variation->delete( true );
			wp_send_json_success();
		}

		// failure
		wp_send_json_error();
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
		
		// min/max
		$field['min'] = (int) $field['min'];
		$field['max'] = (int) $field['max'];
		
		$field['sub_fields'] = $this->get_variation_fields( $field );
		// return
		return $field;

	}

	function get_variation_fields( $field ){
		if( empty( $field['fields_settings'] ) ){
			$field['fields_settings'] = $this->defaults['fields_settings'];
		}


		$sub_fields = array(
			array(
				'variation_field_type' => 'attributes',
				'parent' => $field['key'],
				'type' => 'multiple_selection',
				'key' => 'variable_attributes',
				'name' => 'variable_attributes',
				'_name' => 'variable_attributes',
				'field_label_hide' => 1,
				'label'	=> '',
				'required' => 0,
				'wrapper' => array(
					'class' => '-collapsed-target'
				),
			),
		);

		foreach( $field['fields_settings'] as $setting ){
			if( empty( $setting['type'] ) ){
				if( empty( $setting['field_type'] ) ) continue;
				$field_type = $setting['field_type'];
			}else{
				$field_type = $setting['type'];
			}
			$default_label = ucwords( str_replace( '_', ' ', $field_type ) );
			$field_label = $setting['label'] ? $setting['label'] : $default_label;
			$display_mode = isset( $setting['display_mode'] ) ? $setting['display_mode'] : 'edit'; 

			if( isset( $setting['field_label_on'] ) ){
				$field_label_hide = ! $setting['field_label_on'];
			}else{
				$field_label_hide = $setting['field_label_hide'];
			}

			$valid_sub_field = array(
				'variation_field_type' => $field_type,
				'parent' => $field['key'],
				'key' => 'variable_' .$field_type,
				'name' => 'variable_' .$field_type,
				'_name' => 'variable_' .$field_type,
				'field_label_hide' => $field_label_hide,
				'label' => $field_label,
				'frontend_admin_display_mode' => $display_mode,
				'instructions' => $setting['instructions'],
				'required' => $setting['required'],
			);

			switch( $field_type ){
				case 'description':
					$valid_sub_field['type'] = 'textarea';
					$valid_sub_field['placeholder'] = $setting['placeholder'];
					$valid_sub_field['maxlength'] = '';
				break;
				case 'image':
					$valid_sub_field['type'] = 'image';

				break;
				case 'price':
				case 'sale_price':	
					$valid_sub_field['type'] = 'number';
					$valid_sub_field['min'] = isset( $setting['minimum'] ) ? $setting['minimum'] : 0;
					$valid_sub_field['max'] = isset( $setting['maximum'] ) ? $setting['maximum'] : 0;
				break;
				case 'sku':
					$valid_sub_field['type'] = 'text';
					$valid_sub_field['placeholder'] = $setting['placeholder'];
					$valid_sub_field['maxlength'] = '';
				break;
				case 'manage_stock':
					$valid_sub_field['type'] = 'true_false';
					$valid_sub_field['ui'] = 1;
					$valid_sub_field['ui_on_text'] = isset( $setting['ui_on'] ) ? $setting['ui_on'] : 'Yes';
					$valid_sub_field['ui_off_text'] = isset( $setting['ui_off'] ) ? $setting['ui_off'] : 'No';
				break;
				case 'stock_quantity':
					$valid_sub_field['type'] = 'number';
					$valid_sub_field['custom_stock_quantity'] = true;
					$valid_sub_field['min'] = '';
					$valid_sub_field['max'] = '';
					$valid_sub_field['conditional_logic'] = [
						[
							[
								'field' => 'variable_manage_stock',
								'operator' => '==',
								'value' => '1',
							]
						]
					];
				break;			
				case 'allow_backorders':
					$valid_sub_field['type'] = 'select';
					$valid_sub_field['choices'] = array(
						'no' => isset( $setting['do_not_allow'] ) ? $setting['do_not_allow'] :__( 'Do not allow', 'woocommerce' ),
						'notify' => isset( $setting['notify'] ) ? $setting['notify'] : __( 'Notify', 'woocommerce' ),
						'yes' => isset( $setting['allow'] ) ? $setting['allow'] : __( 'Allow', 'woocommerce' ),
					);
					$valid_sub_field['custom_backorders'] = true;
					$valid_sub_field['conditional_logic'] = [
						[
							[
								'field' => 'variable_manage_stock',
								'operator' => '==',
								'value' => '1',
							]
						]
					];
				break;		
				case 'stock_status':
					$valid_sub_field['type'] = 'select';
					$valid_sub_field['choices'] = array(
						'instock' => isset( $setting['instock'] ) ? $setting['instock'] : __( 'In stock', 'woocommerce' ),
						'outofstock' => isset( $setting['outofstock'] ) ? $setting['outofstock'] : __( 'Out of stock', 'woocommerce' ),
						'onbackorder' => isset( $setting['backorder'] ) ? $setting['backorder'] : __( 'On backorder', 'woocommerce' ),
					);
					$valid_sub_field['custom_stock_status'] = true;
					$valid_sub_field['conditional_logic'] = [
						[
							[
								'field' => 'variable_manage_stock',
								'operator' => '!=',
								'value' => '1',
							]
						]
					];
				break;	
				case 'tax_class':
					$valid_sub_field['type'] = 'select';
					$valid_sub_field['choices'] = array();
				break;
			}

			$sub_fields[] = $valid_sub_field;
		}			

		return $sub_fields;
	}

	function prepare_field( $field ) {
		if( isset( $GLOBALS['admin_form'] ) ){
			$form = $GLOBALS['admin_form'];

            if( $form['save_to_product'] == 'edit_product' ){	
				if( ! empty( $form['product_id'] ) && $form['product_id'] != 'none' ){		
					$product = wc_get_product( $form['product_id'] );
					$attrs = $product->get_attributes( 'edit' );

					if( $attrs ){
						$attr_sub_fields = array();

						foreach ( $attrs as $attribute ) {
							if ( ! $attribute->get_variation() ) {
								continue;
							}

							$choices = array();
							if ( $attribute->is_taxonomy() ) : 
								foreach ( $attribute->get_terms() as $option ) :
									$choices[esc_attr( $option->slug )] = esc_html( apply_filters( 'woocommerce_variation_option_name', $option->name, $option, $attribute->get_name(), $product ) );
								endforeach;
							else : 
								foreach ( $attribute->get_options() as $option ) :
									$choices[esc_attr( $option )] = esc_html( apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute->get_name(), $product ) );
								endforeach;
							endif;
									
							$attr_sub_fields[] = array(
								'choices' => $choices,
								'placeholder' => sprintf( esc_html__( 'Any %s&hellip;', 'woocommerce' ), wc_attribute_label( $attribute->get_name() ) ),
								'allow_null' => 1,
								'value' => '',
								'attribute_name' => sanitize_title( $attribute->get_name() ),
							);
						}
						
						$field['sub_fields'][0]['sub_fields'] = $attr_sub_fields;
					}
				}
            }
			
			$fields = $GLOBALS['form_fields'];

			$field['conditional_logic'] = array(
				array(
					array(
						'field' => $fields['product_types'],
						'operator' => '==',
						'value' => 'variable',
					),
				),
			);
        }
		
		// return
		return $field;

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
		
		// vars
		$sub_fields = $field['sub_fields'];
		$show_order = true;
		$show_add = true;
		$show_remove = true;
		
		
		// bail early if no sub fields
		if( empty($sub_fields) ) return;
		
		// div
		$div = array(
			'class' 		=> 'acf-list-item',
			'data-min' 		=> $field['min'],
			'data-max'		=> $field['max']
		);
		
		
		// value
		$value = is_array($field['value']) ? $field['value'] : array();

		$empty = false;
		// empty
		if( empty( $value ) ) {
			if( feadmin_edit_mode() ){
				$value[0] = array();
			}else{
				$div['class'] .= ' -empty';
				$empty = true;
			}		
		}

		if( isset( $GLOBALS['admin_form']['save_to_product'] ) ){
            $form = $GLOBALS['admin_form'];

            if( $form['save_to_product'] == 'edit_product' ){		
				if( ! empty( $form['product_id'] ) && $form['product_id'] != 'none' ){		
					$product = wc_get_product( $form['product_id'] );				
					$attrs = $product->get_attributes( 'edit' );

					foreach( $attrs as $i => $attr ){
						if( ! $attr['variation'] ) unset( $attrs[$i] );
					}
				}				
			}
		}
		if( empty( $attrs ) && ! feadmin_edit_mode() ){
			$div['class'] .= ' -no-attrs';
			$attrs = false;
		} 

		
		// If there are less values than min, populate the extra values
		if( $field['min'] ) {
			
			$value = array_pad($value, $field['min'], array());
			
		}
		
		// If there are more values than max, remove some values
		if( $field['max'] ) {
			
			$value = array_slice($value, 0, $field['max']);
			
			
			// if max 1 row, don't show order
			if( $field['max'] == 1 ) {
			
				$show_order = false;
				
			}
			
			
			// if max == min, don't show add or remove buttons
			if( $field['max'] <= $field['min'] ) {
			
				$show_remove = false;
				$show_add = false;
				
			}
			
		}
	
		
		// setup values for row clone
		$value['acfcloneindex'] = array();
		
		
		// button label
		if( $field['button_label'] === '' ) $field['button_label'] = __('Add Variation', FEA_NS);
		if( $field['save_text'] === '' ) $field['save_text'] = __('Save Changes', FEA_NS);
		
		// field wrap
		$el = 'td';
		$before_fields = '';
		$after_fields = '';
		
		if( $field['layout'] == 'row' ) {
		
			$el = 'div';
			$before_fields = '<td class="acf-fields -left">';
			$after_fields = '</td>';
			
		} elseif( $field['layout'] == 'block' ) {
		
			$el = 'div';
			
			$before_fields = '<td class="acf-fields">';
			$after_fields = '</td>';
			
		}
		
		
		// layout
		$div['class'] .= ' -' . $field['layout'];
		
		
		// collapsed
		if( $field['collapsed'] ) {
			
			// loop
			foreach( $sub_fields as &$sub_field ) {
				
				// add target class
				if( $sub_field['key'] == $field['collapsed'] ) {
					$sub_field['wrapper']['class'] .= ' -collapsed-target';
				}
			}
			unset( $sub_field );
		}

		if( $field['no_attrs_msg'] == '' ){
			$field['no_attrs_msg'] = wp_kses_post( __( 'Before you can add a variation you need to add some variation attributes.', FEA_NS ) );
		}
		if( $field['no_value_msg'] == '' ){
			$no_value_message = __('Click the "%s" button below to add variations to your product',FEA_NS);
			$field['no_value_msg'] = sprintf( $no_value_message, $field['button_label'] );
		}

?>
<div <?php acf_esc_attr_e( $div ); ?>>
	<?php acf_hidden_input(array( 'name' => $field['name'], 'value' => '' )); ?>

	<div class="no-value-message">
		<?php echo $field['no_value_msg']; ?>
	</div>

	<div class="no-attributes-message">
		<?php echo $field['no_attrs_msg']; ?>
	</div>

<table class="acf-table">
	
	<?php if( $field['layout'] == 'table' ): ?>
		<thead>
			<tr>
				<?php if( $show_order ): ?>
					<th class="acf-row-handle"></th>
				<?php endif; ?>
				
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

				<?php if( $show_remove ): ?>
					<th class="acf-row-handle"></th>
				<?php endif; ?>
			</tr>
		</thead>
	<?php endif; ?>
	
	<tbody>
		<?php foreach( $value as $i => $row ): ?>
			<?php if( empty( $row['variation_id'] ) ) $row['variation_id'] = '0000'; ?>
			<tr class="acf-row <?php if( ! feadmin_edit_mode() ){ echo '-collapsed'; }
			if( $i === 'acfcloneindex' ){  echo ' acf-clone'; } ?>" data-id="<?php echo $i; ?>">
				
				<?php if( $show_order ): ?>
					<td class="acf-row-handle order" title="<?php _e('Drag to reorder, Click to toggle.',FEA_NS); ?>">
						<span class="variation-id"><?php if( $i !== 'acfcloneindex' ){ echo '#' . $row['variation_id']; } ?></span><span class="acf-icon -collapse small"></span>					
					</td>
				<?php endif; ?>
				
				<?php echo $before_fields; ?>
				
				<?php acf_hidden_input(array( 'class' => 'row-variation-id', 'name' => $field['name']. '[' .$i. ']'. '[variation_id]', 'value' => $row['variation_id'] )); ?>

				<?php foreach( $sub_fields as $sub_field ): 
					$sub_field['variation_id'] = $row['variation_id'];

					// add value
					if( isset($row[$sub_field['key']]) ) {
						
						// this is a normal value
						$sub_field['value'] = $row[$sub_field['key']];
						
					} elseif( isset($sub_field['default_value']) ) {
						
						// no value, but this sub field has a default value
						$sub_field['value'] = $sub_field['default_value'];
						
					}
					
					
					// update prefix to allow for nested values
					$sub_field['prefix'] = $field['name'] . '[' . $i . ']';
					
					
					// render input
					fea_instance()->form_display->render_field_wrap( $sub_field, $el ); ?>
					
				<?php endforeach; ?>
				
				<?php echo $after_fields; ?>
				
				<?php if( $show_remove ): ?>
					<td class="acf-row-handle remove">
						<a class="acf-icon -plus small acf-js-tooltip" href="#" data-event="add-row" title="<?php _e('Add variation',FEA_NS); ?>"></a>
						<a class="acf-icon -minus small acf-js-tooltip" href="#" data-event="remove-row" data-variation_id="<?php echo $row['variation_id'] ?>" title="<?php _e('Remove variation',FEA_NS); ?>"></a>
					</td>
				<?php endif; ?>
				
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php if( $show_add ): ?>
	
	<div class="acf-actions">
		<a class="acf-button button button-primary add-variation" href="#" data-event="add-row"><?php echo $field['button_label']; ?></a>
		<a class="acf-button button button-primary save-changes" href="#" data-name="save-changes"><?php echo $field['save_text']; ?></a>
	</div>
			
<?php endif; ?>
</div>
<?php
		
	}
	
	function variation_field_options(){
		return array(
			__( 'Product', FEA_NS ) => array(
				'description' => __( 'Description', FEA_NS ),
				'image' => __( 'Image', FEA_NS ),
				'price' => __( 'Price', FEA_NS ),
				'sale_price' => __( 'Sale Price', FEA_NS ),
				'sku' =>  __( 'SKU', FEA_NS ),
				//'tax_class' => __( 'Tax Class', FEA_NS ),
			),
			__( 'Product Inventory', FEA_NS ) => array(
				'stock_status' =>  __( 'Stock Status', FEA_NS ),
				'manage_stock' =>  __( 'Manage Stock', FEA_NS ), 
				'stock_quantity' =>  __( 'Stock Quantity', FEA_NS ), 
				'allow_backorders' =>  __( 'Allow Backorders', FEA_NS ), 
			),
		);
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
			'button_label' => __( 'Add Field', FEA_NS ),
			'show_add' => true,
			'collapsed'	=> 'type',
			'show_remove' => true,
			'layout' => 'block',
			'sub_fields' => array(
				array(
					'key' => 'type',
					'name' => 'type',
					'field_label_hide' => 1,
					'label' => '',
					'type' => 'select',
					'choices' => $this->variation_field_options(),
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
					'key' => 'required',
					'name' => 'required',
					'label' => __( 'Required', FEA_NS ),
					'type' => 'true_false',
					'ui' => 1,
					'wrapper' => array(
						'width' => 30,
					),
				),
				array(
					'key' => 'display_mode',
					'name' => 'display_mode',
					'label' => __( 'Display Mode', FEA_NS ),
					'type' => 'select',
					'choices' => array(
						'edit'	=> __( 'Edit', FEA_NS ),
						'read_only'	=> __( 'Read Only', FEA_NS ),
						'hidden'	=> __( 'Hidden', FEA_NS ),
					),
					'wrapper' => array(
						'width' => 30,
					),
				),
				array(
					'key' => 'placeholder',
					'name' => 'placeholder',
					'label' => __( 'Placeholder', FEA_NS ),
					'type' => 'text',
					'conditions' => array(
						array(
							array(
								'field'     => 'type',
								'operator'  => '==',
								'value'     => 'description',
							),
						),
						array(
							array(
								'field'     => 'type',
								'operator'  => '==',
								'value'     => 'sku',
							),
						),
					)
				),  
				array(
					'key' => 'number_placeholder',
					'name' => 'number_placeholder',
					'label' => __( 'Placeholder', FEA_NS ),
					'type' => 'number',
					'conditions' => array(
						array(
							array(
								'field'     => 'type',
								'operator'  => '==',
								'value'     => 'price',
							),
						),
						array(
							array(
								'field'     => 'type',
								'operator'  => '==',
								'value'     => 'sale_price',
							),
						),
					)
				),  
				array(
					'key' => 'minimum',
					'name' => 'minimum',
					'label' => __( 'Minimum', FEA_NS ),
					'type' => 'number',
					'conditions' => array(
						array(
							array(
								'field'     => 'type',
								'operator'  => '==',
								'value'     => 'price',
							),
						),
						array(
							array(
								'field'     => 'type',
								'operator'  => '==',
								'value'     => 'sale_price',
							),
						),
					),
					'wrapper' => array(
						'width' => 30,
					),
				),  
				array(
					'key' => 'maximum',
					'name' => 'maximum',
					'label' => __( 'Max', FEA_NS ),
					'type' => 'number',
					'conditions' => array(
						array(
							array(
								'field'     => 'type',
								'operator'  => '==',
								'value'     => 'price',
							),
						),
						array(
							array(
								'field'     => 'type',
								'operator'  => '==',
								'value'     => 'sale_price',
							),
						),
					),
					'wrapper' => array(
						'width' => 30,
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
			'label'			=> __('Minimum Rows',FEA_NS),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'min',
			'placeholder'	=> '0',
		));
		
		
		// max
		acf_render_field_setting( $field, array(
			'label'			=> __('Maximum Rows',FEA_NS),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'max',
			'placeholder'	=> '0',
		));
		
		
		// layout
		acf_render_field_setting( $field, array(
			'label'			=> __('Layout',FEA_NS),
			'instructions'	=> '',
			'class'			=> 'acf-list-item-layout',
			'type'			=> 'radio',
			'name'			=> 'layout',
			'layout'		=> 'horizontal',
			'choices'		=> array(
				'table'			=> __('Table',FEA_NS),
				'block'			=> __('Block',FEA_NS),
				'row'			=> __('Row',FEA_NS)
			)
		));
		
		
		// button_label
		acf_render_field_setting( $field, array(
			'label'			=> __('Button Label',FEA_NS),
			'instructions'	=> '',
			'type'			=> 'text',
			'name'			=> 'button_label',
			'placeholder'	=> __('Add Row',FEA_NS)
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
			if( ! $product ) return $rows;
			foreach( $product->get_children() as $i => $variation_id ){
				$variation = wc_get_product( $variation_id );

				$rows[$i]['variation_id'] = $variation_id;

				foreach( $field['sub_fields'] as $sub_field ){
					$type = $sub_field['variation_field_type'];
					switch( $type ){
						case 'attributes':
							$rows[$i][$sub_field['key']] = $variation->get_attributes( 'edit' );
						break;	
						case 'description':
							$rows[$i][$sub_field['key']] = $variation->get_description();
						break;	
						case 'image':
							$rows[$i][$sub_field['key']] = $variation->get_image_id( 'edit' );
						break;	
						case 'price':
							$rows[$i][$sub_field['key']] = $variation->get_regular_price();
						break;	
						case 'sale_price':
							$rows[$i][$sub_field['key']] = $variation->get_sale_price();
						break;	
						case 'sku':
							$rows[$i][$sub_field['key']] = $variation->get_sku();
						break;	
						case 'manage_stock':
							$rows[$i][$sub_field['key']] = $variation->get_manage_stock();
						break;
						case 'stock_quantity':
							$rows[$i][$sub_field['key']] = wc_stock_amount( $variation->get_stock_quantity() );
						break;			
						case 'allow_backorders':
							$rows[$i][$sub_field['key']] = $variation->get_backorders();
						break;	
						case 'stock_status':
							$rows[$i][$sub_field['key']] = $variation->get_stock_status( 'edit' );
						break;	
					}

				}
			}
		}
			
		// return
		return $rows;
		
	}
	
	
	
	
	/*
	*  validate_value
	*
	*  description
	*
	*  @type	function
	*  @date	11/02/2014
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
		
		
		// min
		$min = (int) $field['min'];
		if( $min && $count < $min ) {
			
			// create error
			$error = __('Minimum rows reached ({min} rows)', FEA_NS);
 			$error = str_replace('{min}', $min, $error);
 			
 			// return
			return $error;
		}
		

		// validate value
		if( $count ) {

			// bail early if no sub fields
			if( !$field['sub_fields'] ) {
				return $valid;
			}

			// loop rows
			foreach( $value as $i => $row ) {

				// loop sub fields
				foreach( $field['sub_fields'] as $sub_field ) {

					// vars
					$k = $sub_field['key'];
					
					// test sub field exists
					if( !isset($row[$k]) ) {
						continue;
					}
					// validate
					acf_validate_value( $row[$k], $sub_field, "{$input}[{$i}][{$k}]" );
				}
				// end loop sub fields
			}
			// end loop rows
		}
		
		
		// return
		return $valid;
	}
	
	function format_attributes( $attributes, $variable_attributes ){
		$formatted = array();
		if( is_array( $attributes ) ){
			foreach( $attributes as $j => $choice ){
				$attribute_key = sanitize_title( $variable_attributes[$j] );
				$value[$attribute_key] = $choice;
			}
		}
		return $value;

	}

	/*
	*  update_variation
	*
	*  This function will update a variable
	*
	*  @type	function
	*
	*  @param	$i (int)
	*  @param	$field (array)
	*  @param	$product_attributes (array)
	*  @return	(boolean)
	*/
	
	function update_variation( $row, $i, $field, $variable_attributes ) {
		
		// bail early if no layout reference
		if( !is_array($row) ) return false;		
		$variation_id = $row['variation_id'];
		$variation = wc_get_product( $variation_id );
		$variation->set_menu_order($i);
		
		// loop
		foreach( $field['sub_fields'] as $sub_field ) {
			// value
			$value = null;			
			
			// find value (key)
			if( isset($row[$sub_field['key']]) ) {				
				$value = $row[$sub_field['key']];			
			// find value (name)	
			} else {				
				continue;				
			}
			
			$type = $sub_field['variation_field_type'];
			switch( $type ){
				case 'attributes':
					$value = $this->format_attributes( $value, $variable_attributes );
					$variation->set_attributes( $value );
				break;	
				case 'description':
					$variation->set_description( wp_kses_post( $value ) );
				break;
				case 'image':
					$variation->set_image_id( $value );
				break;	
				case 'price':
					$variation->set_regular_price( $value );
				break;	
				case 'sale_price':
					$variation->set_sale_price( $value );
				break;	
				case 'sku':
					$variation->set_sku( $value );
				break;	
				case 'manage_stock':
					$variation->set_manage_stock( $value );
				break;
				case 'stock_quantity':
					$variation->set_stock_quantity( $value );
				break;			
				case 'allow_backorders':
					$variation->set_backorders( $value );
				break;	
				case 'stock_status':
					$variation->set_stock_status( $value );
				break;	
			}
				
		}
		$variation->set_status( 'publish' );
		$variation->save();

		do_action( 'woocommerce_rest_save_product_variation', $variation_id, $i, $row );
		
		// return
		return true;
		
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
		
		// bail early if no sub fields
		if( empty($field['sub_fields']) ) return false;
		
		
		// loop
		foreach( $field['sub_fields'] as $sub_field ) {
			
			// modify name for delete
			$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";
			
			
			// delete value
			acf_delete_value( $post_id, $sub_field );
			
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
		// bail early if no sub fields
		if( empty( $field['fields_settings'] ) || get_post_type( $post_id ) != 'product' ) return null;
		
		$product = wc_get_product( $post_id );
		if( $product->get_type() != 'variable' ) return null;

		$variable_attributes = array();
		$attrs = $product->get_attributes( 'edit' );

		if( $attrs ){
			foreach ( $attrs as $attribute ) {
				if ( ! $attribute->get_variation() ) {
					continue;
				}
				$variable_attributes[] = $attribute->get_name();
			}
		}
	
		if( !empty($value) ) { $i = -1;
			
			// remove acfcloneindex
			if( isset($value['acfcloneindex']) ) {
			
				unset($value['acfcloneindex']);
				
			}
		

			// loop through rows
			foreach( $value as $row ) {	$i++;
				
				// bail early if no row
				if( !is_array($row) ) continue;
				// update row
				$this->update_variation( $row, $i, $field, $variable_attributes );
				
			}
			
		}			
		
		$product->save();

		// return
		return null;
	}
	
	
	/*
	*  delete_value
	*
	*  description
	*
	*  @type	function
	*  @date	1/07/2015
	*  @since	5.2.3
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function delete_value( $post_id, $key, $field ) {
		
		// get old value (db only)
		$old_value = (int) acf_get_metadata( $post_id, $field['name'] );
		
		
		// bail early if no rows or no sub fields
		if( !$old_value || empty($field['sub_fields']) ) return;
		
		
		// loop
		for( $i = 0; $i < $old_value; $i++ ) {
			
			$this->delete_row( $i, $field, $post_id );
			
		}
			
	}
	
	
	/*
	*  delete_field
	*
	*  description
	*
	*  @type	function
	*  @date	4/04/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function delete_field( $field ) {
		
		// bail early if no sub fields
		if( empty($field['sub_fields']) ) return;
		
		
		// loop through sub fields
		foreach( $field['sub_fields'] as $sub_field ) {
		
			acf_delete_field( $sub_field['ID'] );
			
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
		
		// remove sub fields
		unset($field['sub_fields']);
		
				
		// return		
		return $field;
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
		
		// get sub fields
		$sub_fields = acf_extract_var( $field, 'sub_fields' );
		
		
		// save field to get ID
		$field = acf_update_field( $field );
		
		
		// duplicate sub fields
		acf_duplicate_fields( $sub_fields, $field['ID'] );
		
						
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
		$field['save_text'] = acf_translate( $field['save_text'] );
		
		// return
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
	*  prepare_field_for_export
	*
	*  description
	*
	*  @type	function
	*  @date	11/03/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function prepare_field_for_export( $field ) {
		
		// bail early if no sub fields
		if( empty($field['sub_fields']) ) return $field;
		
		
		// prepare
		$field['sub_fields'] = acf_prepare_fields_for_export( $field['sub_fields'] );
		
		
		// return
		return $field;
		
	}
	
	
	/*
	*  prepare_field_for_import
	*
	*  description
	*
	*  @type	function
	*  @date	11/03/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function prepare_field_for_import( $field ) {
		
		// bail early if no sub fields
		if( empty($field['sub_fields']) ) return $field;
		
		
		// vars
		$sub_fields = $field['sub_fields'];
		
		
		// reset field setting
		$field['sub_fields'] = array();
		
		
		// loop
		foreach( $sub_fields as &$sub_field ) {
			
			$sub_field['parent'] = $field['key'];
			
		}
		
		
		// merge
		array_unshift($sub_fields, $field);
		
		
		// return
		return $sub_fields;
		
	}

}


// initialize
acf_register_field_type( 'acf_field_product_variations' );

endif; // class_exists check

?>
