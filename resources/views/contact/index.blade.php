@extends('layouts.app')

@section('title', 'Contact — ' . ($profile->name ?? ''))

@section('content')

<section style="padding:64px 0 0">
    <h1 style="font-family:var(--font-heading);font-weight:400;font-size:clamp(32px,7vw,44px);margin:0 0 12px;line-height:1.1">Contact</h1>
    <p style="font-size:15px;line-height:1.7;color:var(--color-neutral-700);margin:0 0 30px;max-width:56ch">Open to conversations about backend work, algorithms, or anything on this site.</p>

    @if($channels->isNotEmpty())
        <div style="display:grid;gap:0;margin-bottom:44px">
            @foreach($channels as $channel)
                <a href="{{ $channel->href }}" class="row-link" style="display:flex;justify-content:space-between;gap:16px;align-items:center;padding:14px 4px;border-top:1px solid var(--color-divider);text-decoration:none;color:inherit">
                    <span style="font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--color-neutral-600)">{{ $channel->label }}</span>
                    <span style="font-size:14px;color:var(--color-accent-700)">{{ $channel->value }}</span>
                </a>
            @endforeach
        </div>
    @endif

    <h2 style="font-family:var(--font-heading);font-weight:600;font-size:24px;margin:0 0 20px">Or write here</h2>

    @if($sent)
        <div style="border:1px solid var(--color-accent);border-radius:var(--radius-md);padding:22px">
            <p style="font-size:15px;line-height:1.7;margin:0">Message noted. Replies usually come within a day or two — thank you for writing.</p>
        </div>
    @else
        @if($errors->any())
            <div style="border:1px solid #b00020;border-radius:var(--radius-md);padding:16px;margin-bottom:20px;font-size:13px;color:#b00020">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('contact.store') }}" style="display:grid;gap:16px">
            @csrf
            <label style="display:grid;gap:6px">
                <span style="font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--color-neutral-600)">Name</span>
                <input type="text" name="name" value="{{ old('name') }}" required style="font-family:var(--font-body);font-size:15px;padding:11px 13px;background:transparent;border:1px solid var(--color-divider);border-radius:var(--radius-md);color:var(--color-text)">
            </label>
            <label style="display:grid;gap:6px">
                <span style="font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--color-neutral-600)">Email</span>
                <input type="email" name="email" value="{{ old('email') }}" required style="font-family:var(--font-body);font-size:15px;padding:11px 13px;background:transparent;border:1px solid var(--color-divider);border-radius:var(--radius-md);color:var(--color-text)">
            </label>
            <label style="display:grid;gap:6px">
                <span style="font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--color-neutral-600)">Message</span>
                <textarea name="message" rows="5" required style="font-family:var(--font-body);font-size:15px;padding:11px 13px;background:transparent;border:1px solid var(--color-divider);border-radius:var(--radius-md);color:var(--color-text);resize:vertical">{{ old('message') }}</textarea>
            </label>
            <button type="submit" class="btn-outline" style="justify-self:start;font-family:var(--font-body);font-size:14px;padding:10px 20px;background:transparent;border:1px solid var(--color-accent);border-radius:var(--radius-md);color:var(--color-accent-700);cursor:pointer">Send</button>
        </form>
    @endif
</section>

@endsection
