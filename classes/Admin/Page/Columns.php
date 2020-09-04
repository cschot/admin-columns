<?php

namespace AC\Admin\Page;

use AC\Admin;
use AC\Admin\Banner;
use AC\Admin\Helpable;
use AC\Admin\HelpTab;
use AC\Admin\Page;
use AC\Admin\ScreenOption;
use AC\Admin\Section\Partial\Menu;
use AC\Asset\Assets;
use AC\Asset\Enqueueables;
use AC\Asset\Location;
use AC\Asset\Script;
use AC\Asset\Style;
use AC\Column;
use AC\Controller\ListScreenRequest;
use AC\DefaultColumnsRepository;
use AC\ListScreen;
use AC\ListScreenRepository\Storage;
use AC\Message;
use AC\Type\ListScreenId;
use AC\View;

class Columns extends Page implements Enqueueables, Helpable, Admin\ScreenOptions {

	const NAME = 'columns';

	/**
	 * @var ListScreenRequest
	 */
	private $controller;

	/**
	 * @var Location\Absolute
	 */
	private $location;

	/**
	 * @var DefaultColumnsRepository
	 */
	private $default_columns;

	/**
	 * @var Menu
	 */
	private $menu;

	/**
	 * @var Storage
	 */
	private $storage;

	public function __construct(
		ListScreenRequest $controller,
		Location\Absolute $location,
		DefaultColumnsRepository $default_columns,
		Menu $menu,
		Storage $storage
	) {
		parent::__construct( self::NAME, __( 'Admin Columns', 'codepress-admin-columns' ) );

		$this->controller = $controller;
		$this->location = $location;
		$this->default_columns = $default_columns;
		$this->menu = $menu;
		$this->storage = $storage;
	}

	public function show_read_only_notice( ListScreen $list_screen ) {
		if ( $list_screen->is_read_only() ) {
			$message = sprintf( __( 'The columns for %s are read only and can therefore not be edited.', 'codepress-admin-columns' ), '<strong>' . esc_html( $list_screen->get_title() ? $list_screen->get_title() : $list_screen->get_label() ) . '</strong>' );
			$message = sprintf( '<p>%s</p>', apply_filters( 'ac/read_only_message', $message, $list_screen ) );

			$notice = new Message\InlineMessage( $message );

			echo $notice->set_type( Message::INFO )
			            ->render();
		}
	}

	public function get_assets() {

		return new Assets( [
			new Style( 'jquery-ui-lightness', $this->location->with_suffix( 'assets/ui-theme/jquery-ui-1.8.18.custom.css' ) ),
			new Script( 'jquery-ui-slider' ),
			new Admin\Asset\Columns(
				'ac-admin-page-columns',
				$this->location->with_suffix( 'assets/js/admin-page-columns.js' ),
				$this->default_columns,
				$this->controller->get_list_screen()
			),
			new Style( 'ac-admin-page-columns-css', $this->location->with_suffix( 'assets/css/admin-page-columns.css' ) ),
			new Style( 'ac-select2' ),
			new Script( 'ac-select2' ),
		] );
	}

	public function get_help_tabs() {
		return [
			new HelpTab\Introduction(),
			new HelpTab\Basics(),
			new HelpTab\CustomField(),
		];
	}

	private function get_column_id() {
		return new ScreenOption\ColumnId( new Admin\Preference\ScreenOptions() );
	}

	private function get_column_type() {
		return new ScreenOption\ColumnType( new Admin\Preference\ScreenOptions() );
	}

	private function get_list_screen_id() {
		return new ScreenOption\ListScreenId( new Admin\Preference\ScreenOptions() );
	}

	private function get_list_screen_type() {
		return new ScreenOption\ListScreenType( new Admin\Preference\ScreenOptions() );
	}

	public function get_screen_options() {
		return [
			$this->get_column_id(),
			$this->get_column_type(),
			$this->get_list_screen_id(),
			$this->get_list_screen_type(),
		];
	}

	public function render() {
		$list_screen = $this->controller->get_list_screen();

		if ( ! $this->default_columns->exists( $list_screen->get_key() ) ) {
			$modal = new View( [
				'message' => 'Loading columns',
			] );
			$modal->set_template( 'admin/loading-message' );

			return $this->menu->render( true ) . $modal->render();
		}

		$classes = [];

		if ( $list_screen->has_id() && $this->storage->exists( $list_screen->get_id() ) ) {
			$classes[] = 'stored';
		}

		if ( $this->get_list_screen_id()->is_active() ) {
			$classes[] = 'show-list-screen-id';
		}

		if ( $this->get_list_screen_type()->is_active() ) {
			$classes[] = 'show-list-screen-type';
		}

		ob_start();
		?>

        <div class="ac-admin <?= esc_attr( implode( ' ', $classes ) ); ?>" data-type="<?= esc_attr( $list_screen->get_key() ); ?>">
            <div class="ac-admin__header">

				<?= $this->menu->render(); ?>

				<?php do_action( 'ac/settings/after_title', $list_screen ); ?>

            </div>
            <div class="ac-admin__wrap">

                <div class="ac-admin__sidebar">
					<?php if ( ! $list_screen->is_read_only() ) : ?>

						<?php

						$label_main = __( 'Store settings', 'codepress-admin-columns' );
						$label_second = sprintf( '<span class="clear contenttype">%s</span>', esc_html( $list_screen->get_label() ) );
						if ( 18 > strlen( $label_main ) && ( $truncated_label = $this->get_truncated_side_label( $list_screen->get_label(), $label_main ) ) ) {
							$label_second = sprintf( '<span class="right contenttype">%s</span>', esc_html( $truncated_label ) );
						}

						$delete_confirmation_message = false;

						if ( (bool) apply_filters( 'ac/delete_confirmation', true ) ) {
							$delete_confirmation_message = sprintf( __( "Warning! The %s columns data will be deleted. This cannot be undone. 'OK' to delete, 'Cancel' to stop", 'codepress-admin-columns' ), "'" . $list_screen->get_title() . "'" );
						}

						$actions = new View( [
							'label_main'                  => $label_main,
							'label_second'                => $label_second,
							'list_screen_key'             => $list_screen->get_key(),
							'list_screen_id'              => $list_screen->get_id()->get_id(),
							'delete_confirmation_message' => $delete_confirmation_message,
						] );

						echo $actions->set_template( 'admin/edit-actions' );

					endif; ?>

					<?php do_action( 'ac/settings/sidebox', $list_screen ); ?>

					<?php if ( apply_filters( 'ac/show_banner', true ) ) : ?>

						<?= new Banner(); ?>

						<?= ( new View() )->set_template( 'admin/side-feedback' ); ?>

					<?php endif; ?>

					<?= ( new View() )->set_template( 'admin/side-support' ); ?>

                </div>

                <div class="ac-admin__main">

					<?= $this->show_read_only_notice( $list_screen ); ?>

                    <form method="post" id="listscreen_settings" class="<?= $list_screen->is_read_only() ? '-disabled' : ''; ?>">
						<?php

						$classes = [];

						if ( $list_screen->is_read_only() ) {
							$classes[] = 'disabled';
						}

						if ( $this->get_column_id()->is_active() ) {
							$classes[] = 'show-column-id';
						}

						if ( $this->get_column_type()->is_active() ) {
							$classes[] = 'show-column-type';
						}

						$repo = new DefaultColumnsRepository();

						$columns = $list_screen->has_columns()
							? $list_screen->get_columns()
							: $repo->find_all( $list_screen );

						$columns = new View( [
							'class'          => implode( ' ', $classes ),
							'list_screen'    => $list_screen->get_key(),
							'list_screen_id' => $list_screen->has_id() ? $list_screen->get_id()->get_id() : ListScreenId::generate()->get_id(),
							'title'          => $list_screen->get_title(),
							'columns'        => $columns,
							'show_actions'   => ! $list_screen->is_read_only(),
							'show_clear_all' => apply_filters( 'ac/enable_clear_columns_button', false ),
						] );

						do_action( 'ac/settings/before_columns', $list_screen );

						echo $columns->set_template( 'admin/edit-columns' );

						do_action( 'ac/settings/after_columns', $list_screen );

						?>
                    </form>

                </div>

            </div>

            <div id="add-new-column-template">
				<?= $this->render_column_template( $list_screen ); ?>
            </div>

        </div>

        <div class="clear"></div>

		<?php

		$modal = new View();

		echo $modal->set_template( 'admin/modal-pro' );

		return ob_get_clean();
	}

	/**
	 * @param array $column_types
	 * @param bool $group
	 *
	 * @return Column|false
	 */
	private function get_column_template_by_group( $column_types, $group = false ) {
		if ( ! $group ) {
			return array_shift( $column_types );
		}

		$columns = [];

		foreach ( $column_types as $column_type ) {
			if ( $group === $column_type->get_group() ) {
				$columns[ $column_type->get_label() ] = $column_type;
			}
		}

		$column_keys = array_keys( $columns );
		array_multisort( $column_keys, SORT_NATURAL, $columns );

		$column = array_shift( $columns );

		if ( ! $column ) {
			return false;
		}

		return $column;
	}

	/**
	 * @param ListScreen $list_screen
	 *
	 * @return string
	 */
	private function render_column_template( ListScreen $list_screen ) {
		$column = $this->get_column_template_by_group( $list_screen->get_column_types(), 'custom' );

		if ( ! $column ) {
			$column = $this->get_column_template_by_group( $list_screen->get_column_types() );
		}

		$view = new View( [
			'column' => $column,
		] );

		return $view->set_template( 'admin/edit-column' )->render();
	}

	/**
	 * @param string $label
	 * @param string $main_label
	 *
	 * @return string
	 */
	private function get_truncated_side_label( $label, $main_label = '' ) {
		if ( 34 < ( strlen( $label ) + ( strlen( $main_label ) * 1.1 ) ) ) {
			$label = substr( $label, 0, 34 - ( strlen( $main_label ) * 1.1 ) ) . '...';
		}

		return $label;
	}

}