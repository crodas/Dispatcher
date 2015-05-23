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

use RuntimeException;
use Dispatcher\Compiler;

class ComplexUrl extends Url
{
    public function __construct(Url $url)
    {
        if (!$url->isComplex()) {
            throw new RuntimeException("Invalid constructor");
        }
        foreach (get_object_vars($url) as $key => $value) {
            $this->$key = $value;
        }
        foreach ($this->parts as $part) {
            $part->setIndex('$i');
        }
    }

    public function getMinLength()
    {
        return count($this->parts);
    }

    public function getFirstConstant()
    {
        if ($this->parts[0]->getType() == Component::CONSTANT) {
            return (string)$this->parts[0];
        }
        return false;
    }

    public function getLastConstant()
    {
        $last = count($this->parts)-1;
        if ($this->parts[$last]->getType() == Component::CONSTANT) {
            return (string)$this->parts[$last];
        }
        return false;
    }

    /**
     *  Complex URL weight algorithm
     *
     *  The idea is to rank URLs, the more predictive the complex URL is
     *  the less weight it would have.
     *
     *      /foo/{args}+/xxx    => It's easy to calculate (first elements needs to be foo, and last xxx)
     *      /{args}+            => It's hard, as it matches everything so it needs to be try last
     *  
     *  @return int
     */ 
    public function getWeight(Compiler $cmp = null)
    {
        $info = $this->getRouteInfo();
        $weight  = 0xffff / $info[ Component::LOOP ];

        foreach ($info as $type => $number) {
            if ($type != Component::LOOP) {
                // It is something we can calculate
                $weight -= (0xff-$type) * $number;
            }
        }

        return $weight;
    }

    public function getRouteInfo()
    {
        $types = array();
        foreach ($this->parts as $part) {
            if (empty($types[$part->getType()])) {
                $types[ $part->getType() ] = 0;
            }
            $types[ $part->getType() ]++;
        }
        return $types;
    }

    public function getConstants()
    {
        $consts = array();
        foreach ($this->parts as $part) {
            if ($part->getType() == Component::CONSTANT) {
                $consts[] = (string)$part;
            }
        }
        return $consts;
    }
}
