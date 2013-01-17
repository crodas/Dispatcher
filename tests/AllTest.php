<?php

use Dispatcher\Generator,
    AllTest\Route,
    AllTest\Request;

class AllTest extends \phpunit_framework_testcase
{
    public function testCompile()
    {
        $gen  = new Generator;
        $file = __DIR__ . '/generated/' . __CLASS__ . '.php';
        $this->assertFalse(file_Exists($file));
        $gen->addDirectory(__DIR__ . '/input');
        $gen->setNamespace(__CLASS__);
        $gen->setOutput($file);
        $gen->generate();

        $this->assertTrue(file_Exists($file));

        require ($file);
        // add mockup cache class
        require __DIR__ . "/input/cache_class.php";
    }

    /** @depends testCompile */
    public function testUrlSorting()
    {
        $route = new Route;
        $req   = new Request;
        $req->set('phpunit', $this);
        $out = $route->doRoute($req, array('REQUEST_URI' => '/function/reverse'));
        $this->assertEquals($out, 'some_function');
        $this->assertEquals(NULL, $req->get('reverse'));

        $out = $route->doRoute($req, array('REQUEST_URI' => '/function/esrever'));
        $this->assertEquals($out, 'some_function');
        $this->assertEquals('esrever', $req->get('reverse'));
    }

    /** @depends testCompile */
    public function testSetIfEmpty()
    {
        $route = new Route;
        $req   = new Request;
        $req->set('phpunit', $this);
        $route->setCache(TestCacheClass::getInstance());
        $out = $route->doRoute($req, array('REQUEST_URI' => '/ifempty/algo'));
        $this->assertEquals('ALGO', $req->get('algo-alias'));
    }

    /** @depends testCompile */
    public function testSetIfEmptyCached()
    {
        $route = new Route;
        $req   = new Request;
        $req->set('phpunit', $this);
        $route->setCache(TestCacheClass::getInstance());
        $out = $route->doRoute($req, array('REQUEST_URI' => '/ifempty/algo'));
        $this->assertEquals('ALGO', $req->get('algo-alias'));
        $this->assertTrue($req->get('filter:cached:algo-alias'));
    }

    /** 
     * @depends testCompile
     * @expectedException AllTest\NotFoundException
     */
    public function testPreRouteFilter()
    {
        $route = new Route;
        $req   = new Request;
        $req->set('phpunit', $this);
        $req->set('fail_session', true);
        $out = $route->doRoute($req, array('REQUEST_URI' => '/prefix', 'REQUEST_METHOD' => 'POST'));
    }
    /** @depends testCompile */
    public function testClassInheritance()
    {
        $route = new Route;
        $req   = new Request;
        $req->set('phpunit', $this);
        $out = $route->doRoute($req, array('REQUEST_URI' => '/prefix', 'REQUEST_METHOD' => 'POST'));
        $this->assertEquals($out, 'SomeClass::save');
    }

    /** 
     * @depends testCompile
     * @expectedException AllTest\NotFoundException
     */
    public function testClassInheritanceNotFound()
    {
        $route = new Route;
        $req   = new Request;
        $req->set('phpunit', $this);
        $out = $route->doRoute($req, array('REQUEST_URI' => '/prefix', 'REQUEST_METHOD' => 'GET'));
    }


    /**
     *  @depends testCompile
     */
    public function testComplex1()
    {
        $route = new Route;
        $req   = new Request;
        $req->set('phpunit', $this);
        $num = $route->doRoute($req, array('REQUEST_URI' => '/foobar/12345'));
        $this->assertEquals($num, 'SomeMethodController::get');

        $num = $route->doRoute($req, array('REQUEST_URI' => '/foobar/12345', 'REQUEST_METHOD' => 'DELETE'));
        $this->assertEquals($num, 'SomeMethodController::modify');

        $num = $route->doRoute($req, array('REQUEST_URI' => '/foobar/12345/something', 'REQUEST_METHOD' => 'DELETE'));
        $this->assertEquals($num, 'SomeMethodController::modify_something');
    }

    /**
     *  @depends testCompile
     *  @expectedException AllTest\NotFoundException
     */
    public function testBug001()
    {
        $route = new Route;
        $req   = new Request;
        $req->set('phpunit', $this);
        $num = $route->doRoute($req, array('REQUEST_URI' => '/something/silly'));
        $this->assertEquals($num, $req->get('return'));
    }
    

}
