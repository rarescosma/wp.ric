<?php

class ricAdmin {
	// TO DO: Rewrite branding to store logos and dimensions in Transient API
	static $args = array();

	static function init() {
		// Initialize arguments
		self::init_args();

		// Add admin_head hook
		add_action( 'admin_head', array( __CLASS__, 'admin_head' ) );

		// Add admin_init scripts & hook
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );

		// Add admin_menu hook
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );

		// Add custom MCE styles
		add_filter( 'tiny_mce_before_init', array( __CLASS__, 'customize_mce' ) );

		if( class_exists( 'ricMetabox' ) ) {
			// Are we on the 'Edit' or 'New' screen? If yes, add some metaboxes!
			$where = array(	'post',	'post-new' );

			if ( in_array( WPA_PAGE, $where ) ) {
				add_action( 'init', array( __CLASS__, 'init_metaboxes' ), 20, 0 );
			}
		}

		// Initialize custom Option Panels
		ricOptionPanel::init();

		// Are we in the 'Media Upload' context? Initialize ricUI
		$where = array( 'media-upload', 'async-upload' );

		if ( in_array( WPA_PAGE, $where ) ) ricUI::singleton();
	}

	static function init_args() {
		$post_id = 0;

		if ( isset( $_REQUEST['post'] ) ) {
			$post_id = $_REQUEST['post'];
		}elseif ( isset( $_REQUEST['post_ID'] ) ) {
			$post_id = $_REQUEST['post_ID'];
		}

		$post_type = ( isset( $_REQUEST['post_type'] ) ) ? $_REQUEST['post_type'] : 'post';
		if( isset( $_REQUEST['post_type'] ) ) {
			$post_type = $_REQUEST['post_type'];
		} elseif ( $post_id > 0 ) {
			$post_type = get_post_type( $post_id );
		}

		self::$args = compact( 'post_id', 'post_type' );
	}

	static function admin_head() {
		do_action( 'ric_admin_head' );
	}

	static function admin_init() {
		$prefix = PT_URL . 'admin/';

		$css_query = '?admin_page=' . WPA_PAGE;

		$logo = $prefix . 'images/logos/admin.png';

		$check = wp_check_filetype( $logo );

		if( strstr( $check['type'], 'image' ) ) {
			list( $width, $height ) = getimagesize( $logo );
			$css_query .= '&logo_width=' . $width;
			$css_query .= '&logo_height=' . $height;
		}

		// Load our stylesheet after colors.css
		wp_register_style( 'global-admin', $prefix . 'css/global.css.php' . $css_query, array('colors') );
		wp_enqueue_style( 'global-admin' );

		wp_enqueue_script( 'global-admin', $prefix . 'js/global.js', array('jquery', 'editor'), '', true);

		do_action( 'ric_admin_init' );
	}

	static function admin_menu() {
		do_action( 'ric_admin_menu' );
	}

	static function customize_mce( $init ) {
		$init['theme_advanced_buttons2_add'] = 'image';
		$init['apply_source_formatting'] = 1;

		$styles = apply_filters( 'ric_mce_styles', array() );

		if( count( $styles ) )
			$init['theme_advanced_buttons1_add'] = 'styleselect';

		$tmp = array();

		foreach( $styles as $k => $v)
			$tmp[] = $k . '=' . $v;

		$styles = implode( ',', $tmp );

		$init['theme_advanced_styles'] = $styles;

		return $init;
	}

	static function init_metaboxes() {
		// Add the metaboxes
		new ricMetabox( self::$args );
	}

	static function customize_login() {
		add_action( 'login_head', 'custom_login_css' );
		function custom_login_css() {
			$logo = PT_URL . 'admin/images/logos/login.png';

			$check = wp_check_filetype( $logo );

			if( strstr( $check['type'], 'image' ) ) {
				list( $width, $height ) = getimagesize( $logo );
			?>
				<style type="text/css">
				#login h1 a {
					background:url("<?php echo $logo; ?>") top left no-repeat;
					width:<?php echo $width; ?>px; height:<?php echo $height; ?>px;
					margin:0 auto;
				}
				</style>
			<?php
			}
		}

		add_filter( 'login_headerurl', create_function( '$a', "echo bloginfo( 'url' );" ) );
		add_filter( 'login_headertitle', create_function( '$a', "echo 'Powered by ' . get_option('blogname');" ) );
	}
}

ricAdmin::customize_login();
if( is_admin() ) ricAdmin::init();