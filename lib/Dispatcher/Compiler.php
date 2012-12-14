<?php
/*
  +---------------------------------------------------------------------------------+
  | Copyright (c) 2012 César Rodas                                                  |
  +---------------------------------------------------------------------------------+
  | Redistribution and use in source and binary forms, with or without              |
  | modification, are permitted provided that the following conditions are met:     |
  | 1. Redistributions of source code must retain the above copyright               |
  |    notice, this list of conditions and the following disclaimer.                |
  |                                                                                 |
  | 2. Redistributions in binary form must reproduce the above copyright            |
  |    notice, this list of conditions and the following disclaimer in the          |
  |    documentation and/or other materials provided with the distribution.         |
  |                                                                                 |
  | 3. All advertising materials mentioning features or use of this software        |
  |    must display the following acknowledgement:                                  |
  |    This product includes software developed by César D. Rodas.                  |
  |                                                                                 |
  | 4. Neither the name of the César D. Rodas nor the                               |
  |    names of its contributors may be used to endorse or promote products         |
  |    derived from this software without specific prior written permission.        |
  |                                                                                 |
  | THIS SOFTWARE IS PROVIDED BY CÉSAR D. RODAS ''AS IS'' AND ANY                   |
  | EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED       |
  | WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE          |
  | DISCLAIMED. IN NO EVENT SHALL CÉSAR D. RODAS BE LIABLE FOR ANY                  |
  | DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES      |
  | (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;    |
  | LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND     |
  | ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT      |
  | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS   |
  | SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE                     |
  +---------------------------------------------------------------------------------+
  | Authors: César Rodas <crodas@php.net>                                           |
  +---------------------------------------------------------------------------------+
*/

namespace Dispatcher;

use Notoj\Annotations,
    Dispatcher\Compiler\Component,
    Dispatcher\Compiler\Url,
    Dispatcher\Compiler\UrlGroup_Switch,
    Dispatcher\Compiler\UrlGroup_If;

class Compiler
{
    protected $config;
    protected $annotations;
    protected $urls;
    protected $filters = array();
    protected $route_filters = array();

    public function __construct(Generator $conf, Annotations $annotations)
    {
        $this->config      = $conf;
        $this->annotations = $annotations;

        $this->compile();
    }
    
    protected function groupByMethod(Array $urls)
    {
        $group = new UrlGroup_Switch('$server["REQUEST_METHOD"]');
        foreach ($urls as $url) {
            $method = $url->getMethod();
            if ($method == 'ALL') {
                $method = '';
            }
            $group->addUrl($url, $method);
        }
        return $group;
    }

    public function groupByPartsSize(Array $urls)
    {
        $group = new UrlGroup_Switch('$length');
        foreach ($urls as $url) {
            $group->addUrl($url, count($url->getParts()));
        }
        return $group;
    }

    protected function belongsToGroup(Array $arr, Array $parts)
    {
        return count(array_intersect_assoc(
            $arr, $parts
        )) > 0;
    }

    public function groupByPatterns(Array $urls)
    {
        $indexes = array();
        $groups  = array();
        foreach ($urls as $url) {
            $parts = array_filter($url->getParts(), function($element) {
                return $element->getType() == Component::CONSTANT;
            });
            if (empty($parts)) continue;
            foreach ($indexes as $id => $pattern) {
                if ($this->belongsToGroup($pattern, $parts)) {
                    $groups[$id][] = $url; 
                    continue 2;
                }
            }
            $groups[]  = array($url);
            $indexes[] = $parts;
        }
        if (count($groups) > 1) {
            $patterns = array();
            foreach ($indexes as $id => $rules) {
                if (count($groups[$id]) == 1) {
                    // if the group has just *one*
                    // element
                    $patterns[] = $groups[$id][0];
                    continue;
                }
                $pattern = new UrlGroup_If($rules);
                foreach ($groups[$id] as $id => $url) {
                    $pattern->addUrl($url, $id);
                }
                $patterns[] = $pattern;
            }
            return $patterns;
        }
    }

    protected function readFilters()
    {
        foreach ($this->annotations->get('Filter') as $filterAnnotation) {
            foreach ($filterAnnotation->get('Filter') as $filter) {
                $name = current($filter['args']);
                if (empty($name)) continue;
                $this->filters[$name] = $filterAnnotation;
            }
        }

        $this->route_filters = array();
        foreach(array('preRoute', 'postRoute') as $type) {
            $this->route_filters[$type] = array();
            foreach ($this->annotations->get($type) as $filterRouter) {
                foreach ($filterRouter->get($type) as $filter) {
                    $name = current($filter['args']);
                    if (empty($name)) continue;
                    $this->route_filters[$name][] = array($type, $filterRouter);
                }
            }
        }
    }

    protected function processRoute($routeAnnotation, Array $route)
    {
        $args  = empty($route['args']) ? array() : $route['args'];
        $route = current($args);

        if ($routeAnnotation->isMethod()) {
            $class = $this->annotations->getClassInfo($routeAnnotation['class']);
            if (!empty($class['class'])) {
                $baseRoute = $class['class']->getOne('Route');
                if (!empty($baseRoute)) {
                    $route = current($baseRoute) . '/' . $route;
                }
            }
        }

        if (empty($route)) {
            throw new \RuntimeException("@Route must have an argument");
        }

        $url = new Url($routeAnnotation);
        $url->setRoute($route);
        if (isset($args['set'])) {
            $url->setArguments($args['set']);
        }

        foreach ($this->route_filters as $name => $def) {
            if ($routeAnnotation->get($name)) {
                foreach ($def as $filter) {
                    $url->addFilter($filter[0], $filter[1]);
                }
            }
        }

        if ($routeAnnotation->has('Method')) {
            foreach($routeAnnotation->get('Method') as $method) {
                foreach ($method['args'] as $m) {
                    $nurl = clone $url;
                    $nurl->setMethod($m);
                    $this->urls[] = $nurl;
                }
            }
        } else {
            $this->urls[] = $url;
        }
    }

    protected function createUrlObjects()
    {
        if (!$this->annotations->has('Route')) {
            throw new \RuntimeException("cannot find @Route annotation");
        }

        $this->urls = array();
        foreach ($this->annotations->get('Route') as $routeAnnotation) {
            if ($routeAnnotation->isClass()) {
                if (count($routeAnnotation->get('Route')) > 1) {
                    throw new \RuntimeException("Classes can have only *one* @Route");
                }
                $class = $this->annotations->getClassInfo($routeAnnotation['class']);
                if (empty($class['method'])) {
                    continue;
                }
                foreach ($class['method'] as $annotation) {
                    if (!$annotation->has('Route')) {
                        $this->processRoute($annotation, array());
                    }
                }
                continue;
            }

            foreach ($routeAnnotation->get('Route') as $route) {
                $this->processRoute($routeAnnotation, $route);
            }
        }
    }
    
    public function getFilterExpr($name)
    {
        $parts  = explode(":", $name);
        $filter = $parts[0];
        $name   = var_export(empty($parts[1]) ? $filter : $parts[1], true);

        if (empty($this->filters[$filter])) {
            // filter is not found
            return null;
        }
        
        $filter = $this->filters[$filter];
        return compact('filter', 'name');
    }

    public function getRelativePath($file1, $file2)
    {
        $dir1 = trim(realpath(dirname($file1)),'/');
        $dir2 = trim(realpath(dirname($file2)),'/');
        $to   = explode('/', $dir1);
        $from = explode('/', $dir2);

        $realPath = $to;

        foreach ($from as $depth => $dir) {
            if(isset($to[$depth]) && $dir === $to[$depth]) {
                array_shift($realPath);
            } else {
                $remaining = count($from) - $depth;
                if($remaining) {
                    // add traversals up to first matching dir
                    $padLength = (count($realPath) + $remaining) * -1;
                    $realPath  = array_pad($realPath, $padLength, '..');
                    break;
                }
            }
        }

        return implode("/", $realPath) . '/' . basename($file1); 
    }


    protected function compile()
    {
        $this->readFilters();
        $this->createUrlObjects();

        $groups = $this->groupByMethod($this->urls);
        $groups->iterate(array($this, 'groupByPartsSize'));
        $groups->iterate(array($this, 'groupByPatterns'));
        $groups->sort(function($obj1, $obj2) {
            return $obj1->getWeight() - $obj2->getWeight();
        });

        $config = $this->config;
        $output = $this->config->getOutput();
        $self   = $this;
        $args   = compact('groups', 'config');
        $vm = \Artifex::load(__DIR__ . '/Template/Main.tpl.php', $args);
        $vm->doInclude('Switch.tpl.php');
        $vm->doInclude('Url.tpl.php');
        $vm->doInclude('If.tpl.php');

        /**
         *  Convert every Url or UrlGroup object into
         *  code :-)
         */
        $vm->registerFunction('render', function($obj) use ($vm) {
            if ($obj instanceof UrlGroup_Switch) {
                $fnc = 'render_group';
            } else if ($obj instanceof UrlGroup_If) {
                $fnc = 'render_if';
            } else if ($obj instanceof Url) {
                $fnc = 'render_url';
            } else if (is_array($obj)) {
                $fnc = $vm->getFunction('render');
                $buf = '';
                foreach ($obj as $url) {
                    $buf .= $fnc($url);
                }
                return $buf;
            } else {
                throw new \RuntimeException("Don't know how to render " . get_class($obj));
            }
            $fnc = $vm->getFunction($fnc);
            return $fnc($obj);
        });

        /**
         *  Generate the callback function (from a function or 
         *  a method)
         */
        $vm->registerFunction('callback', $callback=function($annotation) use ($vm, $self, $output) {
            $fileHash = '$file_' . substr(sha1($annotation['file']), 0, 8);
            $filePath = $annotation['file'];
            if (!empty($output)) {
                $filePath = $self->getRelativePath($annotation['file'], $output);
            }

            // prepare loading of the method/function
            // by doing this we avoid the need of having an "autoloader", 
            // also autoloaders doesn't work with functions, this solution does.
            $vm->printIndented("if (empty($fileHash)) {\n");
            $vm->printIndented("   $fileHash = 1;\n");
            if (!empty($output)) {
                $vm->printIndented('   require_once __DIR__ . "/' . addslashes($filePath) . '";' . "\n");
            } else {
                $vm->printIndented('   require_once "' . addslashes($filePath) . '";' . "\n");
            }
            $vm->printIndented("}\n");

            // Get Code representation out of arguments array
            $args = func_get_args();
            array_shift($args);
            array_walk($args, function($param) {
                $param = (string)$param;
                return $param[0] == '$' ? $param : var_export($param, true);
            });
            $arguments = implode(", ", $args);
            

            // check if the filter is cachable
            if (count($args) == 3) {
                $cache = intval($annotation->getOne('Cache'));
            }

            if ($annotation->isFunction()) {
                // generate code for functions 
                $function = "\\" . $annotation['function'];
                if (!empty($cache)) { 
                    return  '$this->doCachedFilter(' . var_export($function,true) . ", $arguments, $cache)";
                } else {
                    return "$function($arguments)";
                }
            } else if ($annotation->isMethod()) {
                // It is a method, *for now* we don't care if the method
                // is static so we instanciate an object if it wasn't done before
                $class  = "\\" . $annotation['class'];
                $method = $annotation['function'];
                $obj    = "\$obj_filt_" . substr(sha1($class), 0, 8);
                $vm->printIndented("if (empty($obj)) {\n");
                $vm->printIndented("    $obj = new $class;\n");
                $vm->printIndented("}\n");
                if (!empty($cache)) { 
                    return  '$this->doCachedFilter(array(' . "{$obj}, '{$method}'), $arguments, $cache)";
                } else {
                    return "{$obj}->{$method}($arguments)";
                }
            } else {
                throw new \RuntimeException("Invalid callback");
            }
        });

        
        /**
         *  Generate expressions
         */
        $vm->registerFunction('expr', function(Array $rules) use ($self, $callback) {
            if (count($rules) == 0) return array();
            $expr = array();
            foreach ($rules as $rule) {
                $expr[] = $rule->getExpr($self, $callback);
            }
            return implode(' && ', array_filter($expr));
        });

        $this->output = $vm->run();
    }
    
    public function getOutput()
    {
        return $this->output;
    }
   
}
