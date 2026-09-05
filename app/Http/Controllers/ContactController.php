<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\SocialLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return view('contact.index', [
            'channels' => SocialLink::onContact()->ordered()->get(),
            'sent' => session('contact_sent', false),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactMessage::create($data);

        return redirect()
            ->route('contact')
            ->with('contact_sent', true);
    }
}
