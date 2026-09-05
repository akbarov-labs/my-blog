@extends('layouts.app')

@section('title', 'Projects — ' . ($profile->name ?? ''))

@section('content')

<section style="padding:64px 0 0">
    <h1 style="font-family:var(--font-heading);font-weight:400;font-size:clamp(32px,7vw,44px);margin:0 0 12px;line-height:1.1">Projects</h1>
    <p style="font-size:15px;line-height:1.7;color:var(--color-neutral-700);margin:0 0 32px;max-width:56ch">Things built to learn something specific. Most are small on purpose.</p>

    <div style="display:grid;gap:16px">
        @forelse($projects as $project)
            <div style="border:1px solid var(--color-divider);border-radius:var(--radius-md);padding:22px">
                <div style="display:flex;flex-wrap:wrap;align-items:baseline;justify-content:space-between;gap:8px;margin-bottom:10px">
                    <h3 style="font-family:var(--font-heading);font-weight:600;font-size:21px;margin:0">{{ $project->name }}</h3>
                    @if($project->year)
                        <span style="font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--color-neutral-600)">{{ $project->year }}</span>
                    @endif
                </div>
                <p style="font-size:14px;line-height:1.7;color:var(--color-neutral-700);margin:0 0 16px">{{ $project->description }}</p>
                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px">
                    @foreach(($project->stack ?? []) as $tech)
                        <span style="font-size:11px;letter-spacing:.08em;text-transform:uppercase;padding:4px 9px;border:1px solid var(--color-divider);border-radius:var(--radius-sm);color:var(--color-neutral-600)">{{ $tech }}</span>
                    @endforeach
                    @if($project->link)
                        <a href="{{ $project->link }}" target="_blank" rel="noopener" style="font-size:13px;margin-left:auto;text-decoration:none;color:var(--color-accent-700)">Source →</a>
                    @endif
                </div>
            </div>
        @empty
            <p style="font-size:14px;color:var(--color-neutral-600)">No projects yet — add one from the admin panel.</p>
        @endforelse
    </div>
</section>

@endsection
