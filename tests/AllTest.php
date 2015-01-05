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
    public function testBug01Sorting()
    {
        $route = new Route;
        $req   = new Request;
        $req->set('phpunit', $this);
        $out = $route->doRoute($req, array('REQUEST_URI' => '/foo/barxxx'));
        $this->assertEquals($out, 'bug01\foobar');

        $route = new Route;
        $req   = new Request;
        $req->set('phpunit', $this);
        $out = $route->doRoute($req, array('REQUEST_URI' => '/foo/bar'));
        $this->assertEquals($out, 'bug01\barfoo');
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
        $this->assertTrue($req->get('__all__'));
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
        $this->assertTrue($req->get('__all__'));
        $this->assertTrue($req->get('__post__'));
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

    /** 
     * @depends testCompile
     * @expectedException AllTest\NotFoundException
     */
    public function testClassPreRouteFilter()
    {
        $route = new Route;
        $req   = new Request;
        $req->set('phpunit', $this);
        $out = $route->doRoute($req, array('REQUEST_URI' => '/prefix'));
        $this->assertEquals($out, 'SomeClass::save');
        $this->assertTrue($req->get('run_all'));
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
    /**
     *  @depends testCompile
     */
    public function testRoot() 
    {
        $route = new Route;
        $req   = new Request;
        $req->set('phpunit', $this);
        $num = $route->doRoute($req, array('REQUEST_URI' => "/"));
        $this->assertEquals('empty_level_2', $num);
    }

    

    /**
     *  @depends testCompile
     */
    public function testWithNoGroup()
    {
        $route = new Route;
        $req   = new Request;
        $req->set('phpunit', $this);
        $num = $route->doRoute($req, array('REQUEST_URI' => "/zzzsfasd_prefix_93"));
        $this->assertEquals(93, $num);
    }

    /**
     *  @depends testCompile
     */
    public function testWithNoGroupSimple()
    {
        $route = new Route;
        $req   = new Request;
        $req->set('phpunit', $this);
        $controller = $route->doRoute($req, array('REQUEST_URI' => "/deadly-simple"));
        $this->assertEquals($controller, $req->get('controller'));
    }

    public function testComplexUrlWithNoFilter()
    {
        $route = new Route;
        $req   = new Request;
        $req->set('phpunit', $this);
        $controller = $route->doRoute($req, array('REQUEST_URI' => "/hola/que/tal/route"));
        $this->assertEquals($controller, $req->get('controller'));
        $this->assertEquals($req->get('foobar_nofilter'), array('hola', 'que', 'tal'));

        $route = new Route;
        $req   = new Request;
        $req->set('phpunit', $this);
        $controller = $route->doRoute($req, array('REQUEST_URI' => "/router/hola/que/tal"));
        $this->assertEquals($controller, $req->get('controller'));
        $this->assertEquals($req->get('foobar_nofilter'), array('hola', 'que', 'tal'));

        $route = new Route;
        $req   = new Request;
        $req->set('phpunit', $this);
        $controller = $route->doRoute($req, array('REQUEST_URI' => "/routex/hola/que/tal/all"));
        $this->assertEquals($controller, $req->get('controller'));
        $this->assertEquals($req->get('foobar_nofilter'), array('hola', 'que', 'tal'));
    }

    public function testComplexUrl()
    {
        $route = new Route;
        $req   = new Request;
        $req->set('phpunit', $this);
        $controller = $route->doRoute($req, array('REQUEST_URI' => "/loop-00/l-1-1/l-2-3/l-3-4/loop/4/5/bar"));
        $this->assertEquals($controller, $req->get('controller'));
        $this->assertEquals($req->get('numeric'), '00');
        $this->assertEquals($req->get('a'), array('1', '2', '3'));
        $this->assertEquals($req->get('x'), array('1', '3', '4'));
        $this->assertEquals($req->get('b'), array('4', '5'));
    }

}
