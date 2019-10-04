<?php

function format_title($title_txt, $default=FALSE) {
    if (!isset($title_txt) || empty($title_txt))
        return $default;
    if(is_array($title_txt)) {
        $title_txt = implode(", ", $title_txt);
    }
    return htmlspecialchars($title_txt);
}


// convert large sizes (in bytes) to better readable unit
function filesize_formatted($size)
{
	$units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	$power = $size > 0 ? floor(log($size, 1024)) : 0;
	return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}

?>
