<?php
include_once('worker.class.php');
$scripts = array("jquery", "parallax");
$title = "DrakeWork 0.0.1";
$theme = "beta";
$website = 0;
$home = new worker($title, $theme, $website, $scripts);
echo $home->build_site();
?>