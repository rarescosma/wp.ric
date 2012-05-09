<?php

$post_type = '';
$admin_page = '';

extract($_GET);
header('Content-type:text/css');

?>

/* Dashboard Logo */
#icon-index {
	width:<?php echo $logo_width; ?>px;
	height:<?php echo $logo_height; ?>px;
	background:url("../images/logos/admin.png") left top no-repeat;;
	margin:0 20px 10px 8px;
	position:relative;
	top:12px;
}

/* Hide the admin footer */
/* Metaboxes */
.acf_image_uploader img {
    max-width:300px;
    height:auto;
}
