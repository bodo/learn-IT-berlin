<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class UserRoleManager extends Component
{
    use WithPagination;

    public string $search = '';

    /** @var list<array{value:string,label:string}> */
    public array $availableRoles = [];

    public function mount()
    {
        abort_unless(auth()->user()->isSuperuser(), 403);

        $this->availableRoles = array_map(
            fn (UserRole $role) => ['value' => $role->value, 'label' => $role->label()],
            UserRole::cases(),
        );
    }

    public function changeRole(User $user, string $role)
    {
        abort_unless(auth()->user()->isSuperuser(), 403);

        // Prevent changing your own role
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot change your own role.');

            return;
        }

        $roleEnum = UserRole::tryFrom($role);

        if (!$roleEnum) {
            session()->flash('error', 'Unknown role selection.');

            return;
        }

        $user->role = $roleEnum;
        $user->save();

        session()->flash('success', "User {$user->name} role changed to {$roleEnum->label()}.");

        Log::info('User role updated by superuser', [
            'actor_id' => auth()->id(),
            'target_id' => $user->id,
            'new_role' => $roleEnum->value,
        ]);
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search !== '', fn (Builder $query) => $query->where(function (Builder $builder) {
                $builder->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            }))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.user-role-manager', [
            'users' => $users,
        ]);
    }
}
