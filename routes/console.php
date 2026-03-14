<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 每天凌晨清理回收站超过 30 天的文章
Schedule::call(function () {
    \App\Models\Post::query()
        ->where('status', \App\Enums\PostStatus::TRASH)
        ->where('updated_at', '<=', now()->subDays(30))
        ->delete();
})->daily()->name('cleanup-trashed-posts');
