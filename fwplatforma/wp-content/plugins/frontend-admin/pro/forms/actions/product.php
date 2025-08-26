<?php
namespace Frontend_Admin\Actions;

use Frontend_Admin\Plugin;
use Frontend_Admin\Classes\ActionBase;
use Frontend_Admin\Forms\Actions;
use Elementor\Controls_Manager;
use ElementorPro\Modules\QueryControl\Module as Query_Module;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'ActionProduct' ) ) :

	class ActionProduct extends ActionBase {


		public function get_name() {
			return 'product';
		}

		public function get_label() {
			return __( 'Product', 'acf-frontend-form-element' );
		}


		public function get_fields_display( $form_field, $local_field, $element = '', $sub_fields = false, $saving = false ) {
			$field_appearance = isset( $form_field['field_taxonomy_appearance'] ) ? $form_field['field_taxonomy_appearance'] : 'checkbox';
			$field_add_term   = isset( $form_field['field_add_term'] ) ? $form_field['field_add_term'] : 0;
			switch ( $form_field['field_type'] ) {
				case 'price':
					$local_field['type'] = 'product_price';
					break;
				case 'sale_price':
					$local_field['type'] = 'product_sale_price';
					break;
				case 'description':
					$local_field['type']       = 'product_description';
					$local_field['field_type'] = isset( $form_field['editor_type'] ) ? $form_field['editor_type'] : 'wysiwyg';
					break;
				case 'main_image':
					$local_field['type']          = 'main_image';
					$local_field['default_value'] = empty( $form_field['default_featured_image']['id'] ) ? '' : $form_field['default_featured_image']['id'];
					break;
				case 'images':
					$local_field['type'] = 'product_images';
					break;
				case 'short_description':
					$local_field['type'] = 'product_short_description';
					break;
				case 'product_categories':
					$local_field['type']            = 'related_terms';
					$local_field['taxonomy']        = 'product_cat';
					$local_field['field_type']      = $field_appearance;
					$local_field['allow_null']      = 0;
					$local_field['add_term']        = $field_add_term;
					$local_field['load_post_terms'] = 1;
					$local_field['save_terms']      = 1;
					$local_field['custom_taxonomy'] = true;
					break;
				case 'product_tags':
					$local_field['type']            = 'related_terms';
					$local_field['taxonomy']        = 'product_tag';
					$local_field['field_type']      = $field_appearance;
					$local_field['allow_null']      = 0;
					$local_field['add_term']        = $field_add_term;
					$local_field['load_post_terms'] = 1;
					$local_field['save_terms']      = 1;
					$local_field['custom_taxonomy'] = true;
					break;
				case 'tax_class':
					$local_field['type'] = 'product_tax_class';
					break;
				case 'tax_status':
					$local_field['type'] = 'product_tax_status';
					break;
				case 'product_type':
					$local_field['type']          = 'product_types';
					$local_field['default_value'] = isset( $form_field['default_product_type'] ) ? $form_field['default_product_type'] : 'simple';
					$local_field['field_type']    = isset( $form_field['role_appearance'] ) ? $form_field['role_appearance'] : 'radio';
					$local_field['layout']        = isset( $form_field['role_radio_layout'] ) ? $form_field['role_radio_layout'] : 'vertical';
					break;
				case 'is_virtual':
				case 'is_downloadable':
				case 'manage_stock':
				case 'product_enable_reviews':
					$local_field['type']        = $form_field['field_type'];
					$local_field['ui_on_text']  = isset( $form_field['ui_on'] ) ? $form_field['ui_on'] : 'Yes';
					$local_field['ui_off_text'] = isset( $form_field['ui_off'] ) ? $form_field['ui_off'] : 'No';
					break;
				case 'attributes':
					$form_field = feadmin_parse_args(
						$form_field,
						array(
							'button_text'      => '',
							'save_button_text' => '',
							'no_value_msg'     => '',
						)
					);

					if ( is_array( $sub_fields ) ) {
						 $sub_settings = array(
							 'field_label_on' => 0,
							 'label'          => '',
							 'instructions'   => '',
							 'placeholder'    => '',
							 'products_page'  => '',
							 'for_variations' => '',
							 'button_label'   => '',
						 );
						 foreach ( $sub_fields as $i => $sub_field ) {
							 $sub_fields[ $i ] = feadmin_parse_args( $sub_fields[ $i ], $sub_settings );
						 }
					}
					$local_field['type']            = 'product_attributes';
					$local_field['button_label']    = $form_field['button_text'];
					$local_field['save_text']       = $form_field['save_button_text'];
					$local_field['no_value_msg']    = $form_field['no_value_msg'];
					$local_field['fields_settings'] = array(
						'name'         => array(
							'field_label_hide' => ! $sub_fields[0]['field_label_on'],
							'label'            => $sub_fields[0]['label'],
							'placeholder'      => $sub_fields[0]['placeholder'],
							'instructions'     => $sub_fields[0]['instructions'],
						),
						'locations'    => array(
							'field_label_hide' => ! $sub_fields[1]['field_label_on'],
							'label'            => $sub_fields[1]['label'],
							'instructions'     => $sub_fields[1]['instructions'],
							'choices'          => array(
								'products_page'  => $sub_fields[1]['products_page'],
								'for_variations' => $sub_fields[1]['for_variations'],
							),
						),
						'custom_terms' => array(
							'field_label_hide' => ! $sub_fields[2]['field_label_on'],
							'label'            => $sub_fields[2]['label'],
							'instructions'     => $sub_fields[2]['instructions'],
							'button_label'     => $sub_fields[2]['button_label'],
						),
						'terms'        => array(
							'field_label_hide' => ! $sub_fields[3]['field_label_on'],
							'label'            => $sub_fields[3]['label'],
							'instructions'     => $sub_fields[3]['instructions'],
							'button_label'     => $sub_fields[3]['button_label'],
						),
					);
					break;
				case 'variations':
					$form_field                     = feadmin_parse_args(
						$form_field,
						array(
							'button_text'      => '',
							'save_button_text' => '',
							'no_value_msg'     => '',
							'no_attrs_msg'     => '',
						)
					);
					$local_field['type']            = 'product_variations';
					$local_field['button_label']    = $form_field['button_text'];
					$local_field['save_text']       = $form_field['save_button_text'];
					$local_field['no_value_msg']    = $form_field['no_value_msg'];
					$local_field['no_attrs_msg']    = $form_field['no_attrs_msg'];
					$local_field['fields_settings'] = $sub_fields;
					break;
				case 'grouped_products':
					$group_field         = true;
					$local_field['type'] = 'product_grouped';
					break;
				case 'upsells':
					$group_field         = true;
					$local_field['type'] = 'product_upsells';
					break;
				case 'cross_sells':
					$group_field         = true;
					$local_field['type'] = 'product_cross_sells';
					break;
				case 'sku':
					$local_field['type'] = 'product_sku';
					break;
				case 'allow_backorders':
					$local_field['type']       = 'allow_backorders';
					$local_field['choices']    = array(
						'no'     => isset( $form_field['do_not_allow'] ) ? $form_field['do_not_allow'] : __( 'Do not allow', 'woocommerce' ),
						'notify' => isset( $form_field['notify'] ) ? $form_field['notify'] : __( 'Notify', 'woocommerce' ),
						'yes'    => isset( $form_field['allow'] ) ? $form_field['allow'] : __( 'Allow', 'woocommerce' ),
					);
					$local_field['field_type'] = isset( $form_field['role_appearance'] ) ? $form_field['role_appearance'] : 'radio';
					$local_field['layout']     = isset( $form_field['role_radio_layout'] ) ? $form_field['role_radio_layout'] : 'vertical';
					break;
				case 'stock_status':
					$local_field['type']       = 'stock_status';
					$local_field['choices']    = array(
						'instock'     => isset( $form_field['instock'] ) ? $form_field['instock'] : __( 'In stock', 'woocommerce' ),
						'outofstock'  => isset( $form_field['outofstock'] ) ? $form_field['outofstock'] : __( 'Out of stock', 'woocommerce' ),
						'onbackorder' => isset( $form_field['backorder'] ) ? $form_field['backorder'] : __( 'On backorder', 'woocommerce' ),
					);
					$local_field['field_type'] = isset( $form_field['role_appearance'] ) ? $form_field['role_appearance'] : 'radio';
					$local_field['layout']     = isset( $form_field['role_radio_layout'] ) ? $form_field['role_radio_layout'] : 'vertical';
					break;
				case 'sold_individually':
					$local_field['type']        = 'sold_individually';
					$local_field['ui']          = 1;
					$local_field['ui_on_text']  = isset( $form_field['ui_on'] ) ? $form_field['ui_on'] : 'Yes';
					$local_field['ui_off_text'] = isset( $form_field['ui_off'] ) ? $form_field['ui_off'] : 'No';
					break;
				default:
					$local_field['type'] = $form_field['field_type'];
			}

			if ( isset( $group_field ) ) {
				if ( ! empty( $form_field['add_edit_product'] ) ) {
					$local_field['add_edit_post'] = 1;
					if ( ! empty( $form_field['add_product_text'] ) ) {
						   $local_field['add_post_button'] = $form_field['add_product_text'];
					}
				} else {
					$local_field['add_edit_post'] = 0;
				}

				if ( ! empty( $form_field['product_authors_to_filter'] ) ) {
					$user_ids                   = str_replace( array( '[', ']' ), '', $form_field['product_authors_to_filter'] );
					$local_field['post_author'] = explode( ',', $user_ids );
				} else {
					$local_field['post_author'] = array();
				}
			}

			return $local_field;
		}


		public function get_default_fields( $form, $action = '' ) {
			switch ( $action ) {
				case 'delete':
					$default_fields = array(
						'delete_product',
					);
					break;
				case 'status':
					$default_fields = array(
						'product_status',
						'submit_button',
					);
					break;
				case 'new':
					$default_fields = array(
						'product_title',
						'product_price',
						'product_description',
						'product_short_description',
						'main_image',
						'product_images',
						'product_status',
						'submit_button',
					);
					break;
				default:
					$default_fields = array(
						'product_to_edit',
						'product_title',
						'product_price',
						'product_short_description',
						'main_image',
						'product_images',
						'product_status',
						'submit_button',
					);
			}
			return $this->get_valid_defaults( $default_fields, $form );
		}

		public function get_form_builder_options( $form ) {
			 return array(
				 array(
					 'key'               => 'save_to_product',
					 'field_label_hide'  => 0,
					 'type'              => 'select',
					 'instructions'      => __( 'If there is a "Product to Edit" field in the form, these settings will be overwritten.', 'acf-frontend-form-element' ),
					 'required'          => 0,
					 'conditional_logic' => 0,
					 'choices'           => array(
						 'edit_product'      => __( 'Edit Product', 'acf-frontend-form-element' ),
						 'new_product'       => __( 'New Product', 'acf-frontend-form-element' ),
						 'duplicate_product' => __( 'Duplicate Product', 'acf-frontend-form-element' ),
					 ),
					 'allow_null'        => 0,
					 'multiple'          => 0,
					 'ui'                => 0,
					 'return_format'     => 'value',
					 'ajax'              => 0,
					 'placeholder'       => '',
				 ),
				 array(
					 'key'               => 'product_to_edit',
					 'label'             => __( 'Product', 'acf-frontend-form-element' ),
					 'type'              => 'select',
					 'instructions'      => '',
					 'required'          => 0,
					 'conditional_logic' => array(
						 array(
							 array(
								 'field'    => 'save_to_product',
								 'operator' => '!=',
								 'value'    => 'new_product',
							 ),
						 ),
					 ),
					 'choices'           => array(
						 'current_product' => __( 'Current Product', 'acf-frontend-form-element' ),
						 'url_query'       => __( 'URL Query', 'acf-frontend-form-element' ),
						 'select_product'  => __( 'Specific Product', 'acf-frontend-form-element' ),
					 ),
					 'default_value'     => false,
					 'allow_null'        => 0,
					 'multiple'          => 0,
					 'ui'                => 0,
					 'return_format'     => 'value',
					 'ajax'              => 0,
					 'placeholder'       => '',
				 ),
				 array(
					 'key'               => 'url_query_product',
					 'label'             => __( 'URL Query Key', 'acf-frontend-form-element' ),
					 'type'              => 'text',
					 'instructions'      => '',
					 'required'          => 0,
					 'conditional_logic' => array(
						 array(
							 array(
								 'field'    => 'save_to_product',
								 'operator' => '!=',
								 'value'    => 'new_product',
							 ),
							 array(
								 'field'    => 'product_to_edit',
								 'operator' => '==',
								 'value'    => 'url_query',
							 ),
						 ),
					 ),
					 'placeholder'       => '',
				 ),
				 array(
					 'key'               => 'select_product',
					 'label'             => __( 'Specific Product', 'acf-frontend-form-element' ),
					 'name'              => 'select_product',
					 'prefix'            => 'form',
					 'type'              => 'post_object',
					 'instructions'      => '',
					 'required'          => 0,
					 'conditional_logic' => array(
						 array(
							 array(
								 'field'    => 'save_to_product',
								 'operator' => '!=',
								 'value'    => 'new_product',
							 ),
							 array(
								 'field'    => 'product_to_edit',
								 'operator' => '==',
								 'value'    => 'select_product',
							 ),
						 ),
					 ),
					 'post_type'         => 'product',
					 'taxonomy'          => '',
					 'allow_null'        => 0,
					 'multiple'          => 0,
					 'return_format'     => 'object',
					 'ui'                => 1,
				 ),
			 );
		}

		public function load_data( $form, $objects ) {
			if ( empty( $form['save_to_product'] ) ) {
				return $form;
			}

			switch ( $form['save_to_product'] ) {
				case 'new_product':
					if ( isset( $objects['product'] ) && feadmin_can_edit_post( $objects['product'], $form ) ) {
						$form['product_id']      = $objects['product'];
						$form['save_to_product'] = 'edit_product';
					} else {
						$status = 'no_change';
						if ( isset( $form['new_product_status'] ) ) {
							$status = $form['new_product_status'];
						}
						if ( $status == 'no_change' ) {
							$status = 'publish';
						}

						$form['product_id'] = 'add_product';

						if ( ! empty( $form['new_product_terms'] ) ) {
							if ( $form['new_product_terms'] == 'select_terms' ) {
								$form['product_terms'] = $form['new_product_terms_select'];
							}
							if ( $form['new_product_terms'] == 'current_term' ) {
								$form['product_terms'] = get_queried_object()->term_id;
							}
						}
					}
					break;
				case 'edit_product':
				case 'duplicate_product':
				case 'delete_product':
					if ( empty( $form['product_to_edit'] ) ) {
						$form['product_to_edit'] = 'current_product';
					}

					global $post;
					if ( $form['product_to_edit'] == 'select_product' ) {
						if ( ! empty( $form['select_product'] ) ) {
							$form['product_id'] = $form['select_product'];
						} else {
							if ( isset( $form['product_select'] ) ) {
								$form['product_id'] = $form['product_select'];
							}
						}
					}
					if ( $form['product_to_edit'] == 'url_query' ) {
						if ( isset( $_GET[ $form['url_query_product'] ] ) ) {
							$form['product_id'] = absint( $_GET[ $form['url_query_product'] ] );
						}
					}
					if ( $form['product_to_edit'] == 'current_product' && get_post_type( $post ) == 'product' ) {
						$form['product_id'] = $post->ID;
					}

					if ( empty( $form['product_id'] ) || ! get_post_status( $form['product_id'] ) ) {
						$form['product_id'] = 'none';
					}
					break;
			}
			return $form;
		}

		public function register_settings_section( $widget ) {
			$widget->start_controls_section(
				'section_edit_product',
				array(
					'label'     => $this->get_label(),
					'tab'       => Controls_Manager::TAB_CONTENT,
					'condition' => array(
						'admin_forms_select' => '',
					),
				)
			);
			$this->action_controls( $widget );

			$widget->add_control(
				'delete_button_deprecated_product',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => __( 'The delete button option is now a different widget. Search for the "Trash Button"', 'acf-frontend-form-element' ),
					'content_classes' => 'acf-fields-note',
				)
			);

			$widget->end_controls_section();

		}


		public function action_controls( $widget, $step = false, $type = '' ) {
			if ( ! empty( $widget->form_defaults['save_to_product'] ) ) {
				$type = $widget->form_defaults['save_to_product'];
			}
			if ( $step ) {
				$condition = array(
					'field_type'         => 'step',
					'overwrite_settings' => 'true',
				);
			}
			$args = array(
				'label'   => __( 'Product', 'acf-frontend-form-element' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'edit_product'      => __( 'Edit Product', 'acf-frontend-form-element' ),
					'new_product'       => __( 'New Product', 'acf-frontend-form-element' ),
					'duplicate_product' => __( 'Duplicate Product', 'acf-frontend-form-element' ),
				),
				'default' => $widget->get_name(),
			);
			if ( $step ) {
				$condition         = array(
					'field_type'         => 'step',
					'overwrite_settings' => 'true',
				);
				$args['condition'] = $condition;
			} else {
				$condition = array();
			}
			if ( $type ) {
				$args = array(
					'type'    => Controls_Manager::HIDDEN,
					'default' => $type,
				);
			}

			$widget->add_control( 'save_to_product', $args );
			$condition['save_to_product'] = array( 'edit_product', 'new_product', 'duplicate_product' );

			$condition['save_to_product'] = array( 'edit_product', 'duplicate_product', 'delete_product' );

			$widget->add_control(
				'product_to_edit',
				array(
					'label'     => __( 'Specific Product', 'acf-frontend-form-element' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'current_product',
					'options'   => array(
						'current_product' => __( 'Current Product', 'acf-frontend-form-element' ),
						'url_query'       => __( 'Url Query', 'acf-frontend-form-element' ),
						'select_product'  => __( 'Specific Product', 'acf-frontend-form-element' ),
					),
					'condition' => $condition,
				)
			);
			$condition['product_to_edit'] = 'url_query';
			$widget->add_control(
				'url_query_product',
				array(
					'label'       => __( 'URL Query', 'acf-frontend-form-element' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => __( 'product_id', 'acf-frontend-form-element' ),
					'default'     => __( 'product_id', 'acf-frontend-form-element' ),
					'required'    => true,
					'description' => __( 'Enter the URL query parameter containing the id of the product you want to edit', 'acf-frontend-form-element' ),
					'condition'   => $condition,
				)
			);
			$condition['product_to_edit'] = 'select_product';
			$widget->add_control(
				'product_select',
				array(
					'label'       => __( 'Product', 'acf-frontend-form-element' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => __( '18', 'acf-frontend-form-element' ),
					'description' => __( 'Enter the product ID', 'acf-frontend-form-element' ),
					'condition'   => $condition,
				)
			);

			unset( $condition['product_to_edit'] );

			$condition['save_to_product'] = 'new_product';

			$widget->add_control(
				'new_product_terms',
				array(
					'label'       => __( 'New Product Terms', 'acf-frontend-form-element' ),
					'type'        => Controls_Manager::SELECT2,
					'label_block' => true,
					'default'     => 'product',
					'options'     => array(
						'current_term' => __( 'Current Term', 'acf-frontend-form-element' ),
						'select_terms' => __( 'Specific Term', 'acf-frontend-form-element' ),
					),
					'condition'   => $condition,
				)
			);
			$condition['new_product_terms'] = 'select_terms';
			if ( ! class_exists( 'ElementorPro\Modules\QueryControl\Module' ) ) {
				$widget->add_control(
					'new_product_terms_select',
					array(
						'label'       => __( 'Terms', 'acf-frontend-form-element' ),
						'type'        => Controls_Manager::TEXT,
						'placeholder' => __( '18, 12, 11', 'acf-frontend-form-element' ),
						'description' => __( 'Enter the a comma-seperated list of term ids', 'acf-frontend-form-element' ),
						'condition'   => $condition,
					)
				);
			} else {
				$widget->add_control(
					'new_product_terms_select',
					array(
						'label'        => __( 'Terms', 'acf-frontend-form-element' ),
						'type'         => Query_Module::QUERY_CONTROL_ID,
						'label_block'  => true,
						'autocomplete' => array(
							'object'  => Query_Module::QUERY_OBJECT_TAX,
							'display' => 'detailed',
						),
						'multiple'     => true,
						'condition'    => $condition,
					)
				);
			}
			unset( $condition['save_to_product'] );
			unset( $condition['new_product_terms'] );
			$widget->add_control(
				'new_product_status',
				array(
					'label'       => __( 'Product Status', 'acf-frontend-form-element' ),
					'type'        => Controls_Manager::SELECT2,
					'label_block' => true,
					'default'     => 'no_change',
					'options'     => array(
						'draft'   => __( 'Draft', 'acf-frontend-form-element' ),
						'private' => __( 'Private', 'acf-frontend-form-element' ),
						'pending' => __( 'Pending Review', 'acf-frontend-form-element' ),
						'publish' => __( 'Published', 'acf-frontend-form-element' ),
					),
					'condition'   => $condition,
				)
			);
		}


		public function get_core_fields() {
			 return array(
				 'product_title'             => 'post_title',
				 'product_slug'              => 'post_name',
				 'product_description'       => 'post_content',
				 'product_short_description' => 'post_excerpt',
				 'product_date'              => 'post_date',
				 'product_author'            => 'post_author',
				 'product_menu_order'        => 'menu_order',
				 'product_allow_comments'    => 'allow_comments',
			 );
		}

		public function run( $form ) {
			$record = $form['record'];
			if ( empty( $record['product'] ) || empty( $record['fields']['woo_product'] ) ) {
				return $form;
			}

			$product_id = sanitize_text_field( $record['product'] );

			// allow for custom save
			$product_id = apply_filters( 'acf/pre_save_product', $product_id, $form );

			switch ( $form['save_to_product'] ) {
				case 'edit_product':
					$product_to_edit['ID'] = $product_id;
					break;
				case 'new_product':
					$product_to_edit['ID']        = 0;
					$product_to_edit['post_type'] = 'product';
					break;
				case 'duplicate_product':
					$product_to_duplicate           = get_post( $product_id );
					$product_to_edit                = get_object_vars( $product_to_duplicate );
					$product_to_edit['ID']          = 0;
					$product_to_edit['post_author'] = get_current_user_id();
					break;
				default:
					return $form;
			}

			$core_fields  = $this->get_core_fields();
			$product_type = 'simple';

			if ( ! empty( $record['fields']['woo_product'] ) ) {
				foreach ( $record['fields']['woo_product'] as $name => $_field ) {
					if ( ! isset( $_field['key'] ) ) {
						continue;
					}
					$field = acf_get_field( $_field['key'] );

					if ( ! $field ) {
						continue;
					}

					$field_type      = $field['type'];
					$field['value']  = $_field['_input'];
					$field['_input'] = $_field['_input'];

					if ( ! in_array( $field_type, array_keys( $core_fields ) ) ) {
						if ( $field_type == 'product_types' ) {
							$product_type = $field['value'];
							$pt_field     = $field;
						} else {
							$metas[ $field['key'] ] = $field;
						}
						continue;
					}

					$product_to_edit[ $core_fields[ $field_type ] ] = $field['value'];
				}
			}

			if ( $form['save_to_product'] == 'duplicate_product' ) {
				if ( $product_to_edit['post_name'] == $product_to_duplicate->post_name ) {
					$product_name = sanitize_title( $product_to_edit['post_title'] );
					if ( ! feadmin_slug_exists( $product_name ) ) {
						   $product_to_edit['post_name'] = $product_name;
					} else {
						 $product_to_edit['post_name'] = feadmin_duplicate_slug( $product_to_duplicate->post_name );
					}
				}
			}

			if ( isset( $record['status'] ) && $record['status'] == 'draft' ) {
				$product_to_edit['post_status'] = 'draft';
			} else {
				$status = 'no_change';
				if ( isset( $form['new_product_status'] ) ) {
					$status = $form['new_product_status'];
				}

				if ( ! empty( $current_step['overwrite_settings'] ) ) {
					$status = $current_step['new_product_status'];
				}

				if ( $status != 'no_change' ) {
					$product_to_edit['post_status'] = $status;
				} elseif ( $form['save_to_product'] == 'new_product' ) {
					$product_to_edit['post_status'] = 'publish';
				} elseif ( $form['save_to_product'] == 'edit_product' ) {
					$product = wc_get_product( $product_id );
					$status  = $product->get_status();
					if ( $status == 'auto-draft' ) {
						$product_to_edit['post_status'] = 'publish';
					}
				}
			}

			if ( $product_to_edit['ID'] == 0 ) {
				if ( empty( $product_to_edit['post_title'] ) ) {
					$product_to_edit['post_title'] = '(no-name)';
				}
				$product_id = wp_insert_post( $product_to_edit );

				$GLOBALS['admin_form']['record']['product'] = $product_id;
				$form['record']['product'] = $product_id;

				update_metadata( 'post', $product_id, 'admin_form_source', $form['id'] );
			} else {
				wp_update_post( $product_to_edit );
				update_metadata( 'post', $product_id, 'admin_form_edited', $form['id'] );
			}

			if ( isset( $form['product_terms'] ) && $form['product_terms'] != '' ) {
				$new_terms = $form['product_terms'];
				if ( is_string( $new_terms ) ) {
					$new_terms = explode( ',', $new_terms );
				}
				if ( is_array( $new_terms ) ) {
					foreach ( $new_terms as $term_id ) {
						   $term = get_term( $term_id );
						if ( $term ) {
							wp_set_object_terms( $product_id, $term->term_id, $term->taxonomy, true );
						}
					}
				}
			}

			if ( isset( $pt_field ) ) {
				acf_update_value( $product_type, $product_id, $pt_field );
			}

			if ( $form['save_to_product'] == 'duplicate_product' ) {
				$taxonomies = get_object_taxonomies( 'product' );
				foreach ( $taxonomies as $taxonomy ) {
					$product_terms = wp_get_object_terms( $product_to_duplicate->ID, $taxonomy, array( 'fields' => 'slugs' ) );
					wp_set_object_terms( $product_id, $product_terms, $taxonomy, false );
				}

				global $wpdb;
				$product_meta_infos = $wpdb->get_results( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$product_to_duplicate->ID" );
				if ( count( $product_meta_infos ) != 0 ) {
					$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
					foreach ( $product_meta_infos as $meta_info ) {
						   $meta_key        = $meta_info->meta_key;
						   $meta_value      = addslashes( $meta_info->meta_value );
						   $sql_query_sel[] = "SELECT $product_id, '$meta_key', '$meta_value'";
					}
					$sql_query .= implode( ' UNION ALL ', $sql_query_sel );
					$wpdb->query( $sql_query );
				}
			}

			if ( ! empty( $metas ) ) {
				foreach ( $metas as $meta ) {
					acf_update_value( $meta['_input'], $product_id, $meta );
				}
			}

			$form['record']['product'] = $product_id;

			do_action( 'frontend_admin/save_product', $form, $product_id );
			do_action( 'acf_frontend/save_product', $form, $product_id );
			return $form;
		}

		public function __construct() {
			 add_filter( 'acf_frontend/save_form', array( $this, 'save_form' ), 4 );
		}

	}

	fea_instance()->local_actions['product'] = new ActionProduct();

endif;
