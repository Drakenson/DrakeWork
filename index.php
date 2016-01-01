<?php
if (isset($_GET['theme'])) {$themerequest = $_GET['theme'];} else {$themerequest = "beta_orange";};
if (isset($_GET['page'])) {$website = $_GET['page'];} else {$website = 'Home';};

include_once('worker.class.php');
include_once('variables.php');
include_once("themes/$themerequest/variables.base");
$scripts = array("jquery", "parallax");
$title = "DrakeWork 0.0.1";

$home = new worker($title, $themerequest, $website, $scripts, $construct, $theme);
echo $home->build_site();
?>