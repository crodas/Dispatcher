<?php

require __DIR__ . "/../packages/autoload.php";

foreach (glob(__DIR__ . "/generated/*") as $file) {
    unlink($file);
}
