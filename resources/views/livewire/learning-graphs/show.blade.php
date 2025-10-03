<div class="space-y-6">
    <x-ui.breadcrumbs :items="[
        ['label' => __('Groups'), 'url' => route('groups.index')],
        ['label' => $group->title, 'url' => route('groups.show', $group)],
        ['label' => __('Learning graphs'), 'url' => route('groups.show', $group).'#learning-graphs'],
        ['label' => $graph->title],
    ]" />

    <div class="card bg-base-100 shadow">
        <div class="card-body space-y-6">
            <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                <div>
                    <h1 class="card-title text-3xl">{{ $graph->title }}</h1>
                    <p class="text-sm text-base-content/70">{{ __('Explore the recommended path to master this topic.') }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('groups.show', $group) }}" class="btn btn-outline">
                        {{ __('Back to group') }}
                    </a>
                    @if ($canManage)
                        <a href="{{ route('admin.learning-graphs.edit', [$group, $graph]) }}" class="btn btn-primary">
                            {{ __('Edit graph') }}
                        </a>
                    @endif
                </div>
            </div>

            @if (empty($networkNodes))
                <div class="rounded-lg border border-dashed border-base-300 p-6 text-center text-base-content/60">
                    {{ __('This learning graph has no nodes yet.') }}
                </div>
            @else
                <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
                    <div class="space-y-4">
                        <div id="learning-graph-canvas" class="h-[480px] rounded-xl border border-base-300 bg-base-200"></div>
                        <p class="text-xs text-base-content/60">
                            {{ __('Tip: Drag nodes to inspect relationships. Scroll to zoom, double click to reset view.') }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-base-300 bg-base-100 p-4 space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold">{{ __('Node details') }}</h2>
                            <span class="badge badge-ghost">{{ __('Markdown supported') }}</span>
                        </div>
                        <div id="learning-graph-details" class="space-y-4 text-sm leading-relaxed">
                            <p class="text-base-content/60">{{ __('Select a node to view its blocks.') }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@once
    @push('scripts')
        <link rel="stylesheet" href="https://unpkg.com/vis-network@9.1.6/dist/vis-network.min.css" />
        <script src="https://unpkg.com/vis-network@9.1.6/dist/vis-network.min.js"></script>
        <script>
            (function () {
                const nodesData = @json($networkNodes);
                const edgesData = @json($networkEdges);
                const nodeDetails = @json($nodeDetails);
                const canvasId = 'learning-graph-canvas';
                const detailsId = 'learning-graph-details';
                const emptyMessage = `{{ __('Select a node to view its blocks.') }}`;

                function renderDetails(nodeId) {
                    const container = document.getElementById(detailsId);
                    if (!container) {
                        return;
                    }

                    const data = nodeDetails[nodeId];

                    if (!data) {
                        container.innerHTML = `<p class="text-base-content/60">${emptyMessage}</p>`;
                        return;
                    }

                    let html = `<div class="space-y-3">`;
                    html += `<div>
                        <p class="text-xs uppercase tracking-wide text-base-content/60">{{ __('Selected node') }}</p>
                        <h3 class="text-xl font-semibold">${data.title}</h3>
                        <p class="text-xs text-base-content/60">{{ __('Level') }} ${data.level} · {{ __('Order') }} ${data.order}</p>
                    </div>`;

                    if (!data.blocks.length) {
                        html += `<p class="text-base-content/60">{{ __('No content blocks yet for this node.') }}</p>`;
                    } else {
                        data.blocks.forEach((block) => {
                            if (block.type === 'text') {
                                html += `<div class="p-3 rounded-lg border border-base-200 bg-base-100">${block.html}</div>`;
                            } else if (block.type === 'image') {
                                html += `<div class="p-3 rounded-lg border border-base-200 bg-base-100">
                                    <img src="${block.url}" alt="" class="rounded-lg max-h-64 object-contain" />
                                </div>`;
                            }
                        });
                    }

                    html += `</div>`;
                    container.innerHTML = html;
                }

                function initNetwork() {
                    const canvas = document.getElementById(canvasId);
                    if (!canvas || !Array.isArray(nodesData) || nodesData.length === 0 || typeof vis === 'undefined') {
                        return;
                    }

                    const network = new vis.Network(canvas, {
                        nodes: new vis.DataSet(nodesData),
                        edges: new vis.DataSet(edgesData),
                    }, {
                        layout: {
                            hierarchical: {
                                enabled: true,
                                direction: 'LR',
                                levelSeparation: 220,
                                nodeSpacing: 160,
                            },
                        },
                        physics: {
                            enabled: false,
                        },
                        interaction: {
                            hover: true,
                            dragNodes: true,
                            navigationButtons: true,
                            keyboard: true,
                        },
                        edges: {
                            smooth: {
                                type: 'curvedCW',
                                roundness: 0.2,
                            },
                        },
                    });

                    const firstNodeId = nodesData[0]?.id ?? null;
                    renderDetails(firstNodeId);
                    if (firstNodeId) {
                        network.selectNodes([firstNodeId]);
                        network.focus(firstNodeId, { scale: 1, animation: true });
                    }

                    network.on('selectNode', (params) => {
                        renderDetails(params.nodes[0]);
                    });

                    network.on('deselectNode', () => {
                        renderDetails(null);
                    });

                    network.on('doubleClick', () => {
                        network.fit({ animation: true });
                    });
                }

                document.addEventListener('DOMContentLoaded', initNetwork);
                document.addEventListener('livewire:navigated', initNetwork);
            })();
        </script>
    @endpush
@endonce
