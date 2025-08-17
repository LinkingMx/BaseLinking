<?php

namespace App\States;

class ApprovedSupervisorPendingTravelState extends DocumentationState
{
    public function getStateName(): string
    {
        return 'approved_supervisor_pending_travel';
    }

    public function getColor(): string
    {
        return 'info';
    }

    public function getIcon(): string
    {
        return 'heroicon-o-check-circle';
    }

    public function getDescription(): string
    {
        return 'Aprobado por supervisor, pendiente de aprobación por Travel';
    }
}
