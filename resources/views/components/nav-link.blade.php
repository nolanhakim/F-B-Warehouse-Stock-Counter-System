@props(['active' => false, 'href' => '#', 'label' => '', 'icon' => ''])
<a href="{{ $href }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-md transition-colors {{ $active ? 'bg-surface text-white' : 'text-muted hover:bg-surface/60 hover:text-ink' }}">
    <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $icon }}"/></svg>
    <span>{{ $label }}</span>
    @if($active)<span class="ml-auto w-1.5 h-1.5 rounded-full bg-brand"></span>@endif
</a>
