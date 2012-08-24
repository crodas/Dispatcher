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

use Notoj\Annotation;

class Component
{
    const CONSTANT  = 1;
    const VARIABLE  = 2;
    const MIXED     = 3;

    protected $raw;
    protected $type;
    protected $parts = array();

    public function  __construct($part)
    {
        $this->raw = $part;
        $this->doParse();
    }

    public function getType()
    {
        return $this->type;
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
                $pos = $i + 1;
                $i   = strpos($str, '}', $i);
                $tmp = substr($str, $pos, $i - $pos);
                $parts[] = array(self::VARIABLE, $tmp);
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
