<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="page-title">Estado de Cuenta</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $client->name }}</p>
            </div>
            <a href="{{ route('clients.index') }}" class="btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="content-wrapper">
        <!-- Información del Cliente -->
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="section-title mb-0">Información del Cliente</h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-16 w-16 rounded-full bg-primary-100 flex items-center justify-center">
                                <span class="text-primary-700 font-bold text-2xl">{{ substr($client->name, 0, 1) }}</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Nombre</p>
                            <p class="text-base font-semibold text-gray-900">{{ $client->name }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Email</p>
                        <p class="text-base text-gray-900">{{ $client->email ?? 'No registrado' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Teléfono</p>
                        <p class="text-base text-gray-900">{{ $client->phone ?? 'No registrado' }}</p>
                    </div>
                </div>
                @if($client->address)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-sm font-medium text-gray-500">Dirección</p>
                        <p class="text-base text-gray-900 mt-1">{{ $client->address }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Lotes y Planes de Pago -->
        @forelse ($client->lots as $lot)
            <div class="card mb-6">
                <div class="card-header">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="section-title mb-0">Lote: {{ $lot->identifier }}</h3>
                            <p class="text-sm text-gray-600 mt-1">Precio Total: ${{ number_format($lot->total_price, 2) }}</p>
                        </div>
                        <span class="badge {{ $lot->status == 'liquidado' ? 'badge-success' : ($lot->status == 'vendido' ? 'badge-warning' : 'badge-gray') }}">
                            {{ ucfirst($lot->status) }}
                        </span>
                    </div>
                </div>
                
                @forelse ($lot->paymentPlans as $plan)
                    <div class="card-body border-b border-gray-200 last:border-b-0">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-semibold text-gray-900">{{ $plan->service->name }}</h4>
                            <span class="text-sm font-medium text-gray-600">Total: ${{ number_format($plan->total_amount, 2) }}</span>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th class="w-16">#</th>
                                        <th>Vencimiento</th>
                                        <th>Total Cuota</th>
                                        <th>Pagado</th>
                                        <th>Adeudo</th>
                                        <th>Estado</th>
                                        <th class="text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($plan->installments->sortBy('installment_number') as $installment)
                                        @php
                                            $totalDue = $installment->base_amount + $installment->interest_amount;
                                            $totalPaid = $installment->transactions->sum('pivot.amount_applied');
                                            $remaining = $totalDue - $totalPaid;
                                            $isPaid = $remaining <= 0.005;
                                        @endphp
                                        <tr class="{{ $isPaid ? 'bg-success-50' : ($installment->status == 'vencida' ? 'bg-danger-50' : '') }}">
                                            <td class="font-medium">{{ $installment->installment_number }}</td>
                                            <td>{{ $installment->due_date->format('d/m/Y') }}</td>
                                            <td class="font-semibold">${{ number_format($totalDue, 2) }}</td>
                                            <td class="text-success-700">${{ number_format($totalPaid, 2) }}</td>
                                            <td class="font-bold {{ $remaining > 0.005 ? 'text-danger-600' : 'text-success-600' }}">
                                                ${{ number_format($remaining, 2) }}
                                            </td>
                                            <td>
                                                @if($isPaid)
                                                    <span class="badge-success">Pagada</span>
                                                @elseif($installment->status == 'vencida')
                                                    <span class="badge-danger">Vencida</span>
                                                @else
                                                    <span class="badge-warning">Pendiente</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if(!$isPaid)
                                                    <a href="{{ route('transactions.create', ['client_id' => $client->id, 'installment_id' => $installment->id]) }}" 
                                                       class="btn-primary btn-sm">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                        </svg>
                                                        Pagar
                                                    </a>
                                                @else
                                                    <span class="badge-success">
                                                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        Liquidada
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="card-body">
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Este lote no tiene planes de pago</p>
                        </div>
                    </div>
                @endforelse
            </div>
        @empty
            <div class="card">
                <div class="card-body">
                    <div class="empty-state">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <p class="empty-state-text mt-4">Este cliente no tiene lotes asignados</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</x-app-layout>