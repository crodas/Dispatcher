//<?php

protected function complexUrl{{$id}}($req, $parts, $length, &$return)
{
    $i = 0;
    $args = array();
    @foreach ($url->getParts() as $part)
        @set($prep, $part->exprPrepare())
        @set($expr, $part->getExpr())
        @if ($part->isRepetitive())
            @if ($expr)
                {{$prep}}
                while ($i < $length && {{$expr}}) {
                    @foreach($part->getVariables('') as $name => $var)
                        @if (count($var) == 1)
                            $args[{{@$name}}][] = $parts[$i];
                        @else
                            $args[{{@$name}}][] = $matches_0[{{$var[1]}}];
                        @end
                    @end
                    ++$i;
                }
            @else
                @set($no_filter, $part)
            @end
        @else
            @if ($expr)
                {{$prep}}
                @if (!empty($no_filter))
                    while ($i < $length && !({{$expr}})) {
                        @foreach($no_filter->getVariables('') as $name => $var)
                            @if (count($var) == 1)
                                $args[{{@$name}}][] = $parts[$i];
                            @else
                                $args[{{@$name}}][] = $matches_0[{{$var[1]}}];
                            @end
                        @end
                        ++$i;
                    }
                    @set($no_filter, null)
                @end
                if ($i >= $length || !({{$expr}})) {
                    return false;
                }
                @foreach ($part->getVariables('') as $name => $var)
                    @if (count($var) == 1)
                        @set($var, 'parts[$i]')
                    @else
                        @set($var, 'matches_0[' . $var[1] . ']')
                    @end
                    $args[{{@$name}}] = ${{$var}};
                @end
                ++$i;
            @end
        @end

    @end

    @if (!empty($no_filter)) 
    while ($i < $length) {
        @foreach ($no_filter->getVariables('') as $name => $var)
        $args[{{@$name}}][] = $parts[$i];
        @end
        ++$i;
    }
    @end

    
    @foreach ($url->getFilters('preroute') as $filter)
        {{ $self->callbackPrepare($filter[0]) }}
        if ({{$self->callback($filter[0], '$req', $filter[1])}} === false) {
            return false;
        }
    @end
    
    @foreach ($url->getArguments() as $name => $var)
    $req->set({{@$name}}, {{@$var}});
    @end
    $attributes = $req->attributes->all();
    foreach ($args as $key => $value) {
        if (empty($attributes[$key])) {
            $attributes[$key] = $value;
        }
    }
    $req->attributes->add($attributes);
    {{ $self->callbackPrepare($url) }}
    $req->attributes->set('__handler__', {{$self->callbackObject($url)}});
    $return = {{$self->callback($url, '$req')}};

    @foreach ($url->getFilters('postroute') as $filter)
        {{ $self->callbackPrepare($filter[0]) }}
        if ( is_array($r={{$self->callback($filter[0], '$req', $filter[1], '$return')}}) ) {
            $return = $r;
        }
    @end

    return true;
}

