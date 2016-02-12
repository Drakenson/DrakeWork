<?php
$a = file_get_contents("themes/beta_orange/variables.json");
$obj = json_decode($a, true);
echo "<pre>";
var_dump($obj);
echo "</pre>";
?>