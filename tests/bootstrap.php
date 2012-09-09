<?php

require "vendor/autoload.php";
require "lib/Dispatcher/autoload.php";

foreach (glob(__DIR__ . "/generated/*") as $file) {
    unlink($file);
}
