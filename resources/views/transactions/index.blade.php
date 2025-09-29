<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Transacciones') }}
            </h2>
            <a href="{{ route('transactions.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Registrar Pago') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Folio</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Monto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase">Fecha</th>
                                <th class="relative px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($transactions as $transaction)
                                <tr>
                                    <td class="px-6 py-4">{{ $transaction->folio_number }}</td>
                                    <td class="px-6 py-4">{{ $transaction->client->name }}</td>
                                    <td class="px-6 py-4">${{ number_format($transaction->amount_paid, 2) }}</td>
                                    <td class="px-6 py-4">{{ $transaction->payment_date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('transactions.pdf', $transaction) }}" class="text-indigo-600 hover:text-indigo-900" target="_blank">Ver Folio</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-4 text-center">No hay transacciones.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if (session('new_transaction_id'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.open("{{ route('transactions.pdf', session('new_transaction_id')) }}", '_blank');
        });
    </script>
    @endif

</x-app-layout>