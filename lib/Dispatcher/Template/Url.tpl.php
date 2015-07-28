@if ($expr)
    {{ $url->exprPrepare() }}
    if ({{$expr}}) {
@end

    @foreach ($url->getArguments() as $name => $var)
    $req->get({{@$name}}, {{@$var}});
    @end

    $attributes = $req->attributes->all();
    $merge      = false;
    @foreach ($url->getVariables() as $name => $var)
        @if (count($var) == 1)
            @set($variable, "parts[" . $var[0] . "]")
        @else
            @set($variable, "matches_" . $var[0] . "[" . $var[1] . "]")
        @end
        if (empty($attributes[{{@$name}}])) {
            $attributes[{{@$name}}] = ${{$variable}};
            $merge = true;
        }
    @end

    if ($merge) {
        $req->attributes->add($attributes);
    }

    $allow = true;
    @if (count($preRoute) > 0) 
        //run preRoute filters
        @foreach ($preRoute as $filter)
            {{ $compiler->callbackPrepare($filter[0]) }}
            if ($allow) {
                $allow &= ({{$compiler->callback($filter[0], '$req', $filter[1])}}) !== false;
            }
        @end
    @end

    if ($allow) {
        {{ $compiler->callbackPrepare($url) }}
        $req->attributes->set('__handler__', {{$compiler->callbackObject($url->getAnnotation())}});
        $response = {{ $compiler->callback($url, '$req') }};

        @foreach ($postRoute as $filter)
            {{ $compiler->callbackPrepare($filter[0]) }}
            $return = {{$compiler->callback($filter[0], '$req', $filter[1], '$response') }};
            if (is_array($return)) {
                $response = $return;
            }
        @end

        return $response;
    }


@if ($expr)
    }
@end 
