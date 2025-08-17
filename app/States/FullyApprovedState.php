<?php

namespace App\States;

class FullyApprovedState extends DocumentationState
{
    public function getStateName(): string
    {
        return 'fully_approved';
    }

    public function getColor(): string
    {
        return 'success';
    }

    public function getIcon(): string
    {
        return 'heroicon-o-check-badge';
    }

    public function getDescription(): string
    {
        return 'Completamente aprobado por todos los niveles requeridos';
    }
}
