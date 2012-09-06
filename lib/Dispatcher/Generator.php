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

use Notoj\Dir as DirParser,
    Notoj\File as FileParser,
    Notoj\Annotations;

class Generator
{
    protected $dirs  = array();
    protected $files = array();
    protected $namespace = NULL;
    protected $output;

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setNamespace($ns)
    {
        if ($ns !== NULL && !preg_match('/^([a-z][a-z0-9_]*\\\\?)+$/i', $ns)) {
            throw new \RuntimeException("{$ns} is not a valid namespace");
        }
        $this->namespace = $ns;

        return $this;
    }

    /**
     *  Add one directory to the stack, it will
     *  scanned later when generate() is called
     *
     *  @param string $directory
     *
     *  @return $this
     */
    public function addDirectory($dir)
    {
        if (!is_dir($dir)) {
            throw new \RuntimeException("{$dir} is not a directory");
        }
        $this->dirs[] = $dir;

        return $this;
    }

    /**
     *  Add one file to the stack, it will scanned
     *  later when generate() is called
     *
     *  @param string $file
     *
     *  @return $this
     */
    public function addFile($file)
    {
        if (!is_file($file)) {
            throw new \RuntimeException("{$file} is not a file");
        }
        $this->files[] = $file;

        return $this;
    }

    public function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }

    public function generate()
    {
        $annotations = new Annotations;
        $isCached = $this->output && file_exists($this->output);

        foreach (array_unique($this->files) as $file) {
            $ann = new FileParser($file);
            $ann->getAnnotations($annotations);
            $isCached &= $ann->isCached();
        }

        foreach (array_unique($this->dirs) as $dir) {
            $ann = new DirParser($dir);
            $ann->getAnnotations($annotations);
            $isCached &= $ann->isCached();
        }

        if ($isCached) {
            /* nothing to do */
            return;
        }
        
        $compiler = new Compiler($this, $annotations);
        if (!empty($this->output)) {
            file_put_contents($this->output, $compiler->getOutput());
        }
        return $compiler->getOutput();
    }
}
