<?php

namespace App\Console\Commands;

use App\Services\HabitacionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LiberarHabitacionesVencidas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'habitaciones:liberar-vencidas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Libera automáticamente habitaciones cuando la fecha de salida de la reserva ha pasado';

    /**
     * Execute the console command.
     */
    public function handle(HabitacionService $habitacionService): int
    {
        try {
            $contador = $habitacionService->liberarHabitacionesVencidas();
            
            $this->info("✓ Se liberaron {$contador} habitaciones automáticamente.");
            
            if ($contador > 0) {
                Log::info("Habitaciones liberadas automáticamente: {$contador}");
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("✗ Error al liberar habitaciones: " . $e->getMessage());
            Log::error("Error al liberar habitaciones: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
