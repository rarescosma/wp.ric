<!DOCTYPE html>
<html lang="en" dir="ltr" class="no-js">
<head>
<meta charset="utf-8">

<title><?php wp_title( '&laquo;', true, 'right' ); bloginfo( 'name' ); ?></title>

<link rel="shortcut icon" href="<?php echo PT_URL; ?>favicon.ico" />
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />

<!--[if !IE 6]><!-->
<link rel="stylesheet" href="<?php echo PT_URL; ?>css/reset.css" media="screen, projection">
<link rel="stylesheet" href="<?php echo PT_URL; ?>css/global.css" media="screen, projection">
<!--<![endif]-->

<!--[if IE 6]>
<link rel="stylesheet" href="http://universal-ie6-css.googlecode.com/files/ie6.0.3.css" media="screen, projection">
<![endif]-->

<?php 
/**** JavaScript ****/
ricUtil::jquery();
ricUtil::js( 'js/assets/modernizr-1.5.min.js' );
ricUtil::js( 'js/global.js', true );

/**** JavaScript Options ****/
$options = array();
if( count( $options ) ) 
	wp_localize_script( 'ric-global-js', 'globalOptions', $options );
?>

<?php wp_head(); ?>
</head>

<!--[if lt IE 7 ]> <body class="ie6"> <![endif]-->
<!--[if IE 7 ]>    <body class="ie7"> <![endif]-->
<!--[if IE 8 ]>    <body class="ie8"> <![endif]-->
<!--[if IE 9 ]>    <body class="ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <body> <!--<![endif]-->

<header>
	<?php
		$args = array(
			'menu' => 'Header Menu',
			'container_id' => 'nav'
		);
		wp_nav_menu( $args );
	?>
</header>