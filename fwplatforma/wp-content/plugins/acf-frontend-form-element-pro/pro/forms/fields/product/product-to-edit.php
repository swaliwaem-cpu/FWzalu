<?php

if ( ! class_exists( 'acf_field_product_to_edit' ) ) :

	class acf_field_product_to_edit extends acf_field_post_to_edit {


		/*
		*  __construct
		*
		*  This function will setup the field type data
		*
		*  @type    function
		*  @date    5/03/2014
		*  @since   5.0.0
		*
		*  @param   n/a
		*  @return  n/a
		*/

		function initialize() {

			// vars
			$this->name     = 'product_to_edit';
			$this->label    = __( 'Product To Edit', 'acf' );
            $this->category = __( "Product", FEA_NS );
			$this->defaults = array(
				'post_type'     => array( 'product' ),
				'taxonomy'      => array(),
				'allow_null'    => 0,
				'add_new'       => 1,
				'add_new_text'  => __( 'New Product', FEA_NS ),
				'multiple'      => 0,
				'ui'            => 1,
                'no_data_collect' => 1,
			);

				// extra
				add_action( 'wp_ajax_acf/fields/product_to_edit/query', array( $this, 'ajax_query' ) );
				add_action( 'wp_ajax_nopriv_acf/fields/product_to_edit/query', array( $this, 'ajax_query' ) );

		}


	
	
		/*
		*  render_field()
		*
		*  Create the HTML interface for your field
		*
		*  @param   $field - an array holding all the field's data
		*
		*  @type    action
		*  @since   3.6
		*  @date    23/01/13
		*/

		function render_field( $field ) {
            if( empty( $field['placeholder'] ) ){
                $field['placeholder'] = __( 'Select Product', FEA_NS );
            }
           
            // Change Field into a select
            $field['allow_null'] = 1;
			$field['type']    = 'select';
			$field['ui']      = 1;
			$field['ajax']    = 1;
            if( $field['add_new'] ){
                $add_new_text = ! empty( $field['add_new_text'] ) ? $field['add_new_text'] : __( 'New Product', FEA_NS );
                $field['choices'] = array( 'add_product' => $add_new_text );
            }else{
    			$field['choices'] = array();
            }
			// load posts
			$posts = $this->get_posts( $field['value'], $field );

			if ( $posts ) {

				foreach ( array_keys( $posts ) as $i ) {

					// vars
					$post = acf_extract_var( $posts, $i );

					// append to choices
					$field['choices'][ $post->ID ] = $this->get_post_title( $post, $field );

				}
			}

			// render
			acf_render_field( $field );

		}
		function get_post_title( $post, $field, $post_id = 0, $is_search = 0 ) {

			// get post_id
			if ( ! $post_id ) {
				$post_id = acf_get_form_data( 'post_id' );
			}

			// vars
			$title = acf_get_post_title( $post, $is_search );

			// filters
			$title = apply_filters( FEA_PREFIX .'/fields/post_to_edit/result', $title, $post, $field, $post_id );
			$title = apply_filters( FEA_PREFIX .'/fields/post_to_edit/result/name=' . $field['_name'], $title, $post, $field, $post_id );
			$title = apply_filters( FEA_PREFIX .'/fields/post_to_edit/result/key=' . $field['key'], $title, $post, $field, $post_id );

			// return
			return $title;
		}



		/*
		*  render_field_settings()
		*
		*  Create extra options for your field. This is rendered when editing a field.
		*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
		*
		*  @type    action
		*  @since   3.6
		*  @date    23/01/13
		*
		*  @param   $field  - an array holding all the field's data
		*/

		function render_field_settings( $field ) {
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Filter by User', FEA_NS ),
					'instructions' => __( 'Only show products by the following users', FEA_NS ),
					'type'         => 'select',
					'name'         => 'post_author',
					'choices'      => array( 'current_user' => __( 'Current User' ) ),
					'multiple'     => 1,
					'ui'           => 1,
					'allow_null'   => 1,
					'placeholder'  => '',
				)
			);
			/* // default_value
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Filter by Taxonomy', 'acf' ),
					'instructions' => '',
					'type'         => 'select',
					'name'         => 'taxonomy',
					'choices'      => acf_get_taxonomy_terms(),
					'multiple'     => 1,
					'ui'           => 1,
					'allow_null'   => 1,
					'placeholder'  => __( 'All taxonomies', 'acf' ),
				)
			); */
            acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Placeholder', FEA_NS ),
					'instructions' => '',
					'name'         => 'placeholder',
					'type'         => 'text',
                    'placeholder'  => __( 'Select Product', FEA_NS ),
				)
			);
            acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Add New Product?', FEA_NS ),
					'instructions' => '',
					'name'         => 'add_new',
					'type'         => 'true_false',
					'ui'           => 1,
				)
			);
            acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'New Product Text', FEA_NS ),
					'instructions' => '',
					'name'         => 'add_new_text',
					'type'         => 'text',
                    'placeholder'  => __( 'New Product', FEA_NS ),
                    'conditions' => array(
                        array(
                            array(
                                'field'     => 'add_new',
                                'operator'  => '==',
                                'value'     => 1,
                            ),
                        ),
                    ) 
				)
			);

			/* // multiple
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Select multiple values?', 'acf' ),
					'instructions' => '',
					'name'         => 'multiple',
					'type'         => 'true_false',
					'ui'           => 1,
				)
			); */

		}


		/*
		*  load_value()
		*
		*  This filter is applied to the $value after it is loaded from the db
		*
		*  @type    filter
		*  @since   3.6
		*  @date    23/01/13
		*
		*  @param   $value (mixed) the value found in the database
		*  @param   $post_id (mixed) the $post_id from which the value was loaded
		*  @param   $field (array) the field array holding all the field options
		*  @return  $value
		*/

		function load_value( $value, $post_id, $field ) {
            if( $post_id == 'none' ){
               return null;
            }

			// return
			return $post_id;

		}




		/*
		*  update_value()
		*
		*  This filter is appied to the $value before it is updated in the db
		*
		*  @type    filter
		*  @since   3.6
		*  @date    23/01/13
		*
		*  @param   $value - the value which will be saved in the database
		*  @param   $post_id - the $post_id of which the value will be saved
		*  @param   $field - the field array holding all the field options
		*
		*  @return  $value - the modified value
		*/

		function update_value( $value, $post_id, $field ) {
            return null;
		}


		/*
		*  get_posts
		*
		*  This function will return an array of posts for a given field value
		*
		*  @type    function
		*  @date    13/06/2014
		*  @since   5.0.0
		*
		*  @param   $value (array)
		*  @return  $value
		*/

		function get_posts( $value, $field ) {

			// numeric
			$value = acf_get_numeric( $value );

			// bail early if no value
			if ( empty( $value ) ) {
				return false;
			}

			$args = array(
				'post__in'  => $value,
				'post_type' => 'product',
			);
			if( ! empty( $field['post_author'] ) ){
				$args['author'] = get_current_user_id();
			}
			// get posts
			$posts = acf_get_posts( $args );

			// return
			return $posts;

		}

	}


	// initialize
	acf_register_field_type( 'acf_field_product_to_edit' );

endif; // class_exists check


