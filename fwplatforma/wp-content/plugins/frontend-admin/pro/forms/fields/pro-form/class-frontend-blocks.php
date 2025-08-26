<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'frontend_blocks' ) ) :

	class frontend_blocks extends Field_Base {


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
			  $this->name     = 'frontend_blocks';
			  $this->label    = __( 'Blocks', 'acf-frontend-form-element' );
			  $this->public   = false;
			  $this->category = __( 'Form', 'acf-frontend-form-element' );
			  $this->defaults = array(
				  'blocks'       => array(),
				  'min'          => '',
				  'max'          => '',
				  'block_labels' => array(
					  'remove'    => __( 'Remove Block', 'acf-frontend-form-element' ),
					  'add'       => __( 'Add Block', 'acf-frontend-form-element' ),
					  'duplicate' => __( 'Duplicate Block', 'acf-frontend-form-element' ),
					  'collapse'  => __( 'Click to Toggle', 'acf-frontend-form-element' ),
					  'button'    => __( 'Add Block', 'acf-frontend-form-element' ),
					  'no_value'  => __( 'Click the button below to add your first ', 'acf-frontend-form-element' ),
				  ),
			  );

			  // ajax
			  $this->add_action( 'wp_ajax_acf/fields/frontend_blocks/block_title', array( $this, 'ajax_block_title' ) );
			  $this->add_action( 'wp_ajax_nopriv_acf/fields/frontend_blocks/block_title', array( $this, 'ajax_block_title' ) );

			  // filters
			  $this->add_filter( 'acf/prepare_field_for_export', array( $this, 'prepare_any_field_for_export' ) );
			  $this->add_filter( 'acf/clone_field', array( $this, 'clone_any_field' ), 10, 2 );
			  $this->add_filter( 'acf/validate_field', array( $this, 'validate_any_field' ) );

			  // field filters
			  $this->add_field_filter( 'acf/get_sub_field', array( $this, 'get_sub_field' ), 10, 3 );
			  $this->add_field_filter( 'acf/prepare_field_for_export', array( $this, 'prepare_field_for_export' ) );
			  $this->add_field_filter( 'acf/prepare_field_for_import', array( $this, 'prepare_field_for_import' ) );

		}


		  /*
		  *  input_admin_enqueue_scripts
		  *
		  *  description
		  *
		  *  @type    function
		  *  @date    16/12/2015
		  *  @since   5.3.2
		  *
		  *  @param   $post_id (int)
		  *  @return  $post_id (int)
		  */

		function input_admin_enqueue_scripts() {

			  // localize
			acf_localize_text(
				array(

					// identifiers
					'block'                            => __( 'block', 'acf-frontend-form-element' ),
					'blocks'                           => __( 'blocks', 'acf-frontend-form-element' ),

					// min / max
					'This field requires at least {min} {label} {identifier}' => __( 'This field requires at least {min} {label} {identifier}', 'acf-frontend-form-element' ),
					'This field has a limit of {max} {label} {identifier}' => __( 'This field has a limit of {max} {label} {identifier}', 'acf-frontend-form-element' ),

					// popup badge
					'{available} {label} {identifier} available (max {max})' => __( '{available} {label} {identifier} available (max {max})', 'acf-frontend-form-element' ),
					'{required} {label} {identifier} required (min {min})' => __( '{required} {label} {identifier} required (min {min})', 'acf-frontend-form-element' ),

					// field settings
					'Blocks requires at least 1 block' => __( 'Blocks requires at least 1 block', 'acf-frontend-form-element' ),
				)
			);
		}


		  /*
		  *  get_valid_block
		  *
		  *  This function will fill in the missing keys to create a valid block
		  *
		  *  @type    function
		  *  @date    3/10/13
		  *  @since   1.1.0
		  *
		  *  @param   $block (array)
		  *  @return  $block (array)
		  */

		function get_valid_block( $block = array() ) {

			  // parse
			$block = wp_parse_args(
				$block,
				array(
					'key'        => uniqid( 'block_' ),
					'name'       => '',
					'label'      => '',
					'display'    => 'block',
					'sub_fields' => array(),
					'min'        => '',
					'max'        => '',
				)
			);

			  // return
			  return $block;
		}


		  /*
		  *  load_field()
		  *
		  *  This filter is appied to the $field after it is loaded from the database
		  *
		  *  @type    filter
		  *  @since   3.6
		  *  @date    23/01/13
		  *
		  *  @param   $field - the field array holding all the field options
		  *
		  *  @return  $field - the field array holding all the field options
		  */

		function load_field( $field ) {

			  // bail early if no field blocks
			if ( empty( $field['blocks'] ) ) {

				  return $field;

			}

			  // vars
			  $sub_fields = acf_get_fields( $field );

			  // loop through blocks, sub fields and swap out the field key with the real field
			foreach ( array_keys( $field['blocks'] ) as $i ) {

				  // extract block
				  $block = acf_extract_var( $field['blocks'], $i );

				  // validate block
				  $block = $this->get_valid_block( $block );

				  // append sub fields
				if ( ! empty( $sub_fields ) ) {

					foreach ( array_keys( $sub_fields ) as $k ) {

						// check if 'parent_block' is empty
						if ( empty( $sub_fields[ $k ]['parent_block'] ) ) {

								// parent_block did not save for this field, default it to first block
								$sub_fields[ $k ]['parent_block'] = $block['key'];

						}

						// append sub field to block,
						if ( $sub_fields[ $k ]['parent_block'] == $block['key'] ) {

								  $block['sub_fields'][] = acf_extract_var( $sub_fields, $k );

						}
					}
				}

				  // append back to blocks
				  $field['blocks'][ $i ] = $block;

			}

			  // return
			  return $field;
		}


		  /*
		  *  get_sub_field
		  *
		  *  This function will return a specific sub field
		  *
		  *  @type    function
		  *  @date    29/09/2016
		  *  @since   5.4.0
		  *
		  *  @param   $sub_field
		  *  @param   $selector (string)
		  *  @param   $field (array)
		  *  @return  $post_id (int)
		  */
		function get_sub_field( $sub_field, $id, $field ) {

			// Get active block.
			$active = get_row_block();

			// Loop over blocks.
			if ( $field['blocks'] ) {
				foreach ( $field['blocks'] as $block ) {

					// Restict to active block if within a have_rows() loop.
					if ( $active && $active !== $block['name'] ) {
						continue;
					}

					// Check sub fields.
					if ( $block['sub_fields'] ) {
						$sub_field = acf_search_fields( $id, $block['sub_fields'] );
						if ( $sub_field ) {
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
		  *  @param   $field - an array holding all the field's data
		  *
		  *  @type    action
		  *  @since   3.6
		  *  @date    23/01/13
		  */

		function render_field( $field ) {
			$default_labels = array(
				'remove'    => __( 'Remove block', 'acf-frontend-form-element' ),
				'add'       => __( 'Add block', 'acf-frontend-form-element' ),
				'duplicate' => __( 'Remove block', 'acf-frontend-form-element' ),
				'collapse'  => __( 'Click to Toggle', 'acf-frontend-form-element' ),
				'button'    => __( 'Add block', 'acf-frontend-form-element' ),
				'no_value'  => __( 'Click the button below to add your first block', 'acf-frontend-form-element' ),
			);

			if ( isset( $field['block_labels'] ) ) {
				$field['block_labels'] = wp_parse_args( $field['block_labels'], $default_labels );
			} else {
				$field['block_labels'] = $default_labels;
			}

			// sort blocks into names
			$blocks = array();

			foreach ( $field['blocks'] as $k => $block ) {

				$blocks[ $block['name'] ] = $block;

			}

			// vars
			$div = array(
				'class'    => 'acf-frontend-blocks',
				'data-min' => $field['min'],
				'data-max' => $field['max'],
			);

			// empty
			if ( empty( $field['value'] ) ) {
				$div['class'] .= ' -empty';
			}

			// no value message
			$no_value_message = $field['block_labels']['no_value'];
			$no_value_message = apply_filters( 'acf/fields/frontend_blocks/no_value_message', $no_value_message, $field );

			?>
<div <?php acf_esc_attr_e( $div ); ?>>
	
			  <?php acf_hidden_input( array( 'name' => $field['name'] ) ); ?>
	
	<div class="no-value-message">
			  <?php echo acf_esc_html( $no_value_message ); ?>
	</div>
	
	<div class="clones">
			  <?php foreach ( $blocks as $block ) : ?>
					<?php $this->render_block( $field, $block, 'acfcloneindex', array() ); ?>
		<?php endforeach; ?>
	</div>
	
	<div class="values">
			  <?php
				if ( ! empty( $field['value'] ) ) :

					foreach ( $field['value'] as $i => $value ) :

						// validate
						if ( empty( $blocks[ $value['fea_block_structure'] ] ) ) {
							continue;
						}

						// render
						$this->render_block( $field, $blocks[ $value['fea_block_structure'] ], $i, $value );

					endforeach;

			  endif;
				?>
	</div>
	
	<div class="acf-actions">
		<a class="acf-button button button-primary" href="#" data-name="add-block"><?php echo acf_esc_html( $field['block_labels']['button'] ); ?></a>
	</div>
	
	<script type="text-html" class="tmpl-popup"><ul>
			  <?php
				foreach ( $blocks as $block ) :

					$atts = array(
						'href'       => '#',
						'data-block' => $block['name'],
						'data-min'   => $block['min'],
						'data-max'   => $block['max'],
					);

					?>
			<li><a <?php acf_esc_attr_e( $atts ); ?>><?php echo acf_esc_html( $block['label'] ); ?></a></li>
					<?php

		  endforeach;
				?>
</ul>
	</script>
	
</div>
			  <?php

		}


		  /*
		  *  render_block
		  *
		  *  description
		  *
		  *  @type    function
		  *  @date    19/11/2013
		  *  @since   5.0.0
		  *
		  *  @param   $post_id (int)
		  *  @return  $post_id (int)
		  */

		function render_block( $field, $block, $i, $value ) {

			// vars
			$order      = 0;
			$el         = 'div';
			$sub_fields = $block['sub_fields'];
			$id         = ( $i === 'acfcloneindex' ) ? 'acfcloneindex' : 'row-' . $i;
			$prefix     = $field['name'] . '[' . $id . ']';

			// div
			$div = array(
				'class'      => 'frontend-block',
				'data-id'    => $id,
				'data-block' => $block['name'],
			);
			// clone
			if ( is_numeric( $i ) ) {
				$div['class'] .= ' -collapsed';
				$order         = $i + 1;
			} else {

				$div['class'] .= ' acf-clone';

			}

			// display
			if ( $block['display'] == 'table' ) {

				$el = 'td';

			}

			// title
			$title = $this->get_block_title( $field, $block, $i, $value );

			// remove row
			reset_rows();

			?>
<div <?php echo acf_esc_attr( $div ); ?>>
			
			  <?php
				acf_hidden_input(
					array(
						'name'  => $prefix . '[fea_block_structure]',
						'value' => $block['name'],
					)
				);
				?>
	
	<div class="acf-frontend-blocks-block-handle" title="<?php esc_attr_e( 'Drag to reorder', 'acf-frontend-form-element' ); ?>" data-name="collapse-block"><?php echo acf_esc_html( $title ); ?></div>
	
	<div class="acf-frontend-blocks-block-controls">
		<a class="acf-icon -plus small light acf-js-tooltip" href="#" data-name="add-block" title="<?php esc_attr_e( $field['block_labels']['add'] ); ?>"></a>
		<a class="acf-icon -duplicate small light acf-js-tooltip" href="#" data-name="duplicate-block" title="<?php esc_attr_e( $field['block_labels']['duplicate'] ); ?>"></a>
		<a class="acf-icon -minus small light acf-js-tooltip" href="#" data-name="remove-block" title="<?php esc_attr_e( $field['block_labels']['remove'] ); ?>"></a>
		<a class="acf-icon -collapse small -clear acf-js-tooltip" href="#" data-name="collapse-block" title="<?php esc_attr_e( $field['block_labels']['collapse'] ); ?>"></a>
	</div>
	
			  <?php if ( ! empty( $sub_fields ) ) : ?>
	
					<?php if ( $block['display'] == 'table' ) : ?>
	<table class="acf-table">
		
		<thead>
			<tr>
						<?php
						foreach ( $sub_fields as $sub_field ) :

							// Set prefix to generate correct "for" attribute on <label>.
							$sub_field['prefix'] = $prefix;

							// Prepare field (allow sub fields to be removed).
							$sub_field = acf_prepare_field( $sub_field );
							if ( ! $sub_field ) {
								continue;
							}

							// Define attrs.
							$attrs              = array();
							$attrs['class']     = 'acf-th';
							$attrs['data-name'] = $sub_field['_name'];
							$attrs['data-type'] = $sub_field['type'];
							$attrs['data-key']  = $sub_field['key'];

							$sub_field['wrapper']['data-id'] = '';
							if ( $sub_field['wrapper']['width'] ) {
								$attrs['data-width'] = $sub_field['wrapper']['width'];
								$attrs['style']      = 'width: ' . $sub_field['wrapper']['width'] . '%;';
							}

							?>
					<th <?php acf_esc_attr_e( $attrs ); ?>>
							<?php acf_render_field_label( $sub_field ); ?>
							<?php acf_render_field_instructions( $sub_field ); ?>
					</th>
						<?php endforeach; ?> 
			</tr>
		</thead>
		
		<tbody>
			<tr class="acf-row">
	<?php else : ?>
	<div class="acf-fields 
		<?php
		if ( $block['display'] == 'row' ) :
			?>
		-left<?php endif; ?>">
	<?php endif; ?>
	
					<?php

					// loop though sub fields
					foreach ( $sub_fields as $sub_field ) {
						if( ! empty( $sub_field['key'] ) ){
							// add value
							if ( isset( $value[ $sub_field['key'] ] ) ) {

								// this is a normal value
								$sub_field['value'] = $value[ $sub_field['key'] ];

							} elseif ( isset( $sub_field['default_value'] ) ) {

								// no value, but this sub field has a default value
								$sub_field['value'] = $sub_field['default_value'];

							}

							// update prefix to allow for nested values
							$sub_field['prefix'] = $prefix;
						}

						// render input
						acf_render_field_wrap( $sub_field, $el );

					}

					?>
			
					<?php if ( $block['display'] == 'table' ) : ?>
			</tr>
		</tbody>
	</table>
	<?php else : ?>
	</div>
	<?php endif; ?>

			<?php endif; ?>

</div>
			  <?php

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

			// bail early if no value
			if ( empty( $value ) || empty( $field['blocks'] ) ) {

				return $value;

			}

			// value must be an array
			$value = acf_get_array( $value );

			// vars
			$rows = array();

			// sort blocks into names
			$blocks = array();
			foreach ( $field['blocks'] as $k => $block ) {

				$blocks[ $block['name'] ] = $block['sub_fields'];

			}

			// loop through rows
			foreach ( $value as $i => $l ) {

				// append to $values
				$rows[ $i ]                        = array();
				$rows[ $i ]['fea_block_structure'] = $l;

				// bail early if block deosnt contain sub fields
				if ( empty( $blocks[ $l ] ) ) {

					continue;

				}

				// get block
				$block = $blocks[ $l ];

				// loop through sub fields
				foreach ( array_keys( $block ) as $j ) {

					// get sub field
					$sub_field = $block[ $j ];

					// bail ealry if no name (tab)
					if ( acf_is_empty( $sub_field['name'] ) ) {
						continue;
					}

					// update full name
					$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";

					// get value
					$sub_value = acf_get_value( $post_id, $sub_field );

					// add value
					$rows[ $i ][ $sub_field['key'] ] = $sub_value;

				}
				// foreach

			}
			// foreach

			// return
			return $rows;

		}


		  /*
		  *  format_value()
		  *
		  *  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
		  *
		  *  @type    filter
		  *  @since   3.6
		  *  @date    23/01/13
		  *
		  *  @param   $value (mixed) the value which was loaded from the database
		  *  @param   $post_id (mixed) the $post_id from which the value was loaded
		  *  @param   $field (array) the field array holding all the field options
		  *
		  *  @return  $value (mixed) the modified value
		  */

		function format_value( $value, $post_id, $field ) {

			// bail early if no value
			if ( empty( $value ) || empty( $field['blocks'] ) ) {

				return false;

			}

			// sort blocks into names
			$blocks = array();
			foreach ( $field['blocks'] as $k => $block ) {

				$blocks[ $block['name'] ] = $block['sub_fields'];

			}

			// loop over rows
			foreach ( array_keys( $value ) as $i ) {

				// get block name
				$l = $value[ $i ]['fea_block_structure'];

				// bail early if block deosnt exist
				if ( empty( $blocks[ $l ] ) ) {
					continue;
				}

				// get block
				$block = $blocks[ $l ];

				// loop through sub fields
				foreach ( array_keys( $block ) as $j ) {

					// get sub field
					$sub_field = $block[ $j ];

					// bail ealry if no name (tab)
					if ( acf_is_empty( $sub_field['name'] ) ) {
						continue;
					}

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
		  *  @type    function
		  *  @date    11/02/2014
		  *  @since   5.0.0
		  *
		  *  @param   $post_id (int)
		  *  @return  $post_id (int)
		  */

		function validate_value( $valid, $value, $field, $input ) {

			// vars
			$count = 0;

			// check if is value (may be empty string)
			if ( is_array( $value ) ) {

				// remove acfcloneindex
				if ( isset( $value['acfcloneindex'] ) ) {
					unset( $value['acfcloneindex'] );
				}

				// count
				$count = count( $value );
			}

			// validate required
			if ( $field['required'] && ! $count ) {
				$valid = false;
			}

			// validate min
			$min = (int) $field['min'];
			if ( $min && $count < $min ) {

				// vars
				$error      = __( 'This field requires at least {min} {label} {identifier}', 'acf-frontend-form-element' );
				$identifier = _n( 'block', 'blocks', $min );

				// replace
				$error = str_replace( '{min}', $min, $error );
				$error = str_replace( '{label}', '', $error );
				$error = str_replace( '{identifier}', $identifier, $error );

				// return
				return $error;
			}

			// find blocks
			$blocks = array();
			foreach ( array_keys( $field['blocks'] ) as $i ) {

				// vars
				$block = $field['blocks'][ $i ];

				// add count
				$block['count'] = 0;

				// append
				$blocks[ $block['name'] ] = $block;
			}

			// validate value
			if ( $count ) {

				// loop rows
				foreach ( $value as $i => $row ) {

					// get block
					$l = $row['fea_block_structure'];

					// bail if block doesn't exist
					if ( ! isset( $blocks[ $l ] ) ) {
						continue;
					}

					// increase count
					$blocks[ $l ]['count']++;

					// bail if no sub fields
					if ( empty( $blocks[ $l ]['sub_fields'] ) ) {
						continue;
					}

					// loop sub fields
					foreach ( $blocks[ $l ]['sub_fields'] as $sub_field ) {

						// get sub field key
						$k = $sub_field['key'];

						// bail if no value
						if ( ! isset( $value[ $i ][ $k ] ) ) {
							continue;
						}

						// validate
						acf_validate_value( $value[ $i ][ $k ], $sub_field, "{$input}[{$i}][{$k}]" );
					}
					// end loop sub fields

				}
				// end loop rows
			}

			// validate blocks
			foreach ( $blocks as $block ) {

				// validate min / max
				$min   = (int) $block['min'];
				$count = $block['count'];
				$label = $block['label'];

				if ( $min && $count < $min ) {

					// vars
					$error      = __( 'This field requires at least {min} {label} {identifier}', 'acf-frontend-form-element' );
					$identifier = _n( 'block', 'blocks', $min );

					// replace
					$error = str_replace( '{min}', $min, $error );
					$error = str_replace( '{label}', '"' . $label . '"', $error );
					$error = str_replace( '{identifier}', $identifier, $error );

					// return
					return $error;
				}
			}

			// return
			return $valid;
		}


		  /**
		   * This function will return a specific block by name from a field
		   *
		   * @date    15/2/17
		   * @since   5.5.8
		   *
		   * @param   string $name
		   * @param   array  $field
		   * @return  array|false
		   */
		function get_block( $name, $field ) {

			// bail early if no blocks
			if ( ! isset( $field['blocks'] ) ) {
				return false;
			}

			// loop
			foreach ( $field['blocks'] as $block ) {

				// match
				if ( $block['name'] === $name ) {
					return $block;
				}
			}

			// return
			return false;

		}


		  /**
		   * This function will delete a value row
		   *
		   * @date    15/2/17
		   * @since   5.5.8
		   *
		   * @param   int   $i
		   * @param   array $field
		   * @param   mixed $post_id
		   * @return  bool
		   */
		function delete_row( $i, $field, $post_id ) {

			// vars
			$value = acf_get_metadata( $post_id, $field['name'] );

			// bail early if no value
			if ( ! is_array( $value ) || ! isset( $value[ $i ] ) ) {
				return false;
			}

			// get block
			$block = $this->get_block( $value[ $i ], $field );

			// bail early if no block
			if ( ! $block || empty( $block['sub_fields'] ) ) {
				return false;
			}

			// loop
			foreach ( $block['sub_fields'] as $sub_field ) {

				// modify name for delete
				$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";

				// delete value
				acf_delete_value( $post_id, $sub_field );

			}

			// return
			return true;

		}


		  /**
		   * This function will update a value row
		   *
		   * @date    15/2/17
		   * @since   5.5.8
		   *
		   * @param   array $row
		   * @param   int   $i
		   * @param   array $field
		   * @param   mixed $post_id
		   * @return  bool
		   */
		function update_row( $row, $i, $field, $post_id ) {

			// bail early if no block reference
			if ( ! is_array( $row ) || ! isset( $row['fea_block_structure'] ) ) {
				return false;
			}

			// get block
			$block = $this->get_block( $row['fea_block_structure'], $field );

			// bail early if no block
			if ( ! $block || empty( $block['sub_fields'] ) ) {
				return false;
			}

			// loop
			foreach ( $block['sub_fields'] as $sub_field ) {

				// value
				$value = null;

				// find value (key)
				if ( isset( $row[ $sub_field['key'] ] ) ) {

					$value = $row[ $sub_field['key'] ];

					// find value (name)
				} elseif ( isset( $row[ $sub_field['name'] ] ) ) {

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
		  *  @type    filter
		  *  @since   3.6
		  *  @date    23/01/13
		  *
		  *  @param   $value - the value which will be saved in the database
		  *  @param   $field - the field array holding all the field options
		  *  @param   $post_id - the $post_id of which the value will be saved
		  *
		  *  @return  $value - the modified value
		  */

		function update_value( $value, $post_id, $field ) {

			// bail early if no blocks
			if ( empty( $field['blocks'] ) ) {
				return $value;
			}

			// vars
			$new_value = array();
			$old_value = acf_get_metadata( $post_id, $field['name'] );
			$old_value = is_array( $old_value ) ? $old_value : array();

			// update
			if ( ! empty( $value ) ) {
				$i = -1;

				// remove acfcloneindex
				if ( isset( $value['acfcloneindex'] ) ) {

					unset( $value['acfcloneindex'] );

				}

				// loop through rows
				foreach ( $value as $row ) {
					$i++;

					// bail early if no block reference
					if ( ! is_array( $row ) || ! isset( $row['fea_block_structure'] ) ) {
						continue;
					}

					// delete old row if block has changed
					if ( isset( $old_value[ $i ] ) && $old_value[ $i ] !== $row['fea_block_structure'] ) {

						$this->delete_row( $i, $field, $post_id );

					}

					// update row
					$this->update_row( $row, $i, $field, $post_id );

					// append to order
					$new_value[] = $row['fea_block_structure'];

				}
			}

			// vars
			$old_count = empty( $old_value ) ? 0 : count( $old_value );
			$new_count = empty( $new_value ) ? 0 : count( $new_value );

			// remove old rows
			if ( $old_count > $new_count ) {

				// loop
				for ( $i = $new_count; $i < $old_count; $i++ ) {

					$this->delete_row( $i, $field, $post_id );

				}
			}

			// save false for empty value
			if ( empty( $new_value ) ) {
				$new_value = '';
			}

			// return
			return $new_value;

		}


		  /*
		  *  delete_value
		  *
		  *  description
		  *
		  *  @type    function
		  *  @date    1/07/2015
		  *  @since   5.2.3
		  *
		  *  @param   $post_id (int)
		  *  @return  $post_id (int)
		  */

		function delete_value( $post_id, $key, $field ) {

			// vars
			$old_value = acf_get_metadata( $post_id, $field['name'] );
			$old_value = is_array( $old_value ) ? $old_value : array();

			// bail early if no rows or no sub fields
			if ( empty( $old_value ) ) {
				return;
			}

			// loop
			foreach ( array_keys( $old_value ) as $i ) {

				$this->delete_row( $i, $field, $post_id );

			}

		}


		  /*
		  *  update_field()
		  *
		  *  This filter is appied to the $field before it is saved to the database
		  *
		  *  @type    filter
		  *  @since   3.6
		  *  @date    23/01/13
		  *
		  *  @param   $field - the field array holding all the field options
		  *  @param   $post_id - the field group ID (post_type = acf)
		  *
		  *  @return  $field - the modified field
		  */

		function update_field( $field ) {

			// loop
			if ( ! empty( $field['blocks'] ) ) {

				foreach ( $field['blocks'] as &$block ) {

					unset( $block['sub_fields'] );

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
		  *  @type    function
		  *  @date    4/04/2014
		  *  @since   5.0.0
		  *
		  *  @param   $post_id (int)
		  *  @return  $post_id (int)
		  */

		function delete_field( $field ) {

			if ( ! empty( $field['blocks'] ) ) {

				// loop through blocks
				foreach ( $field['blocks'] as $block ) {

					// loop through sub fields
					if ( ! empty( $block['sub_fields'] ) ) {

						foreach ( $block['sub_fields'] as $sub_field ) {

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
		  *  @type    filter
		  *  @since   3.6
		  *  @date    23/01/13
		  *
		  *  @param   $field - the field array holding all the field options
		  *
		  *  @return  $field - the modified field
		  */

		function duplicate_field( $field ) {

			// vars
			$sub_fields = array();

			if ( ! empty( $field['blocks'] ) ) {

				// loop through blocks
				foreach ( $field['blocks'] as $block ) {

					// extract sub fields
					$extra = acf_extract_var( $block, 'sub_fields' );

					// merge
					if ( ! empty( $extra ) ) {

						$sub_fields = array_merge( $sub_fields, $extra );

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


		  /*
		  *  ajax_block_title
		  *
		  *  description
		  *
		  *  @type    function
		  *  @date    2/03/2016
		  *  @since   5.3.2
		  *
		  *  @param   $post_id (int)
		  *  @return  $post_id (int)
		  */

		function ajax_block_title() {

			// options
			$options = acf_parse_args(
				$_POST,
				array(
					'post_id'   => 0,
					'i'         => 0,
					'field_key' => '',
					'nonce'     => '',
					'block'     => '',
					'value'     => array(),
				)
			);

			// load field
			$field = acf_get_field( $options['field_key'] );
			if ( ! $field ) {
				die();
			}

			// vars
			$block = $this->get_block( $options['block'], $field );
			if ( ! $block ) {
				die();
			}

			// title
			$title = $this->get_block_title( $field, $block, $options['i'], $options['value'] );

			// echo
			esc_html_e( $title );
			die;

		}


		function get_block_title( $field, $block, $i, $value ) {

			// vars
			$rows       = array();
			$rows[ $i ] = $value;

			// add loop
			acf_add_loop(
				array(
					'selector' => $field['name'],
					'name'     => $field['name'],
					'value'    => $rows,
					'field'    => $field,
					'i'        => $i,
					'post_id'  => 0,
				)
			);

			// vars
			$title = $block['label'];

			// filters
			$title = apply_filters( 'acf/fields/blocks/block_title', $title, $field, $block, $i );
			$title = apply_filters( 'acf/fields/blocks/block_title/name=' . $field['_name'], $title, $field, $block, $i );
			$title = apply_filters( 'acf/fields/blocks/block_title/key=' . $field['key'], $title, $field, $block, $i );

			// remove loop
			acf_remove_loop();

			// prepend order
			$order = is_numeric( $i ) ? $i + 1 : 0;
			$title = '<span class="acf-frontend-blocks-block-order">' . $order . '</span> ' . acf_esc_html( $title );

			// return
			return $title;

		}


		  /*
		  *  clone_any_field
		  *
		  *  This function will update clone field settings based on the origional field
		  *
		  *  @type    function
		  *  @date    28/06/2016
		  *  @since   5.3.8
		  *
		  *  @param   $clone (array)
		  *  @param   $field (array)
		  *  @return  $clone
		  */

		function clone_any_field( $field, $clone_field ) {

			// remove parent_block
			// - allows a sub field to be rendered as a normal field
			unset( $field['parent_block'] );

			// attempt to merger parent_block
			if ( isset( $clone_field['parent_block'] ) ) {

				$field['parent_block'] = $clone_field['parent_block'];

			}

			// return
			return $field;

		}


		  /*
		  *  prepare_field_for_export
		  *
		  *  description
		  *
		  *  @type    function
		  *  @date    11/03/2014
		  *  @since   5.0.0
		  *
		  *  @param   $post_id (int)
		  *  @return  $post_id (int)
		  */

		function prepare_field_for_export( $field ) {

			// loop
			if ( ! empty( $field['blocks'] ) ) {

				foreach ( $field['blocks'] as &$block ) {

					$block['sub_fields'] = acf_prepare_fields_for_export( $block['sub_fields'] );

				}
			}

			// return
			return $field;

		}

		function prepare_any_field_for_export( $field ) {

			// remove parent_block
			unset( $field['parent_block'] );

			// return
			return $field;

		}


		  /*
		  *  prepare_field_for_import
		  *
		  *  description
		  *
		  *  @type    function
		  *  @date    11/03/2014
		  *  @since   5.0.0
		  *
		  *  @param   $post_id (int)
		  *  @return  $post_id (int)
		  */

		function prepare_field_for_import( $field ) {

			// Bail early if no blocks
			if ( empty( $field['blocks'] ) ) {
				return $field;
			}

			// Storage for extracted fields.
			$extra = array();

			// Loop over blocks.
			foreach ( $field['blocks'] as &$block ) {

				// Ensure block is valid.
				$block = $this->get_valid_block( $block );

				// Extract sub fields.
				$sub_fields = acf_extract_var( $block, 'sub_fields' );

				// Modify and append sub fields to $extra.
				if ( $sub_fields ) {
					foreach ( $sub_fields as $i => $sub_field ) {

						// Update atttibutes
						$sub_field['parent']       = $field['key'];
						$sub_field['parent_block'] = $block['key'];
						$sub_field['menu_order']   = $i;

						// Append to extra.
						$extra[] = $sub_field;
					}
				}
			}

			// Merge extra sub fields.
			if ( $extra ) {
				array_unshift( $extra, $field );
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
		  *  @type    function
		  *  @date    30/1/17
		  *  @since   5.5.6
		  *
		  *  @param   $field (array)
		  *  @return  $field
		  */

		function validate_any_field( $field ) {

			// width has changed
			if ( isset( $field['column_width'] ) ) {

				$field['wrapper']['width'] = acf_extract_var( $field, 'column_width' );

			}

			// return
			return $field;

		}


		  /*
		  *  translate_field
		  *
		  *  This function will translate field settings
		  *
		  *  @type    function
		  *  @date    8/03/2016
		  *  @since   5.3.2
		  *
		  *  @param   $field (array)
		  *  @return  $field
		  */

		function translate_field( $field ) {

			// translate
			$field['button_label'] = acf_translate( $field['button_label'] );

			// loop
			if ( ! empty( $field['blocks'] ) ) {

				foreach ( $field['blocks'] as &$block ) {

					$block['label'] = acf_translate( $block['label'] );

				}
			}

			// return
			return $field;

		}

		  /**
		   * Additional validation for the flexible content field when submitted via REST.
		   *
		   * @param bool  $valid
		   * @param int   $value
		   * @param array $field
		   *
		   * @return bool|WP_Error
		   */
		public function validate_rest_value( $valid, $value, $field ) {
			$param = sprintf( '%s[%s]', $field['prefix'], $field['name'] );
			$data  = array(
				'param' => $param,
				'value' => $value,
			);

			if ( ! is_array( $value ) && is_null( $value ) ) {
				$error = sprintf( __( '%s must be of type array or null.', 'acf-frontend-form-element' ), $param );
				return new WP_Error( 'rest_invalid_param', $error, $param );
			}

			$blocks_to_update = array_count_values( array_column( $value, 'fea_block_structure' ) );

			foreach ( $field['blocks'] as $block ) {
				$num_blocks = isset( $blocks_to_update[ $block['name'] ] ) ? $blocks_to_update[ $block['name'] ] : 0;

				if ( '' !== $block['min'] && $num_blocks < (int) $block['min'] ) {
					$error = sprintf(
						_n(
							'%1$s must contain at least %2$s %3$s block.',
							'%1$s must contain at least %2$s %3$s blocks.',
							$block['min'],
							'acf-frontend-form-element'
						),
						$param,
						number_format_i18n( $block['min'] ),
						$block['name']
					);

					return new WP_Error( 'rest_invalid_param', $error, $data );
				}

				if ( '' !== $block['max'] && $num_blocks > (int) $block['max'] ) {
					$error = sprintf(
						_n(
							'%1$s must contain at most %2$s %3$s block.',
							'%1$s must contain at most %2$s %3$s blocks.',
							$block['max'],
							'acf-frontend-form-element'
						),
						$param,
						number_format_i18n( $block['max'] ),
						$block['name']
					);

					return new WP_Error( 'rest_invalid_param', $error, $data );
				}
			}

			return $valid;
		}

		  /**
		   * Return the schema array for the REST API.
		   *
		   * @param array $field
		   * @return array
		   */
		public function get_rest_schema( array $field ) {
			$schema = array(
				'type'     => array( 'array', 'null' ),
				'required' => ! empty( $field['required'] ),
				'items'    => array(
					'oneOf' => array(),
				),
			);

			// Loop through blocks building up a schema for each.
			foreach ( $field['blocks'] as $block ) {
				$block_schema = array(
					'type'       => 'object',
					'properties' => array(
						'fea_block_structure' => array(
							'type'     => 'string',
							'required' => true,
							// By using a pattern match against the block name, data sent in must match an available
							// block on the flexible field. If it doesn't, a 400 Bad Request response will result.
							'pattern'  => '^' . $block['name'] . '$',
						),
					),
				);

				foreach ( $block['sub_fields'] as $sub_field ) {
					if ( $sub_field_schema = acf_get_field_rest_schema( $sub_field ) ) {
						$block_schema['properties'][ $sub_field['name'] ] = $sub_field_schema;
					}
				}

				$schema['items']['oneOf'][] = $block_schema;
			}

			if ( ! empty( $field['min'] ) ) {
				$schema['minItems'] = (int) $field['min'];
			}

			if ( ! empty( $field['max'] ) ) {
				$schema['maxItems'] = (int) $field['max'];
			}

			return $schema;
		}

		  /**
		   * Apply basic formatting to prepare the value for default REST output.
		   *
		   * @param mixed      $value
		   * @param int|string $post_id
		   * @param array      $field
		   * @return array|mixed
		   */
		public function format_value_for_rest( $value, $post_id, array $field ) {
			if ( empty( $value ) || ! is_array( $value ) || empty( $field['blocks'] ) ) {
				return null;
			}

			// Create a map of block sub fields mapped to block names.
			foreach ( $field['blocks'] as $block ) {
				$blocks[ $block['name'] ] = $block['sub_fields'];
			}

			// Loop through each block and within that, each sub field to process sub fields individually.
			foreach ( $value as &$block ) {
				$name = $block['fea_block_structure'];

				if ( empty( $blocks[ $name ] ) ) {
					continue;
				}

				foreach ( $blocks[ $name ] as $sub_field ) {

					// Bail early if the field has no name (tab).
					if ( acf_is_empty( $sub_field['name'] ) ) {
						continue;
					}

					// Extract the sub field 'field_key'=>'value' pair from the $block and format it.
					$sub_value = acf_extract_var( $block, $sub_field['key'] );
					$sub_value = acf_format_value_for_rest( $sub_value, $post_id, $sub_field );

					// Add the sub field value back to the $block but mapped to the field name instead
					// of the key reference.
					$block[ $sub_field['name'] ] = $sub_value;
				}
			}

			return $value;
		}

	}




endif; // class_exists check

?>
