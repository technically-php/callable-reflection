<?php

if (! function_exists('my_global_function')) {
    function my_global_function($value): bool
    {
        return is_object($value);
    }
}
