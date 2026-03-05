<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/set-locale/{locale}', function (string $locale) {
    if (in_array($locale, array_keys(config('app.available_locales')), true)) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('set-locale');
