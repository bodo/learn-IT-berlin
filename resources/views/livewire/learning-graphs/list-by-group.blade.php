<div id="learning-graphs" class="card bg-base-100 shadow">
    <div class="card-body space-y-4">
        <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
            <div>
                <h2 class="card-title">{{ __('Learning graphs') }}</h2>
                <p class="text-sm text-base-content/70">{{ __('Follow structured paths curated by the group.') }}</p>
            </div>
            @if ($canManage)
                <div class="flex gap-2">
                    <a href="{{ route('admin.learning-graphs.index', $group) }}" class="btn btn-outline btn-sm">
                        {{ __('Manage graphs') }}
                    </a>
                    <a href="{{ route('admin.learning-graphs.create', $group) }}" class="btn btn-primary btn-sm">
                        {{ __('Create graph') }}
                    </a>
                </div>
            @endif
        </div>

        @if ($graphs->isEmpty())
            <div class="rounded-lg border border-dashed border-base-300 p-6 text-center text-base-content/60">
                @if ($canManage)
                    {{ __('No learning graphs yet. Start one to guide your members.') }}
                @else
                    {{ __('No learning graphs have been published yet. Check back soon!') }}
                @endif
            </div>
        @else
            <div class="space-y-4">
                @foreach ($graphs as $graph)
                    <div class="rounded-lg border border-base-200 p-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div class="space-y-1">
                            <div class="flex items-center gap-3">
                                <h3 class="text-lg font-semibold">{{ $graph->title }}</h3>
                                @if (! $graph->isPublished())
                                    <span class="badge badge-warning">{{ __('Draft') }}</span>
                                @endif
                            </div>
                            <p class="text-sm text-base-content/70">
                                {{ trans_choice('learning-graphs.node_count', $graph->nodes_count, ['count' => $graph->nodes_count]) }} ·
                                {{ __('Updated :time', ['time' => $graph->updated_at?->diffForHumans() ?? __('n/a')]) }}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('groups.learning-graphs.show', [$group, $graph]) }}" class="btn btn-sm btn-primary">
                                {{ __('View graph') }}
                            </a>
                            @if ($canManage)
                                <a href="{{ route('admin.learning-graphs.edit', [$group, $graph]) }}" class="btn btn-sm btn-outline">
                                    {{ __('Edit') }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
