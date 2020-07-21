<?php
//404 ошибка
$query = $_SERVER['REQUEST_URI'];
echo '<h1>'.$query.'</h1>';
echo '404 Not Found';
header("Status: 404 Not Found");
http_response_code(404);