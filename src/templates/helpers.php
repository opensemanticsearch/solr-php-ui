<?php
function format_title($title_txt, $default=FALSE) {
    if (!isset($title_txt) || empty($title_txt))
        return $default;
    if(is_array($title_txt)) {
        $title_txt = implode(", ", $title_txt);
    }
    return htmlspecialchars($title_txt);
}
?>
