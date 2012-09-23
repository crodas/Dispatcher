Dispatcher
==========

It is a compiler which generates an optimized URL Router class for your application. It can be used as an offline (production friendly) tool or inside your project (development friendly).

The generated Router class searchs for the appropiate `controller` for the current URL. The `callbacks` could be methods or functions.

How to use it
-------------

This is how to use the compiler:

```php
// compiler
$dispatcher = new \Dispatcher\Generator;
$dispatcher
  ->addDirectory(__DIR__ . "/../projects") // where our controllers are located
  ->setNamespace("Project\\Router") // We want the bootstrap file in its own namespace
  ->setOutput(__DIR__ . "/../libs/bootstrap.php") // Where we want to save it
  ->generate();  // do your magic!
```

And this is how to use the generated code:

```php
// Require
require __DIR__ . "/../libs/bootstrap.php"
$router = new \Project\Router\Route;
try {                                                 
    $router->fromRequest(); // route current Request (based on $_SERVER)          
} catch (\Project\Router\NotFoundException $e) {                    
    die('page not found'); // page not found
} catch (Exception $e) {
    die('unknown error'); //another exception thrown by our app
} 
```

It is possible to use compiler to generate code inside our application, however it will load the whole compiler in every request. If you need this behaviour we recommend to turn on Notoj's cache:

```php
\Notoj\Notoj::enableCache("/tmp/out-app-annotations-cache.php");
```

By doing that Notoj will tell the engine when it is neccesary to compiler (when some file has change). Even though it is pretty efficient we recommend for production to generate at the bootstrap file once (for instance a deploying). Soon we will provide an phar executable to make this easy.

The annotations
---------------

We scan your project directory (or directories) looking for the @Route annotation. They can be in `functions`, `methods` or `classes`.

```php
<?php
// one function
/**
 *  @Route("/foo/{bar}")
 *  @Route("/foo", {bar="index"})
 */
function do_something_post(Request $request) {
  print "I've got bar=" . $request->get("bar");
}

// one class and several methods 
/**
 *  @Route("/foobar")
 */
class ControllerClassFoo
{
  /**
   *  @Route("/bar") 
   *  @Method GET
   */
  function list(Request $req) {
    echo "I'm listing something on /foobar/bar";
  }
  
  /**
   *  @Method POST
   */
  function save(Request $req) {
    echo "I'm saving something";
  }
}

// One method per route
class ControllerClassBar {
  /** @Route("/bar") @Route("/bar/index") */
  function indexAction() {
    
  }
}
```

Filters
-------

`Dispatcher` has a filter that allows to verify and modify variables of our URL.

```php
<?php
/** @Route("/profile/{user}") */
function show_profile(Request $req) {
  $user = $req->get("user");
}


/** @Filter("user") */
function some_filter(Request $req, $name, $value) {
  $userobj = DB::getUserById($value);
  if ($userobj) {
    $req->set('user', $userobj);
    return true;
  }
  return false;
}
```

The filter should return a false if `$value` is not valid the router will jump to the next rule or will throw a `notfound` exception. `$name` is used because one callback can be used as multiple filters.

Expensive `Filters` can be cached with `@Cache <ttl>` annotation. The cache mechanism is defined in the application with a class which implements `FilterCache` interface.

  
Valid Patterns
--------------
- `/foo/bar`
- `/foo/{user}`
- `/foo/{user:user1}/{user:user2}`: Inside the `Request` object the user objects will be named `user1` and `user2` to avoid name collisions.
- `/foo/{user:u1}-vs-{user:u2}.{ext}`: We can have multiple variables inside one single directory level as long as they are separated by constants.
