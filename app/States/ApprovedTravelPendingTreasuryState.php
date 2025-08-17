<?php

namespace App\States;

class ApprovedTravelPendingTreasuryState extends DocumentationState
{
    public function getStateName(): string
    {
        return 'approved_travel_pending_treasury';
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
        return 'Aprobado por Travel, pendiente de aprobación por Treasury';
    }
}
