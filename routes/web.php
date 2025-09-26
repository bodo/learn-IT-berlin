<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', \App\Livewire\Dashboard::class)->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    Route::get('admin/users', \App\Livewire\Admin\UserRoleManager::class)
        ->middleware('role:superuser')
        ->name('admin.users');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('admin/groups', \App\Livewire\Admin\Groups\GroupIndex::class)
        ->name('admin.groups.index');
});

Route::middleware(['auth'])->group(function () {
    Route::get('admin/groups/{group}', \App\Livewire\Admin\Groups\GroupManage::class)
        ->name('admin.groups.manage');
});

Route::get('groups', \App\Livewire\Groups\Directory::class)->name('groups.index');
Route::get('groups/{group}', \App\Livewire\Groups\Show::class)->name('groups.show');

require __DIR__.'/auth.php';
