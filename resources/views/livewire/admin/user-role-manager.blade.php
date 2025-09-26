<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold">{{ __('User Role Management') }}</h1>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-error mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="mb-6">
                <input
                    wire:model.live="search"
                    type="text"
                    placeholder="{{ __('Search users by name or email...') }}"
                    class="input input-bordered w-full"
                />
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Current Role') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-neutral text-neutral-content rounded-full w-8">
                                                <span class="text-xs">{{ $user->initials() }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold">{{ $user->name }}</div>
                                            @if ($user->id === auth()->id())
                                                <div class="text-sm opacity-50">{{ __('(You)') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <div class="badge badge-primary">
                                        {{ $user->roleLabel() }}
                                    </div>
                                </td>
                                <td>
                                    @if ($user->id !== auth()->id())
                                        <div class="dropdown dropdown-end">
                                            <div tabindex="0" role="button" class="btn btn-sm btn-ghost">
                                                <x-lucide-more-horizontal class="w-4 h-4" />
                                            </div>
                                            <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                                                @foreach ($availableRoles as $role)
                                                    <li>
                                                        <button
                                                            wire:click="changeRole({{ $user->id }}, '{{ $role['value'] }}')"
                                                            @if($user->role?->value === $role['value']) disabled @endif
                                                            class="@if($user->role?->value === $role['value']) opacity-50 @endif"
                                                        >
                                                            {{ __('Make') }} {{ $role['label'] }}
                                                        </button>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
