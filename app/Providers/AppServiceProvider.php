<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Complaint;
use App\Policies\CommentPolicy;
use App\Policies\ComplaintPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Services use constructor injection and are resolved automatically.
    }

    public function boot(): void
    {
        Gate::policy(Complaint::class, ComplaintPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Paginator::useTailwind();
        // Tambahkan 3 baris ini
    if (env('APP_ENV') !== 'local' || request()->server('HTTP_X_FORWARDED_PROTO') == 'https') {
        URL::forceScheme('https');
    }
    }
    
}
