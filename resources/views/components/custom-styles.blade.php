@props(['target' => 'frontend', 'inline' => false])

@php
    $target = $target ?: 'frontend';
    $customStylesService = app('custom-styles');
@endphp

@if($inline)
    {!! $customStylesService->renderInline($target) !!}
@else
    {!! $customStylesService->render($target) !!}
@endif