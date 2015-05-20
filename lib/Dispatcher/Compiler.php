<?php
/*
  +---------------------------------------------------------------------------------+
  | Copyright (c) 2014 César Rodas                                                  |
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

use Dispatcher\Compiler\Component,
    Dispatcher\Compiler\ComplexUrl,
    Dispatcher\Compiler\Url,
    Dispatcher\Compiler\UrlGroup_Switch,
    Dispatcher\Compiler\UrlGroup_If,
    crodas\SimpleView\FixCode,
    crodas\FileUtil\Path;

class Compiler
{
    protected $config;
    protected $annotations;
    protected $urls;
    protected $filters = array();
    protected $all_filters   = array();
    protected $route_filters = array();
    protected $not_found = array();
    protected $complex = array();

    public function __construct(Generator $conf, \Notoj\Filesystem $annotations)
    {
        $this->config      = $conf;
        $this->annotations = $annotations;

        $this->compile();
    }
    
    protected function groupByMethod(Array $urls)
    {
        $group = new UrlGroup_Switch('$server["REQUEST_METHOD"]');
        foreach ($urls as $url) {
            if ($url->isComplex()) {
                $this->complex[] = new ComplexUrl($url);
                continue;
            }
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
        )) == count($arr);
    }

    public function groupByPatterns(Array $urls)
    {
        $indexes  = array();
        $groups   = array();
        $patterns = array();
        foreach ($urls as $url) {
            $parts = array_filter($url->getParts(), function($element) {
                return $element->getType() == Component::CONSTANT;
            });
            if (empty($parts)) {
                $patterns[] = $url;
                continue;
            }
            foreach ($indexes as $id => $pattern) {
                if ($this->belongsToGroup($pattern, $parts)) {
                    $groups[$id][] = $url; 
                    continue 2;
                }
            }
            $groups[] = array($url);
            for ($i=1; $i <= count($parts); $i++) {
                $indexes[] = array_slice($parts, 0, $i);
            }
        }
        if (count($groups) > 1) {
            foreach ($indexes as $id => $rules) {
                if (empty($groups[$id])) continue;
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

        return $urls;
    }

    protected function readFilters()
    {
        foreach ($this->annotations->get('Filter', 'Callable') as $filterAnnotation) {
            $name = current($filterAnnotation->GetARgs());
            if (empty($name)) continue;
            $this->filters[$name] = $filterAnnotation->GetObject();
        }

        $this->route_filters = array();
        foreach ($this->annotations->get('preroute,postroute', 'Callable') as $filterAnnotation) {
            $type = $filterAnnotation->getName();
            $args = $filterAnnotation->getArgs();
            $name = !empty($args) ? strtolower(current($args)) : null;
            $weight = 10;
            if ($filterAnnotation->getParent()->has('First')) {
                $weight -= 100;
            } else if ($filterAnnotation->getParent()->has('Last')) {
                $weight += 100;
            }

            if (empty($name)) {
                $this->all_filters[$type][] = array($filterAnnotation, $weight);
                continue;
            }
            $this->route_filters[$name][] = array($type, $filterAnnotation, $weight);

        }

    }

    public function getComplexUrls()
    {
        return $this->complex;
    }

    protected function getUrl($routeAnnotation, $route, $args = array())
    {
        $url = new Url($routeAnnotation);
        if (!empty($route)) {
            $url->setRoute($route);
        }

        if (isset($args['set'])) {
            $url->setArguments($args['set']);
        }
        if (!empty($args[1]) || !empty($args['name'])) {
            $url->setName(empty($args[1]) ? $args['name'] : $args[1]);
        }

        $base = 0;
        $filters = $this->all_filters;
        foreach ($this->all_filters as $type => $filters) {
            foreach ($filters as $filter) {
                $url->addFilter($type, $filter[0], array(), $filter[1]+ ++$base);
            }
        }

        $filters = iterator_to_array($routeAnnotation->GetParent());
        if ($routeAnnotation->isMethod()) {
            $classAnn = $routeAnnotation->getObject()->getClass()->get('');
            $filters = array_merge($classAnn, $filters);
        }

        foreach ($filters as $annotation) {
            $name = $annotation->getName();

            if (!empty($this->route_filters[$name])) {
                foreach ($this->route_filters[$name] as $filter) {
                    $url->addFilter($filter[0], $filter[1], $annotation->getArgs(), $filter[2]+ ++$base);
                }
            }
        }

        return $url;
    }

    protected function processRoute($routeAnnotation, Array $args)
    {
        $route = current($args);
        $class = null;

        if ($routeAnnotation->isMethod()) {
            $method = $routeAnnotation->getObject();
            $class = $method->getClass();
            $baseRoute = $class->getOne('Route');
            if (!empty($baseRoute)) {
                $route = current($baseRoute->getArgs()) . '/' . $route;
            }
        }

        if (empty($route)) {
            throw new \RuntimeException("@Route must have an argument");
        }

        $url = $this->getUrl($routeAnnotation, $route, $args);

        if ($routeAnnotation->getParent()->has('Method')) {
            foreach($routeAnnotation->getParent()->get('Method') as $method) {
                foreach ($method->getArgs() as $m) {
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
                if (count($routeAnnotation->getParent()->get('Route')) > 1) {
                    throw new \RuntimeException("Classes can have only *one* @Route");
                }
                $class = $routeAnnotation->getObject();
                $methods = $class->getMethods('Method');
                foreach ($methods as $method) {
                    if (!$method->has('Route')) {
                        $this->processRoute($method->getOne('Method'), array(""));
                    }
                }
                continue;
            }

            $this->processRoute($routeAnnotation, $routeAnnotation->GetArgs());
        }

        foreach($this->annotations->get('NotFound') as $route) {
            $this->not_found[] = $this->getUrl($route, '@NotFound');
        }
    }
    
    public function getFilterExpr($name)
    {
        $parts  = explode(":", $name);
        $filter = $parts[0];
        $name   = empty($parts[1]) ? $filter : $parts[1];

        if (empty($this->filters[$filter])) {
            // filter is not found
            return null;
        }
        
        $filter = $this->filters[$filter];
        return compact('filter', 'name');
    }

    public function getNamedUrls()
    {
        $urls = array();
        foreach ($this->urls as $url) {
            $name = $url->getName();
            if (empty($name)) continue;
            if (empty($urls[$name])) {
                $urls[$name] = array('routes' => array(), 'exception' => '');
            }
            $urls[$name]['routes'][]   = $url;
            $urls[$name]['exception'] .= $url->getRouteDefinition() . " (" . count($url->getVariables()) . " arguments) \n";
        }
        return $urls;
    }

    public function getNotFoundHandler()
    {
        return (Array)$this->not_found;
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
        $args   = compact('self', 'groups', 'config', 'complex');
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

        $vm->registerFunction('callback_object', $callback=function($annotation) use ($vm, $self, $output) {
            if ($annotation->isFunction()) {
                return var_export('\\' . $annotation->getObject()->getName(), true);
            } else if ($annotation->isMethod()) {
                $class  = "\\" . $annotation->getObject()->getClass()->getName();
                $obj    = "\$obj_filt_" . substr(sha1($class), 0, 8);
                return "array($obj, " . var_export($annotation->getObject()->GetName(), true) . ')';
            } else {
                throw new \RuntimeException("Invalid callback");
            }
        });

        /**
         *  Generate the callback function (from a function or 
         *  a method)
         */
        $vm->registerFunction('callback', $callback=function($annotation) use ($vm, $self, $output) {
            $fileHash = '$file_' . substr(sha1($annotation->getFile()), 0, 8);
            $filePath = $annotation->GetFile();
            if (!empty($output)) {
                $filePath = Path::getRelative($annotation->getFile(), $output);
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
            $args = array_map(function($param) {
                $param = is_scalar($param) ? ((string)$param) : $param;
                $text  = !empty($param[0]) && $param[0] == '$' ? $param : var_export($param, true);
                return $text;
            }, $args);
            
            if ($annotation instanceof \Notoj\Annotation\Annotation) {
                $object = $annotation->GetObject();
            } else {
                $object = $annotation;
                $annotation = $annotation->getOne();
            }

            // check if the filter is cachable
            switch (count($args)) {
            case 3:
                $cache = 0;
                if ($annotation->getParent()->has('Cache')) {
                    $cache = intval(current($annotation->getParent()->getOne('Cache')->getArgs()));
                }
                break;
            case 1:
                $zargs = $annotation->getObject()->GetParameters();
                for ($i = 1; $i < count($zargs); $i++) {
                    $args[] = '$req->get(' . var_export(substr($zargs[$i], 1), true) . ')';
                }
                break;
            }

            $arguments = implode(", ", $args);
            if ($annotation->isFunction()) {
                // generate code for functions 
                $function = "\\" . $object->getName();
                if (!empty($cache)) { 
                    return  '$this->doCachedFilter(' . var_export($function,true) . ", $arguments, $cache)";
                } else {
                    return "$function($arguments)";
                }
            } else if ($annotation->isMethod()) {
                // It is a method, *for now* we don't care if the method
                // is static so we instanciate an object if it wasn't done before
                $class  = "\\" . $object->getClass()->getName();
                $method = $object->getName();
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
        $vm->registerFunction('expr', function($rules) use ($self, $callback) {
            if (!is_array($rules)) {
                $rules = array($rules);
            }
            if (count($rules) == 0) return '';
            $expr = array();
            foreach ($rules as $rule) {
                $expr[] = $rule->getExpr($self, $callback) ?: '';
            }
            return implode(' && ', array_filter($expr));
        });

        $this->output = $vm->run();
        //$this->output = FixCode::fix($vm->run());
    }
    
    public function getOutput()
    {
        return $this->output;
    }
   
}
