<?php
namespace AC\Admin\Request\Column;

use AC\Admin\Request\Handler;
use AC\ListScreen;
use AC\ListScreenFactory;
use AC\Request;
use AC\Storage;

class Save extends Handler {

	public function __construct() {
		parent::__construct( 'save' );
	}

	public function request( Request $request ) {
		$list_screen = ListScreenFactory::create( $request->get( 'list_screen' ), $request->get( 'layout' ) );

		if ( ! $list_screen ) {
			wp_die();
		}

		parse_str( $request->get( 'data' ), $formdata );

		if ( ! isset( $formdata['columns'] ) ) {
			wp_send_json_error( array(
					'type'    => 'error',
					'message' => __( 'You need at least one column', 'codepress-admin-columns' ),
				)
			);
		}

		$data = new Storage\DataObject( [
			'type'       => $list_screen->get_key(), // wp-users, wp-ms_users, wp-media, page, car etc.,
			'menu_order' => 5,
			'columns'    => $formdata['columns'],
			// todo
			'settings'   => [],
			'title'      => 'My Label',
			'subtype'    => '',
		] );

		$id = 0;

		if ( $id ) {
			( new Storage\ListScreen() )->update( $id, $data );
		} else {
			( new Storage\ListScreen() )->create( $data );
		}

		do_action( 'ac/columns_stored', $list_screen );

		// Current storage
		// $result = $list_screen->store( $formdata['columns'] );

		$view_link = ac_helper()->html->link( $list_screen->get_screen_link(), sprintf( __( 'View %s screen', 'codepress-admin-columns' ), $list_screen->get_label() ) );

		// todo: do object hash compare to see if there were any changes
		$result = true;

		if ( is_wp_error( $result ) ) {

			if ( 'same-settings' === $result->get_error_code() ) {
				wp_send_json_error( array(
						'type'    => 'notice notice-warning',
						'message' => sprintf( __( 'You are trying to store the same settings for %s.', 'codepress-admin-columns' ), "<strong>" . $this->get_list_screen_message_label( $list_screen ) . "</strong>" ) . ' ' . $view_link,
					)
				);
			}

			wp_send_json_error( array(
					'type'    => 'error',
					'message' => $result->get_error_message(),
				)
			);
		}

		wp_send_json_success(
			sprintf( __( 'Settings for %s updated successfully.', 'codepress-admin-columns' ), "<strong>" . esc_html( $this->get_list_screen_message_label( $list_screen ) ) . "</strong>" ) . ' ' . $view_link
		);
	}

	/**
	 * @param ListScreen $list_screen
	 *
	 * @return string $label
	 */
	private function get_list_screen_message_label( ListScreen $list_screen ) {
		return apply_filters( 'ac/settings/list_screen_message_label', $list_screen->get_label(), $list_screen );
	}

}