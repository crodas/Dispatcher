switch ({{$self->getExpr()}})
{
    @foreach ($self->getUrls() as $case => $urls)
        @if ($case === '') 
            @set($zelse, $urls)
            @continue 
        @end
        case {{@$case}}:
            {{ Dispatcher\Templates::get('urls')->render(array('urls' => $urls), true) }}
            break;
    @end

}

@if (!empty($zelse))
    // DEFAULT
    {{ Dispatcher\Templates::get('urls')->render(array('urls' => $zelse), true) }}
@end
