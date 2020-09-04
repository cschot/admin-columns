<?php

namespace AC;

use WP_Post;

abstract class ListScreenPost extends ListScreen {

	/**
	 * @var string Post type
	 */
	protected $post_type;

	/**
	 * @param string $post_type
	 */
	public function __construct( $post_type ) {
		$this->post_type = $post_type;
		$this->meta_type = new MetaType( MetaType::POST );
	}

	/**
	 * @return string
	 */
	public function get_post_type() {
		return $this->post_type;
	}

	/**
	 * @param int $id
	 *
	 * @return WP_Post
	 */
	protected function get_object( $id ) {
		return get_post( $id );
	}

	/**
	 * @param string $var
	 *
	 * @return string|false
	 */
	protected function get_post_type_label_var( $var ) {
		$post_type_object = get_post_type_object( $this->get_post_type() );

		return $post_type_object && isset( $post_type_object->labels->{$var} ) ? $post_type_object->labels->{$var} : false;
	}

	/**
	 * Register post specific columns
	 */
	protected function register_column_types() {
		$this->register_column_type( new Column\CustomField );
		$this->register_column_type( new Column\Actions );
	}

	public function get_table_url() {
		return add_query_arg( [ 'post_type' => $this->get_post_type() ], admin_url( 'edit.php' ) );
	}

	/**
	 * @param string $post_type
	 *
	 * @return self
	 */
	protected function set_post_type( $post_type ) {
		_deprecated_function( __METHOD__, 'NEWVERSION' );

		$this->post_type = $post_type;

		return $this;
	}

}