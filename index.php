<?php
if (isset($_GET['theme'])) {$themerequest = $_GET['theme'];} else {$themerequest = "beta_orange";};
if (isset($_GET['page'])) {$website = $_GET['page'];} else {$website = 'Home';};

include_once('worker.class.php');
$scripts = array("jquery");
$style = "content/style/style.css";
$title = "DrakeWork 0.0.1";

$home = new worker($title, $themerequest, $website, $scripts, $style);
echo $home->build_site();
?>