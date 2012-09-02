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

/**
 *  @Route("/article/{something}", set={lang:"en"})
 *  @Route("/article/{something}.{ext:type}", set={lang:"en"})
 *  @Route("/articulo/{something}", set={lang:"en"})
 *  @Route("/articulo/{something}.{ext:type}", set={lang:"es"})
 */
function show_article()
{
}

/** @Filter("something") */
function filter_aritle() {
}

$c = new \Dispatcher\Generator;
$c->addFile(__FILE__);
$c->setNamespace('dasdas');
$compiler = $c->generate();
var_dump($compiler);
