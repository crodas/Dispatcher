@if (is_array($urls)) 
    @foreach ($urls as $id => $url)
        {{ Dispatcher\Templates::get('urls')->render(array('urls' => $url), true) }}
    @end
@else 
    {{ $urls }}
@end
