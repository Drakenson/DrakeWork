<?php
include_once('worker.class.php');
$scripts = array("jquery", "parallax");
$title = "DrakeWork 0.0.1";
if (isset($_GET['theme'])) {$theme = $_GET['theme'];} else {$theme = "beta_orange";};
if (isset($_GET['page'])) {$website = $_GET['page'];} else {$website = 'home';};
$home = new worker($title, $theme, $website, $scripts);
echo $home->build_site();
?>