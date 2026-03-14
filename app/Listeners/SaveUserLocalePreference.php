<?php

namespace App\Listeners;

use BezhanSalleh\LanguageSwitch\Events\LocaleChanged;

class SaveUserLocalePreference
{
    public function handle(LocaleChanged $event): void
    {
        if ($user = auth()->user()) {
            $user->update(['locale' => $event->locale]);
        }
    }
}
