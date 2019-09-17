<?php

/**
 * Проверяет на наличие ошибок
 * @param array $errors
 * @param string $field
 * @return mixed|string
 */
function checkErrors(array $errors, string $field) {
    if (isset($errors) && !empty($errors)) {
        return array_key_exists($field, $errors) ? $errors[$field] : '';
    }
}


/**
 * Метод, осуществляющий сжатие строки
 * @param string $str
 * @return string
 */
function stringCompressor(string $str) {
    $result     = '';
    $first_char = $str[0];
	$count      = 0;

	for ($i = 0; $i < strlen($str); $i++) {
        if ($str[$i] == $first_char) $count++;
        else {
            $result    .= $first_char . $count;
            $first_char = $str[$i];
            $count      = 1;
        }
    }
	$result = $result . $first_char . $count;

	if (strlen($result) > strlen($str)) return $str;
	return $result;
}