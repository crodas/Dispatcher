if ({{$self->getExpr()}}) {
    {{ Dispatcher\Templates::get('urls')->render(array('urls' => $self->getUrls()), true) }}
}
