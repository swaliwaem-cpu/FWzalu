<?php

if( ! class_exists('acf_field_product_types') ) :

class acf_field_product_types extends acf_field {
	
	
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
		$this->name = 'product_types';
		$this->label = __( "Product Types",FEA_NS );
		$this->category = __( 'Product Type', FEA_NS );
		$this->defaults = array(
			'taxonomy' 			=> 'product_type',
			'field_type' 		=> 'radio',
			'multiple'			=> 0,
			'allow_null' 		=> 0,
			//'load_save_terms' 	=> 0, // removed in 5.2.7
			'return_format'		=> 'id',
			'add_term'			=> 0, // 5.2.3
			'load_post_terms'		=> 1, // 5.2.7	
			'save_terms'		=> 1 // 5.2.7
		);
		
    	
	}
	
	
	/*
	*  load_value()
	*
	*  This filter is appied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value found in the database
	*  @param	$post_id - the $post_id from which the value was loaded from
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the value to be saved in te database
	*/
	
	function load_value( $value, $post_id, $field ) {
		if( get_post_type( $post_id ) == 'product' ){
			$product = wc_get_product( $post_id );
			$value = $product->get_type();
		}
								
		// return
		return $value;
		
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
		if( get_post_type( $post_id ) == 'product' ){
			$product = wc_get_product( $post_id );

			if( $product->get_type() != $value ){
				$classname = WC_Product_Factory::get_product_classname( $post_id, $value );
				$product = new $classname( $post_id );
				$product->save();
			}
		}
								
		// return
		return null;
	}
	
	function prepare_field( $field ){
        $all_product_types = wc_get_product_types();   
        if( empty( $field['choices'] ) ){     
            if( ! empty( $field['product_type_options'] ) ){
                foreach( $field['product_type_options'] as $slug ){
                    $field['choices'][ $slug ] = $all_product_types[ $slug ];
                }
            }else{
                $field['choices'] = $all_product_types;
            }
        }
		$field['allow_null'] = 0;	

        return $field;
    }
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - an array holding all the field's data
	*/
	
	function render_field( $field ) {
		
		// vars
		$div = array(
			'class'				=> 'acf-product-types-field',
			'data-save'			=> $field['save_terms'],
			'data-ftype'		=> $field['field_type'],
			'data-taxonomy'		=> 'product_type',
			'data-allow_null'	=> $field['allow_null']
		);
		
		
		// get taxonomy
		$taxonomy = get_taxonomy( 'product_type' );
		
		
		// bail early if taxonomy does not exist
		if( !$taxonomy ) return;
		
		
		?>
<div <?php acf_esc_attr_e($div); ?>>

	<?php

	if( $field['field_type'] == 'select' ) {
	
		$field['multiple'] = 0;
		$field['type'] = 'select';
		$field['ui'] = 0;
		$field['ajax'] = 0;
			
	
	} elseif( $field['field_type'] == 'radio' ) {
		$field['other_choice'] = 0;
		$field['layout'] = 'vertical';
		$field['type'] = 'radio';
		
	} 
	acf_render_field( $field );

	?>
</div><?php
		
	}
	

		/*
	*  render_field_checkbox()
	*
	*  Create the HTML interface for your field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - an array holding all the field's data
	*/
	
	function render_field_checkbox( $field ) {
		
		// hidden input
		acf_hidden_input(array(
			'type'	=> 'hidden',
			'name'	=> $field['name'],
		));		
		
		// taxonomy
		$taxonomy_obj = get_taxonomy('product_type');
		
		
		// include walker
		acf_include('includes/walkers/class-acf-walker-taxonomy-field.php');
		
		
		// vars
		$args = array(
			'taxonomy'     		=> 'product_type',
			'show_option_none'	=> sprintf( _x('No %s', 'No terms', FEA_NS), strtolower($taxonomy_obj->labels->name) ),
			'hide_empty'   		=> false,
			'style'        		=> 'none',
			'walker'       		=> new ACF_Taxonomy_Field_Walker( $field ),
		);
		
		?>
<div class="categorychecklist-holder">
	<ul class="acf-checkbox-list acf-bl">
		<?php wp_list_categories( $args ); ?>
	</ul>
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
		
		acf_render_field_setting( $field, array(
            'label'			=> __('Default Type',FEA_NS),
			'instructions'	=> __('Appears when creating a new product',FEA_NS),
            'type'			=> 'select',
            'name'			=> 'default_value',
            'ui'            => 0,
            'choices'		=> wc_get_product_types(),
        ));
                
		acf_render_field_setting( $field, array(
			'label'			=> __('Product Types',FEA_NS),
			'instructions'	=> __('Select the types to choose from',FEA_NS),
			'type'			=> 'select',
			'name'			=> 'product_type_options',
            'multiple'      => 1,
            'ui'            => 1,
			'choices'		=> wc_get_product_types(),
		));
	
		// field_type
		acf_render_field_setting( $field, array(
			'label'			=> __('Appearance',FEA_NS),
			'instructions'	=> __('Select the appearance of this field',FEA_NS),
			'type'			=> 'select',
			'name'			=> 'field_type',
			'optgroup'		=> true,
			'choices'		=> array(
				'radio' => __('Radio Buttons', FEA_NS),
				'select' => _x('Select', 'noun', FEA_NS)
			)
		));
		
		
	}
		
}


// initialize
acf_register_field_type( 'acf_field_product_types' );

endif; // class_exists check

?>