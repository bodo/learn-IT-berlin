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

    Route::get('moderate/comments', \App\Livewire\Moderation\CommentsQueue::class)
        ->middleware('can:moderate-comments')
        ->name('moderate.comments');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('admin/groups', \App\Livewire\Admin\Groups\GroupIndex::class)
        ->name('admin.groups.index');
});

Route::middleware(['auth'])->group(function () {
    Route::get('admin/groups/{group}', \App\Livewire\Admin\Groups\GroupManage::class)
        ->name('admin.groups.manage');

    Route::get('admin/groups/{group}/events', \App\Livewire\Admin\Events\EventIndex::class)
        ->name('admin.events.index');
    Route::get('admin/groups/{group}/events/create', \App\Livewire\Admin\Events\EventForm::class)
        ->defaults('event', null)
        ->name('admin.events.create');
    Route::get('admin/groups/{group}/events/{event}/edit', \App\Livewire\Admin\Events\EventForm::class)
        ->name('admin.events.edit');
    Route::get('admin/groups/{group}/events/{event}/attendees.csv', \App\Http\Controllers\EventAttendeesExportController::class)
        ->name('admin.events.attendees.export');

    Route::get('admin/groups/{group}/learning-graphs', \App\Livewire\Admin\LearningGraphs\GraphIndex::class)
        ->name('admin.learning-graphs.index');
    Route::get('admin/groups/{group}/learning-graphs/create', \App\Livewire\Admin\LearningGraphs\GraphForm::class)
        ->defaults('graph', null)
        ->name('admin.learning-graphs.create');
    Route::get('admin/groups/{group}/learning-graphs/{graph}/edit', \App\Livewire\Admin\LearningGraphs\GraphForm::class)
        ->name('admin.learning-graphs.edit');
});

Route::get('groups', \App\Livewire\Groups\Directory::class)->name('groups.index');
Route::get('events', \App\Livewire\Events\Feed::class)->name('events.index');
Route::get('groups/{group}', \App\Livewire\Groups\Show::class)->name('groups.show');
Route::get('groups/{group}/learning-graphs/{graph}', \App\Livewire\LearningGraphs\Show::class)
    ->name('groups.learning-graphs.show');
Route::get('groups/{group}/events', \App\Livewire\Events\ListByGroup::class)->name('groups.events.index');
Route::get('events/{event}', \App\Livewire\Events\Show::class)->name('events.show');

require __DIR__.'/auth.php';
