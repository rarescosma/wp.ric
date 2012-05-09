<?php
// TO DO: Escape your strings properly (quotes)

class ricOptionPanel {

	protected $args;
	protected $pagehook;

	private $ui;

	function __construct( $args ) {
		// Parse arguments
		$defaults = array(
			'toplevel' => false,
			'icon' => false,
			'parent' => 'options-general.php',
			'page_title' => 'Theme Options',
			'menu_title' => 'Theme Options',
			'page_slug' => '',
			'option_template' => array()
		);

		$this->args = wp_parse_args( $args, $defaults );

		if ( empty( $this->args['page_slug'] ) )
			$this->args['page_slug'] = sanitize_title_with_dashes( $this->args['menu_title'] );

		// Initialize ricUI
		if( $_REQUEST['page'] == $this->args['page_slug'] )
			$this->ui = ricUI::singleton();

		// Admin menu: add extra menus
		add_action( 'ric_admin_menu', array( $this, 'admin_menu' ) );

		// Admin head: add TinyMCE
		add_action( 'ric_admin_head', array( $this, 'admin_head' ) );
	}

	function admin_head() {
		// Add TinyMCE
		wp_print_scripts( 'editor' );
		if ( function_exists( 'wp_tiny_mce' ) )
			wp_tiny_mce();
	}

	function admin_menu() {
		extract($this->args);

		if ( ! $toplevel ) {
			$this->pagehook = add_submenu_page( $parent, $page_title, $menu_title, 'manage_options', $page_slug, array( $this, '_page_content_hook' ) );
		} else {
			$func = 'add_' . $toplevel . '_page';
			$this->pagehook = $func( $page_title, $menu_title, 'manage_options', $page_slug, array( $this, '_page_content_hook' ), $icon );
		}
	}

	function _page_content_hook() {
		$this->page_header();
		$this->page_content();
		$this->page_footer();
		the_editor('', 'bogus_editor');
	}

	// A generic page header
	function page_header() {
	?>
		<div style="display:none;">
			<span id="edButtonHTML"></span>
			<span id="edButtonPreview"></span>
			<span id="quicktags"></span>
		</div>
		<div class="wrap" id="ric-container">
			<div id="icon-options-general" class="icon32"><br /></div>
		<div class="header">
			<h2><?php echo $this->args['page_title']; ?></h2>
		</div>
	<?php
	}

	// This function generates the form
	function page_content() {
		$options = $this->args['option_template'];

		$nav = array();
		$sections = '';

		$i = 0;
		foreach ( $options as $option ) {
			switch ( $option['type'] ) {
				case 'heading':
					$option['click_hook'] = 'nav_' . ricUtil::slugify( $option['title'] );

					if ( $i > 0 ) $sections .= "</div>\n";
					$sections .= "<div class='group' id='{$option['click_hook']}'><h2>{$option['title']}</h2>\n";

					$nav[] = $option;
				break;
				default:
					$option['slug'] = RIC . $option['slug'];
					$value = get_option( $option['slug'] );
					if ( ! $value ) $value = $option['default'];

					$option['value'] = $value;
					$sections .= $this->ui->machine( $option, false );
				break;
			}

			$i++;
		}

		$sections .= "</div>\n";
	?>
	<form action="" enctype="multipart/form-data" id="ric-form">
		<div id="ric-main">
			<div id="ric-nav">
				<ul>
				<?php
					foreach( $nav as $item ) {
						echo html( 'li', html( 'a', array( 'title' => $item['title'], 'href' => "#{$item['click_hook']}" ), $item['title'] ) );
					}
				?>
				</ul>
			</div>
			<div id="ric-content">
				<?php echo $sections; ?>
			</div>
			<br class="clear" />
		</div>
		<div id="ric-save-bar">
			<?php html_img( 'admin/images/icons/ajax/small-grey.gif', array('style' => 'display:none;', 'class' => 'ajax-loading-img ajax-loading-img-bottom', 'alt' => 'Working...') ); ?>
			<input type="submit" value="Save All Changes" class="button submit-button" />
		</div>
	</form>

	<br class="clear" />
	<?php
	}

	// A generic page footer
	function page_footer() {
		echo "</div>\n";
	}

	static function ajax_post() {
		// TO DO: Validation Engine
		$data = $_POST;

		foreach( $data as $key => $value ) {
			if( strpos($key, RIC) === 0 ) {
				// Update the option
				update_option( $key, stripslashes( $value ) );
			}
		}

		die('<p class="ok">Options Saved</p>');
	}

	static function init() {
		// Find all option panel configuration files
		// And initiate an instance for each one
		$config_dir = ricUtil::slash( CONFIG_PATH . '/option-panels' ) ;

		self::walk_config_dir( $config_dir );

		// Add the AJAX handler
		add_action( 'wp_ajax_ric_option_post', array( __CLASS__, 'ajax_post' ) );
	}

	static function walk_config_dir( $base ) {
		$subdirectories = opendir( $base );

		while ( ( $subdirectory = readdir( $subdirectories ) ) !== false ) {
			$path = $base . $subdirectory;
			if (is_file($path) ) {
				if( strtolower( substr( $path, -3 ) ) == 'php') {
					self::parse_config( $path );
				}
			} else {
				if ( ( $subdirectory != '.' ) && ( $subdirectory != '..' ) ) {
					self::walk_config_dir( ricUtil::slash( $path ) );
				}
			}
		}
	}

	static function parse_config( $file ) {
		global $config; unset( $config );
		global $options; unset ( $options );

		include( $file );

		$args = $config;
		$args['option_template'] = $options;

		new ricOptionPanel( $args );
	}
}

