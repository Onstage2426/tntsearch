<?php

if (!function_exists('fuzzyMatch')) {
    function fuzzyMatch($pattern, $items)
    {
        $fm = new TeamTNT\TNTSearch\TNTFuzzyMatch;
        return $fm->fuzzyMatch($pattern, $items);
    }
}

if (!function_exists('fuzzyMatchFromFile')) {
    function fuzzyMatchFromFile($pattern, $path)
    {
        $fm = new TeamTNT\TNTSearch\TNTFuzzyMatch;
        return $fm->fuzzyMatchFromFile($pattern, $path);
    }
}
