<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Installment;
use Illuminate\Support\Carbon;

class UpdateInstallmentStatus extends Command
{
    /**
     * El nombre y la firma del comando de la consola.
     *
     * @var string
     */
    protected $signature = 'installments:update-status';

    /**
     * La descripción del comando de la consola.
     *
     * @var string
     */
    protected $description = 'Actualiza el estado de las cuotas a "vencida" y calcula intereses si corresponde.';

    /**
     * La tasa de interés mensual a aplicar (ej. 5% = 0.05).
     *
     * @var float
     */
    private const MONTHLY_INTEREST_RATE = 0.05;

    /**
     * Ejecuta el comando de la consola.
     */
    public function handle()
    {
        $this->info('Iniciando actualización de estado de cuotas...');

        $today = Carbon::today();
        $updatedCount = 0;

        // Buscar cuotas 'pendientes' cuya fecha de vencimiento ya pasó.
        $installmentsToUpdate = Installment::where('status', 'pendiente')
            ->where('due_date', '<', $today)
            ->with('transactions') // Precargar transacciones para calcular adeudo
            ->get();

        foreach ($installmentsToUpdate as $installment) {
            // 1. Actualizar el estado a 'vencida'
            $installment->status = 'vencida';
            
            // 2. Calcular interés sobre el saldo restante
            $totalOwed = $installment->base_amount; // Interés se calcula sobre el capital, no sobre intereses previos
            $totalPaid = $installment->transactions->sum('pivot.amount_applied');
            $remainingBalance = $totalOwed - $totalPaid;

            if ($remainingBalance > 0) {
                $interest = $remainingBalance * self::MONTHLY_INTEREST_RATE;
                // Usar += para permitir la acumulación si el comando se corre varias veces
                $installment->interest_amount += round($interest, 2);
            }
            
            $installment->save();
            $updatedCount++;
        }

        $this->info("Proceso completado. Se actualizaron {$updatedCount} cuotas.");
        return 0;
    }
}