<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Skill;
use App\Models\SocialLink;

class HomeController extends Controller
{
    public function __invoke()
    {
        return view('home', [
            'profile' => Profile::current(),
            'latest' => Post::published()->latest('published_at')->take(3)->get(),
            'social' => SocialLink::onHero()->ordered()->get(),
            'skills' => Skill::ordered()->get(),
            'experiences' => Experience::ordered()->get(),
        ]);
    }
}
