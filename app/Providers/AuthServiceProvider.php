<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

// Models
use App\Models\Forum;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Calendar;
use App\Models\Event;

// Policies
use App\Policies\ForumPolicy;
use App\Policies\PostPolicy;
use App\Policies\CalendarPolicy;
use App\Policies\EventPolicy;
use App\Policies\CommentPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        Forum::class => ForumPolicy::class,
        Post::class => PostPolicy::class,
        Calendar::class => CalendarPolicy::class,
        Event::class => EventPolicy::class,
        Comment::class => CommentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();
    }
}
