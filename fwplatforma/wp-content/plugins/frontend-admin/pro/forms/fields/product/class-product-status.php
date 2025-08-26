<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'product_status' ) ) :

	class product_status extends post_status {


		function initialize() {
			$this->name     = 'product_status';
			$this->label    = __( 'Product Status', 'acf-frontend-form-element' );
			$this->category = __( 'Product', 'acf-frontend-form-element' );
			$this->defaults = array(
				'product_status'     => array(),
				'field_type'         => 'checkbox',
				'choices'            => array(),
				'default_value'      => '',
				'ui'                 => 0,
				'ajax'               => 0,
				'placeholder'        => '',
				'search_placeholder' => '',
				'layout'             => '',
				'toggle'             => 0,
				'allow_custom'       => 0,
				'return_format'      => 'object',
				'post_status'        => array( 'publish', 'draft', 'pending', 'private' ),
			);

			add_filter( 'acf/update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 3 );
		}

	}



endif;
