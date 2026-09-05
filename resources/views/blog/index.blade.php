@extends('layouts.app')

@section('title', 'Blog — ' . ($profile->name ?? ''))

@section('content')

<section style="padding:64px 0 0">
    <h1 style="font-family:var(--font-heading);font-weight:400;font-size:clamp(32px,7vw,44px);margin:0 0 12px;line-height:1.1">Blog</h1>
    <p style="font-size:15px;line-height:1.7;color:var(--color-neutral-700);margin:0 0 28px;max-width:56ch">Learning logs, problem write-ups and build notes. No schedule — published when something is worth keeping.</p>

    <div style="display:flex;flex-wrap:wrap;gap:8px;padding-bottom:26px">
        @foreach($categories as $cat)
            @php $active = $cat === $activeCategory; @endphp
            <a href="{{ route('blog.index', $cat === 'All' ? [] : ['category' => $cat]) }}"
               class="filter-btn"
               style="text-decoration:none;display:inline-block;font-size:12px;letter-spacing:.1em;text-transform:uppercase;padding:6px 12px;border-radius:var(--radius-md);background:transparent;{{ $active ? 'border:1px solid var(--color-accent);color:var(--color-accent-700)' : 'border:1px solid var(--color-divider);color:var(--color-neutral-600)' }}">
                {{ $cat }}
            </a>
        @endforeach
    </div>

    @forelse($posts as $post)
        <a href="{{ route('blog.show', $post) }}" class="row-link" style="display:block;text-decoration:none;color:inherit;padding:22px 0;border-top:1px solid var(--color-divider)">
            <p style="font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--color-neutral-600);margin:0 0 8px">{{ $post->meta() }}</p>
            <h3 style="font-family:var(--font-heading);font-weight:600;font-size:22px;margin:0 0 8px;line-height:1.25">{{ $post->title }}</h3>
            <p style="font-size:14px;line-height:1.65;color:var(--color-neutral-700);margin:0">{{ $post->excerpt }}</p>
        </a>
    @empty
        <p style="font-size:14px;color:var(--color-neutral-600);padding:22px 0;border-top:1px solid var(--color-divider)">Nothing in this category yet.</p>
    @endforelse
</section>

@endsection
