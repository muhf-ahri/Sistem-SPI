<!-- Detail List Component: pasangan label/value untuk halaman detail -->
<div class="sdx-detail-wrap">
    <dl {{ $attributes->merge(['class' => 'sdx-detail']) }}>
        {{ $slot }}
    </dl>
</div>
