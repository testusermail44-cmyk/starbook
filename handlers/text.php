<?php
function trimText($text, $limit)
{
    $endPos = mb_strpos($text, '<div class="title">');
    if ($endPos !== false) {
        $text = mb_substr($text, 0, $endPos);
    }
    $clean = strip_tags($text);
    if (mb_strlen($clean) > $limit) {
        $clean = mb_substr($clean, 0, $limit) . '...';
    }
    return $clean;
}

function getPlurals($count, $one, $two, $many) {
    return $count % 10 == 1 ? $one : ($count % 10 == 2 || $count % 10 == 3 || $count % 10 == 4 ? $two : $many);
}
?>