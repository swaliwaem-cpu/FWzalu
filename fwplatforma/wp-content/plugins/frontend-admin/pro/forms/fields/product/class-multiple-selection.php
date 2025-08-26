<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'multiple_selection' ) ) :

	class multiple_selection extends Field_Base {



		  /*
		  *  __construct
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
			$this->name     = 'multiple_selection';
			$this->label    = _x( 'Multiple Selection', 'noun', 'acf-frontend-form-element' );
			$this->public   = false;
			$this->defaults = array(
				'multiple'      => 0,
				'allow_null'    => 0,
				'choices'       => array(),
				'default_value' => '',
				'ui'            => 0,
				'ajax'          => 0,
				'placeholder'   => '',
				'return_format' => 'value',
				'sub_fields'    => array(),
			);

		}

		/*
		*  render_field()
		*
		*  Create the HTML interface for your field
		*
		*  @param      $field - an array holding all the field's data
		*
		*  @type      action
		*  @since      3.6
		*  @date      23/01/13
		*/

		function render_field( $field ) {
			if ( ! empty( $field['sub_fields'] ) ) {

				foreach ( $field['sub_fields'] as $sub_field ) {
					// convert
					$value   = acf_get_array( $sub_field['value'] );
					$choices = acf_get_array( $sub_field['choices'] );

					// placeholder
					if ( empty( $sub_field['placeholder'] ) ) {
						$sub_field['placeholder'] = _x( 'Select', 'verb', 'acf-frontend-form-element' );
					}

					// add empty value (allows '' to be selected)
					if ( empty( $value ) ) {
						$value = array( '' );
					}

					$choices = array( '' => "- {$sub_field['placeholder']} -" ) + $choices;

					// vars
					$select = array(
						'id'               => $field['id'],
						'class'            => $field['class'],
						'name'             => $field['name'] . '[]',
						'data-placeholder' => $sub_field['placeholder'],
						'data-allow_null'  => $sub_field['allow_null'],
					);

					// special atts
					if ( ! empty( $field['readonly'] ) ) {
						$select['readonly'] = 'readonly';
					}
					if ( ! empty( $field['disabled'] ) ) {
						$select['disabled'] = 'disabled';
					}
					if ( ! empty( $field['ajax_action'] ) ) {
						$select['data-ajax_action'] = $field['ajax_action'];
					}

					// append
					$select['value']   = $value;
					$select['choices'] = $choices;

					// render
					acf_select_input( $select );
				}
			}
		}


		/*
		*  render_field_settings()
		*
		*  Create extra options for your field. This is rendered when editing a field.
		*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
		*
		*  @type      action
		*  @since      3.6
		*  @date      23/01/13
		*
		*  @param      $field      - an array holding all the field's data
		*/

		function render_field_settings( $field ) {
			  // encode choices (convert from array)
			  $field['choices']       = acf_encode_choices( $field['choices'] );
			  $field['default_value'] = acf_encode_choices( $field['default_value'], false );

			  // choices
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Choices', 'acf-frontend-form-element' ),
					'instructions' => __( 'Enter each choice on a new line.', 'acf-frontend-form-element' ) . '<br /><br />' . __( 'For more control, you may specify both a value and label like this:', 'acf-frontend-form-element' ) . '<br /><br />' . __( 'red : Red', 'acf-frontend-form-element' ),
					'name'         => 'choices',
					'type'         => 'textarea',
				)
			);

			  // default_value
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Default Value', 'acf-frontend-form-element' ),
					'instructions' => __( 'Enter each default value on a new line', 'acf-frontend-form-element' ),
					'name'         => 'default_value',
					'type'         => 'textarea',
				)
			);

			  // allow_null
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Allow Null?', 'acf-frontend-form-element' ),
					'instructions' => '',
					'name'         => 'allow_null',
					'type'         => 'true_false',
					'ui'           => 1,
				)
			);

			  // return_format
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Return Format', 'acf-frontend-form-element' ),
					'instructions' => __( 'Specify the value returned', 'acf-frontend-form-element' ),
					'type'         => 'select',
					'name'         => 'return_format',
					'choices'      => array(
						'value' => __( 'Value', 'acf-frontend-form-element' ),
						'label' => __( 'Label', 'acf-frontend-form-element' ),
						'array' => __( 'Both (Array)', 'acf-frontend-form-element' ),
					),
				)
			);

		}


		/*
		*  load_value()
		*
		*  This filter is applied to the $value after it is loaded from the db
		*
		*  @type      filter
		*  @since      3.6
		*  @date      23/01/13
		*
		*  @param      $value (mixed) the value found in the database
		*  @param      $post_id (mixed) the $post_id from which the value was loaded
		*  @param      $field (array) the field array holding all the field options
		*  @return      $value
		*/

		function load_value( $value, $post_id, $field ) {
			  // ACF4 null
			if ( $value === 'null' ) {
				return false;
			}

			// return
			return $value;
		}


		/*
		*  update_field()
		*
		*  This filter is appied to the $field before it is saved to the database
		*
		*  @type      filter
		*  @since      3.6
		*  @date      23/01/13
		*
		*  @param      $field - the field array holding all the field options
		*  @param      $post_id - the field group ID (post_type = acf)
		*
		*  @return      $field - the modified field
		*/

		function update_field( $field ) {
			  // decode choices (convert to array)
			  $field['choices']       = acf_decode_choices( $field['choices'] );
			  $field['default_value'] = acf_decode_choices( $field['default_value'], true );

			  // return
			  return $field;
		}


		/*
		*  update_value()
		*
		*  This filter is appied to the $value before it is updated in the db
		*
		*  @type      filter
		*  @since      3.6
		*  @date      23/01/13
		*
		*  @param      $value - the value which will be saved in the database
		*  @param      $post_id - the $post_id of which the value will be saved
		*  @param      $field - the field array holding all the field options
		*
		*  @return      $value - the modified value
		*/

		function update_value( $value, $post_id, $field ) {
			  // validate
			if ( empty( $value ) ) {

				return $value;

			}

			// array
			if ( is_array( $value ) ) {

				  // save value as strings, so we can clearly search for them in SQL LIKE statements
				  $value = array_map( 'strval', $value );

			}

			// return
			return $value;
		}


		/*
		*  translate_field
		*
		*  This function will translate field settings
		*
		*  @type      function
		*  @date      8/03/2016
		*  @since      5.3.2
		*
		*  @param      $field (array)
		*  @return      $field
		*/

		function translate_field( $field ) {
			  // translate
			  $field['choices'] = acf_translate( $field['choices'] );

			  // return
			  return $field;

		}


		/*
		*  format_value()
		*
		*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
		*
		*  @type      filter
		*  @since      3.6
		*  @date      23/01/13
		*
		*  @param      $value (mixed) the value which was loaded from the database
		*  @param      $post_id (mixed) the $post_id from which the value was loaded
		*  @param      $field (array) the field array holding all the field options
		*
		*  @return      $value (mixed) the modified value
		*/

		function prepare_field( $field ) {
			  // array
			if ( acf_is_array( $field['value'] ) ) {
				$value = array_values( $field['value'] );

				foreach ( $field['sub_fields'] as $i => $sub_field ) {

					$field['sub_fields'][ $i ]['value'] = $value[ $i ];

				}
			}

			// return
			return $field;

		}


		function format_value_single( $value, $post_id, $field ) {
			  // bail ealry if is empty
			if ( acf_is_empty( $value ) ) {
				return $value;
			}

			// vars
			$label = acf_maybe_get( $field['choices'], $value, $value );

			// value
			if ( $field['return_format'] == 'value' ) {

				  // do nothing

				// label
			} elseif ( $field['return_format'] == 'label' ) {

				$value = $label;

				// array
			} elseif ( $field['return_format'] == 'array' ) {

				$value = array(
					'value' => $value,
					'label' => $label,
				);

			}

			// return
			return $value;

		}

	}




endif; // class_exists check


