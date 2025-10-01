<?php

use App\Enums\EventStatus;
use App\Enums\GroupRole;
use App\Enums\UserRole;
use App\Livewire\Admin\Events\EventForm;
use App\Models\Event;
use App\Models\EventImage;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('persists uploaded images when saving an event', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $group = Group::factory()->create();
    $group->assignRole($admin, GroupRole::Owner);

    $today = Carbon::now()->setTimezone(config('app.timezone'));
    $file = UploadedFile::fake()->image('banner.jpg', 800, 600);

    Livewire::actingAs($admin)
        ->test(EventForm::class, ['group' => $group, 'event' => null])
        ->set('title', 'Test Event')
        ->set('description', 'A great meetup')
        ->set('place', 'Berlin Campus')
        ->set('eventDate', $today->format('Y-m-d'))
        ->set('eventTime', $today->format('H:i'))
        ->set('timezone', config('app.timezone'))
        ->set('status', EventStatus::Published->value)
        ->set('newImages', [$file])
        ->call('save')
        ->assertRedirect(route('admin.events.index', $group));

    $event = Event::firstOrFail();

    expect($event->images)->toHaveCount(1)
        ->and(Storage::disk('public')->exists($event->images->first()->image_path))->toBeTrue();

    Storage::disk('public')->deleteDirectory('event-images');
});
