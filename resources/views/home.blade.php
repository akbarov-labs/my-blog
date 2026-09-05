@extends('layouts.app')

@section('title', ($profile->name ?? 'Home') . ' — ' . ($profile->headline ?? ''))
@section('description', $profile->bio_1 ?? '')

@section('content')

<section style="padding:72px 0 0">
    <p style="font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:var(--color-accent-700);margin:0 0 18px">{{ $profile->headline }}</p>
    <h1 style="font-family:var(--font-heading);font-weight:400;font-size:clamp(38px,9vw,58px);line-height:1.08;margin:0 0 26px;letter-spacing:-.01em">{{ $profile->name }}</h1>
    <p style="text-align:justify;hyphens:auto;font-size:16px;line-height:1.75;margin:0 0 16px;max-width:58ch">{{ $profile->bio_1 }}</p>
    @if($profile->bio_2)
        <p style="text-align:justify;hyphens:auto;font-size:16px;line-height:1.75;margin:0 0 34px;max-width:58ch">{{ $profile->bio_2 }}</p>
    @endif

    @if($skills->isNotEmpty())
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin:0 0 26px">
            @foreach($skills as $skill)
                <span style="font-size:11px;letter-spacing:.08em;text-transform:uppercase;padding:4px 9px;border:1px solid var(--color-divider);border-radius:var(--radius-sm);color:var(--color-neutral-600)">{{ $skill->name }}</span>
            @endforeach
        </div>
    @endif

    @if($social->isNotEmpty())
        <div style="display:flex;flex-wrap:wrap;gap:10px">
            @foreach($social as $link)
                @php $isPrimary = $loop->first; @endphp
                <a href="{{ $link->href }}" class="pill-link" style="font-size:13px;padding:8px 14px;border:1px solid {{ $isPrimary ? 'var(--color-accent)' : 'var(--color-divider)' }};border-radius:var(--radius-md);color:{{ $isPrimary ? 'var(--color-accent-700)' : 'var(--color-neutral-700)' }};text-decoration:none">{{ $link->label }}</a>
            @endforeach
        </div>
    @endif

    @if($experiences->isNotEmpty())
        <div style="margin-top:38px;display:grid;gap:10px">
            @foreach($experiences as $exp)
                <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:16px;font-size:13px;color:var(--color-neutral-700)">
                    <span>{{ $exp->role }} · {{ $exp->company }}</span>
                    <span style="color:var(--color-neutral-600)">{{ $exp->period() }}</span>
                </div>
            @endforeach
        </div>
    @endif
</section>

<hr style="border:0;border-top:1px solid var(--color-divider);margin:56px 0 32px">

<section>
    <div style="display:flex;align-items:baseline;justify-content:space-between;gap:16px;margin-bottom:26px">
        <h2 style="font-family:var(--font-heading);font-weight:600;font-size:24px;margin:0">Latest writing</h2>
        <a href="{{ route('blog.index') }}" style="font-size:13px;text-decoration:none;color:var(--color-accent-700)">All posts →</a>
    </div>

    @forelse($latest as $post)
        <a href="{{ route('blog.show', $post) }}" class="row-link" style="display:block;text-decoration:none;color:inherit;padding:20px 0;border-top:1px solid var(--color-divider)">
            <p style="font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--color-neutral-600);margin:0 0 8px">{{ $post->meta() }}</p>
            <h3 style="font-family:var(--font-heading);font-weight:600;font-size:21px;margin:0 0 8px;line-height:1.25">{{ $post->title }}</h3>
            <p style="font-size:14px;line-height:1.65;color:var(--color-neutral-700);margin:0">{{ $post->excerpt }}</p>
        </a>
    @empty
        <p style="font-size:14px;color:var(--color-neutral-600);padding:20px 0;border-top:1px solid var(--color-divider)">No posts published yet — add one from the admin panel.</p>
    @endforelse
</section>

@endsection
