<?php

namespace AC\Settings\Column;

use AC;
use AC\Collection;
use AC\Settings;
use AC\View;

class CustomFieldType extends Settings\Column
	implements Settings\FormatValue {

	const TYPE_ARRAY = 'array';
	const TYPE_BOOLEAN = 'checkmark';
	const TYPE_COLOR = 'color';
	const TYPE_COUNT = 'count';
	const TYPE_DATE = 'date';
	const TYPE_IMAGE = 'image';
	const TYPE_MEDIA = 'library_id';
	const TYPE_NON_EMPTY = 'has_content';
	const TYPE_NUMERIC = 'numeric';
	const TYPE_POST = 'title_by_id';
	const TYPE_TEXT = 'excerpt';
	const TYPE_URL = 'link';
	const TYPE_USER = 'user_by_id';

	/**
	 * @var string
	 */
	private $field_type;

	protected function define_options() {
		return [ 'field_type' ];
	}

	public function get_dependent_settings() {
		$settings = [];

		switch ( $this->get_field_type() ) {

			case self::TYPE_DATE :
				$settings[] = new Date( $this->column );

				break;
			case self::TYPE_IMAGE  :
			case self::TYPE_MEDIA :
				$settings[] = new Image( $this->column );
				$settings[] = new MediaLink( $this->column );

				break;
			case self::TYPE_TEXT :
				$settings[] = new StringLimit( $this->column );

				break;
			case self::TYPE_URL :
				$settings[] = new LinkLabel( $this->column );

				break;
			case self::TYPE_NUMERIC :
				$settings[] = new NumberFormat( $this->column );
				break;
		}

		return $settings;
	}

	public function create_view() {
		$select = $this->create_element( 'select' );

		$select->set_attribute( 'data-refresh', 'column' )
		       ->set_options( $this->get_grouped_options() )
		       ->set_description( $this->get_description() );

		$tooltip = __( 'This will determine how the value will be displayed.', 'codepress-admin-columns' );

		if ( ! in_array( $this->get_field_type(), [ null, '' ], true ) ) {
			$tooltip .= '<em>' . __( 'Type', 'codepress-admin-columns' ) . ': ' . $this->get_field_type() . '</em>';
		}

		return new View( [
			'label'   => __( 'Field Type', 'codepress-admin-columns' ),
			'tooltip' => $tooltip,
			'setting' => $select,
		] );
	}

	private function get_description_object_ids( $input ) {
		$description = sprintf( __( "Uses one or more %s IDs to display information about it.", 'codepress-admin-columns' ), '<em>' . $input . '</em>' );
		$description .= ' ' . __( "Multiple IDs should be separated by commas.", 'codepress-admin-columns' );

		return $description;
	}

	public function get_description() {
		$description = false;

		switch ( $this->get_field_type() ) {
			case self::TYPE_POST :
				$description = $this->get_description_object_ids( __( "Post Type", 'codepress-admin-columns' ) );

				break;
			case self::TYPE_USER :
				$description = $this->get_description_object_ids( __( "User", 'codepress-admin-columns' ) );

				break;
		}

		return $description;
	}

	/**
	 * Get possible field types
	 * @return array
	 */
	protected function get_field_type_options() {
		$grouped_types = [
			'basic'      => [
				self::TYPE_COLOR   => __( 'Color', 'codepress-admin-columns' ),
				self::TYPE_DATE    => __( 'Date', 'codepress-admin-columns' ),
				self::TYPE_TEXT    => __( 'Text', 'codepress-admin-columns' ),
				self::TYPE_IMAGE   => __( 'Image', 'codepress-admin-columns' ),
				self::TYPE_URL     => __( 'URL', 'codepress-admin-columns' ),
				self::TYPE_NUMERIC => __( 'Number', 'codepress-admin-columns' ),
			],
			'choice'     => [
				self::TYPE_NON_EMPTY => __( 'Has Content', 'codepress-admin-columns' ),
				self::TYPE_BOOLEAN   => __( 'True / False', 'codepress-admin-columns' ),
			],
			'relational' => [
				self::TYPE_MEDIA => __( 'Media', 'codepress-admin-columns' ),
				self::TYPE_POST  => __( 'Post', 'codepress-admin-columns' ),
				self::TYPE_USER  => __( 'User', 'codepress-admin-columns' ),
			],
			'multiple'   => [
				self::TYPE_COUNT => __( 'Number of Fields', 'codepress-admin-columns' ),
				self::TYPE_ARRAY => __( 'Multiple Values', 'codepress-admin-columns' ),
			],
		];

		/**
		 * Filter the available custom field types for the meta (custom field) field
		 *
		 * @param array $field_types Available custom field types ([type] => [label])
		 *
		 * @since 3.0
		 */
		$grouped_types['custom'] = apply_filters( 'ac/column/custom_field/field_types', [] );

		foreach ( $grouped_types as $k => $fields ) {
			natcasesort( $grouped_types[ $k ] );
		}

		return $grouped_types;
	}

	/**
	 * @return array
	 */
	private function get_grouped_options() {
		$field_types = $this->get_field_type_options();

		foreach ( $field_types as $fields ) {
			asort( $fields );
		}

		$groups = [
			'basic'      => __( 'Basic', 'codepress-admin-columns' ),
			'relational' => __( 'Relational', 'codepress-admin-columns' ),
			'choice'     => __( 'Choice', 'codepress-admin-columns' ),
			'multiple'   => __( 'Multiple', 'codepress-admin-columns' ),
			'custom'     => __( 'Custom', 'codepress-admin-columns' ),
		];

		$grouped_options = [];
		foreach ( $field_types as $group => $fields ) {

			if ( ! $fields ) {
				continue;
			}

			$grouped_options[ $group ]['title'] = $groups[ $group ];
			$grouped_options[ $group ]['options'] = $fields;
		}

		// Default option comes first
		$grouped_options = array_merge( [ '' => __( 'Default', 'codepress-admin-columns' ) ], $grouped_options );

		return $grouped_options;
	}

	/**
	 * @param string|array $string
	 *
	 * @return array
	 */
	private function get_values_from_array_or_string( $string ) {
		$string = ac_helper()->array->implode_recursive( ',', $string );

		return ac_helper()->string->comma_separated_to_array( $string );
	}

	/**
	 * @param string|array $string
	 *
	 * @return array
	 */
	private function get_ids_from_array_or_string( $string ) {
		$string = ac_helper()->array->implode_recursive( ',', $string );

		return ac_helper()->string->string_to_array_integers( $string );
	}

	public function format( $value, $original_value ) {

		switch ( $this->get_field_type() ) {

			case self::TYPE_ARRAY :
				if ( ac_helper()->array->is_associative( $value ) ) {
					$value = ac_helper()->array->implode_associative( $value, __( ', ' ) );
				} else {
					$value = ac_helper()->array->implode_recursive( __( ', ' ), $value );
				}

				break;
			case self::TYPE_DATE :
				$timestamp = ac_helper()->date->strtotime( $value );
				if ( $timestamp ) {
					$value = date( 'c', $timestamp );
				}

				break;
			case self::TYPE_POST :
				$values = [];
				foreach ( $this->get_ids_from_array_or_string( $value ) as $id ) {
					$post = get_post( $id );
					$values[] = ac_helper()->html->link( get_edit_post_link( $post ), $post->post_title );
				}

				$value = implode( ac_helper()->html->divider(), $values );

				break;
			case self::TYPE_USER :
				$values = [];
				foreach ( $this->get_ids_from_array_or_string( $value ) as $id ) {
					$user = get_userdata( $id );
					$values[] = ac_helper()->html->link( get_edit_user_link( $id ), ac_helper()->user->get_display_name( $user ) );
				}

				$value = implode( ac_helper()->html->divider(), $values );

				break;
			case self::TYPE_IMAGE :
				$value = new Collection( $this->get_values_from_array_or_string( $value ) );

				break;
			case self::TYPE_MEDIA :
				$value = new Collection( $this->get_ids_from_array_or_string( $value ) );

				break;
			case self::TYPE_BOOLEAN :
				$is_true = ! empty( $value ) && 'false' !== $value && '0' !== $value;

				if ( $is_true ) {
					$value = ac_helper()->icon->dashicon( [ 'icon' => 'yes', 'class' => 'green' ] );
				} else {
					$value = ac_helper()->icon->dashicon( [ 'icon' => 'no-alt', 'class' => 'red' ] );
				}

				break;
			case self::TYPE_COLOR :

				if ( $value && is_scalar( $value ) ) {
					$value = ac_helper()->string->get_color_block( $value );
				} else {
					$value = false;
				}

				break;
			case self::TYPE_COUNT :

				if ( $this->column instanceof AC\Column\Meta ) {
					$value = $this->column->get_meta_value( $original_value, $this->column->get_meta_key(), false );

					if ( $value ) {
						if ( 1 === count( $value ) && is_array( $value[0] ) ) {

							// Value contains a single serialized array with multiple values
							$value = count( $value[0] );
						} else {

							// Count multiple usage of meta keys
							$value = count( $value );
						}
					} else {
						$value = false;
					}
				}

				break;
			case self::TYPE_NON_EMPTY :
				$value = ac_helper()->icon->yes_or_no( $value, $value );

				break;
			default :
				$value = ac_helper()->array->implode_recursive( __( ', ' ), $value );
		}

		return $value;
	}

	/**
	 * @return string
	 */
	public function get_field_type() {
		return $this->field_type;
	}

	/**
	 * @param string $field_type
	 *
	 * @return bool
	 */
	public function set_field_type( $field_type ) {
		$this->field_type = $field_type;

		return true;
	}

}