<?php
/**
 *  Autoloader function generated by crodas/Autoloader
 *
 *  https://github.com/crodas/Autoloader
 *
 *  This is a generated file, do not modify it.
 */


spl_autoload_register(function ($class) {
    /*
        This array has a map of (class => file)
    */

    // classes {{{
    static $classes = array (
  'dispatcher\\compiler\\component' => '/Compiler/Component.php',
  'dispatcher\\compiler\\urlgroup' => '/Compiler/UrlGroup.php',
  'dispatcher\\compiler\\urlgroup_switch' => '/Compiler/UrlGroup/Switch.php',
  'dispatcher\\compiler\\urlgroup_if' => '/Compiler/UrlGroup/If.php',
  'dispatcher\\compiler\\url' => '/Compiler/Url.php',
  'dispatcher\\compiler' => '/Compiler.php',
  'dispatcher\\generator' => '/Generator.php',
  'notfoundexception' => '/Template/Main.tpl.php',
  'request' => '/Template/Main.tpl.php',
  'route' => '/Template/Main.tpl.php',
);
    // }}}

    // deps {{{
    static $deps    = array (
  'dispatcher\\compiler\\urlgroup_switch' => 
  array (
    0 => 'dispatcher\\compiler\\urlgroup',
  ),
  'dispatcher\\compiler\\urlgroup_if' => 
  array (
    0 => 'dispatcher\\compiler\\urlgroup',
  ),
);
    // }}}

    $class = strtolower($class);
    if (isset($classes[$class])) {
        if (!empty($deps[$class])) {
            foreach ($deps[$class] as $zclass) {
                if (!class_exists($zclass, false)) {
                    require __DIR__  . $classes[$zclass];
                }
            }
        }

        if (!class_exists($class, false)) {

            require __DIR__  . $classes[$class];

        }
        return true;
    }

    return false;
});


