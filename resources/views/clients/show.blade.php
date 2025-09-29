<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Estado de Cuenta: {{ $client->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Detalles del Cliente --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Información del Cliente</h3>
                    <p><strong>Email:</strong> {{ $client->email ?? 'N/A' }}</p>
                    <p><strong>Teléfono:</strong> {{ $client->phone ?? 'N/A' }}</p>
                    <p><strong>Dirección:</strong> {{ $client->address ?? 'N/A' }}</p>
                </div>
            </div>

            {{-- Lotes y Planes de Pago --}}
            @foreach ($client->lots as $lot)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium mb-2">Lote: {{ $lot->identifier }}</h3>
                        
                        @forelse ($lot->paymentPlans as $plan)
                            <div class="mt-4">
                                <h4 class="font-semibold">{{ $plan->service->name }} - Total: ${{ number_format($plan->total_amount, 2) }}</h4>
                                <div class="overflow-x-auto mt-2">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-2 py-2 text-left text-xs font-medium uppercase">#</th>
                                                <th class="px-2 py-2 text-left text-xs font-medium uppercase">Vencimiento</th>
                                                <th class="px-2 py-2 text-left text-xs font-medium uppercase">Total Cuota</th>
                                                <th class="px-2 py-2 text-left text-xs font-medium uppercase">Pagado</th>
                                                <th class="px-2 py-2 text-left text-xs font-medium uppercase">Adeudo</th>
                                                <th class="px-2 py-2 text-left text-xs font-medium uppercase">Estado</th>
                                                <th class="relative px-2 py-2"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y">
                                            @foreach ($plan->installments->sortBy('installment_number') as $installment)
                                                @php
                                                    $totalDue = $installment->base_amount + $installment->interest_amount;
                                                    $totalPaid = $installment->transactions->sum('pivot.amount_applied');
                                                    $remaining = $totalDue - $totalPaid;
                                                @endphp
                                                <tr>
                                                    <td class="px-2 py-2">{{ $installment->installment_number }}</td>
                                                    <td class="px-2 py-2">{{ $installment->due_date->format('d/m/Y') }}</td>
                                                    <td class="px-2 py-2">${{ number_format($totalDue, 2) }}</td>
                                                    <td class="px-2 py-2">${{ number_format($totalPaid, 2) }}</td>
                                                    <td class="px-2 py-2 font-bold {{ $remaining > 0.005 ? 'text-red-500' : 'text-green-500' }}">${{ number_format($remaining, 2) }}</td>
                                                    <td class="px-2 py-2">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                            @if($remaining <= 0.005) bg-green-100 text-green-800
                                                            @elseif($installment->status == 'vencida') bg-red-100 text-red-800
                                                            @else bg-yellow-100 text-yellow-800
                                                            @endif">
                                                            {{ $remaining <= 0.005 ? 'Pagada' : ucfirst($installment->status) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-2 py-2 text-right">
                                                        @if($remaining > 0.005)
                                                            <a href="{{ route('transactions.create', ['client_id' => $client->id, 'installment_id' => $installment->id]) }}" 
                                                            class="text-xs text-blue-600 hover:text-blue-900">
                                                                Pagar
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <p class="mt-4">Este lote no tiene planes de pago.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>