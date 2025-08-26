<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'product_date' ) ) :

	class product_date extends post_date {



		  /*
		  *  initialize
		  *
		  *  This function will setup the field type data
		  *
		  *  @type      function
		  *  @date      5/03/2014
		  *  @since      5.0.0
		  *
		  *  @param      n/a
		  *  @return      n/a
		  */

		function initialize() {
			// vars
			$this->name     = 'product_date';
			$this->label    = __( 'Published On', 'acf-frontend-form-element' );
			$this->category = __( 'Product', 'acf-frontend-form-element' );
			$this->defaults = array(
				'data_name'      => 'published_on',
				'display_format' => get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
				'return_format'  => 'd/m/Y g:i a',
				'first_day'      => get_option( 'start_of_week' ),
			);

			add_filter( 'acf/update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 3 );

		}
	}



endif;


