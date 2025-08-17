<?php

use App\Models\Documentation;
use App\States\ApprovedState;
use App\States\ArchivedState;
use App\States\DraftState;
use App\States\PendingApprovalState;
use App\States\PublishedState;
use App\States\RejectedState;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Actualizar documentaciones existentes para asignarles estado basado en su campo status
        $documentations = Documentation::whereNull('state')->orWhere('state', '')->get();

        foreach ($documentations as $doc) {
            $stateClass = match ($doc->status) {
                'draft' => DraftState::class,
                'pending_approval' => PendingApprovalState::class,
                'approved' => ApprovedState::class,
                'rejected' => RejectedState::class,
                'published' => PublishedState::class,
                'archived' => ArchivedState::class,
                default => DraftState::class
            };

            $doc->state = $stateClass;
            $doc->save();
        }
    }

    public function down(): void
    {
        // No es necesario revertir
    }
};
