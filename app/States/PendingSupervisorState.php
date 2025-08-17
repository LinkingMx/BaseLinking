<?php

namespace App\States;

class PendingSupervisorState extends DocumentationState
{
    public function getStateName(): string
    {
        return 'pending_supervisor';
    }

    public function getColor(): string
    {
        return 'warning';
    }

    public function getIcon(): string
    {
        return 'heroicon-o-clock';
    }

    public function getDescription(): string
    {
        return 'Pendiente de aprobación por supervisor';
    }
}
