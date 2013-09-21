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

namespace Dispatcher\Compiler;

use Notoj\Annotation,
    Dispatcher\Compiler;

class Component
{
    const CONSTANT  = 1;
    const VARIABLE  = 20;
    const MIXED     = 30;

    protected $raw;
    protected $type;
    protected $index;
    protected $parts = array();

    public function  __construct($part, $index)
    {
        $this->raw   = $part;
        $this->index = $index;
        $this->doParse();
    }

    public function getType()
    {
        return $this->type;
    }

    public function getExpr(Compiler $cmp, \Closure $callback)
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
                    $f = $cmp->getFilterExpr($part[1]);
                    if (!empty($f)) {
                        $i++;
                        $filters[] = $callback($f['filter'], '$req', $f['name'], "\$matches_{$this->index}[$i]");
                    }
                }
            }
            $regex = var_export("/^" . implode("", $regex) . "/", true);
            $expr  = "preg_match($regex, \$parts[$this->index], \$matches_$this->index) > 0";
            if (count($filters)) {
                $expr .= ' && ' .implode(' && ', $filters);
            }
            break;
        case self::VARIABLE:
            $f = $cmp->getFilterExpr($this->parts[0][1]);
            if (empty($f)) {
                return "";
            }
            $f['filter'] = $callback($f['filter'], '$req', $f['name'], '$parts[' . $this->index . ']');
            $name = "\$filter_" . substr(sha1($f['name']), 0, 8) . "_$this->index";
            $expr = "(!empty($name) || ($name={$f['filter']}))";
        }

        return $expr;
    }

    public function doParse()
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

        foreach ($parts as $part) {
            if (empty($this->type)) {
                $this->type = $part[0];
            } else {
                if ($this->type != $part[0]) {
                    $this->type = self::MIXED;
                    break;
                }
            }
        }
    }

    public function getParts()
    {
        return $this->parts;
    }

    public function __toString()
    {
        return $this->raw;
    }

}
