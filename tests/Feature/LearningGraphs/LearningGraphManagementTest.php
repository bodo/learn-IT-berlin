<?php

use App\Enums\GroupRole;
use App\Enums\LearningGraphBlockType;
use App\Enums\LearningGraphEdgeDirection;
use App\Enums\LearningGraphStatus;
use App\Livewire\Admin\LearningGraphs\GraphForm;
use App\Livewire\LearningGraphs\ListByGroup;
use App\Models\Group;
use App\Models\LearningGraph;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function owner(): User {
    return User::factory()->create();
}

test('group owners can create a learning graph with nodes and edges', function () {
    Storage::fake('public');

    $group = Group::factory()->create();
    $user = owner();
    $group->assignRole($user, GroupRole::Owner);

    $this->actingAs($user);

    $component = Livewire::test(GraphForm::class, [
        'group' => $group,
        'graph' => null,
    ]);

    $component->set('title', 'Mobile Dev Roadmap');
    $component->set('status', LearningGraphStatus::Published->value);

    $component->call('addNode');
    $component->call('addNode');

    $nodes = $component->get('nodes');
    $firstNodeUuid = $nodes[0]['uuid'];
    $secondNodeUuid = $nodes[1]['uuid'];

    $component->call('addBlock', $firstNodeUuid, LearningGraphBlockType::Text->value);

    $component->set('nodes.0.title', 'Foundations');
    $component->set('nodes.0.level', 0);
    $component->set('nodes.0.order', 0);
    $component->set('nodes.0.blocks.0.content', "## Start\n- Learn PHP\n- Practice Laravel");

    $component->set('nodes.1.title', 'Ship Products');
    $component->set('nodes.1.level', 1);
    $component->set('nodes.1.order', 0);

    $component->call('addEdge');
    $component->set('edges.0.label', 'after mastering basics');
    $component->set('edges.0.direction', LearningGraphEdgeDirection::To->value);

    $component->call('save')
        ->assertHasNoErrors();

    $graph = LearningGraph::where('title', 'Mobile Dev Roadmap')->first();

    $component->assertRedirect(route('admin.learning-graphs.edit', [$group, $graph]));

    expect($graph)->not->toBeNull()
        ->and($graph->group_id)->toBe($group->id)
        ->and($graph->status)->toBe(LearningGraphStatus::Published)
        ->and($graph->nodes()->count())->toBe(2)
        ->and($graph->edges()->count())->toBe(1)
        ->and($graph->nodes()->first()->blocks()->count())->toBe(1);
});

test('learning graph listings show drafts only to managers', function () {
    $group = Group::factory()->create();
    $owner = owner();
    $group->assignRole($owner, GroupRole::Owner);

    $published = LearningGraph::factory()->for($group)->published()->create([
        'title' => 'Published roadmap',
    ]);

    $draft = LearningGraph::factory()->for($group)->create([
        'title' => 'Draft roadmap',
        'status' => LearningGraphStatus::Draft,
    ]);

    Livewire::test(ListByGroup::class, ['group' => $group])
        ->assertViewHas('graphs', function ($graphs) use ($published, $draft) {
            return $graphs->contains($published) && ! $graphs->contains($draft);
        });

    $this->actingAs($owner);

    Livewire::test(ListByGroup::class, ['group' => $group])
        ->assertViewHas('graphs', function ($graphs) use ($published, $draft) {
            return $graphs->contains($published) && $graphs->contains($draft);
        });
});

test('draft learning graphs are hidden from non managers', function () {
    $group = Group::factory()->create();
    $owner = owner();
    $group->assignRole($owner, GroupRole::Owner);

    $draft = LearningGraph::factory()->for($group)->create([
        'status' => LearningGraphStatus::Draft,
    ]);

    $this->get(route('groups.learning-graphs.show', [$group, $draft]))
        ->assertNotFound();

    $this->actingAs($owner);

    $this->get(route('groups.learning-graphs.show', [$group, $draft]))
        ->assertOk()
        ->assertSee($draft->title);
});
