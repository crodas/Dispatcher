<?php

#* function render_url($object) 
#   $route    = $object->getRouteDefinition()
#   $expr     = expr($object->getParts())
// Routes for __route__ {{{
#* if ($expr)
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
    # $callback  = callback($object->getAnnotation(), '$req')
    # $preRoute  = $object->getFilters('preRoute')
    # $postRoute = $object->getFilters('postRoute')

    //run preRoute filters (if any)
    $allow = true;
    #* foreach ($preRoute as $filter)
    #   $filterFnc = callback($filter[0], '$req', $filter[1])
    if ($allow) {
        $allow &= __filterFnc__;
    }
    #* end

    // do route
    if ($allow) {
        $return = __callback__;

        // post postRoute (if any)
        #* foreach ($postRoute as $filter)
        #   $filterFnc = callback($filter[0], '$req', $filter[1], '$return')
        $return = __filterFnc__;
        #* end

        return $return;
    }
#* if ($expr) 
}
#* end
// }}} end of __route__

#* end
