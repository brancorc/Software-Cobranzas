<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="page-title">Dashboard</h2>
                <p class="mt-1 text-sm text-gray-600">Bienvenido, {{ Auth::user()->name }}</p>
            </div>
            <div class="text-sm text-gray-600">
                {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </x-slot>

    @php
        use App\Models\Client;
        use App\Models\Lot;
        use App\Models\Transaction;
        use App\Models\Installment;
        use Illuminate\Support\Facades\Cache;
        use Illuminate\Support\Facades\DB;

        // Cachear estadísticas por 5 minutos
        $stats = Cache::remember('dashboard_stats', 300, function () {
            return [
                'total_clients'   => Client::count(),
                'total_lots'      => Lot::count(),
                'available_lots'  => Lot::where('status', 'disponible')->count(),
                'sold_lots'       => Lot::where('status', 'vendido')->count(),
                'monthly_income'  => Transaction::whereMonth('payment_date', now()->month)
                                                ->whereYear('payment_date', now()->year)
                                                ->sum('amount_paid'),
            ];
        });

        // Cuotas vencidas optimizadas con joins
        $overdueInstallments = Installment::select([
                'installments.id',
                'installments.due_date',
                'installments.base_amount',
                'installments.interest_amount',
                'clients.name as client_name',
                'lots.identifier as lot_identifier',
                'services.name as service_name'
            ])
            ->join('payment_plans', 'installments.payment_plan_id', '=', 'payment_plans.id')
            ->join('lots', 'payment_plans.lot_id', '=', 'lots.id')
            ->join('clients', 'lots.client_id', '=', 'clients.id')
            ->join('services', 'payment_plans.service_id', '=', 'services.id')
            ->where('installments.status', 'vencida')
            ->orderBy('installments.due_date', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($installment) {
                $totalOwed = $installment->base_amount + $installment->interest_amount;
                $totalPaid = DB::table('installment_transaction')
                    ->where('installment_id', $installment->id)
                    ->sum('amount_applied');
                $installment->remaining = max(0, $totalOwed - $totalPaid);
                return $installment;
            })
            ->filter(fn($inst) => $inst->remaining > 0.01);
    @endphp

    <div class="content-wrapper">
        <!-- Estadísticas Rápidas -->
        <div class="grid-responsive mb-6">
            <!-- Total Clientes -->
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="stat-label">Total de Clientes</p>
                        <p class="stat-value">{{ $stats['total_clients'] }}</p>
                    </div>
                    <div class="h-12 w-12 rounded-full bg-primary-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('clients.index') }}" class="link text-sm">Ver todos los clientes →</a>
                </div>
            </div>

            <!-- Total Lotes -->
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="stat-label">Total de Lotes</p>
                        <p class="stat-value">{{ $stats['total_lots'] }}</p>
                    </div>
                    <div class="h-12 w-12 rounded-full bg-success-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between text-xs text-gray-600">
                        <span>Disponibles: <strong>{{ $stats['available_lots'] }}</strong></span>
                        <span>Vendidos: <strong>{{ $stats['sold_lots'] }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Ingresos del Mes -->
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="stat-label">Ingresos del Mes</p>
                        <p class="stat-value">${{ number_format($stats['monthly_income'], 2) }}</p>
                    </div>
                    <div class="h-12 w-12 rounded-full bg-warning-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('reports.income') }}" class="link text-sm">Ver reporte completo →</a>
                </div>
            </div>
        </div>

        <!-- Accesos Rápidos -->
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="section-title mb-0">Accesos Rápidos</h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('clients.create') }}" class="group p-4 bg-gray-50 rounded-lg hover:bg-primary-50 hover:border-primary-200 border border-transparent transition-all duration-200">
                        <div class="flex flex-col items-center text-center">
                            <div class="h-12 w-12 rounded-full bg-primary-100 group-hover:bg-primary-200 flex items-center justify-center mb-3 transition-colors">
                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-primary-700">Nuevo Cliente</span>
                        </div>
                    </a>

                    <a href="{{ route('lots.create') }}" class="group p-4 bg-gray-50 rounded-lg hover:bg-success-50 hover:border-success-200 border border-transparent transition-all duration-200">
                        <div class="flex flex-col items-center text-center">
                            <div class="h-12 w-12 rounded-full bg-success-100 group-hover:bg-success-200 flex items-center justify-center mb-3 transition-colors">
                                <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-success-700">Nuevo Lote</span>
                        </div>
                    </a>

                    <a href="{{ route('transactions.create') }}" class="group p-4 bg-gray-50 rounded-lg hover:bg-warning-50 hover:border-warning-200 border border-transparent transition-all duration-200">
                        <div class="flex flex-col items-center text-center">
                            <div class="h-12 w-12 rounded-full bg-warning-100 group-hover:bg-warning-200 flex items-center justify-center mb-3 transition-colors">
                                <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-warning-700">Registrar Pago</span>
                        </div>
                    </a>

                    <a href="{{ route('reports.income') }}" class="group p-4 bg-gray-50 rounded-lg hover:bg-danger-50 hover:border-danger-200 border border-transparent transition-all duration-200">
                        <div class="flex flex-col items-center text-center">
                            <div class="h-12 w-12 rounded-full bg-danger-100 group-hover:bg-danger-200 flex items-center justify-center mb-3 transition-colors">
                                <svg class="w-6 h-6 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-danger-700">Ver Reportes</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Cuotas Vencidas -->
        @if($overdueInstallments->count() > 0)
        <div class="card">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <h3 class="section-title mb-0">Cuotas Vencidas</h3>
                    <span class="badge-danger">{{ $overdueInstallments->count() }} vencidas</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Lote</th>
                            <th>Servicio</th>
                            <th>Vencimiento</th>
                            <th>Monto</th>
                            <th class="text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($overdueInstallments as $installment)
                            <tr>
                                <td>
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center mr-2">
                                            <span class="text-primary-700 font-semibold text-xs">{{ substr($installment->client_name, 0, 1) }}</span>
                                        </div>
                                        <span class="text-gray-900">{{ $installment->client_name }}</span>
                                    </div>
                                </td>
                                <td class="text-gray-600">{{ $installment->lot_identifier }}</td>
                                <td class="text-gray-600">{{ $installment->service_name }}</td>
                                <td class="text-danger-600 font-medium">{{ \Carbon\Carbon::parse($installment->due_date)->format('d/m/Y') }}</td>
                                <td class="font-bold text-danger-600">${{ number_format($installment->remaining, 2) }}</td>
                                <td class="text-right">
                                    <a href="{{ route('transactions.create', ['installment_id' => $installment->id]) }}" class="btn-primary btn-sm">
                                        Pagar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
