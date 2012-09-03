<?php

require "vendor/autoload.php";
require "lib/Dispatcher/autoload.php";

foreach (glob(__DIR__ . "/tmp/*") as $file) {
    unlink($file);
}
