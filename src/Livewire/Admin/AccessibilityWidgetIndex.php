<?php

namespace Agentoom\AccessibilityWidget\Livewire\Admin;

use Agentoom\AccessibilityWidget\Models\AccessibilityWidget;
use Livewire\Component;
use Livewire\WithPagination;

class AccessibilityWidgetIndex extends Component
{
    use WithPagination;

    public bool $show_delete_modal = false;

    public ?int $widget_id_to_delete = null;

    public function showDeleteModal(int $widgetId): void
    {
        $this->widget_id_to_delete = $widgetId;
        $this->show_delete_modal = true;
    }

    public function delete(): void
    {
        if ($this->widget_id_to_delete) {
            AccessibilityWidget::find($this->widget_id_to_delete)?->delete();
            \Flux::toast(__('accessibility-widget::widget.admin.deleted_success'));
        }

        $this->show_delete_modal = false;
        $this->widget_id_to_delete = null;
    }

    public function render()
    {
        return view('accessibility-widget::livewire.admin.accessibility-widget-index', [
            'widgets' => AccessibilityWidget::orderBy('id', 'desc')->paginate(15),
        ])->layout('agentoom-core::layouts.admin');
    }
}
