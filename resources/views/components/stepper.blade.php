<!-- Star Stepper Component
     Motif 5 bintang logo PEI dijadikan indikator tahapan siklus.
     Usage:
     <x-stepper :steps="[
         ['label' => 'Rencana', 'sub' => 'Draft', 'tone' => '#3f7fd4'],
         ['label' => 'Pelaksanaan', 'tone' => '#27a35f'],
     ]" :current="1" />
-->
@props([
    'steps' => [],
    'current' => 0,
])

<ol class="sdx-steps" aria-label="Tahapan">
    @foreach($steps as $i => $step)
        @php
            $tone = $step['tone'] ?? '#2d6ac7';
            $state = $i < $current ? 'done' : ($i == $current ? 'current' : '');
        @endphp
        <li class="sdx-step {{ $state }}" style="--step-tone: {{ $tone }};" aria-current="{{ $i == $current ? 'step' : 'false' }}">
            <span class="sdx-step-node">
                @if($i < $current)
                    <i class="bi bi-star-fill"></i>
                @else
                    <i class="bi bi-star{{ $i == $current ? '-fill' : '' }}"></i>
                @endif
            </span>
            <span class="sdx-step-text">
                <span class="sdx-step-label d-block">{{ $step['label'] }}</span>
                @if(isset($step['sub']))
                    <span class="sdx-step-sub">{{ $step['sub'] }}</span>
                @endif
            </span>
            @if(!$loop->last)
                <span class="sdx-step-line"></span>
            @endif
        </li>
    @endforeach
</ol>
