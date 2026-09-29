<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('accessibility-widget::widget.admin.index_title') }}</flux:heading>
        </div>

        <flux:button variant="primary" icon="plus" :href="route('admin.accessibility-widgets.create')" wire:navigate>
            {{ __('agentoom-core::admin.common.new') }}
        </flux:button>
    </div>

    <flux:card>
        <flux:table :paginate="$widgets">
            <flux:table.columns>
                <flux:table.column sortable>{{ __('agentoom-core::admin.projects.title') }}</flux:table.column>
                <flux:table.column sortable>{{ __('agentoom-core::admin.common.name') }}</flux:table.column>
                <flux:table.column>{{ __('agentoom-core::admin.common.status') }}</flux:table.column>
                <flux:table.column align="right">{{ __('agentoom-core::admin.common.actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($widgets as $widget)
                    <flux:table.row>
                        <flux:table.cell class="font-medium">
                            {{ $widget->project?->name ?? '—' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $widget->name }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="{{ $widget->is_active ? 'green' : 'zinc' }}">
                                {{ $widget->is_active ? __('agentoom-core::admin.common.active') : __('agentoom-core::admin.common.inactive') }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="right">
                            <div class="flex items-center justify-end gap-2">
                                @php
                                    $loaderScript = "<script src=\"" . route('accessibility-widget.loader', ['slug' => $widget->slug]) . "\" async></script>";
                                @endphp
                                <div x-data="{ copied: false, loaderScript: {{ Js::from($loaderScript) }} }" class="flex items-center gap-1">
                                    <flux:button
                                        icon="clipboard"
                                        size="sm"
                                        variant="primary"
                                        color="fuchsia"
                                        x-on:click.prevent="
                                            navigator.clipboard.writeText(loaderScript);
                                            copied = true;
                                            setTimeout(() => copied = false, 2000);
                                        ">
                                        {{ __('agentoom-core::admin.common.copy_script') }}
                                    </flux:button>
                                    <span
                                        x-show="copied"
                                        x-transition.opacity
                                        class="text-xs text-emerald-600"
                                    >
                                        {{ __('agentoom-core::admin.common.copied') }}
                                    </span>
                                </div>

                                <flux:button
                                    href="{{ route('admin.accessibility-widgets.edit', $widget) }}"
                                    icon="pencil"
                                    size="sm"
                                    variant="primary"
                                    color="amber"
                                    wire:navigate>
                                    {{ __('agentoom-core::admin.common.edit') }}
                                </flux:button>
                                <flux:button
                                    variant="danger"
                                    icon="trash"
                                    size="sm"
                                    wire:click="showDeleteModal({{ $widget->id }})">
                                    {{ __('agentoom-core::admin.common.delete') }}
                                </flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="py-12 text-center text-zinc-400">
                            <div class="flex flex-col items-center gap-2">
                                <flux:icon.window class="size-10 opacity-30" />
                                <span>{{ __('agentoom-core::admin.common.no_results') }}</span>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <flux:modal wire:model="show_delete_modal" class="max-w-md">
        <div class="text-center space-y-4">
            <div class="mx-auto flex items-center justify-center size-12 rounded-full bg-red-100 dark:bg-red-900/30">
                <flux:icon.exclamation-triangle class="size-6 text-red-600 dark:text-red-400" />
            </div>

            <flux:heading size="lg">
                {{ __('agentoom-core::admin.common.delete_confirmation_title') }}
            </flux:heading>

            <flux:subheading class="text-zinc-500">
                {{ __('agentoom-core::admin.common.delete_confirmation_text') }}
            </flux:subheading>

            <div class="flex gap-3 justify-center pt-2">
                <flux:button wire:click="$set('show_delete_modal', false)">
                    {{ __('agentoom-core::admin.common.cancel') }}
                </flux:button>
                <flux:button variant="danger" icon="trash" wire:click="delete">
                    {{ __('agentoom-core::admin.common.delete') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
