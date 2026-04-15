<div class="space-y-3">
    @forelse ($actions as $action)
        <div
            wire:key="action-log-{{ $action->id }}"
            class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900"
        >
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <x-filament::badge :color="$action->typeColor">
                            {{ $action->typeLabel }}
                        </x-filament::badge>

                        <div class="text-sm font-medium text-gray-950 dark:text-white">
                            {{ $action->title }}
                        </div>
                    </div>

                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $action->actorName }}
                    </div>

                    <div class="mt-3 whitespace-pre-line wrap-break-word text-sm text-gray-600 dark:text-gray-300">
                        {{ $action->body }}
                    </div>
                </div>

                <div class="flex flex-row-reverse items-center gap-4">
                    @if ($action->canManage)
                        <div class="flex items-center gap-1">
                            <x-filament::icon-button
                                color="gray"
                                icon="heroicon-o-pencil-square"
                                :label="__('actions.edit_action_log')"
                                :tooltip="__('actions.edit_action_log')"
                                size="sm"
                                wire:click="mountAction('editActionLog', { actionLog: {{ $action->id }} })"
                            />

                            <x-filament::icon-button
                                color="danger"
                                icon="heroicon-o-trash"
                                :label="__('actions.delete_action_log')"
                                :tooltip="__('actions.delete_action_log')"
                                size="sm"
                                wire:click="mountAction('deleteActionLog', { actionLog: {{ $action->id }} })"
                            />
                        </div>
                    @endif

                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $action->happenedAt }}
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
            {{ __('messages.timeline.no_actions_yet') }}
        </div>
    @endforelse
</div>
