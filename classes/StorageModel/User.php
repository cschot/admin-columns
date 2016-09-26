<?php
defined( 'ABSPATH' ) or die();

class AC_StorageModel_User extends AC_StorageModel {

	public function init() {
		$this->key = 'wp-users';
		$this->label = __( 'Users' );
		$this->singular_label = __( 'User' );
		$this->type = 'user';
		$this->meta_type = 'user';
		$this->page = 'users';
		$this->screen = 'users';
		$this->table_classname = 'WP_Users_List_Table';
	}

	public function init_manage_value() {
		add_filter( 'manage_users_custom_column', array( $this, 'manage_value' ), 100, 3 );
	}

	/**
	 * @since NEWVERSION
	 * @return string HTML
	 */
	public function get_single_row( $user_id ) {
		return $this->get_list_table()->single_row( get_userdata( $user_id ) );
	}

	/**
	 * @since 2.4.10
	 */
	public function is_current_screen() {
		return parent::is_current_screen() && 'delete' !== filter_input( INPUT_GET, 'action' );
	}

	/**
	 * @since 2.0.2
	 *
	 * @param string $value
	 * @param string $column_name
	 * @param int $user_id
	 */
	public function manage_value( $value, $column_name, $user_id ) {
		return $this->columns()->get_display_value_by_column_name( $column_name, $user_id, $value );
	}

	/**
	 * @since NEWVERSION
	 * @return array|null|object
	 */
	public function get_meta() {
		global $wpdb;

		return $wpdb->get_results( "SELECT DISTINCT meta_key FROM {$wpdb->usermeta} ORDER BY 1", ARRAY_N );
	}

}