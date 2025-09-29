<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Lote: ') . $lot->identifier }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Formulario de Edición del Lote --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Información del Lote</h3>
                    @include('lots._form', ['action' => route('lots.update', $lot), 'method' => 'PUT', 'buttonText' => 'Actualizar Lote'])
                </div>
            </div>

            {{-- Sección de Planes de Pago --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-full">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Planes de Pago</h3>
                    
                    {{-- Formulario para crear nuevo plan --}}
                    <div class="mb-6 border-b pb-6">
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-2">Crear Nuevo Plan de Pago</h4>
                        <form method="POST" action="{{ route('lots.payment-plans.store', $lot) }}">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <x-input-label for="service_id" value="Servicio" />
                                    <select name="service_id" id="service_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        @foreach(\App\Models\Service::all() as $service)
                                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('service_id')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="total_amount" value="Monto Total" />
                                    <x-text-input id="total_amount" name="total_amount" type="number" step="0.01" class="mt-1 block w-full" :value="old('total_amount')" />
                                    <x-input-error :messages="$errors->get('total_amount')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="number_of_installments" value="No. de Cuotas" />
                                    <x-text-input id="number_of_installments" name="number_of_installments" type="number" class="mt-1 block w-full" :value="old('number_of_installments')" />
                                    <x-input-error :messages="$errors->get('number_of_installments')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="start_date" value="Fecha de Inicio" />
                                    <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date')" />
                                    <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                                </div>
                            </div>
                            <div class="flex justify-end mt-4">
                                <x-primary-button>Crear Plan</x-primary-button>
                            </div>
                        </form>
                    </div>

                    {{-- Lista de planes existentes --}}
                    @forelse ($lot->paymentPlans()->with('service', 'installments.transactions')->get() as $plan)
                        <div class="mb-4">
                            <h4 class="font-semibold">{{ $plan->service->name }} - ${{ number_format($plan->total_amount, 2) }} ({{ $plan->number_of_installments }} cuotas)</h4>
                            <div class="overflow-x-auto mt-2">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">#</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Vencimiento</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Monto Base</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Intereses</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estado</th>
                                            <th class="relative px-2 py-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($plan->installments as $installment)
                                            <tr>
                                                @php
                                                    $totalPaid = $installment->transactions->sum('pivot.amount_applied');
                                                    $totalDue = $installment->base_amount + $installment->interest_amount;
                                                    $remaining = $totalDue - $totalPaid;
                                                @endphp
                                                <td class="px-2 py-2">{{ $installment->installment_number }}</td>
                                                <td class="px-2 py-2">{{ $installment->due_date->format('d/m/Y') }}</td>
                                                <td class="px-2 py-2">${{ number_format($installment->base_amount, 2) }}</td>
                                                <td class="px-2 py-2">${{ number_format($installment->interest_amount, 2) }}</td>
                                                <td class="px-2 py-2">
                                                    ${{ number_format($totalDue, 2) }} 
                                                    @if($remaining > 0.005 && $remaining < $totalDue)
                                                        <span class="text-sm text-red-500">(Adeudo: ${{ number_format($remaining, 2) }})</span>
                                                    @endif
                                                </td>
                                                <td class="px-2 py-2">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @switch($installment->status)
                                                            @case('pagada') bg-green-100 text-green-800 @break
                                                            @case('vencida') bg-red-100 text-red-800 @break
                                                            @default bg-yellow-100 text-yellow-800
                                                        @endswitch">
                                                        {{ ucfirst($installment->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-2 py-2 text-right">
                                                    @if ($installment->interest_amount > 0)
                                                        <form action="{{ route('installments.condone', $installment) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas condonar los intereses de esta cuota?');">
                                                            @csrf
                                                            <button type="submit" class="text-xs text-blue-600 hover:text-blue-900">Condonar</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <p>No hay planes de pago para este lote.</p>
                    @endforelse
                </div>
            </div>

            {{-- Sección de Transferencia de Lote --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Transferir Lote</h3>
                    <form method="POST" action="{{ route('lots.transfer', $lot) }}">
                        @csrf
                        <div>
                            <x-input-label for="new_client_id" value="Nuevo Propietario" />
                            <select name="new_client_id" id="new_client_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                                @foreach($clients->where('id', '!=', $lot->client_id) as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('new_client_id')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="transfer_date" value="Fecha de Transferencia" />
                            <x-text-input id="transfer_date" name="transfer_date" type="date" class="mt-1 block w-full" :value="date('Y-m-d')" />
                            <x-input-error :messages="$errors->get('transfer_date')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="notes" value="Notas (Opcional)" />
                            <textarea id="notes" name="notes" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm"></textarea>
                        </div>
                        <div class="flex justify-end mt-4">
                            <x-primary-button>Transferir</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Sección Historial de Propietarios --}}
    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg mt-6">
        <div class="max-w-full">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Historial de Propietarios</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Transferencia</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Propietario Anterior</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nuevo Propietario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notas</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-700">
                        @forelse ($lot->ownershipHistory as $history)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($history->transfer_date)->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $history->previousClient->name ?? 'Sin propietario anterior' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $history->newClient->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4">{{ $history->notes }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center">No hay historial de transferencias para este lote.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>


</x-app-layout>