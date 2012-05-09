<?php

class ricUI {

	protected $args = array();
	protected $field_id = 1;

	private static $instance;

	private function __construct() {
		$this->args = ricAdmin::$args;

		// Admin head: nothing so far
		add_action( 'ric_admin_head', array( $this, 'admin_head' ) );

		// Admin init: enqueue & localize scripts
		add_action( 'ric_admin_init', array( $this, 'admin_init' ) );

		// Admin menu: gettext hijack
		add_action( 'ric_admin_menu', array( $this, 'admin_menu' ) );
	}

	public static function singleton() {
		if ( ! isset( self::$instance ) ) {
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}

	function admin_head() {	}

	function admin_init() {
		global $pagenow;
		$prefix = PT_URL . 'admin/';

		// Load Media Uploader & Thickbox
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

		// jQuery UI Style
		wp_register_style( 'jquery-ui-custom', $prefix . 'css/jquery-ui.custom.css' );
		wp_enqueue_style( 'jquery-ui-custom' );

		// Datepicker
		wp_enqueue_script( 'ric-ui-datepicker', $prefix . 'js/assets/ui.datepicker.js', array('jquery', 'jquery-ui-core') );

		// Colorpicker
		wp_enqueue_script( 'ric-ui-colorpicker', $prefix . 'js/assets/ui.colorpicker.js', array('jquery') );

		// Elastic Textareas
		wp_enqueue_script( 'ric-jquery-elastic', $prefix . 'js/assets/jquery.elastic.js', array('jquery') );

		// jGrowl
		wp_enqueue_script( 'ric-jquery-jgrowl', $prefix . 'js/assets/jquery.jgrowl.js', array('jquery', 'jquery-ui-core') );
		wp_register_style( 'ric-jquery-jgrowl', $prefix . 'css/jquery.jgrowl.css' );
		wp_enqueue_style( 'ric-jquery-jgrowl' );

		// Load ricUI script
		wp_enqueue_script( 'ric-ui', $prefix . 'js/ricUI.js', array('jquery', 'jquery-ui-core', 'ric-ui-datepicker', 'ric-ui-colorpicker', 'quicktags', 'editor' ) );

		// Configure ricUI
		$post_id = $this->args['post_id'];
		$ajaxurl = admin_url( 'admin-ajax.php' );

		wp_localize_script( 'ric-ui', 'ricUI_options', compact( 'post_id', 'ajaxurl' ) );

		// Fix async upload
		if( isset( $_REQUEST['attachment_id'] ) ) {
			$GLOBALS['post'] = get_post( $_REQUEST['attachment_id'] );
		}

		// Load ricUI style
		wp_register_style( 'ric-ui', $prefix . 'css/ricUI.css' );
		wp_enqueue_style( 'ric-ui' );
	}

	function admin_menu() {
		global $pagenow;

		// Hijack the image upload Save button
		if ( 'media-upload.php' == $pagenow || 'async-upload.php' == $pagenow ) {
			add_filter( 'gettext', 'hijack_save', 1, 3 );
		}

		function hijack_save( $translated_text, $source_text, $domain ) {
			if ( 'Insert into Post' == $source_text ) {
				return 'Insert';
			} else {
				return $translated_text;
			}
		}
	}

	function machine( $args = array(), $echo = true ) {
		// Parse arguments
		$defaults = array(
			'type' => 'textfield',
			'slug' => '',
			'title' => 'Generic Input',
			'desc' => 'Generic Input Description',
			'value' => '',
			'extra' => array()
		);

		extract( wp_parse_args( $args, $defaults ) );

		if ( empty( $slug ) ) $slug = ricUtil::slugify( $title );

		$valid = true;

		$body = '';

		$header = 	'<div class="ric-ui">'.
						'<h4>' . $title . '</h4>'.
						'<div class="ric-ui-' . $type . '-controls">';

		$footer =			'<br class="clear" />'.
						'</div>'.
						'<div class="description">'.
							'<p>' . $desc . '</p>'.
						'</div>'.
					'</div>';

		switch ( $type ) {

		case 'checkbox':
			$checked = ( $value == '1' ) ? ' checked="checked"' : '';

			$body = '<input id="ricui-' . $this->field_id . '"' . $checked . ' class="ric-ui-checkbox" name="' . $slug . '" type="checkbox" value="1" />';
			$footer =			'<br class="clear" />'.
						'</div>'.
						'<div class="description">'.
							'<label for="ricui-' . $this->field_id . '">' . $desc . '</label>'.
						'</div>'.
					'</div>';
		break;

		case 'checkboxes':
			$options = $extra;
			$rows = '';

			$checked_ones = explode( ',', $value );

			foreach( $options as $index => $text ) {
				$checked = ( in_array( $index, $checked_ones ) ) ? ' checked="checked"' : '';
				$rows .= "<div style='padding:3px;'><input class='ric-ui-checkbox' type='checkbox' id='cb_{$slug}_{$index}' name='{$slug}[{$index}]' value='{$index}' {$checked}>&nbsp;<label for='cb_{$slug}_{$index}'>{$text}</label></div>";
			}

			$body = '<div class="ric-ui-checkboxes">' . $rows . '</div>';
		break;

		case 'colorpicker':
			$body =	'<div id="ric-ui-colorpicker_' . $slug . '" class="ric-ui-colorpicker">'.
						'<div></div>'.
					'</div>'.
					'<input class="ric-ui-colorpicker" name="'. $slug .'" type="text" value="'. $value .'" />';
		break;

		case 'counting-textarea':
			$limit = $extra['limit'];

			$body = '<textarea class="ric-ui-counting-textarea" name="' . $slug . '" maxlength="' . $limit . '">' . $value . '</textarea>'.
			'<div class="counter"><p>' . ( $limit - strlen( $value ) ) . '</p></div>';
		break;

		case 'datepicker':
			$body = '<div class="icon"></div>'.
				'<input class="ric-ui-datepicker" name="' . $slug . '" type="text" value="' . $value . '" />';
		break;

		case 'hidden':
			$body = '<input class="ric-ui-hidden" name="' . $slug . '" type="hidden" value="' . $value . '" />';
			$header = '';
			$footer = '';
		break;

		case 'image':
			$body = '<div class="icon"></div>'.
				'<input id="ricui-' . $this->field_id . '" class="ric-ui-image" name="' . $slug . '" type="text" value="' . $value . '" />';
			$args = array(
				'post_id' => $this->args['post_id'],
				'url' => $value,
				'meta_slug' => $slug,
				'option_slug' => $slug,
				'size' => 'thumbnail',
				'echo' => false
			);
			$img = attach_img( $args );
			if( $img ) $body .= '<br /><span class="thumb">' . $img . '</span>';
		break;

		case 'file':
			$body = '<input class="ric-ui-file" name="' . $slug . '" type="file" value="' . $value . '" />';
			$footer =			'<br class="clear" />'.
						'</div>'.
						'<div class="description">'.
							'<p>' . $desc;
			$attach_id = get_post_meta( $this->args['post_id'], $slug, true );
			if( $attach_id ) {
				$file = wp_get_attachment_url( $attach_id );
				if( $file ) {
					$base = explode( '/', $file ); $base = array_pop( $base );
					$footer .= ' Current file: <a href="' . $file . '">' . $base . '</a>';
				}
			}

			$footer .= '</p>'.
						'</div>'.
					'</div>';
		break;

		case 'mce':
			$body = '<textarea class="ric-ui-mce mceEditor" name="' . $slug . '" id="ric-ui-mce_' . $slug . '">' . apply_filters( 'the_content', $value ) . '</textarea>';

			$footer =	'<br class="clear" />'.
						'</div>'.
						'<div class="description">'.
							'<p>' . $desc . ' <a class="toggle" href="#">[Toggle Editor]</a></p>'.
						'</div>'.
					'</div>';
		break;

		case 'select':
			$options = $extra;
			$options_body = '';

			foreach( $options as $index => $text ) {
				$selected = ( $index == $value ) ? ' selected="selected"' : '';
				$options_body .= '<option' . $selected . ' value="' . $index . '">' . $text . '</option>';
			}

			$body = '<select class="ric-ui-select" name="' . $slug . '">'.$options_body.'</select>';
		break;

		case 'textarea':
			$body = '<textarea class="ric-ui-textarea" name="' . $slug . '">' . $value . '</textarea>';
		break;

		case 'custom':
			$body = $value;
			$header = '';
			$footer = '';
		break;

		default: // textfield
			$body = '<input class="ric-ui-textfield" name="' . $slug . '" type="text"  value="' . $value . '" />';
		break;

		}

		$this->field_id++;

		$ret = $header . $body . $footer;

		if ( $echo ) echo $ret;
		return $ret;
	}

	static function table( $args = array() ) {
		$defaults = array(
			'columns' => array( 'Column 1', 'Column 2' ),
			'data' => array(),
			'table_class' => 'widefat page fixed',
			'table_id' => false,
			'echo' => true,
			'footer' => true
		);

		// Parse the arguments and extract them for easy variable naming
		extract( wp_parse_args( $args, $defaults ) );

		$r = '';

		// Table header and footer
		$head_columns = '';
		foreach( $columns as $column ) {
			$slug = ricUtil::slugify( $column );
			$head_columns .= '<th scope="col" id="' . $slug . '" class="manage-column" style="">' . $column . '</th>';
		}

		$r .= '<thead><tr>' . $head_columns . '</tr></thead>';

		if($footer) $r .= '<tfoot><tr>' . $head_columns . '</tr></tfoot>';

		// Table data
		$i = 0;
		foreach($data as $id => $columns){
			$class = ($i%2) ? 'alternate iedit' : 'iedit';
			$r .= '<tr id="row-' . $id . '" class="' . $class . '">';
			foreach($columns as $k => $v ){
				$r .= '<td class="column-' . $k . '">' . $v . '</td>';
			}
			$i++;
		}

		// Table class and ID
		$table_atts = array(
			'class' => ($table_class) ? $table_class : '',
			'id' => ($table_id) ? $table_id : '',
			'cellspacing' => '0'
		);

		$r = html( 'table', $table_atts, $r );

		if($echo)	echo $r;

		return $r;
	}
}

