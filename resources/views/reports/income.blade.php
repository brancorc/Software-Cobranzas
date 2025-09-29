<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Reporte de Ingresos
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- Formulario de Fechas --}}
                    <form method="GET" action="{{ route('reports.income') }}">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                            <div>
                                <x-input-label for="start_date" value="Fecha de Inicio" />
                                <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="$startDate" />
                            </div>
                            <div>
                                <x-input-label for="end_date" value="Fecha de Fin" />
                                <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" :value="$endDate" />
                            </div>
                            <div>
                                <x-primary-button>Filtrar</x-primary-button>
                            </div>
                        </div>
                    </form>

                    {{-- Resultados --}}
                    <div class="mt-6 border-t pt-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium">Resultados para el período</h3>
                            <div class="text-xl font-bold">
                                Total Ingresado: <span class="text-green-600">${{ number_format($totalIncome, 2) }}</span>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Folio</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Fecha de Pago</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Cliente</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Monto</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @forelse ($transactions as $transaction)
                                        <tr>
                                            <td class="px-6 py-4"><a href="{{ route('transactions.pdf', $transaction) }}" target="_blank" class="text-indigo-600 hover:underline">{{ $transaction->folio_number }}</a></td>
                                            <td class="px-6 py-4">{{ $transaction->payment_date->format('d/m/Y') }}</td>
                                            <td class="px-6 py-4">{{ $transaction->client->name }}</td>
                                            <td class="px-6 py-4">${{ number_format($transaction->amount_paid, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center">No se encontraron transacciones en este período.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>