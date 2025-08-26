<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'form_step' ) ) :

	class form_step extends Field_Base {

		function initialize() {
			$this->name = 'form_step';
			// $this->public = false;
			$this->label    = __( 'Step', 'acf-frontend-form-element' );
			$this->category = __( 'Form', 'acf-frontend-form-element' );
			$this->defaults = array(
				'next_button_text' => '',
				'prev_button_text' => __( 'Previous', 'acf-frontend-form-element' ),
			);

			add_filter( 'frontend_admin/pre_render_fields', array( $this, 'prepare_form_fields' ), 10, 2 );
			add_action( 'acf/render_field_settings/type=tab', array( $this, 'tab_to_step' ) );
		}

		function prepare_form_fields( $fields, $form = false ) {
			if ( empty( $fields ) ) {
				return $fields;
			}

			if ( ! $form ) {
				$form = $GLOBALS['admin_form'];
			}
			if ( isset( $form['admin_options'] ) ) {
				return $fields;
			}
			$steps_settings = acf_extract_vars(
				$form,
				array(
					'steps_tabs_display',
					'steps_counter_display',
					'steps_display',
					'tab_links',
					'tabs_align',
					'counter_prefix',
					'counter_suffix',
					'counter_text',
					'step_number',
					'validate_steps',
				)
			);

			  $field_count = 0;
			  $_fields     = array();
			foreach ( $fields as $key => $field ) {
				if ( is_string( $field ) ) {
					$field = acf_maybe_get_field( $field );
				}

				if ( ! $field ) {
					return $fields;
				}

				if ( ! empty( $field['endpoint'] ) && ! empty( $steps_wrapper ) ) {
					$step          = 0;
					$steps_wrapper = 0;
					$field_count++;
					continue;
				}
				if ( $field['type'] != 'form_step' && empty( $field['frontend_step'] ) ) {
					if ( ! empty( $steps_wrapper ) ) {
							$_fields[ $field_count ]['steps'][ $step ]['sub_fields'][] = $field;
					} else {
						  $_fields[] = $field;
						  $field_count++;
					}
				} else {
					if ( $field['key'] != $field['_name'] ) {
							$field['name']  = $field['key'];
							$field['_name'] = $field['key'];
							acf_update_field( $field );
					}
					if ( empty( $steps_wrapper ) ) {
							  $step        = 0;
							$steps_wrapper = array_merge(
								$steps_settings,
								array(
									'name'          => $field['key'],
									'key'           => $field['key'] . '_step_wrapper',
									'type'          => 'form_step',
									'steps_wrapper' => 1,
								)
							);
							$_fields[]     = acf_get_valid_field(
								$steps_wrapper
							);
					}
						  $step++;
						  $_fields[ $field_count ]['steps'][ $step ] = $field;
				}
			}
			if ( $_fields ) {
				return $_fields;
			}
			return $fields;
		}

		function tab_to_step( $field ) {
			acf_render_field_setting(
				$field,
				array(
					'label' => __( 'Show as Step on Frontend', 'acf-frontend-form-element' ),
					'name'  => 'frontend_step',
					'type'  => 'true_false',
					'ui'    => 1,
				)
			);
			  $this->render_field_settings( $field, true );
		}

		function render_field_settings( $field, $tab = false ) {
			  $conditions = array(
				  array(
					  array(
						  'field'    => 'endpoint',
						  'operator' => '!=',
						  'value'    => '1',
					  ),
				  ),
			  );
			  if ( $tab ) {
					$conditions[0][] = array(
						array(
							'field'    => 'frontend_step',
							'operator' => '==',
							'value'    => '1',
						),
					);
			  } else {
					acf_render_field_setting(
						$field,
						array(
							'label' => __( 'Endpoint', 'acf-frontend-form-element' ),
							'name'  => 'endpoint',
							'type'  => 'true_false',
							'ui'    => 1,
						)
					);
			  }
            acf_render_field_setting(
				$field,
				array(
					'label'         => __( 'Step Navigation', 'acf-frontend-form-element' ),
					'type'          => 'checkbox',
					'name'          => 'step_buttons',
					'instructions'  => __( 'Previous button will not appear in first step. Next button will submit the form on last step', 'acf-frontend-form-element' ),
					'choices'       => array(
						'previous' => __( 'Previous', 'acf-frontend-form-element' ),
						'next'     => __( 'Next', 'acf-frontend-form-element' ),
					),
					'default_value' => array( 'next', 'previous' ),
					'conditions'    => $conditions,
				)
			);
			if ( ! $conditions ) {
				$step_button = '1';
				$conditions  = array( array() );
			} else {
				$step_button = '2';
			}
			$conditions[0][ $step_button ] = array(
				'field'    => 'step_buttons',
				'operator' => '==',
				'value'    => 'previous',
			);
			acf_render_field_setting(
				$field,
				array(
					'label'       => __( 'Previous Button Text', 'acf-frontend-form-element' ),
					'type'        => 'text',
					'name'        => 'prev_button_text',
					'placeholder' => __( 'Previous', 'acf-frontend-form-element' ),
					'conditions'  => $conditions,
				)
			);
			  $conditions[0][ $step_button ] = array(
				  'field'    => 'step_buttons',
				  'operator' => '==',
				  'value'    => 'next',
			  );
			  acf_render_field_setting(
				$field,
				array(
					'label'       => __( 'Next Button Text', 'acf-frontend-form-element' ),
					'type'        => 'text',
					'name'        => 'next_button_text',
					'placeholder' => __( 'Next', 'acf-frontend-form-element' ),
					'conditions'  => $conditions,
				)
			);

		}

		function render_field( $field ) {
			if ( isset( $field['steps_wrapper'] ) ) {
				if ( empty( $field['steps'] ) ) {
					return;
				}
				$GLOBALS['admin_form']['submit_button_field'] = 1;
				if ( ! $field['value'] ) {
					$field['value'] = 1;
				}
				echo '<div class="frontend-admin-steps frontend-admin-tabs-view-' . esc_attr( $field['tabs_align'] ) . '" data-current-step="' . esc_attr( $field['value'] ) . '" data-validate-steps="' . esc_attr( $field['validate_steps'] ) . '">';
				$this->render_step_tabs( $field );

				$this->render_steps( $field );

				echo '</div>';
			}
		}
		function render_step_tabs( $field ) {
			  $current_step = $field['value'];
			  $total_steps  = count( $field['steps'] );
			  $editor       = feadmin_edit_mode();
			  $current_post = get_post();
			  $active_user  = wp_get_current_user();
			  $screens      = array( 'desktop', 'tablet', 'phone' );

			  $tabs_responsive = '';
			if ( ! empty( $field['steps_tabs_display'] ) ) {
				foreach ( $screens as $screen ) {
					if ( ! in_array( $screen, $field['steps_tabs_display'] ) ) {
						  $tabs_responsive .= 'frontend-admin-hidden-' . $screen . ' ';
					}
				}
			}

			$counter_responsive = '';
			if ( ! empty( $field['steps_counter_display'] ) ) {
				foreach ( $screens as $screen => $label ) {
					if ( ! in_array( $screen, $field['steps_counter_display'] ) ) {
						$counter_responsive .= 'frontend-admin-hidden-' . $label . ' ';
					}
				}
			}

			if ( ! empty( $field['steps_display'] ) ) {

				if ( in_array( 'counter', $field['steps_display'] ) ) {
					$the_step = '<span class="current-step">' . $current_step . '</span>';

					if ( isset( $field['counter_text'] ) ) {
						$counter_text = str_replace( '[current_step]', $the_step, $field['counter_text'] );
						$counter_text = str_replace( '[total_steps]', $total_steps, $counter_text );
					} else {
						  $counter_text = $field['counter_prefix'] . $the_step . $field['counter_suffix'];
					}
					echo '<div class="' . esc_attr( $counter_responsive ) . 'step-count"><p>' . wp_kses_post( $counter_text ) . '</p></div>';
				}

				if ( in_array( 'tabs', $field['steps_display'] ) ) {
					  echo '<div class="frontend-admin-tabs-wrapper ' . esc_attr( $tabs_responsive ) . '">';

					foreach ( $field['steps'] as $step_count => $form_step ) {

						$active = '';
						if ( $step_count == $current_step ) {
							$active = 'active';
						}

						$change_form = '';
						if ( $editor || $field['tab_links'] ) {
							  $change_form = ' change-step';
						}

						if ( isset( $form_step['step_tab_text'] ) ) {
							  $step_title = $form_step['step_tab_text'];
						} else {
							  $step_title = $form_step['label'];
						}
						if ( $step_title == '' ) {
							  $step_title = __( 'Step', 'acf-frontend-form-element' ) . ' ' . $step_count;
						}
						if ( ! empty( $field['step_number'] ) ) {
							  $step_title = $step_count . '. ' . $step_title;
						}

						echo '<a class="form-tab ' . esc_attr( $active ) . esc_attr( $change_form ) . '" data-step="' . esc_attr( $step_count ) . '"><p class="step-name">' . wp_kses_post( $step_title ) . '</p></a>';
					}
					echo '</div>';
				}
			}

		}
		function render_steps( $field ) {
			  $total      = count( $field['steps'] );
			  $input_name = str_replace( '_step_wrapper', '', $field['name'] );
			acf_hidden_input(
				array(
					'name'  => $input_name,
					'value' => $field['value'],
					'class' => 'step-input',
				)
			);
			foreach ( $field['steps'] as $count => $step ) {
				if( ! empty( $step['sub_fields'] ) ){
					$this->render_step_fields( $count, $step, $total, $field );
				}
			}
		}
		function render_step_fields( $count, $step, $total, $wrapper ) {
			?>
			<div class="acf-fields
			<?php
			if ( $count != $wrapper['value'] ) {
				echo ' frontend-admin-hidden'; }
			?>
			" data-step="<?php esc_attr_e( $count ); ?>">
			<?php
			fea_instance()->form_display->render_fields( $step['sub_fields'] );
			$active = 0;
			$this->render_buttons( $step, $count, $total, $wrapper );
			?>
			</div>
			<?php
		}
		public function render_buttons( $step, $count = 1, $total = 2, $wrapper = false ) {
			?>
				  <div class="<?php echo esc_attr( $count ); ?>">
				<?php
				if ( ! isset( $step['step_buttons'] ) ) {
					$step_buttons = array( 'next', 'previous' );
				} else {
					$step_buttons = $step['step_buttons'];
				}

				$prev_button = $next_button = $buttons_class = '';

				if ( $count > 1 && in_array( 'previous', $step_buttons ) ) {
					if ( $step['prev_button_text'] ) {
						$prev_text = $step['prev_button_text'];
					} else {
						$prev_text = __( 'Previous', 'acf-frontend-form-element' );
					}
						$prev_step     = $count - 1;
						$prev_button  .= '<button type="button" name="prev_step" class="prev-button change-step button" data-step="' . esc_attr( $prev_step ) . '">' . wp_kses_post( $prev_text ) . '</button> ';
						$buttons_class = 'frontend-admin-multi-buttons-align';
				}

				if ( in_array( 'next', $step_buttons ) ) {
					  $next_button_text = $step['next_button_text'] ? $step['next_button_text'] : __( 'Next', 'acf-frontend-form-element' );
					if ( $count == $total && ! $step['next_button_text'] ) {
						$next_button_text = __( 'Submit', 'acf-frontend-form-element' );
					}
					$nb_attrs = array(
						'class' => 'button',
					);
					if ( $count == $total ) {
						$next_step = 'submit';
					} else {
						$next_step = $count + 1;
					}

						$nb_attrs = array(
							'type'        => 'button',
							'data-button' => 'next',
							'class'       => 'change-step button',
							'data-step'   => $next_step,
						);

						$next_button = '<button ' . acf_esc_attrs( $nb_attrs ) . '>' . $next_button_text . '</button>';
						if ( ! empty( $wrapper['validate_steps'] ) || $count == $total ) {
							$next_button .= '<span class="acf-loading acf-hidden">';
						}
				}

				if ( $next_button || $prev_button ) {
					  echo '<div class="fea-submit-buttons ' . esc_attr( $buttons_class ) . '">' . $prev_button . $next_button . '</div>';
				}
				?>
				  </div>
			<?php
		}

	}




endif; // class_exists check

?>
