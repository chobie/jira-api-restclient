<?php

$result = array(
	'_SERVER' => $_SERVER,
	'_GET' => $_GET,
	'_POST' => $_POST,
	'_COOKIE' => $_COOKIE,
	'_FILES' => $_FILES,
	'INPUT' => file_get_contents('php://input'),
);

$path_info_variables = array();
$path_info = explode('/', trim($_SERVER['PATH_INFO'], '/'));

while ( $path_info ) {
	$variable_name = array_shift($path_info);
	$variable_value = array_shift($path_info);

	$path_info_variables[$variable_name] = $variable_value;
}

if ( array_key_exists('http_code', $path_info_variables) ) {
	// Complete code list: https://developer.mozilla.org/en-US/docs/Web/HTTP/Response_codes.
	$http_code_messages = array(
		200 => 'OK',
		201 => 'Created',
		204 => 'No Content',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not Found',
	);

	$http_code = (int)$path_info_variables['http_code'];

	if ( $http_code !== 200 ) {
		header('HTTP/1.1 ' . $http_code . ' ' . $http_code_messages[$http_code]);
	}
}

if ( array_key_exists('response_mode', $path_info_variables) ) {
	$response_mode = $path_info_variables['response_mode'];
}
else {
	$response_mode = 'trace';
}

if ( $response_mode === 'trace' ) {
	echo serialize($result);
}
elseif ( $response_mode === 'empty' ) {
	echo '';
}
elseif ( $response_mode === 'null' ) {
	echo null;
}

