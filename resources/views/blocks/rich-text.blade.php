@if (filled(trim(strip_tags($html, '<img><iframe><hr>'))))
    <div class="fb-prose">{!! $html !!}</div>
@endif
