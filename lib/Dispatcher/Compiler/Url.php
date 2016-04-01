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

namespace Dispatcher\Compiler;

use Notoj\Annotation\Annotation;
use Dispatcher\Compiler;
use Dispatcher\Templates;

class Url
{
    protected $route = '';
    protected $def;
    protected $parts   = array();
    protected $args    = array();
    protected $method  = 'ALL';
    protected $filters = array();
    protected $name;

    protected $compiler;

    protected $allowedMethods = array('GET', 'POST', 'PUT', 'HEAD', 'DELETE', 'ALL');

    public function __construct(Annotation $def, Compiler $cmp)
    {
        $this->def = $def;
        $this->compiler = $cmp;
    }

    public function setRouteAndArgs($route, $args)
    {
        if (!empty($route)) {
            $this->setRoute($route);
        }

        if (isset($args['set'])) {
            $this->setArguments($args['set']);
        }
        if (!empty($args[1]) || !empty($args['name'])) {
            $this->setName(empty($args[1]) ? $args['name'] : $args[1]);
        }
    }

    public function addFiltersFromAnnotations(Array $routeFilters, Array $filters, $base = 0)
    {
        foreach ($filters as $annotation) {
            $name = $annotation->getName();

            if (!empty($routeFilters[$name])) {
                foreach ($routeFilters[$name] as $filter) {
                    $this->addFilter($filter[0], $filter[1], $annotation->getArgs(), $filter[2]+ ++$base);
                }
            }
        }
    }

    public function addFilters(Array $allFilters, $base = 0)
    {
        foreach ($allFilters as $type => $filters) {
            foreach ($filters as $filter) {
                $this->addFilter($type, $filter[0], array(), $filter[1]+ ++$base);
            }
        }

        return $base;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getWeight()
    {
        $weight = 0;
        foreach ($this->parts as $part) {
            $weight += $part->getWeight();
        }
        return $weight;
    }

    public function addFilter($type, Annotation $def, $args = array(), $weight = 10)
    {
        if (empty($this->filters[$type])) {
            $this->filters[$type] = array();
        }
        $filterApps = Compiler::getApplications($def);
        if ($filterApps) {
            $found = false;
            $apps  = $this->getApplications();
            foreach ($filterApps as $app) {
                if (in_array($app, $apps)) {
                    $found = true;
                    break;
                }
            }
            if(!$found) return;
        }
        $this->filters[$type][] = array($def, $args, $weight);
    }

    public function getFilters($type)
    {
        if (empty($this->filters[$type])) {
            return array();
        }
        usort($this->filters[$type], function($a, $b) {
            return $a[2] - $b[2];
        });

        return $this->filters[$type];
    }

    public function getAnnotation()
    {
        return $this->def;
    }

    public function getRouteDefinition()
    {
        return $this->route;
    }

    public function setArguments(Array $args)
    {
        $this->args = $args;
    }

    public function getArguments()
    {
        return $this->args;
    }

    public function setMethod($method)
    {
        if (!in_array($method, $this->allowedMethods)) {
            throw new \RuntimeException("{$method} is not a valid method");
        }
        $this->method = $method;
        return $this;
    }

    public function getVariables() {
        $vars = array();
        foreach($this->parts as $id => $part) {
            switch($part->getType()){
            case Component::MIXED:
            case Component::VARIABLE:
                $vars = array_merge($vars, $part->getVariables($id));
                break;
            }
        }

        return $vars;
    }

    public function getApplications()
    {
        return Compiler::getApplications($this->def);
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setRoute($route)
    {
        $this->route = $route;
        if ($route == '@NotFound') {
            return;
        }

        $this->parts = array_values(array_filter(explode("/", $route)));
        $this->parts = array_map(function($part, $index){ 
            return new Component($part, $index, $this->compiler);
        }, $this->parts, array_keys($this->parts));
    }

    public function isComplex()
    {
        foreach($this->parts as $id => $part) {
            switch($part->getType()){
            case Component::LOOP:
                return true;
            }
        }

        return false;
    }

    public function getParts()
    {
        return $this->parts;
    }

    public function getConstants()
    {
        return array_filter($this->parts, function($e) {
            return $e->getType() == Component::CONSTANT;
        });
    }

    public function getGeneratorFilter()
    {
        $expr[] = '$count == ' . count($this->getVariables());
        $pos    = 0;
        foreach ($this->parts as $part) {
            switch($part->getType()){
            case Component::MIXED:
            case Component::VARIABLE:
                foreach ($part->getParts() as $part) {
                    if ($part[0] == Component::VARIABLE) {
                        $expr[] = '(!empty($args["' . $part[2] .'"]) || !empty($args[' . ($pos++) .']))';
                    }
                }
            }
        }
        return implode(' && ', $expr);
    }

    public function getExpr()
    {
        $expr  = array();
        $rules = (array)$this->getParts();
        foreach ($rules as $rule) {
            $expr[] = $rule->getExpr();
        }

        return implode(' && ', array_filter($expr));
    }

    public function getGeneratorExpr()
    {
        $expr = "";
        $pos  = 0;
        foreach ($this->parts as $part) {
            switch($part->getType()){
            case Component::CONSTANT:
                $expr .= "/" . addslashes($part);
                break;
            case Component::MIXED:
            case Component::VARIABLE:
                $expr .= "/";
                foreach ($part->getParts() as $part) {
                    if ($part[0] == Component::VARIABLE) {
                        $expr .= '" . (!empty($args["' . $part[2] .'"]) ? $args["'. $part[2] . '"] : $args[' . ($pos++) .']) . "';
                    } else {
                        $expr .= addslashes($part[1]);
                    }
                }
            }
        }

        return '"'. $expr. '"';
    }

    public function exprPrepare()
    {
        $prep = array();
        foreach ($this->parts as $part) {
            $prep[] = $part->exprPrepare();
        }

        return implode("\n", array_filter($prep));
    }

    public function __toString()
    {
        $args = array(
            'expr' => $this->getExpr(),
            'preRoute' => $this->getFilters('preroute'),
            'postRoute' => $this->getFilters('postroute'),
            'compiler' => $this->compiler,
            'url' => $this
        );

        return Templates::get('url')->render($args, true);
    }
}
