<?php

use App\Enums\CommentStatus;
use App\Enums\EventStatus;
use App\Enums\GroupRole;
use App\Enums\UserRole;
use App\Models\CommentModerationLog;
use App\Models\Event;
use App\Models\Group;
use App\Models\User;
use App\Notifications\PendingCommentsDigest;
use App\Services\CommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function createEventForModeration(): array
{
    $group = Group::factory()->create();
    $event = Event::factory()->for($group)->create([
        'status' => EventStatus::Published,
    ]);

    return [$group, $event];
}

it('queues pending comment for regular user and notifies group moderators', function () {
    Notification::fake();

    [$group, $event] = createEventForModeration();
    $owner = User::factory()->create();
    $moderator = User::factory()->create();
    $group->assignRole($owner, GroupRole::Owner);
    $group->assignRole($moderator, GroupRole::Moderator);

    $author = User::factory()->create(['role' => UserRole::User]);

    $comment = app(CommentService::class)->submit($event->fresh(), $author, 'Looking forward to this event!');

    expect($comment->status)->toBe(CommentStatus::Pending)
        ->and($comment->approved_by)->toBeNull();

    Notification::assertSentTo($owner, PendingCommentsDigest::class);
    Notification::assertSentTo($moderator, PendingCommentsDigest::class);
});

it('auto approves comment from trusted user without notifying moderators', function () {
    Notification::fake();

    [$group, $event] = createEventForModeration();
    $trusted = User::factory()->create(['role' => UserRole::TrustedUser]);

    $comment = app(CommentService::class)->submit($event->fresh(), $trusted, 'Excited to help out!');

    expect($comment->status)->toBe(CommentStatus::Approved)
        ->and($comment->approved_by)->toBe($trusted->id)
        ->and($comment->approved_at)->not->toBeNull();

    Notification::assertNothingSent();
});

it('resets approved comment to pending when edited by regular user', function () {
    Notification::fake();

    [$group, $event] = createEventForModeration();
    $owner = User::factory()->create();
    $group->assignRole($owner, GroupRole::Owner);

    $author = User::factory()->create(['role' => UserRole::User]);
    $service = app(CommentService::class);

    $comment = $service->submit($event->fresh(), $author, 'Initial thought.');
    $service->approve($comment->fresh(), $owner, 'Looks good');

    Notification::assertSentToTimes($owner, PendingCommentsDigest::class, 1);

    $updated = $service->update($comment->fresh(), $author, 'Updated after more info.');

    expect($updated->status)->toBe(CommentStatus::Pending)
        ->and($updated->approved_by)->toBeNull();

    Notification::assertSentToTimes($owner, PendingCommentsDigest::class, 2);
});

it('allows moderators to reject and delete comments while logging actions', function () {
    Notification::fake();

    [$group, $event] = createEventForModeration();
    $owner = User::factory()->create();
    $group->assignRole($owner, GroupRole::Owner);
    $author = User::factory()->create();

    $service = app(CommentService::class);
    $comment = $service->submit($event->fresh(), $author, 'This needs review.');

    $rejected = $service->reject($comment->fresh(), $owner, 'Off topic');
    expect($rejected->status)->toBe(CommentStatus::Rejected)
        ->and($rejected->approved_by)->toBe($owner->id)
        ->and($rejected->approved_at)->not->toBeNull();

    $log = CommentModerationLog::where('comment_id', $comment->id)
        ->where('action', 'rejected')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->notes)->toBe('Off topic');

    $service->delete($rejected->fresh(), $owner);
    expect($comment->fresh()->trashed())->toBeTrue();
});

it('records reports from other users without duplicates', function () {
    Notification::fake();

    [$group, $event] = createEventForModeration();
    $owner = User::factory()->create();
    $group->assignRole($owner, GroupRole::Owner);

    $author = User::factory()->create();
    $reporter = User::factory()->create();

    $service = app(CommentService::class);
    $comment = $service->submit($event->fresh(), $author, 'Please check this.');

    $report = $service->report($comment->fresh(), $reporter, 'Spam');
    $reportDuplicate = $service->report($comment->fresh(), $reporter, 'Spam');

    expect($report->id)->toBe($reportDuplicate->id)
        ->and($comment->reports()->count())->toBe(1);
});
