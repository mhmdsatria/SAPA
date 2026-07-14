@props(['category' => null, 'label' => null, 'color' => null])
@php
$model = $category instanceof \App\Models\Category ? $category : null;
$text = $label ?? $model?->name ?? match((string)$category){'jalan'=>'Jalan Raya','kebersihan'=>'Kebersihan','penerangan'=>'Penerangan',default=>'Lainnya'};
$hex = $color ?? $model?->color ?? match((string)$category){'jalan'=>'#ef4444','kebersihan'=>'#10b981','penerangan'=>'#f59e0b',default=>'#6366f1'};
@endphp
<span {{ $attributes->merge(['class'=>'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold']) }} style="background: color-mix(in srgb, {{ $hex }} 14%, transparent); color: {{ $hex }}"><span class="h-1.5 w-1.5 rounded-full" style="background:{{ $hex }}"></span>{{ $text }}</span>
