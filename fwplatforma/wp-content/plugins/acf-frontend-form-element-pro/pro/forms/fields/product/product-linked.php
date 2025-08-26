<?php

if( ! class_exists('acf_field_product_linked') ) :

class acf_field_product_linked extends acf_field_relationship {
	
	
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
		$this->name = 'product_linked';
		$this->label = __("Linked Products",FEA_NS);
		$this->public = false;	
		$this->defaults = array(
			'post_type'			=> array( 'product' ),
			'taxonomy'			=> array(),
			'exclude_current'	=> 1,
			'min' 				=> 0,
			'max' 				=> 0,
			'filters'			=> array('search', 'taxonomy'),
			'elements' 			=> array(),
			'return_format'		=> 'object',
			'add_edit_post'		=> 0,
			'add_post_button'	=> __("Add Product",FEA_NS),
			'form_width'		=> 600,
		);
		add_filter( 'acf/fields/relationship/query', array( $this, 'exclude_currrent_post' ), 10, 3 );

	}
	
	/*
	* exclude_currrent_post
	*
	*  description
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function exclude_currrent_post( $args, $field, $post_id ) {
		if( empty( $field['exclude_current'] ) ) return $args;

		if( isset( $_POST['product_id'] ) ) $post_id = $_POST['product_id'];

		if( empty( $args['post__not_in'] ) ){
			$args['post__not_in'] = array( $post_id );
		}else{
			array_unshift( $args['post__not_in'], $post_id );
		}
			
		return $args;
	}

	function load_field( $field ) {
		if( ! isset( $field['add_edit_post'] ) ) return $field;

		if( isset( $field['form_width'] ) ){
			$field['wrapper']['data-form_width'] = $field['form_width'];
		}

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
		$post_type = array( 'product' );
				
		$term_choices = array();
		$filter_taxonomy_choices = array(
			''	=> '-- ' . _x( 'Product type', 'Taxonomy name', 'woocommerce' ) . ' --'
		);
		
			$terms = wc_get_product_types();
			$term_choices = array();

			foreach( $terms as $slug => $label ){
				$term_choices['product_type:'.$slug] = $label;
			}
		
		// append term choices
		$filter_taxonomy_choices = $filter_taxonomy_choices + $term_choices;
			
		
		// div attributes
		$atts = array(
			'id'				=> $field['id'],
			'class'				=> "acf-relationship {$field['class']}",
			'data-min'			=> $field['min'],
			'data-max'			=> $field['max'],
			'data-s'			=> '',
			'data-paged'		=> 1,
			'data-post_type'	=> '',
			'data-taxonomy'		=> '',
		);

		if( isset( $GLOBALS['admin_form'] ) ){
            $form = $GLOBALS['admin_form'];
			$atts['data-product_id'] = $form['product_id'];
		}
		
		?>
<div <?php acf_esc_attr_e($atts); ?>>
	
	<?php acf_hidden_input( array('name' => $field['name'], 'value' => '') ); ?>
	
	<div class="filters -f2">
	
		<div class="filter -search">
			<?php acf_text_input( array('placeholder' => __("Search...",FEA_NS), 'data-filter' => 's') ); ?>
		</div>
		
		<div class="filter -taxonomy">
			<?php acf_select_input( array('choices' => $filter_taxonomy_choices, 'data-filter' => 'taxonomy') ); ?>
		</div>	
	</div>
	
	<div class="selection">
		<div class="choices">
			<ul class="acf-bl list choices-list"></ul>
		</div>
		<div class="values">
			<ul class="acf-bl list values-list">
			<?php if( !empty($field['value']) ): 
				
				// get posts
				$posts = acf_get_posts(array(
					'post__in' => $field['value'],
					'post_type'	=> 'product'
				));
				
				
				// loop
				foreach( $posts as $post ): ?>
					<li>
						<?php acf_hidden_input( array('name' => $field['name'].'[]', 'value' => $post->ID) ); ?>
						<span data-id="<?php echo esc_attr($post->ID); ?>" class="acf-rel-item">
							<?php echo acf_esc_html( $this->get_post_title( $post, $field ) ); ?>
							<a href="#" class="acf-icon -minus small dark" data-name="remove_item"></a>
						</span>
					</li>
				<?php endforeach; ?>
			<?php endif; ?>
			</ul>
		</div>
	</div>
</div>
<?php
		if( ! empty( $field['add_edit_post'] ) ) : 
			$add_post_button = ( $field['add_post_button'] ) ? $field['add_post_button'] : __( 'Add Product', FEA_NS );
			?>
				<div class="margin-top-10 acf-actions">
					<a class="add-rel-post acf-button button button-primary" href="#" data-name="add_item"><?php echo $add_post_button ?></a>
				</div>
				
		<?php endif;
		
	}
	public function render_field_settings( $field ) {
		$users = get_users();
		$label = __( 'Dynamic', FEA_NS );
		$user_choices = [ $label => ['current_user' => __( 'Current User', FEA_NS ) ] ];
		// Append.
		if( $users ) {
			$user_label = __( 'Users', FEA_NS );
			$user_choices[ $user_label ] = [];
			foreach( $users as $user ) {
				$user_text = $user->user_login;	
				// Add name.
				if( $user->first_name && $user->last_name ) {
					$user_text .= " ({$user->first_name} {$user->last_name})";
				} elseif( $user->first_name ) {
					$user_text .= " ({$user->first_name})";
				}
				$user_choices[ $user_label ][ $user->ID ] = $user_text;
			}
		}		
		acf_render_field_setting( $field, array(
			'label'			=> __( 'Filter by Post Author',FEA_NS ),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'post_author',
			'choices'		=> $user_choices,
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'   => __( "All Users",FEA_NS ),
		));
		
	}
	
}



// initialize
acf_register_field_type( 'acf_field_product_linked' );

endif; // class_exists check

?>
