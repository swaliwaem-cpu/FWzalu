<?php
namespace Frontend_Admin\Pro\Elementor;

use Elementor\Controls_Manager;
use ElementorPro\Modules\QueryControl\Module as Query_Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ContentTab {
	public function widget_controls( $widget, $controls ){
		$widget->add_control(
			'multi',
			array(
				'label'        => __( 'Multi Step', 'acf-frontend-form-element' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'acf-frontend-form-element' ),
				'label_off'    => __( 'No', 'acf-frontend-form-element' ),
				'return_value' => 'true',
				'condition'    => array(
					'admin_forms_select' => '',
				),
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'field_type',
			array(
				'type'    => \Elementor\Controls_Manager::HIDDEN,
				'default' => 'step',
			)
		);

		$controls->register_step_controls( $repeater, $widget, true );

		$widget->add_control(
			'first_step',
			array(
				'show_label'    => false,
				'type'          => \Elementor\Controls_Manager::REPEATER,
				'fields'        => $repeater->get_controls(),
				'title_field'   => 'Step',
				'prevent_empty' => true,
				'item_actions'  => array(
					'add'       => false,
					'duplicate' => false,
					'remove'    => false,
					'sort'      => false,
				),
				'default'       => array(
					array(
						'field_type' => 'step',
					),
				),
				'condition'     => array(
					'multi'              => 'true',
					'admin_forms_select' => '',
				),
			)
		);
	}

	public function multi_step_settings( $widget ) {
		$widget->start_controls_section(
			'multi_step_section',
			array(
				'label'     => __( 'Steps Settings', 'acf-frontend-form-element' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array(
					'admin_forms_select' => '',
				),
			)
		);
		$post_type_choices = feadmin_get_post_type_choices();
		$widget->add_control(
			'validate_steps',
			array(
				'label'        => __( 'Validate Each Step', 'acf-frontend-form-element' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'acf-frontend-form-element' ),
				'label_off'    => __( 'No', 'acf-frontend-form-element' ),
				'return_value' => 'true',
			)
		);
		$widget->add_control(
			'steps_display',
			array(
				'label'       => __( 'Steps Display', 'acf-frontend-form-element' ),
				'type'        => Controls_Manager::SELECT2,
				'label_block' => true,
				'default'     => array(
					'tabs',
				),
				'multiple'    => 'true',
				'options'     => array(
					'tabs'    => __( 'Tabs', 'acf-frontend-form-element' ),
					'counter' => __( 'Counter', 'acf-frontend-form-element' ),
				),

			)
		);
		$widget->add_control(
			'responsive_description',
			array(
				'raw'             => __( 'Responsive visibility will take effect only on preview or live page, and not while editing in Elementor.', 'elementor' ),
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-descriptor',
			)
		);
		$widget->add_control(
			'steps_tabs_display',
			array(
				'label'       => __( 'Step Tabs Display', 'acf-frontend-form-element' ),
				'type'        => Controls_Manager::SELECT2,
				'label_block' => 'true',
				'default'     => array(
					'desktop',
					'tablet',
				),
				'multiple'    => 'true',
				'options'     => array(
					'desktop' => __( 'Desktop', 'acf-frontend-form-element' ),
					'tablet'  => __( 'Tablet', 'acf-frontend-form-element' ),
					'phone'   => __( 'Mobile', 'acf-frontend-form-element' ),
				),
				'condition'   => array(
					'steps_display' => 'tabs',
				),
			)
		);
		$widget->add_control(
			'tabs_align',
			array(
				'label'     => __( 'Tabs Position', 'elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'horizontal',
				'options'   => array(
					'horizontal' => __( 'Top', 'elementor' ),
					'vertical'   => __( 'Side', 'elementor' ),
				),
				'condition' => array(
					'steps_display' => 'tabs',
				),
			)
		);

		$widget->add_control(
			'steps_counter_display',
			array(
				'label'       => __( 'Step Counter Display', 'acf-frontend-form-element' ),
				'type'        => Controls_Manager::SELECT2,
				'label_block' => 'true',
				'default'     => array(
					'desktop',
					'tablet',
					'phone',
				),
				'multiple'    => 'true',
				'options'     => array(
					'desktop' => __( 'Desktop', 'acf-frontend-form-element' ),
					'tablet'  => __( 'Tablet', 'acf-frontend-form-element' ),
					'phone'   => __( 'Mobile', 'acf-frontend-form-element' ),
				),
				'condition'   => array(
					'steps_display' => 'counter',
				),
			)
		);
		$widget->add_control(
			'counter_prefix',
			array(
				'label'       => __( 'Counter Prefix', 'acf-frontend-form-element' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Step ', 'acf-frontend-form-element' ),
				'default'     => __( 'Step ', 'acf-frontend-form-element' ),
				'dynamic'     => array(
					'active' => true,
				),
				'condition'   => array(
					'steps_display' => 'counter',
				),
			)
		);
		$widget->add_control(
			'counter_suffix',
			array(
				'label'     => __( 'Counter Suffix', 'acf-frontend-form-element' ),
				'type'      => Controls_Manager::TEXT,
				'dynamic'   => array(
					'active' => true,
				),
				'condition' => array(
					'steps_display' => 'counter',
				),
			)
		);

		$widget->add_control(
			'step_number',
			array(
				'label'        => __( 'Step Number in Tabs', 'acf-frontend-form-element' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'show', 'acf-frontend-form-element' ),
				'label_off'    => __( 'hide', 'acf-frontend-form-element' ),
				'return_value' => 'true',
				'condition'    => array(
					'steps_display' => 'tabs',
				),
			)
		);

		$widget->add_control(
			'tab_links',
			array(
				'label'        => __( 'Link to Step in Tabs', 'acf-frontend-form-element' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'acf-frontend-form-element' ),
				'label_off'    => __( 'No', 'acf-frontend-form-element' ),
				'return_value' => 'true',
				'condition'    => array(
					'steps_display' => 'tabs',
				),
			)
		);
		$widget->end_controls_section();

	}

	public function submit_limit_setting( $widget ) {
		$widget->add_control(
			'limit_reached',
			array(
				'label'       => __( 'Limit Reached Message', 'acf-frontend-form-element' ),
				'type'        => Controls_Manager::SELECT,
				'label_block' => true,
				'default'     => 'show_message',
				'options'     => array(
					'show_message'   => __( 'Limit Message', 'acf-frontend-form-element' ),
					'custom_content' => __( 'Custom Content', 'acf-frontend-form-element' ),
					'show_nothing'   => __( 'Nothing', 'acf-frontend-form-element' ),
				),
			)
		);
		$widget->add_control(
			'limit_submit_message',
			array(
				'label'       => __( 'Reached Limit Message', 'acf-frontend-form-element' ),
				'type'        => Controls_Manager::TEXTAREA,
				'label_block' => true,
				'rows'        => 4,
				'default'     => __( 'You have already submitted this form the maximum amount of times that you are allowed', 'acf-frontend-form-element' ),
				'placeholder' => __( 'you have already submitted this form the maximum amount of times that you are allowed', 'acf-frontend-form-element' ),
				'condition'   => array(
					'limit_reached' => 'show_message',
				),
			)
		);
		$widget->add_control(
			'limit_submit_content',
			array(
				'label'       => __( 'Reached Limit Content', 'acf-frontend-form-element' ),
				'type'        => Controls_Manager::WYSIWYG,
				'placeholder' => 'You have already submitted this form the maximum amount of times that you are allowed',
				'label_block' => true,
				'render_type' => 'none',
				'condition'   => array(
					'limit_reached' => 'custom_content',
				),
			)
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'rule_name',
			array(
				'label'       => __( 'Rule Name', 'acf-frontend-form-element' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Rule Name', 'acf-frontend-form-element' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'allowed_submits',
			array(
				'label'   => __( 'Allowed Submissions', 'acf-frontend-form-element' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => '',
			)
		);

		$repeater->add_control(
			'limit_to_everyone',
			array(
				'label'        => __( 'Limit For Everyone', 'acf-frontend-form-element' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'acf-frontend-form-element' ),
				'label_off'    => __( 'No', 'acf-frontend-form-element' ),
				'return_value' => 'true',
			)
		);

		$user_roles = feadmin_get_user_roles();

		$repeater->add_control(
			'limit_by_role',
			array(
				'label'       => __( 'Limit By Role', 'acf-frontend-form-element' ),
				'type'        => Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple'    => true,
				'default'     => 'subscriber',
				'options'     => $user_roles,
				'condition'   => array(
					'limit_to_everyone' => '',
				),
			)
		);
		if ( ! class_exists( 'ElementorPro\Modules\QueryControl\Module' ) ) {
			$repeater->add_control(
				'limit_by_user',
				array(
					'label'       => __( 'Limit By User', 'acf-frontend-form-element' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => __( '18', 'acf-frontend-form-element' ),
					'description' => __( 'Enter a commma seperated list of user ids', 'acf-frontend-form-element' ),
					'condition'   => array(
						'limit_to_everyone' => '',
					),
				)
			);
		} else {
			$repeater->add_control(
				'limit_by_user',
				array(
					'label'        => __( 'Limit By User', 'acf-frontend-form-element' ),
					'type'         => Query_Module::QUERY_CONTROL_ID,
					'label_block'  => true,
					'autocomplete' => array(
						'object'  => Query_Module::QUERY_OBJECT_USER,
						'display' => 'detailed',
					),
					'multiple'     => true,
					'condition'    => array(
						'limit_to_everyone' => '',
					),
				)
			);
		}

		$widget->add_control(
			'limiting_rules',
			array(
				'label'         => __( 'Add Limiting Rules', 'acf-frontend-form-element' ),
				'type'          => Controls_Manager::REPEATER,
				'fields'        => $repeater->get_controls(),
				'prevent_empty' => false,
				'default'       => array(
					array(
						'rule_name' => __( 'Subscribers', 'acf-frontend-form-element' ),
					),
				),
				'title_field'   => '{{{ rule_name }}}',
			)
		);

	}

	public function elementor_widget_types( $widgets ){
		if ( class_exists( 'woocommerce' ) ) {
			$widgets['products'] = array(
				'edit_product'      => 'Edit_Product_Widget',
				'new_product'       => 'New_Product_Widget',
				'duplicate_product' => 'Duplicate_Product_Widget',
				'delete_product'    => 'Delete_Product_Widget',
			);
		}
		$widgets['general']['edit_options'] = 'Edit_Options_Widget';
		return $widgets;
	}

	public function fields_to_choose( $fields, $type ) {
		if ( $type == 'all' || $type == 'options' ) {
			$fields['options'] = array(
				'label'   => __( 'Site', 'acf-frontend-form-element' ),
				'options' => array(
					'site_title'   => __( 'Site Title', 'acf-frontend-form-element' ),
					'site_tagline' => __( 'Site Tagline', 'acf-frontend-form-element' ),
					'site_logo'    => __( 'Site Logo', 'acf-frontend-form-element' ),
				),
			);
		}
	
		if ( $type == 'all' ) {
			$fields['security'] = array(
				'label'   => __( 'Security', 'acf-frontend-form-element' ),
				'options' => array(
					'recaptcha' => __( 'Recaptcha', 'acf-frontend-form-element' ),
				),
			);
		}
		if ( class_exists( 'woocommerce' ) ) {
			if ( $type == 'all' || $type == 'product' ) {
				$fields['product_type']         = array(
					'label'   => __( 'Product Type', 'acf-frontend-form-element' ),
					'options' => array(
						'product_type'    => __( 'Product Types', 'woocommerce' ),
						'is_virtual'      => __( 'Virtual', 'woocommerce' ),
						'is_downloadable' => __( 'Downloadable', 'woocommerce' ),
					),
				);
				$fields['product']              = array(
					'label'   => __( 'Product Information', 'acf-frontend-form-element' ),
					'options' => array(
						'product_title'      => __( 'Product Title', 'acf-frontend-form-element' ),
						'product_slug'       => __( 'Slug', 'acf-frontend-form-element' ),
						'price'              => __( 'Price', 'acf-frontend-form-element' ),
						'sale_price'         => __( 'Sale Price', 'acf-frontend-form-element' ),
						'description'        => __( 'Description', 'acf-frontend-form-element' ),
						'main_image'         => __( 'Main Image', 'acf-frontend-form-element' ),
						'images'             => __( 'Product Images', 'acf-frontend-form-element' ),
						'short_description'  => __( 'Short Description', 'acf-frontend-form-element' ),
						'product_categories' => __( 'Categories', 'acf-frontend-form-element' ),
						'product_tags'       => __( 'Tags', 'acf-frontend-form-element' ),
						'tax_status'         => __( 'Tax Status', 'acf-frontend-form-element' ),
						'tax_class'          => __( 'Tax Class', 'acf-frontend-form-element' ),
					),
				);
				$fields['product_downloadable'] = array(
					'label'   => __( 'Product Downloads', 'acf-frontend-form-element' ),
					'options' => array(
						'download_limit'     => __( 'Download Limit', 'woocommerce' ),
						'download_expiry'    => __( 'Download Expiry', 'woocommerce' ),
						'downloadable_files' => __( 'Downloadable Files', 'woocommerce' ),
					),
				);
				$fields['product_shipping']     = array(
					'label'   => __( 'Product Shipping', 'acf-frontend-form-element' ),
					'options' => array(
						'product_weight'         => __( 'Weight', 'woocommerce' ),
						'product_length'         => __( 'Length', 'woocommerce' ),
						'product_width'          => __( 'Width', 'woocommerce' ),
						'product_height'         => __( 'Height', 'woocommerce' ),
						'product_shipping_class' => __( 'Shipping Class', 'woocommerce' ),
					),
				);
				$fields['product_external']     = array(
					'label'   => __( 'External/Affiliate product', 'woocommerce' ),
					'options' => array(
						'external_url' => __( 'Product URL', 'acf-frontend-form-element' ),
						'button_text'  => __( 'Button Text', 'acf-frontend-form-element' ),
					),
				);
				$fields['product_linked']       = array(
					'label'   => __( 'Linked Products', 'acf-frontend-form-element' ),
					'options' => array(
						'grouped_products' => __( 'Grouped Products', 'acf-frontend-form-element' ),
						'upsells'          => __( 'Upsells', 'acf-frontend-form-element' ),
						'cross_sells'      => __( 'Cross Sells', 'acf-frontend-form-element' ),
					),
				);
				$fields['product_attributes']   = array(
					'label'   => __( 'Product Attributes', 'acf-frontend-form-element' ),
					'options' => array(
						'attributes' => __( 'Attributes', 'acf-frontend-form-element' ),
						'variations' => __( 'Variations', 'acf-frontend-form-element' ),
					),
				);
				$fields['product_inventory']    = array(
					'label'   => __( 'Product Inventory', 'acf-frontend-form-element' ),
					'options' => array(
						'sku'                 => __( 'sku', 'acf-frontend-form-element' ),
						'stock_status'        => __( 'Stock Status', 'acf-frontend-form-element' ),
						'sold_individually'   => __( 'Sold Individually', 'acf-frontend-form-element' ),
						'manage_stock'        => __( 'Manage Stock', 'acf-frontend-form-element' ),
						'stock_quantity'      => __( 'Stock Quantity', 'acf-frontend-form-element' ),
						'allow_backorders'    => __( 'Allow Backorders', 'acf-frontend-form-element' ),
						'low_stock_threshold' => __( 'Low Stock Threshold', 'acf-frontend-form-element' ),
					),
				);
				$fields['product_advanced']     = array(
					'label'   => __( 'Advanced Product Options', 'acf-frontend-form-element' ),
					'options' => array(
						'product_purchase_note'  => __( 'Purchase Note', 'acf-frontend-form-element' ),
						'product_menu_order'     => __( 'Menu Order', 'acf-frontend-form-element' ),
						'product_enable_reviews' => __( 'Enable Reviews', 'acf-frontend-form-element' ),
					),
				);
			}
		}
		if ( $type == 'all' ) {
			$fields['layout']['options']['step'] = __( 'Step', 'acf-frontend-form-element' );
		}
		return $fields;
	}

	public function __construct() {
		add_filter( 'frontend_admin/elementor/widget_types', array( $this, 'elementor_widget_types' ) );
		add_action( 'frontend_admin/multi_step_settings', array( $this, 'multi_step_settings' ) );
		add_action( 'frontend_admin/limit_submit_settings', array( $this, 'submit_limit_setting' ) );
		add_filter( 'frontend_admin/form/elementor/field_select_options', array( $this, 'fields_to_choose' ), 10, 2 );
		require_once( 'style-tab.php' );
	}

}

new ContentTab();
