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

use Notoj\Dir as DirParser,
    Notoj\File as FileParser,
    Notoj\Annotations,
    crodas\FileUtil\File,
    WatchFiles\Watch;

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
        $this->dirs[]  = dirname($file);

        return $this;
    }

    public function setOutput($output = '')
    {
        $this->output = $output;

        return $this;
    }

    public function getOutput()
    {
        return $this->output ?: File::generateFilepath('dispatcher', getcwd());
    }

    public function generate()
    {
        $output = $this->getOutput();
        $cache = new Watch($output . '.cache');
        $dirs  = array_unique($this->dirs);
        $files = array_unique($this->files);

        $cache->watchFiles($files);
        $cache->watchDirs($dirs);

        if (!$cache->hasChanged()) {
            return;
        }

        $annotations = new \Notoj\Filesystem(array_unique(array_merge($dirs, $files)));
        $cache->watchFiles($files)->watchDirs($dirs);
        $cache->watch();
        
        $compiler = new Compiler($this, $annotations);
        file_put_contents($output, $compiler->getOutput());

        return $compiler->getOutput();
    }
}
