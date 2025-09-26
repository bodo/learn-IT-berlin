<?php

namespace App\Livewire\Groups;

use App\Enums\GroupRole;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Group $group;
    public bool $isMember = false;
    public bool $isOwner = false;

    public function mount(Group $group): void
    {
        $this->group = $group->loadCount(['allUsers as members_count']);

        if (Auth::check()) {
            $role = Auth::user()->groupRole($group);
            $this->isMember = (bool) $role;
            $this->isOwner = $role === GroupRole::Owner;
        }
    }

    public function toggleMembership(): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('login');

            return;
        }

        $user = Auth::user();
        $role = $user->groupRole($this->group);

        if ($role) {
            if ($role === GroupRole::Owner && $this->group->owners()->count() <= 1) {
                session()->flash('error', __('You must promote another owner before leaving.'));

                return;
            }

            $this->group->removeUser($user);
            $this->isMember = false;
            $this->isOwner = false;
        } else {
            $this->group->assignRole($user, GroupRole::Member);
            $this->isMember = true;
        }

        $this->group->loadCount(['allUsers as members_count']);
    }

    public function render()
    {
        $owners = $this->group->owners()->limit(5)->get();
        $moderators = $this->group->moderators()->limit(5)->get();

        return view('livewire.groups.show', [
            'owners' => $owners,
            'moderators' => $moderators,
        ]);
    }
}
