if (!{{$filter}}( {{@$name }}, false)) {
    require {{@$filePath}};
}

@if (!empty($obj))
    if (empty({{$obj}})) {
        {{$obj}} = new {{$class}};
    }
@end
