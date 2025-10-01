<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            Reporte de Ingresos
        </h2>
    </x-slot>

    <div class="content-wrapper">
        <!-- Filtros -->
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="section-title mb-0">Filtrar por Período</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('reports.income') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div class="form-group">
                            <label for="start_date" class="form-label">Fecha de Inicio</label>
                            <input 
                                id="start_date" 
                                name="start_date" 
                                type="date" 
                                class="form-input" 
                                value="{{ $startDate }}"
                            />
                        </div>
                        <div class="form-group">
                            <label for="end_date" class="form-label">Fecha de Fin</label>
                            <input 
                                id="end_date" 
                                name="end_date" 
                                type="date" 
                                class="form-input" 
                                value="{{ $endDate }}"
                            />
                        </div>
                        <div>
                            <button type="submit" class="btn-primary w-full">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                </svg>
                                Aplicar Filtros
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resumen de Ingresos -->
        <div class="card mb-6">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Total de Ingresos</p>
                        <p class="text-4xl font-bold text-success-600">${{ number_format($totalIncome, 2) }}</p>
                    </div>
                    <div class="h-20 w-20 rounded-full bg-success-100 flex items-center justify-center">
                        <svg class="w-10 h-10 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Período:</span>
                        <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm mt-2">
                        <span class="text-gray-500">Total de Transacciones:</span>
                        <span class="font-medium text-gray-900">{{ $transactions->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalle de Transacciones -->
        <div class="card">
            <div class="card-header">
                <h3 class="section-title mb-0">Detalle de Transacciones</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Fecha de Pago</th>
                            <th>Cliente</th>
                            <th class="text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td>
                                    <a href="{{ route('transactions.pdf', $transaction) }}" 
                                       target="_blank" 
                                       class="link inline-flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        {{ $transaction->folio_number }}
                                    </a>
                                </td>
                                <td class="text-gray-600">{{ $transaction->payment_date->format('d/m/Y') }}</td>
                                <td>
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center mr-2">
                                            <span class="text-primary-700 font-semibold text-xs">{{ substr($transaction->client->name, 0, 1) }}</span>
                                        </div>
                                        <span class="text-gray-900">{{ $transaction->client->name }}</span>
                                    </div>
                                </td>
                                <td class="text-right font-bold text-success-600">${{ number_format($transaction->amount_paid, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="empty-state-text mt-4">No se encontraron transacciones en este período</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>