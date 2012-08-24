Dispatcher
==========

h2. WARNING: This is project is not finished yet.

It's a compiler which generates code to route HTTP requests to classes or function. The URL patterns are defined with `annotations`.

This is how the `annotations` looks like:

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
   *  @Method GET
   */
  function list(Request $req) {
    echo "I'm listing something";
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

The compiler will generate a `Dispatcher` class (optionally could be under some namespace) which is easy to use:

```php
<?php
$router = new Dispatcher;
$router->routeCurrentRequest(); 
// or
$router->route(array("REQUEST_METHOD" => "GET", "PATH_INFO" => "/foo/bar" ...)); // $_SERVER
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
  return DB::getUserById($value); // should return FALSE or NULL if the user doesn't exists
}
```

The filter should return a false (or equivalent value `0, "", NULL`) if `$value` is not valid and will jump to the next rule. `$name` is used because one callback can be used as multiple filters.

At some point the results of the filters will be cached (disabled by default) to speed up things if the filter calling is expensive.

Built-in filters
---------------
- `{numeric}`
- `{alnum}`

Valid Patterns
--------------
- `/foo/bar`
- `/foo/{user}`
- `/foo/{user:user1}/{user:user2}`: Inside the `Request` object the user objects will be named `user1` and `user2` to avoid name collisions.
- `/foo/{user:u1}-vs-{user:u2}.{ext}`: We can have multiple variables inside one single directory level as long as they are separated by constants.
