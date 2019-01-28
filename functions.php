<?php

if (!function_exists('rsssl_uses_gutenberg')) {
    function rsssl_uses_gutenberg()
    {

    if (function_exists('has_block') && !class_exists('Classic_Editor')) {
    return true;
    }
    return false;
    }
}

?>