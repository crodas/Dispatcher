<?php
/**
 *  This file was generated with crodas/SimpleView (https://github.com/crodas/SimpleView)
 *  Do not edit this file.
 *
 */

namespace {


    class base_template_48f91366c839e056a4dcd440512f99e82188c46a
    {
        protected $parent;
        protected $child;
        protected $context;

        public function yield_parent($name, $args)
        {
            $method = "section_" . sha1($name);

            if (is_callable(array($this->parent, $method))) {
                $this->parent->$method(array_merge($this->context, $args));
                return true;
            }

            if ($this->parent) {
                return $this->parent->yield_parent($name, $args);
            }

            return false;
        }

        public function do_yield($name, Array $args = array())
        {
            if ($this->child) {
                // We have a children template, we are their base
                // so let's see if they have implemented by any change
                // this section
                if ($this->child->do_yield($name, $args)) {
                    // yes!
                    return true;
                }
            }

            // Do I have this section defined?
            $method = "section_" . sha1($name);
            if (is_callable(array($this, $method))) {
                // Yes!
                $this->$method(array_merge($this->context, $args));
                return true;
            }

            // No :-(
            return false;
        }

    }

    /** 
     *  Template class generated from callback.tpl.php
     */
    class class_d5b1e9c7792768d5c2401b262ea8a886c76d1f6a extends base_template_48f91366c839e056a4dcd440512f99e82188c46a
    {

        public function hasSection($name)
        {

            return false;
        }


        public function renderSection($name, Array $args = array(), $fail_on_missing = true)
        {
            if (!$this->hasSection($name)) {
                if ($fail_on_missing) {
                    throw new \RuntimeException("Cannot find section {$name}");
                }
                return "";
            }

        }

        public function enhanceException(Exception $e, $section = NULL)
        {
            if (!empty($e->enhanced)) {
                return;
            }

            $message = $e->getMessage() . "( IN " . 'callback.tpl.php';
            if ($section) {
                $message .= " | section: {$section}";
            }
            $message .= ")";

            $object   = new ReflectionObject($e);
            $property = $object->getProperty('message');
            $property->setAccessible(true);
            $property->setValue($e, $message);

            $e->enhanced = true;
        }

        public function render(Array $vars = array(), $return = false)
        {
            try {
                return $this->_render($vars, $return);
            } catch (Exception $e) {
                if ($return) ob_get_clean();
                $this->enhanceException($e);
                throw $e;
            }
        }

        public function _render(Array $vars = array(), $return = false)
        {
            $this->context = $vars;

            extract($vars);
            if ($return) {
                ob_start();
            }

            echo "if (!" . ($filter) . "( ";
            var_export($name);
            echo ", false)) {\n    require ";
            var_export($filePath);
            echo ";\n}\n\n";
            if (!empty($obj)) {
                echo "    if (empty(" . ($obj) . ")) {\n        ";
                echo $obj . " = new " . ($class) . ";\n    }\n";
            }

            if ($return) {
                return ob_get_clean();
            }

        }
    }

    /** 
     *  Template class generated from groups/if.tpl.php
     */
    class class_745a9a8e49cd4450b0d544f3f4422d08c6232d9e extends base_template_48f91366c839e056a4dcd440512f99e82188c46a
    {

        public function hasSection($name)
        {

            return false;
        }


        public function renderSection($name, Array $args = array(), $fail_on_missing = true)
        {
            if (!$this->hasSection($name)) {
                if ($fail_on_missing) {
                    throw new \RuntimeException("Cannot find section {$name}");
                }
                return "";
            }

        }

        public function enhanceException(Exception $e, $section = NULL)
        {
            if (!empty($e->enhanced)) {
                return;
            }

            $message = $e->getMessage() . "( IN " . 'groups/if.tpl.php';
            if ($section) {
                $message .= " | section: {$section}";
            }
            $message .= ")";

            $object   = new ReflectionObject($e);
            $property = $object->getProperty('message');
            $property->setAccessible(true);
            $property->setValue($e, $message);

            $e->enhanced = true;
        }

        public function render(Array $vars = array(), $return = false)
        {
            try {
                return $this->_render($vars, $return);
            } catch (Exception $e) {
                if ($return) ob_get_clean();
                $this->enhanceException($e);
                throw $e;
            }
        }

        public function _render(Array $vars = array(), $return = false)
        {
            $this->context = $vars;

            extract($vars);
            if ($return) {
                ob_start();
            }

            echo "if (" . ($self->getExpr()) . ") {\n    ";
            echo Dispatcher\Templates::get('urls')->render(array('urls' => $self->getUrls()), true) . "\n}\n";

            if ($return) {
                return ob_get_clean();
            }

        }
    }

    /** 
     *  Template class generated from groups/switch.tpl.php
     */
    class class_71415cc4ee21136a1a3e4a031229a7b5762a39df extends base_template_48f91366c839e056a4dcd440512f99e82188c46a
    {

        public function hasSection($name)
        {

            return false;
        }


        public function renderSection($name, Array $args = array(), $fail_on_missing = true)
        {
            if (!$this->hasSection($name)) {
                if ($fail_on_missing) {
                    throw new \RuntimeException("Cannot find section {$name}");
                }
                return "";
            }

        }

        public function enhanceException(Exception $e, $section = NULL)
        {
            if (!empty($e->enhanced)) {
                return;
            }

            $message = $e->getMessage() . "( IN " . 'groups/switch.tpl.php';
            if ($section) {
                $message .= " | section: {$section}";
            }
            $message .= ")";

            $object   = new ReflectionObject($e);
            $property = $object->getProperty('message');
            $property->setAccessible(true);
            $property->setValue($e, $message);

            $e->enhanced = true;
        }

        public function render(Array $vars = array(), $return = false)
        {
            try {
                return $this->_render($vars, $return);
            } catch (Exception $e) {
                if ($return) ob_get_clean();
                $this->enhanceException($e);
                throw $e;
            }
        }

        public function _render(Array $vars = array(), $return = false)
        {
            $this->context = $vars;

            extract($vars);
            if ($return) {
                ob_start();
            }

            echo "//<?php \nswitch (";
            echo $self->getExpr() . ")\n{\n";
            foreach($self->getUrls() as $case => $urls) {

                $this->context['case'] = $case;
                $this->context['urls'] = $urls;
                if ($case == '') {
                    $zelse = $urls;
                    $this->context['zelse'] = $zelse;
                    continue;
                }
                echo "        case ";
                var_export($case);
                echo ":\n            ";
                echo Dispatcher\Templates::get('urls')->render(array('urls' => $urls), true) . "\n            break;\n";
            }
            echo "\n}\n\n";
            if (!empty($zelse)) {
                echo "    " . (Dispatcher\Templates::get('urls')->render(array('urls' => $zelse), true)) . "\n";
            }

            if ($return) {
                return ob_get_clean();
            }

        }
    }

    /** 
     *  Template class generated from Main.tpl.php
     */
    class class_e3e00f73fbb9382e9bcbf6d5da438f81c0276903 extends base_template_48f91366c839e056a4dcd440512f99e82188c46a
    {

        public function hasSection($name)
        {

            return false;
        }


        public function renderSection($name, Array $args = array(), $fail_on_missing = true)
        {
            if (!$this->hasSection($name)) {
                if ($fail_on_missing) {
                    throw new \RuntimeException("Cannot find section {$name}");
                }
                return "";
            }

        }

        public function enhanceException(Exception $e, $section = NULL)
        {
            if (!empty($e->enhanced)) {
                return;
            }

            $message = $e->getMessage() . "( IN " . 'Main.tpl.php';
            if ($section) {
                $message .= " | section: {$section}";
            }
            $message .= ")";

            $object   = new ReflectionObject($e);
            $property = $object->getProperty('message');
            $property->setAccessible(true);
            $property->setValue($e, $message);

            $e->enhanced = true;
        }

        public function render(Array $vars = array(), $return = false)
        {
            try {
                return $this->_render($vars, $return);
            } catch (Exception $e) {
                if ($return) ob_get_clean();
                $this->enhanceException($e);
                throw $e;
            }
        }

        public function _render(Array $vars = array(), $return = false)
        {
            $this->context = $vars;

            extract($vars);
            if ($return) {
                ob_start();
            }

            echo "<?php\n/**\n *  Router dispatcher generated by crodas/Dispatcher\n *\n *  https://github.com/crodas/Dispatcher\n *\n *  This is a generated file, do not modify it.\n */\n";
            if ($config->getNamespace()) {
                echo "namespace " . ($config->getNamespace()) . ";\n";
            }
            echo "\nclass NotFoundException extends \\Exception \n{\n}\n\nclass RouteNotFoundException extends \\Exception \n{\n}\n\ninterface FilterCache\n{\n    public function has(\$key);\n    public function set(\$key, \$value, \$ttl);\n    public function get(\$key);\n}\n\nclass Request\n{\n    protected \$var = array();\n    protected \$changes = array();\n    protected \$watch   = false;\n    protected \$begin;\n\n    public function __construct()\n    {\n        \$this->begin = microtime(true);\n    }\n\n    public function getResponseTime()\n    {\n        return microtime(true) - \$this->begin;\n    }\n\n";
            foreach(array("GET", "PUT", "DELETE", "POST", "HEAD") as $type) {

                $this->context['type'] = $type;
                echo "    public function is" . ($type) . "()\n    {\n        return \$_SERVER['REQUEST_METHOD'] === ";
                var_export($type);
                echo ";\n    }\n";
            }
            echo "\n    public function isAjax()\n    {\n        return !empty(\$_SERVER['HTTP_X_REQUESTED_WITH']) && \n            strtolower(\$_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';\n    }\n\n    public function watchChanges()\n    {\n        \$this->watch   = true;\n        \$this->changes = array();\n        return true;\n    }\n\n    public function getChanges()\n    {\n        \$this->watch = false;\n        return \$this->changes;\n    }\n\n    protected function handleNotFound()\n    {\n        \$req = \$this;\n        #* render(\$self->getNotFoundHandler())\n\n        return false;\n    }\n\n    public function notFound()\n    {\n        if (\$this->handleNotFound() !== false) {\n            /** \n             * Was it handled? Yes!\n             */\n            exit;\n        }\n\n        throw new NotFoundException;\n    }\n\n    public function setIfEmpty(\$name, \$value)\n    {\n        if (empty(\$this->var[\$name])) {\n            \$this->var[\$name] = \$value;\n            if (\$this->watch) {\n                \$this->changes[] = \$name;\n            }\n        }\n        return \$this;\n    }\n\n    public function set(\$name, \$value)\n    {\n        \$this->var[\$name] = \$value;\n        if (\$this->watch) {\n            \$this->changes[] = \$name;\n        }\n        return \$this;\n    }\n\n    public function get(\$name)\n    {\n        if (array_key_exists(\$name, \$this->var)) {\n            return \$this->var[\$name];\n        }\n        return NULL;\n    }\n}\n\nclass Route\n{\n    protected \$cache;\n\n    public function setCache(FilterCache \$cache)\n    {\n        \$this->cache = \$cache;\n    }\n\n    // doCachedFilter\n    /**\n     *  Cache layer for Filters.\n     *\n     *  If a filter is cachable and a cache object is setup this method will\n     *  cache the output of a filter (and all their modifications to a request).\n     *\n     *  This function is designed to help with those expensive filters which \n     *  for instance talks to databases.\n     */\n    protected function doCachedFilter(\$callback, Request \$req, \$key, \$value, \$ttl)\n    {\n        if (empty(\$this->cache)) {\n            // no cache layer, we're just a proxy, call to the original callback\n            if (is_string(\$callback)) {\n                \$return = \$callback(\$req, \$key, \$value);\n            } else {\n                \$return = \$callback[0]->{\$callback[1]}(\$req, \$key, \$value);\n            }\n            return \$return;\n        }\n\n        \$objid = \"{\$key}\\n{\$value}\";\n        if (\$v=\$this->cache->get(\$objid)) {\n            \$req->set('filter:cached:' . \$key, true);\n            \$object = unserialize(\$v);\n            foreach (\$object['set'] as \$key => \$value) {\n                \$req->set(\$key, \$value);\n            }\n            return \$object['return'];\n        }\n\n        // not yet cached yet so we call the filter as normal\n        // but we save all their changes it does on Request object\n        \$req->watchChanges();\n        if (is_string(\$callback)) {\n            \$return = \$callback(\$req, \$key, \$value);\n        } else {\n            \$return = \$callback[0]->{\$callback[1]}(\$req, \$key, \$value);\n        }\n        \$keys = \$req->setIfEmpty(\$key, \$value)->getChanges();\n        \$set  = array();\n        foreach (\$keys as \$key) {\n            \$set[\$key] = \$req->get(\$key);\n        }\n\n        \$this->cache->set(\$objid, serialize(compact('return', 'set')), 3600); \n\n        \n        return \$return;\n    }\n\n    public function fromRequest(Request \$req = NULL)\n    {\n        if (empty(\$req)) {\n            \$req = new Request;\n        }\n        return \$this->doRoute(\$req, \$_SERVER);\n    }\n\n    public function doRoute(Request \$req, \$server)\n    {\n        \$uri    = \$server['REQUEST_URI'];\n        \$uri    = (\$p = strpos(\$uri, '?')) ? substr(\$uri, 0, \$p) : \$uri;\n        \$parts  = array_values(array_filter(explode(\"/\", \$uri)));\n        \$length = count(\$parts);\n        \$req->uri = \$uri;\n\n        if (empty(\$server['REQUEST_METHOD'])) {\n            \$server['REQUEST_METHOD'] = 'GET';\n        }\n\n        ";
            echo $groups->__toString() . "\n\n        // We couldn't find any handler for the URL,\n        // let's find in our complex url set (if there is any)\n        //\$this->handleComplexUrl(\$req, \$parts, \$length, \$server);\n    }\n\n}\n";

            if ($return) {
                return ob_get_clean();
            }

        }
    }

    /** 
     *  Template class generated from url.tpl.php
     */
    class class_bcdf040d0776ca67087d70f20e8996e6c1f0ca9d extends base_template_48f91366c839e056a4dcd440512f99e82188c46a
    {

        public function hasSection($name)
        {

            return false;
        }


        public function renderSection($name, Array $args = array(), $fail_on_missing = true)
        {
            if (!$this->hasSection($name)) {
                if ($fail_on_missing) {
                    throw new \RuntimeException("Cannot find section {$name}");
                }
                return "";
            }

        }

        public function enhanceException(Exception $e, $section = NULL)
        {
            if (!empty($e->enhanced)) {
                return;
            }

            $message = $e->getMessage() . "( IN " . 'url.tpl.php';
            if ($section) {
                $message .= " | section: {$section}";
            }
            $message .= ")";

            $object   = new ReflectionObject($e);
            $property = $object->getProperty('message');
            $property->setAccessible(true);
            $property->setValue($e, $message);

            $e->enhanced = true;
        }

        public function render(Array $vars = array(), $return = false)
        {
            try {
                return $this->_render($vars, $return);
            } catch (Exception $e) {
                if ($return) ob_get_clean();
                $this->enhanceException($e);
                throw $e;
            }
        }

        public function _render(Array $vars = array(), $return = false)
        {
            $this->context = $vars;

            extract($vars);
            if ($return) {
                ob_start();
            }

            if ($expr) {
                echo "    if (" . ($expr) . ") {\n";
            }
            echo "\n";
            foreach($url->getArguments() as $name => $var) {

                $this->context['name'] = $name;
                $this->context['var'] = $var;
                echo "    \$req->get(";
                var_export($name);
                echo ", ";
                var_export($var);
                echo ");\n";
            }
            echo "\n";
            foreach($url->getVariables() as $name => $var) {

                $this->context['name'] = $name;
                $this->context['var'] = $var;
                echo "    \$req->setIfEmpty(";
                var_export($name);
                echo ", \"\");\n";
            }
            echo "\n    \$allow = true;\n";
            if (count($preRoute) > 0) {
                echo "        //run preRoute filters\n";
                foreach($preRoute as $filter) {

                    $this->context['filter'] = $filter;
                    echo "            " . ($compiler->callbackPrepare($filter[0])) . "\n            if (\$allow) {\n                \$allow &= (";
                    echo $compiler->callback($filter[0], '$req', $filter[1]) . ") !== false;\n            }\n";
                }
            }
            echo "\n    if (\$allow) {\n        ";
            echo $compiler->callbackPrepare($url) . "\n        \$req->setIfEmpty('__handler__', ";
            echo $compiler->callbackObject($url->getAnnotation()) . ");\n        \$response = ";
            echo $compiler->callback($url, '$req') . ";\n\n";
            foreach($postRoute as $filter) {

                $this->context['filter'] = $filter;
                echo "            \$return = " . ($compiler->callback($filter[0], '$req', $filter[1], '$response')) . ";\n            if (is_array(\$return)) {\n                \$response = \$return;\n            }\n";
            }
            echo "\n        return \$response;\n    }\n\n\n";
            if ($expr) {
                echo "    }\n";
            }

            if ($return) {
                return ob_get_clean();
            }

        }
    }

    /** 
     *  Template class generated from urls.tpl.php
     */
    class class_c2276662ea4974cb085a05fe2d4490ddbb0e2e41 extends base_template_48f91366c839e056a4dcd440512f99e82188c46a
    {

        public function hasSection($name)
        {

            return false;
        }


        public function renderSection($name, Array $args = array(), $fail_on_missing = true)
        {
            if (!$this->hasSection($name)) {
                if ($fail_on_missing) {
                    throw new \RuntimeException("Cannot find section {$name}");
                }
                return "";
            }

        }

        public function enhanceException(Exception $e, $section = NULL)
        {
            if (!empty($e->enhanced)) {
                return;
            }

            $message = $e->getMessage() . "( IN " . 'urls.tpl.php';
            if ($section) {
                $message .= " | section: {$section}";
            }
            $message .= ")";

            $object   = new ReflectionObject($e);
            $property = $object->getProperty('message');
            $property->setAccessible(true);
            $property->setValue($e, $message);

            $e->enhanced = true;
        }

        public function render(Array $vars = array(), $return = false)
        {
            try {
                return $this->_render($vars, $return);
            } catch (Exception $e) {
                if ($return) ob_get_clean();
                $this->enhanceException($e);
                throw $e;
            }
        }

        public function _render(Array $vars = array(), $return = false)
        {
            $this->context = $vars;

            extract($vars);
            if ($return) {
                ob_start();
            }

            if (is_array($urls)) {
                foreach($urls as $id => $url) {

                    $this->context['id'] = $id;
                    $this->context['url'] = $url;
                    echo "        " . (Dispatcher\Templates::get('urls')->render(array('urls' => $url), true)) . "\n";
                }
            }
            else {
                echo "    " . ($urls) . "\n";
            }

            if ($return) {
                return ob_get_clean();
            }

        }
    }

}

namespace Dispatcher {


    class Templates
    {
        public static function getAll()
        {
            return array (
                0 => 'callback',
                1 => 'groups/if',
                2 => 'groups/switch',
                3 => 'main',
                4 => 'url',
                5 => 'urls',
            );
        }

        public static function getAllSections($name, $fail = true)
        {
            switch ($name) {
            default:
                if ($fail) {
                    throw new \RuntimeException("Cannot find section {$name}");
                }

                return array();
            }
        }

        public static function exec($name, Array $context = array(), Array $global = array())
        {
            $tpl = self::get($name);
            return $tpl->render(array_merge($global, $context));
        }

        public static function get($name, Array $context = array())
        {
            static $classes = array (
                'callback.tpl.php' => 'class_d5b1e9c7792768d5c2401b262ea8a886c76d1f6a',
                'callback' => 'class_d5b1e9c7792768d5c2401b262ea8a886c76d1f6a',
                'groups/if.tpl.php' => 'class_745a9a8e49cd4450b0d544f3f4422d08c6232d9e',
                'groups/if' => 'class_745a9a8e49cd4450b0d544f3f4422d08c6232d9e',
                'groups/switch.tpl.php' => 'class_71415cc4ee21136a1a3e4a031229a7b5762a39df',
                'groups/switch' => 'class_71415cc4ee21136a1a3e4a031229a7b5762a39df',
                'main.tpl.php' => 'class_e3e00f73fbb9382e9bcbf6d5da438f81c0276903',
                'main' => 'class_e3e00f73fbb9382e9bcbf6d5da438f81c0276903',
                'url.tpl.php' => 'class_bcdf040d0776ca67087d70f20e8996e6c1f0ca9d',
                'url' => 'class_bcdf040d0776ca67087d70f20e8996e6c1f0ca9d',
                'urls.tpl.php' => 'class_c2276662ea4974cb085a05fe2d4490ddbb0e2e41',
                'urls' => 'class_c2276662ea4974cb085a05fe2d4490ddbb0e2e41',
            );
            $name = strtolower($name);
            if (empty($classes[$name])) {
                throw new \RuntimeException("Cannot find template $name");
            }

            $class = "\\" . $classes[$name];
            return new $class;
        }
    }

}
