<?php
require_once 'dbHandler.php';
$p = new printHandler();

$ids = ['25', '26', '25'];
$p->saveSelect($ids, '')->Output();



?>
