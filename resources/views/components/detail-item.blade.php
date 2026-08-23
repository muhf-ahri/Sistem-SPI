<!-- Detail Item Component -->
@props(['label'])

<dt>{{ $label }}</dt>
<dd {{ $attributes }}>{{ $slot }}</dd>
