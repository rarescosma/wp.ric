<?php
/*
Template Name: Test
*/
?>
<?php get_header(); ?>
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<div id="container">
	<div id="content">
	<h2><?php the_title(); ?></h2>
	<?php the_content(); ?>
	</div>
	<!-- /end #content --> 
	<br class="clear" />
</div>

<!-- /end #container -->

<?php endwhile; endif; ?> 
<?php get_footer(); ?>