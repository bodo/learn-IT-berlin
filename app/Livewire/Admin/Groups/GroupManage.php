<?php

namespace App\Livewire\Admin\Groups;

use App\Enums\GroupRole;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class GroupManage extends Component
{
    use WithFileUploads;

    public Group $group;

    public string $title = '';
    public ?string $description = null;
    public $banner = null;

    public string $ownerEmail = '';
    public string $moderatorEmail = '';
    public string $memberEmail = '';

    public function mount(Group $group): void
    {
        abort_unless($group->canManage(Auth::user()), 403);

        $this->group = $group;
        $this->title = $group->title;
        $this->description = $group->description;
    }

    public function updateGroup(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255', Rule::unique('groups', 'title')->ignore($this->group->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'banner' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($this->banner) {
            $bannerPath = $this->banner->store('group-banners', 'public');

            if ($this->group->banner_image_path && Storage::disk('public')->exists($this->group->banner_image_path)) {
                Storage::disk('public')->delete($this->group->banner_image_path);
            }

            $this->group->banner_image_path = $bannerPath;
        }

        $this->group->fill([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ])->save();

        session()->flash('success', __('Group details updated.'));

        $this->reset('banner');
        $this->group->refresh();
    }

    public function removeBanner(): void
    {
        if ($this->group->banner_image_path && Storage::disk('public')->exists($this->group->banner_image_path)) {
            Storage::disk('public')->delete($this->group->banner_image_path);
        }

        $this->group->update(['banner_image_path' => null]);
        $this->group->refresh();
    }

    public function addOwner(): void
    {
        $this->assignRole($this->ownerEmail, GroupRole::Owner);
        $this->ownerEmail = '';
    }

    public function addModerator(): void
    {
        $this->assignRole($this->moderatorEmail, GroupRole::Moderator);
        $this->moderatorEmail = '';
    }

    public function addMember(): void
    {
        $this->assignRole($this->memberEmail, GroupRole::Member);
        $this->memberEmail = '';
    }

    public function removeOwner(int $userId): void
    {
        if ($this->group->owners()->count() <= 1) {
            session()->flash('error', __('A group must have at least one owner.'));

            return;
        }

        $this->group->users()->updateExistingPivot($userId, [
            'role' => GroupRole::Member->value,
        ]);
        $this->group->refresh();
    }

    public function removeModerator(int $userId): void
    {
        $this->group->users()->updateExistingPivot($userId, [
            'role' => GroupRole::Member->value,
        ]);
        $this->group->refresh();
    }

    public function removeMember(int $userId): void
    {
        $this->group->users()->wherePivot('role', GroupRole::Member->value)->detach($userId);
        $this->group->refresh();
    }

    protected function assignRole(string $email, GroupRole $role): void
    {
        $email = trim($email);

        if ($email === '') {
            return;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            session()->flash('error', __('Please provide a valid email address.'));

            return;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            session()->flash('error', __('No user found with that email.'));

            return;
        }

        $this->group->assignRole($user, $role);
        $this->group->refresh();
        session()->flash('success', __('Role updated for :name.', ['name' => $user->name]));
    }

    public function render()
    {
        $owners = $this->group->owners()->get();
        $moderators = $this->group->moderators()->get();
        $members = $this->group->members()->get();

        return view('livewire.admin.groups.group-manage', [
            'owners' => $owners,
            'moderators' => $moderators,
            'members' => $members,
        ]);
    }
}
