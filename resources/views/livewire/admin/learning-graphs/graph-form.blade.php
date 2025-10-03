@php
    use Illuminate\Support\Facades\Storage;
@endphp

<div class="space-y-6">
    <x-ui.breadcrumbs :items="[
        ['label' => __('Groups'), 'url' => route('groups.index')],
        ['label' => $group->title, 'url' => route('groups.show', $group)],
        ['label' => __('Learning graphs'), 'url' => route('admin.learning-graphs.index', $group)],
        ['label' => $graph->exists ? $graph->title : __('Create')],
    ]" />

    <div class="card bg-base-100 shadow">
        <div class="card-body space-y-6">
            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <h1 class="card-title">
                        {{ $graph->exists ? __('Edit learning graph') : __('Create learning graph') }}
                    </h1>
                    <p class="text-sm text-base-content/70">
                        {{ __('Define nodes, content blocks, and relationships for your learning roadmap.') }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.learning-graphs.index', $group) }}" class="btn btn-outline">
                        {{ __('Back') }}
                    </a>
                    @if ($graph->exists && $graph->isPublished())
                        <a href="{{ route('groups.learning-graphs.show', [$group, $graph]) }}" class="btn btn-primary">
                            {{ __('View live') }}
                        </a>
                    @endif
                </div>
            </div>

            @if (session()->has('success'))
                <div class="alert alert-success">
                    <x-lucide-check class="w-4 h-4" />
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form wire:submit.prevent="save" class="space-y-6">
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-control w-full">
                        <div class="label"><span class="label-text">{{ __('Title') }}</span></div>
                        <input type="text" class="input input-bordered" wire:model.defer="title" required />
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
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="button" class="btn btn-outline" wire:click="addNode">
                        <x-lucide-plus class="w-4 h-4" />
                        {{ __('Add node') }}
                    </button>
                    <button type="button" class="btn btn-outline" wire:click="addEdge" @disabled(count($nodes) < 2)>
                        <x-lucide-git-branch class="w-4 h-4" />
                        {{ __('Add edge') }}
                    </button>
                </div>

                <div class="space-y-4">
                    @forelse ($nodes as $nodeIndex => $node)
                        <div class="rounded-xl border border-base-300 p-4 space-y-4" wire:key="node-{{ $node['uuid'] }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="space-y-2 flex-1">
                                    <label class="form-control w-full">
                                        <div class="label"><span class="label-text">{{ __('Node title') }}</span></div>
                                        <input type="text" class="input input-bordered" wire:model.defer="nodes.{{ $nodeIndex }}.title" />
                                        @error('nodes.'.$nodeIndex.'.title')
                                            <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                                        @enderror
                                    </label>
                                </div>
                                <button type="button" class="btn btn-sm btn-error btn-outline" wire:click="removeNode('{{ $node['uuid'] }}')">
                                    <x-lucide-trash class="w-4 h-4" />
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="form-control">
                                    <div class="label"><span class="label-text">{{ __('Level (layout)') }}</span></div>
                                    <input type="number" min="0" class="input input-bordered" wire:model.defer="nodes.{{ $nodeIndex }}.level" />
                                    @error('nodes.'.$nodeIndex.'.level')
                                        <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                                    @enderror
                                </label>
                                <label class="form-control">
                                    <div class="label"><span class="label-text">{{ __('Order within level') }}</span></div>
                                    <input type="number" min="0" class="input input-bordered" wire:model.defer="nodes.{{ $nodeIndex }}.order" />
                                    @error('nodes.'.$nodeIndex.'.order')
                                        <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                                    @enderror
                                </label>
                            </div>

                            <div class="space-y-3">
                                <h3 class="font-semibold text-base">{{ __('Content blocks') }}</h3>

                                @forelse ($node['blocks'] as $blockIndex => $block)
                                    <div class="rounded-lg border border-base-200 p-4 space-y-3" wire:key="block-{{ $block['uuid'] }}">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="badge badge-outline">
                                                {{ $block['type'] === \App\Enums\LearningGraphBlockType::Text->value ? __('Text block') : __('Image block') }}
                                            </span>
                                            <button type="button" class="btn btn-xs btn-error btn-outline" wire:click="removeBlock('{{ $node['uuid'] }}', '{{ $block['uuid'] }}')">
                                                <x-lucide-trash class="w-4 h-4" />
                                            </button>
                                        </div>

                                        @if ($block['type'] === \App\Enums\LearningGraphBlockType::Text->value)
                                            <label class="form-control">
                                                <div class="label"><span class="label-text">{{ __('Markdown content') }}</span></div>
                                                <textarea class="textarea textarea-bordered h-32" wire:model.defer="nodes.{{ $nodeIndex }}.blocks.{{ $blockIndex }}.content"></textarea>
                                                @error('nodes.'.$nodeIndex.'.blocks.'.$blockIndex.'.content')
                                                    <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                                                @enderror
                                            </label>
                                        @else
                                            <div class="space-y-3">
                                                @if (! empty($block['upload']))
                                                    <div class="rounded-lg border border-dashed border-base-300 p-3">
                                                        <p class="text-sm font-medium">{{ __('Selected image preview') }}</p>
                                                        <img src="{{ $block['upload']->temporaryUrl() }}" alt="{{ __('Preview') }}" class="mt-2 max-h-48 rounded" />
                                                    </div>
                                                @elseif(! empty($block['image_path']))
                                                    <div class="rounded-lg border border-dashed border-base-300 p-3">
                                                        <p class="text-sm font-medium">{{ __('Current image') }}</p>
                                                        <img src="{{ Storage::disk('public')->url($block['image_path']) }}" alt="{{ __('Current image') }}" class="mt-2 max-h-48 rounded" />
                                                    </div>
                                                @endif

                                                <label class="form-control">
                                                    <div class="label"><span class="label-text">{{ __('Upload image') }}</span></div>
                                                    <input type="file" class="file-input file-input-bordered" wire:model="nodes.{{ $nodeIndex }}.blocks.{{ $blockIndex }}.upload" accept="image/*" />
                                                    @error('nodes.'.$nodeIndex.'.blocks.'.$blockIndex.'.upload')
                                                        <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                                                    @enderror
                                                </label>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-sm text-base-content/60">{{ __('No blocks yet. Add text or visual guidance for this node.') }}</p>
                                @endforelse

                                <div class="flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-sm btn-outline" wire:click="addBlock('{{ $node['uuid'] }}', '{{ \App\Enums\LearningGraphBlockType::Text->value }}')">
                                        <x-lucide-type class="w-4 h-4" />
                                        {{ __('Add text block') }}
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline" wire:click="addBlock('{{ $node['uuid'] }}', '{{ \App\Enums\LearningGraphBlockType::Image->value }}')">
                                        <x-lucide-image class="w-4 h-4" />
                                        {{ __('Add image block') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-base-300 p-6 text-center text-base-content/60">
                            {{ __('No nodes yet. Start by adding your first learning milestone.') }}
                        </div>
                    @endforelse
                </div>

                <div class="space-y-3">
                    <h2 class="text-lg font-semibold">{{ __('Edges') }}</h2>
                    @forelse ($edges as $edgeIndex => $edge)
                        <div class="grid gap-4 md:grid-cols-[2fr_2fr_2fr_auto]" wire:key="edge-{{ $edge['uuid'] }}">
                            <label class="form-control">
                                <div class="label"><span class="label-text">{{ __('From node') }}</span></div>
                                <select class="select select-bordered" wire:model="edges.{{ $edgeIndex }}.from">
                                    <option value="">{{ __('Select node') }}</option>
                                    @foreach ($nodeOptions as $option)
                                        <option value="{{ $option['uuid'] }}">{{ $option['title'] }}</option>
                                    @endforeach
                                </select>
                                @error('edges.'.$edgeIndex.'.from')
                                    <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                                @enderror
                            </label>

                            <label class="form-control">
                                <div class="label"><span class="label-text">{{ __('To node') }}</span></div>
                                <select class="select select-bordered" wire:model="edges.{{ $edgeIndex }}.to">
                                    <option value="">{{ __('Select node') }}</option>
                                    @foreach ($nodeOptions as $option)
                                        <option value="{{ $option['uuid'] }}">{{ $option['title'] }}</option>
                                    @endforeach
                                </select>
                                @error('edges.'.$edgeIndex.'.to')
                                    <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                                @enderror
                            </label>

                            <label class="form-control">
                                <div class="label"><span class="label-text">{{ __('Direction') }}</span></div>
                                <select class="select select-bordered" wire:model="edges.{{ $edgeIndex }}.direction">
                                    @foreach ($directions as $direction)
                                        <option value="{{ $direction->value }}">{{ $direction->label() }}</option>
                                    @endforeach
                                </select>
                                @error('edges.'.$edgeIndex.'.direction')
                                    <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                                @enderror
                            </label>

                            <label class="form-control">
                                <div class="label"><span class="label-text">{{ __('Label') }}</span></div>
                                <input type="text" class="input input-bordered" wire:model.defer="edges.{{ $edgeIndex }}.label" />
                                @error('edges.'.$edgeIndex.'.label')
                                    <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                                @enderror
                            </label>

                            <div class="flex items-end">
                                <button type="button" class="btn btn-error btn-outline" wire:click="removeEdge('{{ $edge['uuid'] }}')">
                                    <x-lucide-trash class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-base-content/60">{{ __('Add edges to connect nodes and define progression.') }}</p>
                    @endforelse
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Save learning graph') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
