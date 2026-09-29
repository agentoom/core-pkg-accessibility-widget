<?php

namespace Agentoom\AccessibilityWidget\Livewire\Admin;

use Agentoom\AccessibilityWidget\Models\AccessibilityWidget;
use Agentoom\Core\Models\AppearancePreset;
use Agentoom\Core\Models\Project;
use Agentoom\Core\Services\ResourceLimitManager;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class AccessibilityWidgetCreateEdit extends Component
{
    use WithFileUploads;

    public ?AccessibilityWidget $widget = null;

    public string $presetName = '';

    public ?int $project_id = null;

    public string $name = '';

    public bool $is_active = true;

    public string $allowed_domains = '';

    public string $position = 'bottom-right';

    public ?string $icon_trigger = null;

    public array $settings = [];

    public $icon_trigger_file = null;

    public bool $remove_icon_trigger = false;

    public string $primary_color = '#2563eb';

    public string $bg_color = '#ffffff';

    public string $text_color = '#1f2937';

    public string $preset = '';

    public function mount(?AccessibilityWidget $widget = null): void
    {
        $this->widget = $widget;

        if ($widget && $widget->exists) {
            $this->project_id = $widget->project_id;
            $this->name = $widget->name;
            $this->is_active = $widget->is_active;
            $this->position = $widget->position ?? 'bottom-right';
            $this->icon_trigger = $widget->icon_trigger;
            $this->allowed_domains = implode(', ', $widget->allowed_domains ?? []);
            $this->settings = array_merge(config('accessibility-widget.defaults'), $widget->settings ?? []);

            $this->primary_color = $this->settings['primary_color'] ?? '#2563eb';
            $this->bg_color = $this->settings['bg_color'] ?? '#ffffff';
            $this->text_color = $this->settings['text_color'] ?? '#1f2937';
        } else {
            $this->settings = config('accessibility-widget.defaults');
        }
    }

    public function updatedPreset(string $value): void
    {
        if ($value) {
            $this->applyPreset($value);
        }
    }

    public function applyPreset(string $preset): void
    {
        $presets = [
            'default' => [
                'primary_color' => '#2563eb',
                'bg_color' => '#ffffff',
                'text_color' => '#1f2937',
            ],
            'dark' => [
                'primary_color' => '#3b82f6',
                'bg_color' => '#1f2937',
                'text_color' => '#f9fafb',
            ],
            'high_contrast' => [
                'primary_color' => '#ffff00',
                'bg_color' => '#000000',
                'text_color' => '#ffffff',
            ],
        ];

        $data = $presets[$preset] ?? null;

        if (! $data) {
            $dbPreset = AppearancePreset::where('type', 'accessibility-widget')->where('name', $preset)->first();
            $data = $dbPreset?->settings;
        }

        if ($data) {
            $this->primary_color = $data['primary_color'] ?? $this->primary_color;
            $this->bg_color = $data['bg_color'] ?? $this->bg_color;
            $this->text_color = $data['text_color'] ?? $this->text_color;
        }
    }

    public function savePreset(): void
    {
        $this->validate([
            'presetName' => 'required|string|max:255',
        ]);

        $settings = [
            'primary_color' => $this->primary_color,
            'bg_color' => $this->bg_color,
            'text_color' => $this->text_color,
        ];

        AppearancePreset::updateOrCreate(
            ['name' => $this->presetName, 'type' => 'accessibility-widget'],
            ['settings' => $settings]
        );

        $this->presetName = '';

        \Flux::toast(__('agentoom-core::admin.common.saved_success'));
    }

    #[Computed]
    public function userPresets()
    {
        return AppearancePreset::where('type', 'accessibility-widget')->get();
    }

    public function selectAllFeatures(): void
    {
        foreach ($this->getFeatures() as $feature) {
            $this->settings[$feature['key']] = true;
        }
    }

    public function unselectAllFeatures(): void
    {
        foreach ($this->getFeatures() as $feature) {
            $this->settings[$feature['key']] = false;
        }
    }

    public function getFeatures(): array
    {
        return [
            ['key' => 'contrast', 'label' => 'contrast', 'icon' => 'contrast'],
            ['key' => 'saturation', 'label' => 'saturation', 'icon' => 'saturation'],
            ['key' => 'monochrome', 'label' => 'monochrome', 'icon' => 'monochrome'],
            ['key' => 'dyslexic_font', 'label' => 'readable_fonts', 'icon' => 'font'],
            ['key' => 'high_cursor', 'label' => 'big_cursor', 'icon' => 'cursor'],
            ['key' => 'highlight_links', 'label' => 'highlight_links', 'icon' => 'link'],
            ['key' => 'highlight_titles', 'label' => 'highlight_titles', 'icon' => 'type'],
            ['key' => 'stop_animations', 'label' => 'stop_animations', 'icon' => 'stop'],
            ['key' => 'reading_guide', 'label' => 'reading_aid', 'icon' => 'aid'],
            ['key' => 'screen_reader', 'label' => 'screen_reader', 'icon' => 'screen-reader'],
            ['key' => 'text_reader', 'label' => 'text_reader', 'icon' => 'text-reader'],
            ['key' => 'keyboard_navigation', 'label' => 'keyboard_navigation', 'icon' => 'keyboard'],
            ['key' => 'voice_navigation', 'label' => 'voice_navigation', 'icon' => 'voice'],
            ['key' => 'text_position', 'label' => 'text_position', 'icon' => 'text-position'],
            ['key' => 'page_structure', 'label' => 'page_structure', 'icon' => 'structure'],
            ['key' => 'hide_images', 'label' => 'hide_images', 'icon' => 'image-off'],
            ['key' => 'dictionary', 'label' => 'dictionary', 'icon' => 'dictionary'],
            ['key' => 'tooltips', 'label' => 'tooltips', 'icon' => 'tooltip'],
        ];
    }

    public function submit()
    {
        if (! ($this->widget && $this->widget->exists) && app(ResourceLimitManager::class)->isLimitReached('accessibility_widgets')) {
            $this->addError('name', __('agentoom-core::admin.resource_limits.usage.limit_reached'));

            return;
        }

        $this->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'allowed_domains' => 'nullable|string',
            'position' => 'required|string|in:bottom-right,bottom-left,top-right,top-left',
            'icon_trigger_file' => 'nullable|image|max:1024',
        ]);

        $domains = array_filter(array_map('trim', explode(',', $this->allowed_domains)));

        $this->settings['primary_color'] = $this->primary_color;
        $this->settings['bg_color'] = $this->bg_color;
        $this->settings['text_color'] = $this->text_color;

        $data = [
            'project_id' => $this->project_id,
            'name' => $this->name,
            'is_active' => $this->is_active,
            'position' => $this->position,
            'allowed_domains' => $domains,
            'settings' => $this->settings,
        ];

        if ($this->icon_trigger_file) {
            $data['icon_trigger'] = $this->icon_trigger_file->store('widgets/icons', 'public');
        } elseif ($this->remove_icon_trigger) {
            $data['icon_trigger'] = null;
        }

        if ($this->widget && $this->widget->exists) {
            $this->widget->update($data);
            \Flux::toast(__('accessibility-widget::widget.admin.updated_success'));
        } else {
            $this->widget = AccessibilityWidget::create($data);
            \Flux::toast(__('accessibility-widget::widget.admin.created_success'));
        }

        return $this->redirect(route('admin.accessibility-widgets.index'), navigate: true);
    }

    public function render()
    {
        return view('accessibility-widget::livewire.admin.accessibility-widget-create-edit', [
            'projects' => Project::orderBy('name')->get(),
        ])->layout('agentoom-core::layouts.admin');
    }
}
