<?php

require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/../lib/Dispatcher/autoload.php";

foreach (glob(__DIR__ . "/generated/*") as $file) {
    unlink($file);
}
