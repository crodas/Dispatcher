@if ($self->hasRules())
switch ({{$self->getExpr()}}) {
    @foreach ($self->getUrls() as $case => $urls)
        case {{@$case}}:
            {{ Dispatcher\Templates::get('urls')->render(array('urls' => $urls), true) }}
            @if ($self->isApp())
                goto not_found;
            @else
                break;
            @end
    @end
}
@end

@if ($self->getElse())
    // DEFAULT
    {{ Dispatcher\Templates::get('urls')->render(array('urls' => $self->getElse()), true) }}
@end
