<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Installment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $interestAppliedCount = 0;

        try {
            DB::beginTransaction();

            // Buscar cuotas 'pendientes' cuya fecha de vencimiento ya pasó
            $installmentsToUpdate = Installment::where('status', 'pendiente')
                ->where('due_date', '<', $today)
                ->with('transactions')
                ->lockForUpdate() // Bloqueo pesimista para evitar race conditions
                ->get();

            foreach ($installmentsToUpdate as $installment) {
                // 1. Actualizar el estado a 'vencida'
                $installment->status = 'vencida';
                
                // 2. Calcular interés SOLO si no se ha aplicado interés previamente
                // Verificamos si interest_amount es 0 para evitar acumulación
                if ($installment->interest_amount == 0) {
                    $totalPaid = $installment->transactions->sum('pivot.amount_applied');
                    $remainingBalance = $installment->base_amount - $totalPaid;

                    if ($remainingBalance > 0) {
                        $interest = $remainingBalance * self::MONTHLY_INTEREST_RATE;
                        $installment->interest_amount = round($interest, 2);
                        $interestAppliedCount++;
                        
                        // Log para auditoría
                        Log::info('Interés aplicado', [
                            'installment_id' => $installment->id,
                            'remaining_balance' => $remainingBalance,
                            'interest_applied' => $installment->interest_amount,
                            'date' => $today->toDateString()
                        ]);
                    }
                }
                
                $installment->save();
                $updatedCount++;
            }

            DB::commit();

            $this->info("Proceso completado exitosamente.");
            $this->info("Cuotas actualizadas a 'vencida': {$updatedCount}");
            $this->info("Cuotas con interés aplicado: {$interestAppliedCount}");
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->error('Error durante la actualización: ' . $e->getMessage());
            Log::error('Error en UpdateInstallmentStatus', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}