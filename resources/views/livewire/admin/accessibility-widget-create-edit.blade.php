<div class="space-y-6">
    <flux:heading size="xl">{{ $widget && $widget->exists ? __('accessibility-widget::widget.admin.edit_title') : __('accessibility-widget::widget.admin.create_title') }}</flux:heading>

    <form wire:submit="submit" class="space-y-6">
        <flux:card>
            <flux:tab.group>
                <flux:tabs variant="segmented" class="mb-6">
                    <flux:tab name="general">{{ __('accessibility-widget::widget.admin.general_tab') }}</flux:tab>
                    <flux:tab name="appearance">{{ __('accessibility-widget::widget.admin.appearance_tab') }}</flux:tab>
                    <flux:tab name="settings">{{ __('accessibility-widget::widget.admin.settings_tab') }}</flux:tab>
                </flux:tabs>

                <flux:tab.panel name="general">
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <flux:select wire:model="project_id" label="{{ __('agentoom-core::admin.projects.title') }}" placeholder="{{ __('accessibility-widget::widget.admin.select_project') }}">
                                @foreach($projects as $project)
                                    <flux:select.option value="{{ $project->id }}">{{ $project->name }}</flux:select.option>
                                @endforeach
                            </flux:select>

                            <flux:input wire:model="name" label="{{ __('agentoom-core::admin.common.name') }}" placeholder="My Website Widget" />
                        </div>

                        <flux:textarea wire:model="allowed_domains" label="{{ __('accessibility-widget::widget.admin.allowed_domains') }}" placeholder="example.com, mysite.org" description="{{ __('accessibility-widget::widget.admin.allowed_domains_help') }}" />

                        <div class="flex justify-between items-center pt-4">
                            <flux:switch wire:model="is_active" label="{{ __('agentoom-core::admin.common.active') }}" />
                        </div>
                    </div>
                </flux:tab.panel>

                <flux:tab.panel name="appearance">
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                            <flux:select wire:model.live="position" label="{{ __('accessibility-widget::widget.admin.position') }}">
                                <flux:select.option value="bottom-right">{{ __('agentoom-chat-widget::admin.widgets.bottom_right') }}</flux:select.option>
                                <flux:select.option value="bottom-left">{{ __('agentoom-chat-widget::admin.widgets.bottom_left') }}</flux:select.option>
                                <flux:select.option value="top-right">{{ __('agentoom-chat-widget::admin.widgets.top_right') }}</flux:select.option>
                                <flux:select.option value="top-left">{{ __('agentoom-chat-widget::admin.widgets.top_left') }}</flux:select.option>
                            </flux:select>

                            <x-agentoom-core::icon-upload
                                :label="__('accessibility-widget::widget.admin.icon_trigger')"
                                wire-model="icon_trigger_file"
                                :existing-url="$icon_trigger_file?->temporaryUrl() ?? (!empty($icon_trigger) ? asset('storage/' . $icon_trigger) : null)"
                                remove-action="$wire.$set('remove_icon_trigger', true)"
                            />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                            <flux:select label="{{ __('accessibility-widget::widget.admin.presets') }}" wire:model.live="preset">
                                <flux:select.option value="">{{ __('agentoom-core::admin.common.select') }}</flux:select.option>
                                <option disabled>-- {{ __('System Presets') }} --</option>
                                <flux:select.option value="default">{{ __('accessibility-widget::widget.admin.preset_default') }}</flux:select.option>
                                <flux:select.option value="dark">{{ __('accessibility-widget::widget.admin.preset_dark') }}</flux:select.option>
                                <flux:select.option value="high_contrast">{{ __('accessibility-widget::widget.admin.preset_high_contrast') }}</flux:select.option>
                                @if($this->userPresets->count() > 0)
                                    <option disabled>-- {{ __('Your Presets') }} --</option>
                                    @foreach($this->userPresets as $userPreset)
                                        <flux:select.option value="{{ $userPreset->name }}">{{ $userPreset->name }}</flux:select.option>
                                    @endforeach
                                @endif
                            </flux:select>

                            <div class="flex items-end gap-3 p-4 bg-zinc-50 dark:bg-white/5 rounded-xl border border-zinc-200 dark:border-white/10">
                                <flux:input wire:model="presetName" label="{{ __('Save as a preset') }}" placeholder="{{ __('Preset name') }}" class="flex-1" />
                                <flux:button wire:click="savePreset" variant="subtle" icon="bookmark">{{ __('Save Preset') }}</flux:button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                            <flux:input type="color" wire:model.live="primary_color" label="{{ __('accessibility-widget::widget.admin.primary_color') }}" />
                            <flux:input type="color" wire:model.live="bg_color" label="{{ __('accessibility-widget::widget.admin.bg_color') }}" />
                            <flux:input type="color" wire:model.live="text_color" label="{{ __('accessibility-widget::widget.admin.text_color') }}" />
                        </div>

                        <div class="pt-6 border-t border-zinc-100 dark:border-zinc-800">
                            <flux:heading size="sm" class="mb-4">{{ __('accessibility-widget::widget.admin.preview') }}</flux:heading>
                            <div
                                x-data="{
                                    isOpen: true,
                                    position: @entangle('position'),
                                    primaryColor: @entangle('primary_color'),
                                    bgColor: @entangle('bg_color'),
                                    textColor: @entangle('text_color')
                                }"
                                class="relative bg-zinc-200 dark:bg-zinc-900 rounded-xl p-8 overflow-hidden border border-zinc-300 dark:border-zinc-700"
                                :style="'height:600px; --primary-color: ' + primaryColor + '; --bg-color: ' + bgColor + '; --text-color: ' + textColor"
                            >
                                {{-- Background Placeholder to simulate a website --}}
                                <div class="absolute inset-0 p-12 opacity-10 pointer-events-none select-none">
                                    <div class="space-y-4">
                                        <div class="h-8 bg-zinc-400 rounded w-1/3"></div>
                                        <div class="h-4 bg-zinc-400 rounded w-full"></div>
                                        <div class="h-4 bg-zinc-400 rounded w-full"></div>
                                        <div class="h-4 bg-zinc-400 rounded w-3/4"></div>
                                        <div class="grid grid-cols-3 gap-4 mt-8">
                                            <div class="h-32 bg-zinc-400 rounded"></div>
                                            <div class="h-32 bg-zinc-400 rounded"></div>
                                            <div class="h-32 bg-zinc-400 rounded"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Widget Container --}}
                                <div
                                    class="absolute flex gap-4 transition-all duration-500"
                                    :class="{
                                        'bottom-8 right-8 flex-col items-end': position === 'bottom-right',
                                        'bottom-8 left-8 flex-col items-start': position === 'bottom-left',
                                        'top-8 right-8 flex-col-reverse items-end': position === 'top-right',
                                        'top-8 left-8 flex-col-reverse items-start': position === 'top-left'
                                    }"
                                >
                                    {{-- Panel Preview --}}
                                    <div
                                        x-show="isOpen"
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                        class="w-80 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.3)] overflow-hidden border border-zinc-200 dark:border-zinc-800 z-10 flex flex-col"
                                        :style="'background-color: ' + bgColor + '; color: ' + textColor + '; height: 450px;'"
                                    >
                                        {{-- Header --}}
                                        <div class="bg-[#374151] text-white p-3 flex justify-between items-center shrink-0">
                                            <span class="font-bold text-sm">{{ __('accessibility-widget::widget.title') }}</span>
                                            <span class="text-xl cursor-pointer" @click="isOpen = false">&times;</span>
                                        </div>

                                        <div class="flex-grow overflow-y-auto" :style="'background-color: ' + bgColor">
                                            {{-- Language --}}
                                            <div class="border-b border-zinc-100 dark:border-zinc-800 p-3 flex items-center justify-between" :style="'background-color: ' + bgColor">
                                                <div class="flex items-center gap-2">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                                    <span class="text-xs font-bold">{{ __('accessibility-widget::widget.language') }}</span>
                                                </div>
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rotate-180"><path d="m6 9 6 6 6-6"/></svg>
                                            </div>
                                            <div class="p-3">
                                                <div class="w-full border border-zinc-200 rounded p-1.5 text-[10px] flex justify-between items-center" :style="'background-color: ' + bgColor">
                                                    <span>English</span>
                                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                                </div>
                                            </div>

                                            {{-- Profiles --}}
                                            <div class="border-b border-zinc-100 dark:border-zinc-800 p-3 flex items-center justify-between" :style="'background-color: ' + bgColor">
                                                <div class="flex items-center gap-2">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                                    <span class="text-xs font-bold">{{ __('accessibility-widget::widget.profiles') }}</span>
                                                </div>
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                            </div>

                                            {{-- Main Options --}}
                                            <div class="border-b border-zinc-100 dark:border-zinc-800 p-3 flex items-center justify-between" :style="'background-color: ' + bgColor">
                                                <div class="flex items-center gap-2">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                                                    <span class="text-xs font-bold">{{ __('accessibility-widget::widget.main_options') }}</span>
                                                </div>
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rotate-180"><path d="m6 9 6 6 6-6"/></svg>
                                            </div>

                                            <div class="p-3 grid grid-cols-2 gap-2">
                                                @foreach($this->getFeatures() as $feature)
                                                    @if($settings[$feature['key']] ?? false)
                                                        <div class="border border-zinc-200 rounded-lg p-2 flex flex-col items-center gap-1.5 text-center transition-colors" :style="'background-color: ' + bgColor">
                                                            <div class="w-5 h-5 opacity-70">
                                                                @if($feature['icon'] === 'screen-reader')
                                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8z"/><path d="M2 14h20"/><path d="M6 10h12"/><path d="M6 18h12"/></svg>
                                                                @elseif($feature['icon'] === 'contrast')
                                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 18a6 6 0 0 0 0-12v12z"/></svg>
                                                                @elseif($feature['icon'] === 'saturation')
                                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 22 4.44-4.44a7 7 0 1 0-8.88 0L12 22Z"/></svg>
                                                                @elseif($feature['icon'] === 'monochrome')
                                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M12 22a10 10 0 0 0 0-20"/><path d="M12 18a6 6 0 0 0 0-12"/></svg>
                                                                @elseif($feature['icon'] === 'link')
                                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                                                @elseif($feature['icon'] === 'type')
                                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>
                                                                @elseif($feature['icon'] === 'font')
                                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/><path d="M11 13h2"/></svg>
                                                                @elseif($feature['icon'] === 'cursor')
                                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 3 7.07 16.97 2.51-7.39 7.39-2.51L3 3z"/><path d="m13 13 6 6"/></svg>
                                                                @elseif($feature['icon'] === 'stop')
                                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                                                                @elseif($feature['icon'] === 'aid')
                                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                                @elseif($feature['icon'] === 'keyboard')
                                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M6 8h.01"/><path d="M10 8h.01"/><path d="M14 8h.01"/><path d="M18 8h.01"/><path d="M6 12h.01"/><path d="M10 12h.01"/><path d="M14 12h.01"/><path d="M18 12h.01"/><path d="M7 16h10"/></svg>
                                                                @elseif($feature['icon'] === 'voice')
                                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="22"/><line x1="8" y1="22" x2="16" y2="22"/></svg>
                                                                @elseif($feature['icon'] === 'text-reader')
                                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                                                @elseif($feature['icon'] === 'text-position')
                                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="21" y1="6" x2="3" y2="6"/><line x1="15" y1="12" x2="3" y2="12"/><line x1="17" y1="18" x2="3" y2="18"/></svg>
                                                                @elseif($feature['icon'] === 'structure')
                                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v18H3z"/><path d="M3 9h18"/><path d="M3 15h18"/><path d="M9 3v18"/></svg>
                                                                @elseif($feature['icon'] === 'dictionary')
                                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M9 7h6"/><path d="M9 11h6"/></svg>
                                                                @elseif($feature['icon'] === 'image-off')
                                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="2" y1="2" x2="22" y2="22"/><path d="M10.41 10.41 4.67 16.14a2 2 0 0 0 2.83 2.83l5.73-5.74"/><path d="m14.59 14.59 5.74 5.74a2 2 0 0 0 2.83-2.83l-5.74-5.73"/><path d="M9 3h10a2 2 0 0 1 2 2v10"/><path d="M3 13v2a2 2 0 0 0 2 2h2"/></svg>
                                                                @elseif($feature['icon'] === 'tooltip')
                                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                                                @endif
                                                            </div>
                                                            <span class="text-[8px] font-medium leading-tight">{{ __('accessibility-widget::widget.'.$feature['label']) }}</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Footer --}}
                                        <div class="p-3 border-t border-zinc-100 dark:border-zinc-800 flex justify-between items-center shrink-0" :style="'background-color: ' + bgColor">
                                            <div class="flex items-center gap-1 overflow-hidden max-w-[120px]">
                                                <div class="flex items-center gap-1.5" :style="'color: ' + primaryColor">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="none" class="w-4 h-4">
                                                        <defs>
                                                            <linearGradient id="logo-gradient-admin" x1="0%" y1="0%" x2="100%" y2="100%">
                                                                <stop offset="0%" style="stop-color:#00fbff;stop-opacity:1" />
                                                                <stop offset="100%" style="stop-color:#ad33ff;stop-opacity:1" />
                                                            </linearGradient>
                                                        </defs>
                                                        <path d="M16 5L26 25H21L16 15L11 25H6L16 5Z" fill="url(#logo-gradient-admin)"></path>
                                                    </svg>
                                                    <span class="text-xs font-bold font-inter tracking-tight" style="background: linear-gradient(to right, #00fbff, #ad33ff); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Agentoom</span>
                                                </div>
                                            </div>
                                            <div class="px-3 py-1.5 rounded-md text-[9px] font-bold flex items-center gap-1 cursor-pointer" :style="'background-color: ' + bgColor + '; border: 1px solid ' + primaryColor">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                                {{ __('accessibility-widget::widget.reset_all') }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Trigger Preview --}}
                                    <div
                                        @click="isOpen = !isOpen"
                                        class="w-14 h-14 rounded-full flex items-center justify-center shadow-lg cursor-pointer transition-transform hover:scale-110 active:scale-95"
                                        :style="'background-color: ' + primaryColor + '; color: #fff'"
                                    >
                                        @if($icon_trigger_file)
                                            <img src="{{ $icon_trigger_file->temporaryUrl() }}" class="w-7 h-7 object-contain" />
                                        @elseif($icon_trigger && !$remove_icon_trigger)
                                            <img src="{{ asset('storage/' . $icon_trigger) }}" class="w-7 h-7 object-contain" />
                                        @else
                                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="1" /><path d="m9 20 3-6 3 6" /><path d="m6 8 6 2 6-2" /><path d="M12 10v4" /></svg>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </flux:tab.panel>

                <flux:tab.panel name="settings">
                    <div class="flex gap-4 mb-6">
                        <flux:button size="sm" wire:click="selectAllFeatures">{{ __('accessibility-widget::widget.admin.select_all_features') }}</flux:button>
                        <flux:button size="sm" wire:click="unselectAllFeatures">{{ __('accessibility-widget::widget.admin.unselect_all_features') }}</flux:button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($this->getFeatures() as $feature)
                            <flux:checkbox wire:model="settings.{{ $feature['key'] }}" label="{{ __('accessibility-widget::widget.'.$feature['label']) }}" />
                        @endforeach

                        <div class="space-y-2">
                            <flux:label>Font Size (%)</flux:label>
                            <flux:input type="number" wire:model="settings.font_size" min="50" max="200" />
                        </div>

                        <div class="space-y-2">
                            <flux:label>Line Height</flux:label>
                            <flux:input type="number" wire:model="settings.line_height" step="0.1" min="1" max="3" />
                        </div>

                        <div class="space-y-2">
                            <flux:label>Letter Spacing (px)</flux:label>
                            <flux:input type="number" wire:model="settings.letter_spacing" step="0.5" min="0" max="10" />
                        </div>
                    </div>
                </flux:tab.panel>
            </flux:tab.group>
        </flux:card>

        <div class="flex justify-end gap-3">
            <flux:button :href="route('admin.accessibility-widgets.index')" wire:navigate>
                {{ __('accessibility-widget::widget.admin.cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary">
                {{ __('accessibility-widget::widget.admin.save') }}
            </flux:button>
        </div>
    </form>
</div>
