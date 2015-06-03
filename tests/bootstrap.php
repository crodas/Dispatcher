<?php

require __DIR__ . "/../vendor/autoload.php";

foreach (glob(__DIR__ . "/generated/*") as $file) {
    unlink($file);
}
