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

use Notoj\Annotation,
    Dispatcher\Compiler;

class Component
{
    const CONSTANT  = 1;
    const VARIABLE  = 20;
    const MIXED     = 30;
    const LOOP      = 40;
    const EXPENSIVE = 0xfff;

    protected $raw;
    protected $type;
    protected $stype;
    protected $index;
    protected $isLoop;
    protected $parts = array();

    protected $compiler;

    public function  __construct($part, $index, Compiler $cmp)
    {
        $this->raw   = $part;
        $this->index = $index;
        $this->compiler = $cmp;
        $this->doParse();
    }

    public function getWeight()
    {

        if (($this->type == self::VARIABLE || $this->type == self::LOOP)) {
            if (!$this->compiler->getFilterExpr($this->parts[0][1])) {
                return self::EXPENSIVE;
            }
        }

        return $this->type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function exprPrepare()
    {
        switch ($this->type) {
        case self::MIXED:
            break;
        case self::VARIABLE:
            $f = $this->compiler->getFilterExpr($this->parts[0][1]);
            if (!empty($f)) {
                return $this->compiler->callbackPrepare($f['filter']);
            }
        }

        return "";
    }

    public function getExpr()
    {
        switch ($this->type) {
        case self::CONSTANT:
            $expr = '$parts[' . $this->index . '] === ' . var_export($this->raw, true);
            break;
        case self::MIXED:
            $regex = array();
            $filters = array();
            $i = 0;
            foreach ($this->parts as $id => $part) {
                if ($part[0] == self::CONSTANT) {
                    $regex[] = preg_quote($part[1]);
                } else {
                    $regex[] = "(.+)";
                    $f = $this->compiler->getFilterExpr($part[1]);
                    if (!empty($f)) {
                        $i++;
                        $index = (int)$this->index;
                        $filters[] = $this->compiler->callback($f['filter'], '$req', $f['name'], "\$matches_{$index}[$i]");
                    }
                }
            }
            $regex = var_export("/^" . implode("", $regex) . "/", true);
            $index = (int)$this->index;
            $expr  = "preg_match($regex, \$parts[{$this->index}], \$matches_{$index}) > 0";
            if (count($filters)) {
                $expr .= ' && ' .implode(' && ', $filters);
            }
            break;
        case self::VARIABLE:
            $f = $this->compiler->getFilterExpr($this->parts[0][1]);
            if (empty($f)) {
                return "";
            }
            $f['filter'] = $this->compiler->callback($f['filter'], '$req', $f['name'], '$parts[' . $this->index . ']');
            $name = "\$filter_" . substr(sha1($f['name']), 0, 8) . "_" . intval($this->index);
            if ($this->isLoop) {
                $expr = "($name={$f['filter']})";
            } else {
                $expr = "(!empty($name) || ($name={$f['filter']}))";
            }
            break;
        case self::LOOP:
            $this->type = $this->stype;
            $expr = $this->getExpr();
            $this->type = self::LOOP;
        }

        return $expr;
    }

    protected function parseParts()
    {
        $str = $this->raw;
        $len = strlen($str);

        $parts  = array();
        $buffer = "";
        for($i=0; $i < $len; $i++) {
            switch ($str[$i]) {
            case '{':
                if (!empty($buffer)) {
                    $parts[] = array(self::CONSTANT, $buffer);
                    $buffer  = "";
                } else if ($i != 0) {
                    throw new RuntimeException("If you use multiple variable they should be separated by a constant");
                }
                $pos  = $i + 1;
                $i    = strpos($str, '}', $i);
                $tmp  = substr($str, $pos, $i - $pos);
                $name = explode(":", $tmp, 2);
                $parts[] = array(self::VARIABLE, $tmp, end($name));
                break;
            default: 
                $buffer .= $str[$i];
            }
        }

        if (!empty($buffer)) {
            $parts[] = array(self::CONSTANT, $buffer);
        }

        $this->parts = $parts;
    }

    protected function parseType()
    {
        $type = 0;
        foreach ($this->parts as $part) {
            if (empty($type)) {
                $type = $part[0];
            } else if ($type != $part[0]) {
                $type = self::MIXED;
                break;
            }
        }
        return $type;
    }

    protected function parseIsLoop()
    {
        $parts = $this->parts;
        $last = end($parts);
        $this->isLoop = false;
        if (substr($last[1], -1) == '+') {
            $this->isLoop  = true;
            $parts[count($parts)-1][1] = rtrim($parts[count($parts)-1][1], '+');
            if (empty($parts[count($parts)-1][1])) {
                array_pop($parts);
            }

            $this->type  = self::LOOP;
        }

        $this->parts = $parts;

        return $this->isLoop;
    }

    public function doParse()
    {
        $this->parseParts();
        if ($this->parseIsLoop()) {
            $this->stype = $this->parseType();
        } else {
            $this->type  = $this->parseType();
            $this->stype = $this->type;
        }


    }


    public function getVariables($id)
    {
        $isVariable = $this->stype == Component::VARIABLE;
        $id1 = 1;
        $vars = array();
        foreach ($this->getParts(Component::VARIABLE) as $part) {
            $name = ($i=strpos($part[1], ':')) ? substr($part[1], $i+1) : $part[1];
            if ($isVariable) {
                $vars[$name] = array($id);
            } else {
                $vars[$name] = array($id, $id1++);
            }
        }
        return $vars;
    }

    public function setIndex($index)
    {
        $this->index = $index;
    }

    public function isRepetitive()
    {
        return $this->type == self::LOOP;
    }

    public function isConstant()
    {
        if ($this->type == self::CONSTANT) {
            return $this->raw;
        }
        return false;
    }

    public function getParts($filter = 0)
    {
        if ($filter) {
            return array_filter($this->parts, function($t) use ($filter) {
                return $t[0] == $filter;
            });
        }
        return $this->parts;
    }

    public function __toString()
    {
        return $this->raw;
    }

}
