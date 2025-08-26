<?php

namespace ACFCustomDatabaseTables\Intercept;

use ACFCustomDatabaseTables\Facade\Factory;
use ACFCustomDatabaseTables\Facade\Map;
use ACFCustomDatabaseTables\Model\ACFSelector;
use ACFCustomDatabaseTables\Service\ACFLocalReferenceFallback;
use ACFCustomDatabaseTables\Utils\Error;
use WP_Error;

/**
 * Class ACFGetFieldIntercept
 * @package ACFCustomDatabaseTables\Intercept
 */
class ACFGetFieldIntercept extends InterceptBase {

	public function init() {
		$this->enable();
	}

	/**
	 * Hook anything needed by the intercept in order to return data from custom db tables.
	 */
	public function enable() {
		add_filter( 'acf/pre_load_metadata', [ $this, '_load_data' ], 10, 4 );
	}

	/**
	 * Stop intercepting calls and allow all data to be retrieved from core meta tables only.
	 */
	public function disable() {
		remove_filter( 'acf/pre_load_metadata', [ $this, '_load_data' ], 10 );
	}

	/**
	 * @param null|mixed $null
	 * @param string|int $post_id
	 * @param string $name
	 * @param bool $hidden
	 *
	 * @return mixed|null
	 */
	public function _load_data( $null, $post_id, $name, $hidden ) {
		// Ignore field key references.
		if ( $hidden ) {
			return $null;
		}

		//$post_id = acf_get_valid_post_id( $post_id );

		// Get the field array. Bail if we can't.
		if ( ! $field_arr = acf_maybe_get_field( $name, $post_id ) ) {
			return $null;
		}

		// Prepare required objects.
		$selector = ACFSelector::make( $post_id );
		$field = Factory::make_field_object_from_array( $field_arr );

		// Bail if field is not supported or does not have a table.
		if ( ! $field->is_supported() or ! Map::has_table( $field, $selector ) ) {
			return $null;
		}

		$field = $this->coordinator->load_field_value( $field, $selector );

		if ( is_wp_error( $field ) ) {
			return Error::log( 'Failed to retrieve data from table. Error: ' . $field->get_error_message() )->return( $null );
		}

		return $field->get_value();
	}

	/**
	 * @param $field_key
	 * @param $field_name
	 * @param $post_id
	 *
	 * @return mixed
	 * @deprecated This was the original handler but we want to adopt our internal naming convention of prefixing hooke
	 * methods with an underscore.
	 *
	 */
	public function maybe_get_local_field_reference( $field_key, $field_name, $post_id ) {
		_deprecated_function( __METHOD__, '1.1',
			'\ACFCustomDatabaseTables\Service\ACFLocalReferenceFallback::_fall_back_to_local_references(). This method will be removed in version 1.2' );

		$compat = new ACFLocalReferenceFallback();

		return $compat->_fall_back_to_local_references( $field_key, $field_name, $post_id );
	}

	/**
	 * @param $null
	 * @param $selector
	 * @param $field
	 *
	 * @return array|mixed|object|WP_Error|null
	 * @deprecated This was the original handler but we want to adopt our internal naming convention of prefixing hooke
	 * methods with an underscore.
	 *
	 */
	public function fetch_value( $null, $selector, $field ) {
		_deprecated_function( __METHOD__, '1.1',
			'No replacement. This method will be removed in version 1.2' );
	}

}