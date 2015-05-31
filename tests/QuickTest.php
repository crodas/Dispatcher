<?php

use Symfony\Component\HttpFoundation\Request;
use Dispatcher\Router;

/** @NotFound */
function __not_found($req) {
    $req->attributes->set('__not_found__', true);
}

/** @postRoute @Last */
function __1last($req, $args, $return) {
    $phpunit = $req->attributes->get('phpunit');
    $phpunit->assertTrue($req->attributes->get('__1b'));
    $req->attributes->set('__1a', 'xxx');
    return $return;
}

/** @postRoute @First */
function __1first($req, $args, $return) {
    $phpunit = $req->attributes->get('phpunit');
    $phpunit->assertNull($req->attributes->get('__1a'));
    $req->attributes->set('__1b', true);
    return $return;
}

/** @preRoute @Last */
function __last($req) {
    $phpunit = $req->attributes->get('phpunit');
    $phpunit->assertTrue($req->attributes->get('__b'));
    $req->attributes->set('__a', 'xxx');
    return true;
}

/** @preRoute @First */
function __first($req) {
    $phpunit = $req->attributes->get('phpunit');
    $phpunit->assertNull($req->attributes->get('__a'));
    $req->attributes->set('__b', true);
    return true;
}

/** @postRoute @Last */
function __last_for_all($req, $args, $return)
{
    $req->attributes->set('last_for_all', true);
    return $return;
}

/** @preRoute buffer */
function __buffer_start($req)
{
    ob_start();
    return true;
}

/** @postRoute buffer @Last */
function __buffer_end($req, $args, $return)
{
    $req->attributes->set('__buffer__', ob_get_clean());
    return $return;
}

/** @Filter foo */
function filter_1($Req) {
    $req->attributes->set('filter_1', true);
}
/** @Filter bar */
function filter_2() {
    $req->attributes->set('filter_2', true);
}

/**
 *  @Route("/foo/bar/{foo}")
 *  @Route("/foo/bar/{bar}", "foobar_x")
 *  @Route("/foo/bar/xxx-{bar:xx}", "foobar_xx")
 *  @Route("/foo/bar/xxx", foobar_xx)
 */
function TestingMultiple()
{
}

/** @Route("/buffer") @buffer */
function TestBuffer()
{
    echo "Hi there!\n";
}

/**
 *  @Route("/foo/function")
 *  @Route("/xxx/{foobar}")
 *  @Method GET
 */
function Controller($req)
{
    $self = $req->attributes->get('phpunit');
    $self->assertTrue(true);
    $req->attributes->set('return', 'fnc:' . mt_rand());
    
    return $req->attributes->get('return');
}

class Foo
{

    /**
     *  @Filter foobar
     */
    function simple_filter($req, $field, $value)
    {
        $self = $req->attributes->get('phpunit');
        $self->assertEquals($field, 'foobar');
        $req->attributes->set('simple_filter', true);
        return $value == 'foobar';
    }

    /**
     *  @Filter ext
     */
    function ext_filter($req, $field, $value)
    {
        return $value == 'php';
    }

    /**
     *  @Route("/foo/method")
     */
    public function Bar($req)
    {
        $self = $req->attributes->get('phpunit');
        $self->assertTrue(true);
        $req->attributes->set('return', 'method:' . mt_rand());
    
        return $req->attributes->get('return');
    }

    /**
     * @Route("/foo/{foobar}.{ext:extension}") 
     */
    function TestingComplexUri($req)
    {
        $self = $req->attributes->get('phpunit');
        $self->assertTrue(true);
        $self->assertEquals($req->attributes->get('extension'), 'php');
    }
}

class QuickTest extends \phpunit_framework_testcase
{
    public function testCompile()
    {
        $file = __DIR__ . '/generated/' . __CLASS__ . '.php';
        define('xfile', $file);

        $router = new Dispatcher\Router($file);
        $router
            ->addFile(__FILE__)
            ->setNamespace(__CLASS__);

        $this->assertFalse(file_Exists($file));
        $router->load();
        $this->assertTrue(file_Exists($file));
    }

    /**
     *  @depends testCompile
     */
    public function testMatch()
    {
        $route = new Router(xfile);
        $req   = Request::create('/foo/function');
        $req->attributes->set('phpunit', $this);
        $num = $route->doRoute($req);
        $this->assertEquals($num, $req->attributes->get('return'));
    }

    /**
     *  @depends testCompile
     */
    public function testMatchWithFilter()
    {
        $route = new Router(xfile);
        $req   = Request::create('/xxx/foobar');
        $req->attributes->set('phpunit', $this);
        $num = $route->doRoute($req);
        $this->assertEquals($num, $req->attributes->get('return'));
        $this->assertTrue($req->attributes->get('simple_filter'));
    }

    /**
     *  @depends testCompile
     */
    public function testMatchMethod()
    {
        $route = new Router(xfile);
        $req   = Request::create('/foobar/method');
        $req->attributes->set('phpunit', $this);
        $num = $route->doRoute($req);
        $this->assertEquals($num, $req->attributes->get('return'));
    }

    /**
     *  @depends testCompile
     */
    public function testMatchMixed()
    {
        $route = new Router(xfile);
        $req   = Request::create('/foo/foobar.php');
        $req->attributes->set('phpunit', $this);
        $num = $route->doRoute($req);
    }

    /**
     *  @depends testCompile
     */
    public function test404()
    {
        $route = new Router(xfile);
        $req   = Request::create('/foo/function/something');
        $req->attributes->set('phpunit', $this);
        $num = $route->doRoute($req);
        $this->assertTrue($req->attributes->get('__not_found__'));
    }

    /**
     *  @depends testCompile
     */
    public function test404WithFilter()
    {
        $route = new Router(xfile);
        $req   = Request::create('/xxx/barfoo');
        $req->attributes->set('phpunit', $this);
        $num = $route->doRoute($req);
        $this->assertTrue($req->attributes->get('__not_found__'));
   }

    /**
     *  @depends testCompile
     */
    public function testLast()
    {
        $route = new Router(xfile);
        $req   = Request::create('/buffer');
        $req->attributes->set('phpunit', $this);
        $route->doRoute($req);
        $this->assertEquals($req->attributes->get('__buffer__'), "Hi there!\n");
        $this->assertTrue($req->attributes->get('last_for_all'));
    }

    /**
     *  @depends testCompile
     */
    public function testGenerator()
    {
        //$this->assertequals(\quicktest\route::getroute("foobar_x", 'foobar'), '/foo/bar/foobar');
        //$this->assertEquals(Router(xfile)::getRoute("foobar_xx", 'foobar'), '/foo/bar/xxx-foobar');
        //$this->assertEquals(Router(xfile)::getRoute("foobar_xx"), '/foo/bar/xxx');
    }

    /**
     *  @depends testCompile
     *  @expectedException QuickTest\RouteNotFoundException
     */
    public function ztestGeneratorNotFound()
    {
        //Router(xfile)::getRoute("foobar_xdsdasdada");
    }

    /**
     *  @depends testCompile
     *  @expectedException QuickTest\RouteNotFoundException
     */
    public function ztestGeneratorInvalidArgs()
    {
        //\quicktest\route::getroute("foobar_x", 'foobar', 'xxx');
    }
}
