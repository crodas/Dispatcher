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

use Notoj\Annotation;

class Url
{
    protected $route = '';
    protected $def;
    protected $parts   = array();
    protected $args    = array();
    protected $method  = 'ALL';
    protected $filters = array();
    protected $name;

    protected $allowedMethods = array('GET', 'POST', 'PUT', 'HEAD', 'DELETE', 'ALL');

    public function __construct(Annotation $def)
    {
        $this->def = $def;
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
            $weight += $part->GetType();
        }
        if (count($this->parts) == 1 && $part->GetType() == Component::VARIABLE) {
            // the url has only a single component (/{foobar}) so it *must*
            // the last rule to evaluate
            return 0xfffff;
        }
        return $weight;
    }

    public function addFilter($type, Annotation $def, $args = array(), $weight = 10)
    {
        if (empty($this->filters[$type])) {
            $this->filters[$type] = array();
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
            throw new \RuntimeException("{$exception} is not a valid method");
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
                $isVariable = $part->getType() == Component::VARIABLE;
                $id1 = 1;
                foreach ($part->getParts() as $part) {
                    if ($part[0] == Component::VARIABLE) {
                        $name = ($i=strpos($part[1], ':')) ? substr($part[1], $i+1) : $part[1];
                        if ($isVariable) {
                            $vars[$name] = array($id);
                        } else {
                            $vars[$name] = array($id, $id1++);
                        }
                    }
                }
                break;
            }
        }

        return $vars;
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
            return new Component($part, $index);
        }, $this->parts, array_keys($this->parts));
    }

    public function getParts()
    {
        return $this->parts;
    }

    public function getRouteFilter()
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

    public function getRouteExpr()
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
}
