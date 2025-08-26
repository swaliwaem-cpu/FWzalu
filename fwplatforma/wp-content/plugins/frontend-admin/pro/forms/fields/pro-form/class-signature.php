<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'signature' ) ) :
	class signature extends Field_Base {



		/*
		*  __construct
		*
		*  This function will setup the field type data
		*
		*  @type    function
		*  @date    5/03/2014
		*  @since    5.0.0
		*
		*  @param    n/a
		*  @return    n/a
		*/

		function initialize() {
			$this->name     = 'form_signature';
			$this->label    = __( 'Signature', 'acf-frontend-form-element' );
			$this->category = __( 'Form', 'acf-frontend-form-element' );
			$this->defaults = array();
			$this->l10n     = array(
				'error' => __( 'Please sign below', 'acf-frontend-form-element' ),
			);

		}


		/*
		*  render_field_settings()
		*
		*  Create extra settings for your field. These are visible when editing a field
		*
		*  @type    action
		*  @since    3.6
		*  @date    23/01/13
		*
		*  @param    $field (array) the $field being edited
		*  @return    n/a
		*/

		function render_field_settings( $field ) {
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Clear Button Text', 'acf-frontend-form-element' ),
					'instructions' => __( 'Set the text of the Clear Signature button', 'acf-frontend-form-element' ),
					'type'         => 'text',
					'name'         => 'clear_text',
					'placeholder'  => __( 'Clear', 'acf-frontend-form-element' ),
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Font Size', 'acf-frontend-form-element' ),
					'instructions' => __( 'Set the font size of the signature', 'acf-frontend-form-element' ),
					'type'         => 'number',
					'name'         => 'font_size',
					'prepend'      => 'px',
				)
			);

		}



		/*
		*  render_field()
		*
		*  Create the HTML interface for your field
		*
		*  @param    $field (array) the $field being rendered
		*
		*  @type    action
		*  @since    3.6
		*  @date    23/01/13
		*
		*  @param    $field (array) the $field being edited
		*  @return    n/a
		*/

		function render_field( $field ) {
			if ( isset( $field['value']['src'] ) ) {
				$src = $field['value']['src'];
				$id  = $field['value']['id'];
			} else {
				$src = '';
				$id  = $field['value']['id'];
			}
			/*
			*  Create a simple text input using the 'font_size' setting.
			*/
			if ( ! empty( $field['clear_text'] ) ) {
				$clear_text = $field['clear_text'];
			} else {
				$clear_text = __( 'Clear', 'acf-frontend-form-element' );
			}

			?>
			<div class="acf-input-wrap">
				<div id="signature-pad" class="fea-signature-pad">
					<input type="hidden" class="signature-source" name="<?php echo esc_attr( $field['name'] ) . '[src]'; ?>" value="<?php echo $src; ?>" />
					<input type="hidden" class="signature-id" name="<?php echo esc_attr( $field['name'] ) . '[id]'; ?>" value="<?php esc_attr_e( $id ); ?>" />
					<input type="hidden" class="signature-changed" name="<?php echo esc_attr( $field['name'] ) . '[changed]'; ?>" value />

					<div class="fea-signature-pad--body">
						<canvas></canvas>
					</div>
					<div class="fea-signature-pad--footer">
						<a href="#clear" class="fea-signature-pad--clear btn btn-default btn-xs button button-small" data-action="clear"><?php esc_html_e( $clear_text ); ?></a>
					</div>
				</div>
			</div>
			<?php
		}


		/*
		*  input_admin_enqueue_scripts()
		*
		*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
		*  Use this action to add CSS + JavaScript to assist your render_field() action.
		*
		*  @type    action (admin_enqueue_scripts)
		*  @since    3.6
		*  @date    23/01/13
		*
		*  @param    n/a
		*  @return    n/a
		*/

		function input_admin_enqueue_scripts() {
			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';
			// register & include JS
			wp_register_script( 'fea-signature-field', FEA_URL . 'pro/assets/js/signature_pad' . $min . '.js', array( 'acf-input' ), FEA_VERSION );
			wp_enqueue_script( 'fea-signature-field' );

		}


		/*
		*  load_value()
		*
		*  This filter is applied to the $value after it is loaded from the db
		*
		*  @type    filter
		*  @since    3.6
		*  @date    23/01/13
		*
		*  @param    $value (mixed) the value found in the database
		*  @param    $post_id (mixed) the $post_id from which the value was loaded
		*  @param    $field (array) the field array holding all the field options
		*  @return    $value
		*/

		function load_value( $value, $post_id, $field ) {
			$data = array( 'id' => $value );
			// apply setting
			if ( $value && is_string( $value ) ) {
				$val = explode( '/signatures/', $value );
				if ( isset( $val[1] ) ) {
					$value = $val[1];
					$value = str_replace( '.png', '', $value );
				}
				$upload     = wp_upload_dir();
				$upload_dir = $upload['basedir'];
				$upload_dir = $upload_dir . '/signatures/';
				if ( ! file_exists( $upload_dir . $value . '.png' ) ) {
					return $data;
				}

				$src  = $this->get_signature( $value );
				$data = array(
					'src' => $src,
					'id'  => $value,
				);
			}
			return $data;

		}


		/*
		*  format_value()
		*
		*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
		*
		*  @type    filter
		*  @since    3.6
		*  @date    23/01/13
		*
		*  @param    $value (mixed) the value which was loaded from the database
		*  @param    $post_id (mixed) the $post_id from which the value was loaded
		*  @param    $field (array) the field array holding all the field options
		*
		*  @return    $value (mixed) the modified value
		*/


		function format_value( $value, $post_id, $field ) {
			 // bail early if no value
			if ( empty( $value ) ) {
				return $value;
			}
			// apply setting
			if ( $value && is_string( $value ) ) {
				$value = $this->load_value( $value, $post_id, $field );
			}

			if ( isset( $value['src'] ) ) {
				$data = $this->get_signature( $value['id'] );
				if ( $data ) {
					$value = '<img width="200" src="' . $data . '" alt="' . $field['label'] . '" />';
				}
			} else {
				$value = '';
			}
			// return
			return $value;
		}

		/*
		*  get_signature()
		*
		*  Gets the protected signature file via the file id stored in the database
		*
		*  @type    filter
		*  @since    3.6
		*  @date    23/01/13
		*
		*  @param    $id (mixed) the id of the signature file stored in the database
		*
		*  @return    $data (mixed) the base64 string of the file or false if no such file exists
		*/
		function get_signature( $id ) {
			 $upload        = wp_upload_dir();
			$upload_dir     = $upload['basedir'];
			$signature_path = $upload_dir . '/signatures/' . $id . '.png';

			if ( ! file_exists( $signature_path ) ) {
				return false;
			}

			
			$data = file_get_contents( $signature_path );

			if ( $data ) {
				return 'data:image/png;base64,' . base64_encode( $data );
			}

			return false;
		}


		/*
		*  validate_value()
		*
		*  This filter is applied before the form is submitted and saved in the database
		*
		*  @type    filter
		*  @since    3.6
		*  @date    23/01/13
		*
		*  @param    $is_valid (mixed) whether or not the value is valid
		*  @param    $value (mixed) the value which was submitted in this field
		*  @param    $field (array) the field array holding all the field options
		*  @param    $input (array) the input tag name attribute
		*
		*  @return    $value (mixed) A string message if there is an error or true/false
		*/

		function validate_value( $is_valid, $value, $field, $input ) {
			if ( empty( $value['src'] ) && ! empty( $field['required'] ) ) {
				return __( 'Please sign below', 'acf-frontend-form-element' );
			}

			return $is_valid;

		}

	}


	// create field
	
endif;

?>
