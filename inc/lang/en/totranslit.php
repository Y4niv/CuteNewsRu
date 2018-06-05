<?php
////////////////////////////////////////////////////////
// Function:    totranslit

function totranslit($text, $that = '-'){

    $text = strtolower(strip_tags(html_entity_decode($text)));
    $text = preg_replace('/\W/', $that, strip_tags($text));
    $text = chicken_dick($text, $that);

return $text;
}
?>