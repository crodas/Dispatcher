<?php

use Dispatcher\Generator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;
use Dispatcher\Router;

class AllTest extends \phpunit_framework_testcase
{
    public function testCompile()
    {
        define('file', __DIR__ . '/generated/' . __CLASS__ . '.php');
        $gen  = new Generator;
        $this->assertFalse(file_Exists(file));
        $gen->addDirectory(__DIR__ . '/input');
        $gen->setOutput(file);
        $gen->generate();

        $this->assertTrue(file_Exists(file));

        // add mockup cache class
        require __DIR__ . "/input/cache_class.php";
    }
    
    /** @depends testCompile */
    public function testBug01Sorting()
    {
        $route = new Router(file);
        $req   =  Request::create('/foo/barxxx?x=1', 'GET');
        $req->attributes->get('phpunit', $this);
        $out = $route->doRoute($req);
        $this->assertEquals($out, 'bug01\foobar');

        $req   =  Request::create('/foo/bar?x=1', 'GET');
        $route = new Router(file);
        $req->attributes->get('phpunit', $this);
        $out = $route->doRoute($req);
        $this->assertEquals($out, 'bug01\barfoo');
    }

    /** @depends testCompile */
    public function testUrlSorting()
    {
        $route = new Router(file);
        $req   = Request::create('/function/reverse?x=1', 'GET');
        $req->attributes->get('phpunit', $this);
        $out = $route->doRoute($req);
        $this->assertEquals($out, 'some_function');
        $this->assertFalse($req->attributes->has('reverse'));

        $req   = Request::create('/function/esrever', 'GET');
        $req->attributes->get('phpunit', $this);
        $out = $route->doRoute($req);
        $this->assertEquals($out, 'some_function');
        $this->assertEquals('esrever', $req->attributes->get('reverse'));
        $this->assertTrue($req->attributes->get('__all__'));
    }

    /** @depends testCompile */
    public function testSetIfEmpty()
    {
        $route = new Router(file);
        $req   = Request::create('/ifempty/algo?x=1', 'GET');
        $req->attributes->set('phpunit', $this);
        $route->setCache(TestCacheClass::getInstance());
        $out = $route->doRoute($req);
        $this->assertEquals('ALGO', $req->attributes->get('algo-alias'));
        $this->assertTrue($req->attributes->get('__all__'));
        $this->assertTrue($req->attributes->get('__post__'));
    }

    /** @depends testCompile */
    public function testSetIfEmptyCached()
    {
        $route = new Router(file);
        $route->setCache(TestCacheClass::getInstance());
        $req   = Request::create('/ifempty/algo?x=1', 'GET');
        $req->attributes->set('phpunit', $this);
        $out = $route->doRoute($req);
        $this->assertEquals('ALGO', $req->attributes->get('algo-alias'));
        $this->assertTrue($req->attributes->get('filter:cached:algo-alias'));
    }

    /** 
     * @depends testCompile
     * @expectedException Dispatcher\Exception\HttpException
     */
    public function testPreRouteFilter()
    {
        $route = new Router(file);
        $req = Request::create('/prefix', 'POST');
        $out = $route->doRoute($req);
        $req->attributes->set('phpunit', $this);
        $req->attributes->set('fail_session', true);
        $out = $route->doRoute($req);
    }

    /** 
     * @depends testCompile
     * @expectedException Dispatcher\Exception\HttpException
     */
    public function testClassPreRouteFilter()
    {
        $route = new Router(file);
        $req   = Request::create('/prefix');
        $req->attributes->set('phpunit', $this);
        $out = $route->doRoute($req);
        $this->assertEquals($out, 'SomeClass::save');
        $this->assertTrue($req->attributes->get('run_all'));
    }

    /** @depends testCompile */
    public function testClassInheritance()
    {
        $route = new Router(file);
        $req = Request::create('/prefix', 'POST');
        $req->attributes->set('phpunit', $this);
        $out = $route->doRoute($req);
        $this->assertEquals($out, 'SomeClass::save');
        $this->assertTrue($req->attributes->get('run_all'));
    }

    /** 
     * @depends testCompile
     * @expectedException Dispatcher\Exception\HttpException
     */
    public function testClassInheritanceNotFound()
    {
        $route = new Router(file);
        $req   = Request::create('/prefix', 'GET');
        $req->attributes->set('phpunit', $this);
        $out = $route->doRoute($req);
    }


    /**
     *  @depends testCompile
     */
    public function testComplex1()
    {
        $route = new Router(file);
        $req   = Request::create('/foobar/12345');
        $req->attributes->set('phpunit', $this);
        $num = $route->doRoute($req);
        $this->assertEquals($num, 'SomeMethodController::get');

        $req   = Request::create('/foobar/12345', 'DELETE');
        $req->attributes->set('phpunit', $this);
        $num = $route->doRoute($req);
        $this->assertEquals($num, 'SomeMethodController::modify');

        $req   = Request::create('/foobar/12345/something', 'DELETE');
        $req->attributes->set('phpunit', $this);
        $num = $route->doRoute($req);
        $this->assertEquals($num, 'SomeMethodController::modify_something');
    }

    /**
     *  @depends testCompile
     *  @expectedException Dispatcher\Exception\HttpException
     */
    public function testBug001()
    {
        $route = new Router(file);
        $req   = Request::create('/somthing/silly');
        $req->attributes->set('phpunit', $this);
        $num = $route->doRoute($req);
        $this->assertEquals($num, $req->attributes->get('return'));
    }
    /**
     *  @depends testCompile
     */
    public function testRoot() 
    {
        $route = new Router(file);
        $req   = Request::create('/');
        $req->attributes->set('phpunit', $this);
        $num = $route->doRoute($req);
        $this->assertEquals('empty_level_2', $num);
    }

    

    /**
     *  @depends testCompile
     */
    public function testWithNoGroup()
    {
        $route = new Router(file);
        $req   = Request::create('/zzzsfasd_prefix_93');
        $req->attributes->set('phpunit', $this);
        $num = $route->doRoute($req);
        $this->assertEquals(93, $num);
    }

    /**
     *  @depends testCompile
     */
    public function testWithNoGroupSimple()
    {
        $route = new Router(file);
        $req   = Request::create('/deadly-simple');
        $req->attributes->set('phpunit', $this);
        $controller = $route->doRoute($req, array('REQUEST_URI' => "/deadly-simple"));
        $this->assertEquals($controller, $req->attributes->get('controller'));
    }

    public function testComplexUrlWithNoFilter()
    {
        $route = new Router(file);
        $req   = Request::create('/hola/que/tal/route');
        $req->attributes->set('phpunit', $this);
        $controller = $route->doRoute($req);
        $this->assertEquals($controller, $req->attributes->get('controller'));
        $this->assertEquals(array('hola', 'que', 'tal'), $req->attributes->get('foobar_nofilter'));

        $req   = Request::create('/router/hola/que/tal');
        $req->attributes->set('phpunit', $this);
        $controller = $route->doRoute($req);
        $this->assertEquals($controller, $req->attributes->get('controller'));
        $this->assertEquals(array('hola', 'que', 'tal'), $req->attributes->get('foobar_nofilter'));

        $req   = Request::create('/routex/hola/que/tal/all');
        $req->attributes->set('phpunit', $this);
        $controller = $route->doRoute($req);
        $this->assertEquals($controller, $req->attributes->get('controller'));
        $this->assertEquals(array('hola', 'que', 'tal'), $req->attributes->get('foobar_nofilter'));
    }

    public function testComplexUrl()
    {
        $route = new Router(file);
        $req   = Request::create("/loop-00/l-1-1/l-2-3/l-3-4/loop/4/5/bar");
        $req->attributes->set('phpunit', $this);
        $controller = $route->doRoute($req);
        $this->assertEquals($controller, $req->attributes->get('controller'));
        $this->assertEquals($req->attributes->get('numeric'), '00');
        $this->assertEquals($req->attributes->get('a'), array('1', '2', '3'));
        $this->assertEquals($req->attributes->get('x'), array('1', '3', '4'));
        $this->assertEquals($req->attributes->get('b'), array('4', '5'));
    }

    public static function urlAndcontrollers()
    {
        $ajax1 = Request::create('/just-ajax', 'GET', [], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
        $ajax2 = Request::create('/just-ajax-2', 'GET', [], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json']);
        return array(
            array('/numeric/0/-2', 'numbers'),
            array('/numeric/0/2', 'numbers'),
            array('/numeric/2/0', 'numbers'),
            array('/numeric/1/2', 'numbers'),
            array('/numeric/0/2.4', 'numbers'),
            array('/numeric/1.1/0', 'numbers'),
            array('/numeric/1.1/2.4', 'numbers'),
            array('/int/1/2', 'x_int'),
            array('/crodas@php.net', 'email_controller'),
            array('/aef123456789afedbdbaaaaa', 'mongoid_controller'),
            array($ajax1, 'is_ajax'),
            array($ajax2, 'is_ajax_json'),
        );
    }

    public static function urlAndControllers404()
    {
        $ajax2 = Request::create('/just-ajax-2', 'GET', [], [], [], ['CONTENT_TYPE' => 'application/xml']);
        return array(
            array('/int/1.9/2.9'),
            array('/int/1/2x'),
            array('/1crodas@1phpnet'),
            array('/aef123456789afedbdbaaaaaa'),
            array('/aef123456789afedbdbaaaaz'),
            array('/just-ajax'),
            array($ajax2),
        );
    }

    /**
     *  @dataProvider urlAndControllers404
     *  @expectedException Dispatcher\Exception\HttpException
     */
    public function testBuiltInFilter404($url)
    {
        $route = new Router(file);
        if (is_string($url)) {
            $url = Request::create($url);
        }
        $this->assertEquals(null, $route->doRoute($url));
    }

    /**
     *  @dataProvider urlAndControllers
     */
    public function testBuiltInFilter($url, $controller)
    {
        $route = new Router(file);
        if (is_string($url)) {
            $url = Request::create($url);
        }
        $this->assertEquals($controller, $route->doRoute($url));
    }

    public function testCustomErrorHandler()
    {
        $route = new Router(file);
        $req   = Request::create('/something/silly/error');
        $this->assertEquals('Handling exception RuntimeException', $route->doRoute($req));
    }

}
