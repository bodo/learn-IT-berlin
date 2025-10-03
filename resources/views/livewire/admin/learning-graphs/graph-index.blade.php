<div class="space-y-6">
    <x-ui.breadcrumbs :items="[
        ['label' => __('Groups'), 'url' => route('groups.index')],
        ['label' => $group->title, 'url' => route('groups.show', $group)],
        ['label' => __('Learning graphs')],
    ]" />

    <div class="card bg-base-100 shadow">
        <div class="card-body space-y-4">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="card-title">{{ __('Learning graphs') }}</h1>
                    <p class="text-sm text-base-content/70">{{ __('Create structured roadmaps for your group members.') }}</p>
                </div>
                <a href="{{ route('admin.groups.manage', $group) }}" class="btn btn-outline">
                    {{ __('Back to group settings') }}
                </a>
            </div>

            @if (session()->has('success'))
                <div class="alert alert-success">
                    <x-lucide-check class="w-4 h-4" />
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form class="grid gap-4 md:grid-cols-[2fr_1fr_auto]" wire:submit.prevent="createGraph">
                <label class="form-control w-full">
                    <div class="label"><span class="label-text">{{ __('Title') }}</span></div>
                    <input type="text" class="input input-bordered" wire:model.defer="title" placeholder="{{ __('e.g. Freelance mobile developer roadmap') }}" />
                    @error('title')
                        <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                    @enderror
                </label>

                <label class="form-control w-full">
                    <div class="label"><span class="label-text">{{ __('Status') }}</span></div>
                    <select class="select select-bordered" wire:model="status">
                        @foreach ($statuses as $graphStatus)
                            <option value="{{ $graphStatus->value }}">{{ $graphStatus->label() }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                    @enderror
                </label>

                <div class="flex items-end">
                    <button type="submit" class="btn btn-primary w-full md:w-auto">
                        {{ __('Create') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($graphs as $graph)
            <div class="card bg-base-100 shadow">
                <div class="card-body space-y-4">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h2 class="card-title text-lg">{{ $graph->title }}</h2>
                            <p class="text-sm text-base-content/70">
                                {{ trans_choice('learning-graphs.node_count', $graph->nodes_count, ['count' => $graph->nodes_count]) }} ·
                                {{ __('Updated :time', ['time' => $graph->updated_at?->diffForHumans() ?? __('n/a')]) }}
                            </p>
                        </div>
                        <span class="badge {{ $graph->isPublished() ? 'badge-success' : 'badge-ghost' }}">
                            {{ $graph->status?->label() ?? __('Draft') }}
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.learning-graphs.edit', [$group, $graph]) }}" class="btn btn-sm btn-primary">
                            {{ __('Edit') }}
                        </a>
                        <a href="{{ route('groups.learning-graphs.show', [$group, $graph]) }}" class="btn btn-sm btn-outline">
                            {{ __('View') }}
                        </a>
                        <button type="button" class="btn btn-sm btn-error btn-outline"
                            onclick="if(confirm('{{ __('Are you sure you want to delete this learning graph?') }}')) { $wire.deleteGraph({{ $graph->id }}); }">
                            {{ __('Delete') }}
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="md:col-span-2 xl:col-span-3">
                <div class="card bg-base-100 shadow">
                    <div class="card-body text-center text-base-content/70">
                        {{ __('No learning graphs yet. Create one to get started.') }}
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
