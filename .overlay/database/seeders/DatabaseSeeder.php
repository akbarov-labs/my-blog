<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use App\Models\Experience;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdminUser();
        $this->seedProfile();
        $this->seedSkills();
        $this->seedExperiences();
        $this->seedSocialLinks();
        $this->seedProjects();
        $this->seedPosts();
    }

    protected function seedAdminUser(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Sarvar Akbarov',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
    }

    protected function seedProfile(): void
    {
        $profile = Profile::current();
        $profile->update([
            'name' => 'Sarvar Akbarov',
            'headline' => 'Backend Developer',
            'bio_1' => 'I build and maintain backend systems at a local company. Right now I am in the middle of a deliberate push to get better — deeper into system design, sharper at algorithms, and more fluent in English.',
            'bio_2' => 'This site is the record of that. I write down what I learn, what I break, and what I ship, mostly so future me can read it back.',
            'footer_location' => 'Written in Tashkent',
            'footer_note' => '',
        ]);
    }

    protected function seedSkills(): void
    {
        $skills = ['Go', 'PHP / Laravel', 'PostgreSQL', 'Redis', 'System Design', 'Algorithms', 'English'];

        foreach ($skills as $i => $name) {
            Skill::updateOrCreate(['name' => $name], ['sort_order' => $i]);
        }
    }

    protected function seedExperiences(): void
    {
        Experience::updateOrCreate(
            ['role' => 'Backend Developer', 'company' => 'Local company'],
            [
                'description' => 'Build and maintain backend systems in production.',
                'start_date' => '2023-01-01',
                'end_date' => null,
                'sort_order' => 0,
            ]
        );
    }

    protected function seedSocialLinks(): void
    {
        $links = [
            ['label' => 'Email', 'value' => 'sarvar@example.com', 'href' => 'mailto:sarvar@example.com'],
            ['label' => 'GitHub', 'value' => 'github.com/sarvar', 'href' => 'https://github.com/'],
            ['label' => 'LinkedIn', 'value' => 'in/sarvar-akbarov', 'href' => 'https://linkedin.com/'],
            ['label' => 'Telegram', 'value' => '@sarvar', 'href' => 'https://t.me/'],
            ['label' => 'X', 'value' => '@sarvar', 'href' => 'https://x.com/'],
        ];

        foreach ($links as $i => $link) {
            SocialLink::updateOrCreate(
                ['label' => $link['label']],
                array_merge($link, [
                    'show_on_hero' => true,
                    'show_on_contact' => true,
                    'sort_order' => $i,
                ])
            );
        }
    }

    protected function seedProjects(): void
    {
        $projects = [
            [
                'name' => 'Ratelim',
                'year' => '2026',
                'description' => 'Distributed token-bucket rate limiter in Go, backed by Redis Lua scripts so refill and take happen atomically across instances.',
                'stack' => ['Go', 'Redis', 'Lua'],
                'link' => 'https://github.com/',
            ],
            [
                'name' => 'Wordbank',
                'year' => '2026',
                'description' => 'Spaced-repetition vocabulary service used daily for English practice. REST API, Postgres, and a scheduler that decides what to review each morning.',
                'stack' => ['Go', 'Postgres'],
                'link' => 'https://github.com/',
            ],
            [
                'name' => 'Grid',
                'year' => '2025',
                'description' => 'A small LeetCode journal: stores each attempt, the invariant written down, and how long it took, to see which patterns still cost the most time.',
                'stack' => ['Python', 'SQLite'],
                'link' => 'https://github.com/',
            ],
            [
                'name' => 'Hookrelay',
                'year' => '2025',
                'description' => 'Webhook receiver with retries, exponential backoff and a dead-letter queue. Built after a production integration silently dropped events for a week.',
                'stack' => ['Go', 'RabbitMQ'],
                'link' => 'https://github.com/',
            ],
        ];

        foreach ($projects as $i => $project) {
            Project::updateOrCreate(
                ['name' => $project['name']],
                array_merge($project, ['sort_order' => $i])
            );
        }
    }

    protected function seedPosts(): void
    {
        $posts = [
            [
                'title' => 'Sliding window finally clicked',
                'category' => 'LeetCode',
                'published_at' => '2026-08-28',
                'excerpt' => 'I solved the same longest-substring problem three times over two months. The third time I understood why the window shrinks.',
                'blocks' => [
                    ['type' => 'text', 'content' => 'For a long time I treated sliding window as a trick I had memorised: two pointers, a hash map, move right, sometimes move left. It worked on the problems I had already seen and fell apart on everything else.'],
                    ['type' => 'text', 'content' => 'What changed was asking one question before writing anything: what invariant must hold inside the window? Once the invariant is named, the shrink condition writes itself — you move the left pointer exactly while the invariant is violated.'],
                    ['type' => 'heading', 'content' => 'Longest substring without repeating characters'],
                    ['type' => 'code', 'content' => "func lengthOfLongestSubstring(s string) int {\n    last := map[byte]int{}\n    best, left := 0, 0\n    for right := 0; right < len(s); right++ {\n        c := s[right]\n        if i, ok := last[c]; ok && i >= left {\n            left = i + 1\n        }\n        last[c] = right\n        if right-left+1 > best {\n            best = right - left + 1\n        }\n    }\n    return best\n}"],
                    ['type' => 'text', 'content' => 'The invariant here is: no character repeats between left and right. When a repeat appears, left jumps past its previous position — not one step at a time. That single line is the whole problem.'],
                    ['type' => 'quote', 'content' => 'If you cannot state the invariant in one sentence, you are still pattern-matching, not solving.'],
                    ['type' => 'text', 'content' => 'The invariant is now written as a comment above every two-pointer solution before the loop. It is slower for ten minutes and much faster afterwards.'],
                ],
            ],
            [
                'title' => 'Month three of shadowing English',
                'category' => 'English',
                'published_at' => '2026-08-14',
                'excerpt' => 'Fifteen minutes a day repeating podcast sentences out loud. Reading improved less than expected; listening improved much more.',
                'blocks' => [
                    ['type' => 'text', 'content' => 'My English was built from documentation and Stack Overflow, which means I could read almost anything and understand almost nothing spoken at normal speed. In June I started shadowing: play one sentence, repeat it aloud immediately, match the rhythm rather than the words.'],
                    ['type' => 'heading', 'content' => 'What actually changed'],
                    ['type' => 'text', 'content' => 'Listening comprehension moved first. Around week five I noticed I was following a conference talk without subtitles, which had never happened before. Speaking followed, slowly — the sentences are still simple, but they arrive without a pause in the middle.'],
                    ['type' => 'text', 'content' => 'Vocabulary barely moved, and that was surprising. Shadowing trains the ear and the mouth, not the dictionary. Ten new words a week were added separately, from articles read anyway.'],
                    ['type' => 'heading', 'content' => 'The routine'],
                    ['type' => 'text', 'content' => 'Fifteen minutes, every morning, one podcast episode stretched across a whole week. Repetition of the same audio matters more than variety — by day four the correction is of my own stress patterns instead of hunting for meaning.'],
                    ['type' => 'quote', 'content' => 'The goal was never a perfect accent. It was being understood the first time something is said.'],
                ],
            ],
            [
                'title' => 'Building a rate limiter I actually understand',
                'category' => 'Projects',
                'published_at' => '2026-07-30',
                'excerpt' => 'Token bucket in Go with Redis behind it. Mostly an excuse to think carefully about clock skew and atomicity.',
                'blocks' => [
                    ['type' => 'text', 'content' => 'Every backend worked on had a rate limiter someone else wrote. The goal was one that could be explained line by line, so a token bucket over Redis was built, then broken on purpose.'],
                    ['type' => 'heading', 'content' => 'Why the naive version is wrong'],
                    ['type' => 'text', 'content' => 'The obvious implementation reads the bucket, computes the refill, and writes it back. Between the read and the write, another instance does the same, and both allow a request that should have been rejected. Under load this is not rare — it is the common case.'],
                    ['type' => 'code', 'content' => "-- refill and take, atomically\nlocal tokens = tonumber(redis.call('HGET', KEYS[1], 'tokens') or ARGV[1])\nlocal last   = tonumber(redis.call('HGET', KEYS[1], 'ts') or ARGV[4])\nlocal filled = math.min(tonumber(ARGV[1]), tokens + (ARGV[4] - last) * ARGV[2])\nif filled < 1 then return 0 end\nredis.call('HSET', KEYS[1], 'tokens', filled - 1, 'ts', ARGV[4])\nreturn 1"],
                    ['type' => 'text', 'content' => 'Moving the whole read-modify-write into a Lua script solved it: Redis runs the script atomically, so no two instances can interleave. The timestamp comes from the caller rather than the server, which is a deliberate trade — it keeps the script deterministic for replication, at the cost of trusting client clocks.'],
                    ['type' => 'heading', 'content' => 'What would change next'],
                    ['type' => 'text', 'content' => 'The next version returns the retry-after delay instead of a bare boolean, so callers can back off intelligently instead of hammering the endpoint. That information is already in the script — it simply was not returned.'],
                ],
            ],
            [
                'title' => 'The index I added that made things slower',
                'category' => 'Projects',
                'published_at' => '2026-07-11',
                'excerpt' => 'A composite index on the wrong column order turned a 12ms query into 400ms. Reading the plan would have taken two minutes.',
                'blocks' => [
                    ['type' => 'text', 'content' => 'A slow endpoint got an index added on the columns in the WHERE clause, and the endpoint got slower. The column order was wrong: the planner could not use the index for the range condition because the equality column came second.'],
                    ['type' => 'code', 'content' => "EXPLAIN ANALYZE\nSELECT id, created_at FROM events\nWHERE tenant_id = $1 AND created_at > $2\nORDER BY created_at DESC LIMIT 50;"],
                    ['type' => 'text', 'content' => 'The rule now followed: equality columns first, then the range column, then the ordering column. Once the index matched that order the plan collapsed to an index scan and the query went back under fifteen milliseconds.'],
                    ['type' => 'quote', 'content' => 'An index is a promise about the order rows are stored in. If the order does not match how you ask, it is dead weight.'],
                ],
            ],
            [
                'title' => 'Why I started writing this down',
                'category' => 'English',
                'published_at' => '2026-06-25',
                'excerpt' => 'Three years of work and almost no record of it. Writing in English does two jobs at once, so it is the obvious place to start.',
                'blocks' => [
                    ['type' => 'text', 'content' => 'A lot of problems have been solved whose solutions are no longer remembered. The knowledge existed for a week and then dissolved, because nothing forced a clear statement of it.'],
                    ['type' => 'text', 'content' => 'Writing forces the statement. If a bug cannot be explained in five sentences it was not understood — it was only made to stop happening. And doing it in English means every post is also practice for the thing being worked on hardest.'],
                    ['type' => 'text', 'content' => 'So the plan is simple: a short post whenever something surprises me. No schedule, no minimum length, no attempt to sound like anyone else.'],
                ],
            ],
        ];

        foreach ($posts as $post) {
            Post::updateOrCreate(
                ['title' => $post['title']],
                $post + ['is_published' => true]
            );
        }
    }
}
