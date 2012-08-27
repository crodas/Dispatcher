<?php
require "lib/Dispatcher/autoload.php";
require "vendor/autoload.php";

/**
 * @Route("/print/{something}.{ext:type}", set={print:true}) 
 * @Route("/print/{something}", set={print: true, type:"html"}) 
 * @Route("/{something}.{ext:type}")
 * @Route("/{something}", set={type:"html"}) 
 * @Method("GET", "HEAD")
 */
function foo() {
}

/**
 *  @Route("/{something}.{ext:type}")
 *  @Method("POST")
 */
function save_post() {
}

/** @Route("/") */
function index() {
}

$c = new \Dispatcher\Generator;
$c->addFile(__FILE__);
$compiler = $c->generate();
var_dump($compiler);
