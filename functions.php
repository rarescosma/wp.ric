<?php

// Define paths
$comps = array(
    'admin',
    'config',
    'ric',
    'project'
);

foreach ( $comps as $comp ) {
    $var = $comp . '_path';
    $$var = TEMPLATEPATH . '/' . $comp . '/';
}

// Assets
if( FIRE_DEBUG )
	require_once ( $ric_path . 'assets/FirePHP.class.php' ); // FirePHP

require_once( $ric_path . 'assets/less/bootstrap.php' ); // LESS

// Framework
require_once( $ric_path . '_load_.php' );

// Project
require_once( $project_path . '_load_.php' );

// Common
require_once( $config_path . 'common.php' ); // Common functions for templates

// Appearance
require_once( $config_path . 'appearance.php' ); // Widgets, Menus, Thumbnails, Image Sizes, TinyMCE, etc.

?>