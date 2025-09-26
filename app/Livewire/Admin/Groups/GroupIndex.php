<?php

namespace App\Livewire\Admin\Groups;

use App\Enums\GroupRole;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class GroupIndex extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';
    public bool $showCreateModal = false;
    public string $title = '';
    public ?string $description = null;
    public $banner = null;

    protected $listeners = [
        'groupUpdated' => '$refresh',
    ];

    public function mount(): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['title', 'description', 'banner']);
        $this->showCreateModal = true;
    }

    public function createGroup(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255', Rule::unique('groups', 'title')],
            'description' => ['nullable', 'string', 'max:2000'],
            'banner' => ['nullable', 'image', 'max:2048'],
        ]);

        $bannerPath = null;

        if ($this->banner) {
            $bannerPath = $this->banner->store('group-banners', 'public');
        }

        $group = Group::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'banner_image_path' => $bannerPath,
        ]);

        $group->assignRole(Auth::user(), GroupRole::Owner);

        session()->flash('success', __('Group :title created.', ['title' => $group->title]));

        $this->showCreateModal = false;
        $this->reset(['title', 'description', 'banner']);
    }

    public function deleteGroup(int $groupId): void
    {
        $group = Group::findOrFail($groupId);

        $group->delete();

        session()->flash('success', __('Group deleted.'));

        $this->resetPage();
    }

    public function render()
    {
        $groups = Group::query()
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%'.$this->search.'%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.groups.group-index', [
            'groups' => $groups,
        ]);
    }
}
