<?php

use MatthiasMullie\Minify;
use Core\Helper;

foreach (Helper::listFolder(PATH_HOME . VENDOR) as $item) {

    //Minify Assets Vendor
    foreach (Helper::listFolder(PATH_HOME . VENDOR . $lib . "/assets") as $file) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $name = pathinfo($file, PATHINFO_BASENAME);
        if (preg_match('/(^\.min)\.[js|css]$/i', $file) && !file_exists(PATH_HOME . VENDOR . $lib . "/assets/{$name}.min.{$ext}")) {
            if (preg_match('/\.js$/i', $file))
                $minifier = new Minify\JS(file_get_contents(PATH_HOME . VENDOR . $lib . "/assets/{$file}"));
            else
                $minifier = new Minify\CSS(file_get_contents(PATH_HOME . VENDOR . $lib . "/assets/{$file}"));

            $minifier->minify(PATH_HOME . VENDOR . $lib . "/assets/{$name}.min.{$ext}");
        }
    }

    //Exe File
    if (file_exists(PATH_HOME . VENDOR . "{$item}/config.php"))
        require_once PATH_HOME . VENDOR . "{$item}/config.php";

    //Get Entitys

    //Up Data

}

$data = [
    "response" => 3,
    "data" => HOME . "dashboard"
];
