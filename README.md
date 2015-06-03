Dispatcher
==========

`Dispatcher` is a routing library that maps URLs to actions. 

Defining routes
---------------

Routes are defined using annotations for methods or functions.

```php
use Symfony\Component\HttpFoundation\Request;

/** 
 * A single function can have multiple routes.
 * 
 * @Route("/{user}") 
 * @Route("/user/{user}")
 */
function handler(Request $req, $user)
{
  return "Hello {$user}";
}

/**
 *  You can also set default values and use multiple routes
 *
 *  @Route("/foo/{bar}")
 *  @Route("/foo", {bar="index"})
 */
function do_something_with_bar(Request $request, $bar) {
  print "I've got bar=" . $bar;
}

/**
 *  If @Route is define at a class it would behave as a
 *  namespace of prefix for the routes defined at their
 *  methods
 *
 *  @Route("/admin")
 */
class Foobar
{
  /**
   *  This handles POST /admin/login
   *
   *  @Route("/login")
   *  @Method POST
   */
  public function do_login(Request $req)
  {
  }
  
  /**
   *  This handles GET /admin/login
   *
   *  @Route("/login")
   */
  public function login(Request $req)
  {
  }
}
```

`Dispatcher` walks over the filesystem looking for `@Route` annotations on functions and methods.

How to use it
-------------

```php
$router = new Dispatcher\Router;
$router->
  // where our controllers are located
  ->addDirectory(__DIR__ . "/../projects");

// Do the router
$router->doRoute();
```
By default, it runs in `production mode` and it won't rebuild the routes on changes. If you wish to run on development mode, you should do this:

```php
$router->development();

```


Filters
-------

To simply things, `Dispatcher` doesn't allow you *yet* to define regular expressions to validate your placeholders. Instead it lets you to define functions which validates and modified the values of your placeholders.


```php
/**
 * In this URL, the placeholder user have a Filter, that means
 * if the controller is called we can be sure we get a valid
 * user object.
 *
 * @Route("/profile/{user}")
 */
function show_profile(Request $req, $user) {
  return "Hi {$user->name}";
}


/**
 * Validate {user} placeholders
 *
 * Check if the user exists in the database, if it does exists
 * it will return true and the controller will be called.
 *
 * @Filter("user") 
 */
function some_filter(Request $req, $name, $value) {
  $userobj = DB::getUserById($value);
  if ($userobj) {
    /* I'm overriding the placeholder $name for an object of the Database */
    $req->attributes->set($name, $userobj);
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
