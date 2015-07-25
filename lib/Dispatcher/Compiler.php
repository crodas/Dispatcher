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
    Notoj\Annotation\Annotation,
    crodas\SimpleView\FixCode,
    crodas\FileUtil\Path;

class Compiler
{
    protected $config;
    protected $annotations;
    protected $urls;
    protected $filters = array();
    protected $allFilterss   = array();
    protected $routeFilters = array();
    protected $errorHandler = array();
    protected $complex = array();

    public function __construct(Generator $conf, \Notoj\Filesystem $annotations)
    {
        $this->config      = $conf;
        $this->annotations = $annotations;

        $this->compile();
    }

    protected function getAnnotationAndObject($annotation)
    {
        if (is_callable(array($annotation, 'getAnnotation'))) {
            $annotation = $annotation->getAnnotation();
        }
        if ($annotation instanceof \Notoj\Annotation\Annotation) {
            $object = $annotation->GetObject();
        } else {
            $object = $annotation;
            $annotation = $annotation->getOne();
        }

        return array($annotation, $object);
    }

    public function callbackObject($annotation)
    {
        list($annotation, $object) = $this->getAnnotationAndObject($annotation);
        if ($annotation->isFunction()) {
            return var_export($object->getName(), true);
        }

        $class  = "\\". $object->getClass()->getName();
        $obj    = "\$obj_filt_" . substr(sha1($class), 0, 8);
        return "array({$obj}, ". var_export($object->getName(), true) . ")";
    }

    public function callbackPrepare($annotation)
    {
        list($annotation, $object) = $this->getAnnotationAndObject($annotation);
        if ($object->has('builtin')) {
            return '';
        }

        $args = array(
            'filePath' => $annotation->getFile(),
            'annotation' => $annotation,
            'name'      => $object->GetName(),
            'filter'    => 'is_callable',
        );
        if ($annotation->isMethod()) {
            $class         = "\\" . $object->getClass()->getName();
            $args['obj']   = '$obj_filt_' . substr(sha1($class), 0, 8);
            $args['class'] = $class; 
            $args['name']  = substr($class, 1);
            $args['filter'] = 'class_exists';
        }
        return Templates::get('callback')->render($args, true);
    }

    protected function getCallbackArgs(Array $args)
    {
        array_shift($args);
        return array_map(function($param) {
            $param = is_scalar($param) ? ((string)$param) : $param;
            $text  = !empty($param[0]) && $param[0] == '$' ? $param : var_export($param, true);
            return $text;
        }, $args);
    }

    protected function isCacheable($annotation, Array & $args)
    {
        $cache = 0;
        switch (count($args)) {
        case 3:
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

        return $cache;
    }

    public function getApps()
    {
        $apps = [];
        foreach ($this->annotations->get('app,application') as $ann) {
            $apps[] = current($ann->getArgs());
        }

        return array_unique(array_filter($apps));
    }

    public function callback($annotation)
    {
        list($annotation, $object) = $this->getAnnotationAndObject($annotation);
        $rargs = func_get_args();
        $args  = $this->getCallbackArgs($rargs);
        $cache = $this->isCacheable($annotation, $args); 

        if ($object->has('builtin')) {
            if (empty($args[2])) {
                return $object->exec($args[0], $rargs[2]);
            }
            return "(" . $object->exec($args[2], $args[1], $args[0]) . ")";
        }
        
        $arguments = implode(", ", $args);
        if ($annotation->isFunction()) {
            // generate code for functions 
            $function = "\\" . $object->getName();
            if (!empty($cache)) { 
                return  '$this->wrapper->doCachedFilter(' . var_export($function,true) . ", $arguments, $cache)";
            }
            return "$function($arguments)";
        } else if ($annotation->isMethod()) {
            // It is a method, *for now* we don't care if the method
            // is static so we instanciate an object if it wasn't done before
            $class  = "\\" . $object->getClass()->getName();
            $method = $object->getName();
            $obj    = "\$obj_filt_" . substr(sha1($class), 0, 8);
            if (!empty($cache)) { 
                return  '$this->wrapper->doCachedFilter(array(' . "{$obj}, '{$method}'), $arguments, $cache)";
            }
            return "{$obj}->{$method}($arguments)";
        }

        throw new \RuntimeException("Invalid callback");
    }

    protected function groupByApps(Array $urls)
    {
        $group = new UrlGroup_Switch('$this->currentApp');
        $extra = array();
        foreach ($urls as $url) {
            foreach ($url->getApplication() as $app) {
                $group->addUrl($url, $app);
            }
        }

        return $group;
    }
    
    public function groupByMethod(Array $urls)
    {
        $group = new UrlGroup_Switch('$req->getMethod()');
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
        $self = $this;
        usort($this->complex, function ($a, $b) use ($self) {
            return $a->getWeight($self) - $b->getWeight($self);
        });
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

    protected function getUrlGroupsPatternsIndexes($urls)
    {
        $groups   = array();
        $indexes  = array();
        $patterns = array();
        foreach ($urls as $url) {
            $parts = $url->getConstants();
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

        return array($groups, $patterns, $indexes);
    }

    public function groupByPatterns(Array $urls)
    {
        $indexes  = array();
        list($groups, $patterns, $indexes) = $this->getUrlGroupsPatternsIndexes($urls);
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
        $this->filters = array();

        foreach ($this->annotations->get('Filter', 'Callable') as $filterAnnotation) {
            $name = strtolower(current($filterAnnotation->GetArgs()));
            if (empty($name)) continue;
            $this->filters[$name] = $filterAnnotation->GetObject();
        }

        $this->routeFilters = array();
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
                $this->allFilterss[$type][] = array($filterAnnotation, $weight);
                continue;
            }
            $this->routeFilters[$name][] = array($type, $filterAnnotation, $weight);

        }

    }

    public function getComplexUrls()
    {
        return $this->complex;
    }

    protected function getUrl($routeAnnotation, $route, $args = array())
    {
        $url = new Url($routeAnnotation, $this);
        $url->setRouteAndArgs($route, $args);

        $base = $url->addFilters($this->allFilterss);

        $filters = iterator_to_array($routeAnnotation->GetParent());
        if ($routeAnnotation->isMethod()) {
            $classAnn = $routeAnnotation->getObject()->getClass()->get('');
            $filters = array_merge($classAnn, $filters);
        }

        $url->addFiltersFromAnnotations($this->routeFilters, $filters, $base);

        return $url;
    }

    protected function processRoute(Annotation $routeAnnotation, Array $args)
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

        foreach($this->annotations->get('NotFound,Error,ErrorHandler') as $route) {
            if ($route->getName() == 'notfound') {
                $code = 404;
            } else {
                $code = current($route->getArgs());
            }
            $this->errorHandler[$code] = $this->getUrl($route, '@NotFound');
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

    public function getErrorHandlers()
    {
        return $this->errorHandler;
    }

    protected function compile()
    {
        $this->readFilters();
        $this->createUrlObjects();

        $groups = $this->groupByApps($this->urls);
        $groups->iterate(array($this, 'groupByMethod'));
        $groups->iterate(array($this, 'groupByPartsSize'));
        $groups->iterate(array($this, 'groupByPatterns'));
        $self = $this;
        $groups->sort(function($obj1, $obj2) use ($self) {
            return $obj1->getWeight($self) - $obj2->getWeight($self);
        });

        $config = $this->config;
        $output = $this->config->getOutput();
        $self   = $this;
        $args   = compact('self', 'groups', 'config', 'complex');
        $this->output = Templates::get('main')->render($args, true);
        $this->output = FixCode::fix($this->output);
    }
    
    public function getOutput()
    {
        return $this->output;
    }
   
}
