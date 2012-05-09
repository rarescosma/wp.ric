<?php

require dirname(__FILE__).'/lib/Plugin.class.php';
$WPLessPlugin = WPPluginToolkitPlugin::create('WPLess', __FILE__,'WPLessPlugin');
$WPLessPlugin->dispatch();

?>