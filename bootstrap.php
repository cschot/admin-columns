<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/api.php';
require_once __DIR__ . '/classes/Autoloader.php';

AC\Autoloader::instance()->register_prefix( 'AC', __DIR__ . '/classes' );

// TODO decide if this is a good place for this
AC\Autoloader\Alias::instance()
                   ->add_alias( 'AC\Column' );

/**
 * For loading external resources, e.g. column settings.
 * Can be called from plugins and themes.
 */
do_action( 'ac/ready', AC() );