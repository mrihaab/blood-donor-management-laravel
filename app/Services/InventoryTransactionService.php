<?php

namespace App\Services;

use App\Models\BloodUnit;
use App\Models\InventoryTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventoryTransactionService
{
    public function logTransaction(
        BloodUnit $bloodUnit,
        string $transactionType,
        int $previousQuantity,
        int $quantityChanged,
        int $resultingQuantity,
        string $reason,
        ?User $actor = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): InventoryTransaction {
        return DB::transaction(function () use (
            $bloodUnit,
            $transactionType,
            $previousQuantity,
            $quantityChanged,
            $resultingQuantity,
            $reason,
            $actor,
            $referenceType,
            $referenceId
        ) {
            $transaction = InventoryTransaction::create([
                'blood_unit_id' => $bloodUnit->id,
                'blood_group_id' => $bloodUnit->blood_group_id,
                'component_id' => $bloodUnit->component_id,
                'transaction_type' => $transactionType,
                'previous_quantity' => $previousQuantity,
                'quantity_changed' => $quantityChanged,
                'resulting_quantity' => $resultingQuantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'user_id' => $actor ? $actor->id : auth()->id(),
                'reason' => $reason,
            ]);

            activity()
                ->causedBy($actor ?? auth()->user())
                ->performedOn($bloodUnit)
                ->log("Stock transaction '{$transactionType}' logged for unit {$bloodUnit->unit_number}. Reason: {$reason}");

            return $transaction;
        });
    }
}
