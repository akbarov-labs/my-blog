@extends('layouts.app')

@section('title', $post->title . ' — ' . ($profile->name ?? ''))
@section('description', $post->excerpt)

@section('content')

<article style="padding:48px 0 0">
    <a href="{{ route('blog.index') }}" style="font-size:13px;text-decoration:none;color:var(--color-neutral-600)">← Blog</a>

    <p style="font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--color-accent-700);margin:30px 0 12px">{{ $post->meta() }}</p>
    <h1 style="font-family:var(--font-heading);font-weight:400;font-size:clamp(30px,6.5vw,42px);line-height:1.15;margin:0 0 28px">{{ $post->title }}</h1>
    <hr style="border:0;border-top:1px solid var(--color-divider);margin:0 0 30px">

    @foreach($post->blocks ?? [] as $block)
        @php $type = $block['type'] ?? 'text'; $content = $block['content'] ?? ''; @endphp

        @if($type === 'text')
            <p style="text-align:justify;hyphens:auto;font-size:16px;line-height:1.8;margin:0 0 20px">{{ $content }}</p>
        @elseif($type === 'heading')
            <h2 style="font-family:var(--font-heading);font-weight:600;font-size:24px;margin:36px 0 14px">{{ $content }}</h2>
        @elseif($type === 'code')
            <pre style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:13px;line-height:1.6;background:var(--color-surface);border:1px solid var(--color-divider);border-radius:var(--radius-md);padding:16px;overflow-x:auto;margin:0 0 24px">{{ $content }}</pre>
        @elseif($type === 'quote')
            <p style="border-left:2px solid var(--color-accent);padding-left:18px;font-style:italic;font-size:16px;line-height:1.75;margin:0 0 22px;color:var(--color-neutral-700)">{{ $content }}</p>
        @endif
    @endforeach

    <hr style="border:0;border-top:1px solid var(--color-divider);margin:40px 0 22px">
    <a href="{{ route('blog.index') }}" style="font-size:13px;text-decoration:none;color:var(--color-accent-700)">← All posts</a>
</article>

@endsection
