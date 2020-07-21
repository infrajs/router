<?php
//404 ошибка
$query = $_SERVER['REQUEST_URI'];
header("Status: 404 Not Found");
http_response_code(404);
echo '<h1>'.$query.'</h1>';
echo '404 Not Found';
