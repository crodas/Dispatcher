<?php

#* function render_url($object) 
#   $route    = $object->getRouteDefinition()
#   $expr     = expr($object->getParts())
// Routes for __route__
#* if (count($expr) > 0) 
if (__expr__) {
#* end
    #* 
    # foreach ($object->getArguments() as $name => $var)
    $req->set(__@name__, __@var__);
    #*
    # end
    # foreach ($object->getVariables() as $name => $var)
    #   if (count($var) == 1)
    #       $variable = "parts[" . $var[0] . "]"
    #   else
    #       $variable = "matches_" . $var[0] . "[" . $var[1] . "]"
    #   end
    $req->setIfEmpty(__@name__, $__variable__);
    #* end
    # $callback = callback($object->getAnnotation())
    // do route
    return __callback__($req);
#* if ($expr) 
}
#* end
// end of __route__

#* end
