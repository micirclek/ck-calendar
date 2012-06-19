<?php
require_once('../include/init.php');
require_once(BASE_PATH . '/lib/Response.php');

$response = new Response();
$response->set_status('error');

$response->add_item('msg', 'unknown error');

echo $response->emit();
?>
