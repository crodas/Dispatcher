<?php
/** @NotFound */
function __not_found($req) {
    $req->set('__not_found__', true);
}

/** @postRoute @Last */
function __1last($req, $args, $return) {
    $phpunit = $req->get('phpunit');
    $phpunit->assertTrue($req->Get('__1b'));
    $req->set('__1a', 'xxx');
    return $return;
}

/** @postRoute @First */
function __1first($req, $args, $return) {
    $phpunit = $req->get('phpunit');
    $phpunit->assertNull($req->Get('__1a'));
    $req->set('__1b', true);
    return $return;
}

/** @preRoute @Last */
function __last($req) {
    $phpunit = $req->get('phpunit');
    $phpunit->assertTrue($req->Get('__b'));
    $req->set('__a', 'xxx');
    return true;
}

/** @preRoute @First */
function __first($req) {
    $phpunit = $req->get('phpunit');
    $phpunit->assertNull($req->Get('__a'));
    $req->set('__b', true);
    return true;
}

/** @postRoute @Last */
function __last_for_all($req, $args, $return)
{
    $req->set('last_for_all', true);
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
    $req->set('__buffer__', ob_get_clean());
    return $return;
}

/** @Filter foo */
function filter_1($Req) {
    $Req->set('filter_1', true);
}
/** @Filter bar */
function filter_2() {
    $Req->set('filter_2', true);
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
    $self = $req->get('phpunit');
    $self->assertTrue(true);
    $req->set('return', 'fnc:' . mt_rand());
    
    return $req->get('return');
}

class Foo
{

    /**
     *  @Filter foobar
     */
    function simple_filter($req, $field, $value)
    {
        $self = $req->get('phpunit');
        $self->assertEquals($field, 'foobar');
        $req->set('simple_filter', true);
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
        $self = $req->get('phpunit');
        $self->assertTrue(true);
        $req->set('return', 'method:' . mt_rand());
    
        return $req->get('return');
    }

    /**
     * @Route("/foo/{foobar}.{ext:extension}") 
     */
    function TestingComplexUri($req)
    {
        $self = $req->get('phpunit');
        $self->assertTrue(true);
        $self->assertEquals($req->get('extension'), 'php');
    }
}

class QuickTest extends \phpunit_framework_testcase
{
    public function testCompile()
    {
        $gen  = new Dispatcher\Generator;
        $file = __DIR__ . '/generated/' . __CLASS__ . '.php';
        $this->assertFalse(file_Exists($file));
        $gen->addFile(__FILE__); 
        $gen->setNamespace(__CLASS__);
        $gen->setOutput($file);
        $gen->generate();

        $this->assertTrue(file_Exists($file));

        require ($file);
    }

    /**
     *  @depends testCompile
     */
    public function testMatch()
    {
        $route = new \QuickTest\Route;
        $req   = new \QuickTest\Request;
        $req->set('phpunit', $this);
        $num = $route->doRoute($req, array('REQUEST_URI' => '/foo/function'));
        $this->assertEquals($num, $req->get('return'));
    }

    /**
     *  @depends testCompile
     */
    public function testMatchWithFilter()
    {
        $route = new \QuickTest\Route;
        $req   = new \QuickTest\Request;
        $req->set('phpunit', $this);
        $num = $route->doRoute($req, array('REQUEST_URI' => '/xxx/foobar'));
        $this->assertEquals($num, $req->get('return'));
        $this->assertTrue($req->get('simple_filter'));
    }

    /**
     *  @depends testCompile
     */
    public function testMatchMethod()
    {
        $route = new \QuickTest\Route;
        $req   = new \QuickTest\Request;
        $req->set('phpunit', $this);
        $num = $route->doRoute($req, array('REQUEST_URI' => '/foo/method'));
        $this->assertEquals($num, $req->get('return'));
    }

    /**
     *  @depends testCompile
     */
    public function testMatchMixed()
    {
        $route = new \QuickTest\Route;
        $req   = new \QuickTest\Request;
        $req->set('phpunit', $this);
        $num = $route->doRoute($req, array('REQUEST_URI' => '/foo/foobar.php'));
    }

    /**
     *  @depends testCompile
     */
    public function test404()
    {
        $route = new \QuickTest\Route;
        $req   = new \QuickTest\Request;
        $req->set('phpunit', $this);
        $num = $route->doRoute($req, array('REQUEST_URI' => '/foo/function/something'));
        $this->assertTrue($req->get('__not_found__'));
    }

    /**
     *  @depends testCompile
     */
    public function test404WithFilter()
    {
        $route = new \QuickTest\Route;
        $req   = new \QuickTest\Request;
        $req->set('phpunit', $this);
        $num = $route->doRoute($req, array('REQUEST_URI' => '/xxx/barfoo'));
        $this->assertTrue($req->get('__not_found__'));
   }

    /**
     *  @depends testCompile
     */
    public function testLast()
    {
        $route = new \QuickTest\Route;
        $req   = new \QuickTest\Request;
        $req->set('phpunit', $this);
        $route->doRoute($req, array('REQUEST_URI' => '/buffer'));
        $this->assertEquals($req->get('__buffer__'), "Hi there!\n");
        $this->assertTrue($req->get('last_for_all'));
    }

    /**
     *  @depends testCompile
     */
    public function testGenerator()
    {
        $this->assertequals(\quicktest\route::getroute("foobar_x", 'foobar'), '/foo/bar/foobar');
        $this->assertEquals(\QuickTest\Route::getRoute("foobar_xx", 'foobar'), '/foo/bar/xxx-foobar');
        $this->assertEquals(\QuickTest\Route::getRoute("foobar_xx"), '/foo/bar/xxx');
    }

    /**
     *  @depends testCompile
     *  @expectedException QuickTest\RouteNotFoundException
     */
    public function testGeneratorNotFound()
    {
        \QuickTest\Route::getRoute("foobar_xdsdasdada");
    }

    /**
     *  @depends testCompile
     *  @expectedException QuickTest\RouteNotFoundException
     */
    public function testGeneratorInvalidArgs()
    {
        \quicktest\route::getroute("foobar_x", 'foobar', 'xxx');
    }
}
