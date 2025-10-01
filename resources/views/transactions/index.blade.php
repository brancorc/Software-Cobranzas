<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="page-title">
                Historial de Transacciones
            </h2>
            <a href="{{ route('transactions.create') }}" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Registrar Pago
            </a>
        </div>
    </x-slot>

    <div class="content-wrapper">
        <div class="card">
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Cliente</th>
                            <th>Monto</th>
                            <th>Fecha</th>
                            <th class="text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td>
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-lg bg-success-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="font-medium text-gray-900">{{ $transaction->folio_number }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center mr-2">
                                            <span class="text-primary-700 font-semibold text-xs">{{ substr($transaction->client->name, 0, 1) }}</span>
                                        </div>
                                        <span class="text-gray-900">{{ $transaction->client->name }}</span>
                                    </div>
                                </td>
                                <td class="font-bold text-success-600">${{ number_format($transaction->amount_paid, 2) }}</td>
                                <td class="text-gray-600">{{ $transaction->payment_date->format('d/m/Y') }}</td>
                                <td class="text-right">
                                    <a href="{{ route('transactions.pdf', $transaction) }}" 
                                       class="btn-primary btn-sm" 
                                       target="_blank">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Ver Recibo
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        <p class="empty-state-text mt-4">No hay transacciones registradas</p>
                                        <a href="{{ route('transactions.create') }}" class="btn-primary mt-4">
                                            Registrar primer pago
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if ($transactions->hasPages())
                <div class="pagination">
                    {{ $transactions->links() }}
                </div>
            @endif
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