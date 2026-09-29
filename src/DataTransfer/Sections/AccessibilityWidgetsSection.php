<?php

namespace Agentoom\AccessibilityWidget\DataTransfer\Sections;

use Agentoom\AccessibilityWidget\Models\AccessibilityWidget;
use Agentoom\Core\DataTransfer\AbstractImportExportSection;
use Agentoom\Core\DataTransfer\ImportExportRegistry;
use Agentoom\Core\Models\Project;
use Illuminate\Support\Collection;

class AccessibilityWidgetsSection extends AbstractImportExportSection
{
    public function key(): string
    {
        return 'accessibility_widgets';
    }

    public function group(): string
    {
        return ImportExportRegistry::GROUP_COMMUNICATION;
    }

    public function permission(): ?string
    {
        return 'accessibility-widgets.view';
    }

    public function importLevel(): int
    {
        return 5;
    }

    public function options(): Collection
    {
        return AccessibilityWidget::all();
    }

    public function projectIds(array $projectIds): ?array
    {
        return AccessibilityWidget::whereIn('project_id', $projectIds)->pluck('id')->toArray();
    }

    public function export(array $ids): array
    {
        $query = AccessibilityWidget::query();
        if (($ids = $this->selectedIds($ids)) !== null) {
            $query->whereIn('id', $ids);
        }

        return $query->get()->map(function ($widget) {
            return [
                'name' => $widget->name,
                'slug' => $widget->slug,
                'is_active' => $widget->is_active,
                'position' => $widget->position,
                'logo' => $widget->logo,
                'icon_trigger' => $widget->icon_trigger,
                'settings' => $widget->settings,
                'allowed_domains' => $widget->allowed_domains,
                'project_name' => $widget->project?->name,
            ];
        })->toArray();
    }

    public function import(array $payload, array $options = []): void
    {
        foreach ($payload as $widgetData) {
            $project = null;
            if (isset($widgetData['project_name'])) {
                $project = Project::where('name', $widgetData['project_name'])->first();
            }

            AccessibilityWidget::updateOrCreate(
                ['slug' => $widgetData['slug']],
                [
                    'name' => $widgetData['name'],
                    'is_active' => $widgetData['is_active'] ?? true,
                    'position' => $widgetData['position'] ?? 'bottom-right',
                    'logo' => $widgetData['logo'] ?? null,
                    'icon_trigger' => $widgetData['icon_trigger'] ?? null,
                    'settings' => $widgetData['settings'] ?? [],
                    'allowed_domains' => $widgetData['allowed_domains'] ?? [],
                    'project_id' => $project?->id,
                ]
            );
        }
    }
}
