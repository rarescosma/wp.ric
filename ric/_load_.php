<?php

class theFramework {

	static function init() {
		self::constants();
		self::load();
	}

	static function load() {
		$classes = array(
			'ricUtil',
			// 'ricMail',
			// 'ricTime',
			'ricCPT',
			'ricUI',
			'ricMetabox',
			'ricOptionPanel',
			'ricAdmin'
		);

		foreach ( $classes as $class_name ) {
			if ( class_exists( $class_name ) )
				continue;

			require_once( PT_PATH . '/ric/' . substr( $class_name, 3 ) . '.php' );
		}
	}

	static function constants() {
		// Prefix
		define( 'RIC', 'ric_' );

		// Textdomain
		define( 'RICTD', 'ric' );

		// Parent Theme
		if( defined( MULTISITE ) ) switch_to_blog( 1 );
			// URL
			define( PT_URL, trim( dirname( get_bloginfo( 'stylesheet_url' ) ), '/' ) . '/' );

			// wp-includes, wp-content, wp-admin
			$prefix = get_bloginfo( 'wpurl' );
			define( WPI_URL, $prefix . '/wp-includes/' );
			define( WPC_URL, $prefix . '/wp-content/' );
			define( WPA_URL, $prefix . '/wp-admin/' );

			// Path
			define( PT_PATH, TEMPLATEPATH );
			define( CONFIG_PATH, PT_PATH . '/config' );

		if( defined( MULTISITE ) ) restore_current_blog();

		// Child Theme URL
		define( CT_URL, trim( dirname( get_bloginfo( 'stylesheet_url' ) ), '/' ) . '/' );

		// Child Theme Path
		define( CT_PATH, STYLESHEETPATH );

		// Admin Page
		if( is_admin() ) {
			define( WPA_PAGE, substr( strrchr( $_SERVER['PHP_SELF'], '/' ), 1, -4 ) );
		}
	}
}

theFramework::init();
