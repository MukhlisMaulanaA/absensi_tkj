<?php

namespace App\Filament\Traits;

use Filament\Actions\Action;

trait UsesSweetAlert
{
    /**
     * Create an action with SweetAlert confirmation
     */
    protected function createSweetAlertAction(
        string $name,
        string $label,
        callable $callback,
        string $confirmText = 'Yes, proceed!',
        string $cancelText = 'Cancel',
        string $icon = 'heroicon-m-exclamation-triangle',
        string $color = 'info'
    ): Action {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->action(function () use ($callback) {
                $callback();
            })
            ->modalHeading($label)
            ->modalDescription('Are you sure?')
            ->requiresConfirmation();
    }

    /**
     * Create an action with custom SweetAlert notification via JavaScript
     */
    protected function createSweetAlertNotification(
        string $name,
        string $label,
        callable $callback,
        string $icon = 'heroicon-m-information-circle',
        string $color = 'info'
    ): Action {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->action(function () use ($callback) {
                $callback();
            });
    }
}
