<div class="space-y-3">
    @forelse ($actions as $action)
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="text-sm font-medium text-gray-950 dark:text-white">
                        {{ $action->title }}
                    </div>

                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $action->actorName }}
                    </div>

                    <div class="mt-3 whitespace-pre-line break-words text-sm text-gray-600 dark:text-gray-300">
                        {{ $action->body }}
                    </div>
                </div>

                <div class="shrink-0 text-xs text-gray-500 dark:text-gray-400">
                    {{ $action->happenedAt }}
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
            {{ __('messages.timeline.no_actions_yet') }}
        </div>
    @endforelse
</div>
