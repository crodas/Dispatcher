<?php
require "lib/Dispatcher/autoload.php";
require "vendor/autoload.php";

/**
 * @Route("/foobar/{something}.{ext:type}") 
 * @Route("/foobar/{something}", set={type:"html"}) 
 * @Method("GET") 
 */
function foo() {
}

$c = new \Dispatcher\Generator;
$c->addFile(__FILE__);
$compiler = $c->generate();
var_dump($compiler);
