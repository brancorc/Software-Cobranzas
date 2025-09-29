<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Registrar Nuevo Pago') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100" 
                     x-data="{ 
                        clientId: '{{ $selectedClientId ?? '' }}', 
                        amountPaid: 0,
                        installments: [],
                        selectedInstallments: [],
                        loading: false,
                        init() {
                            if (this.clientId) {
                                this.fetchInstallments();
                            }
                        },
                        fetchInstallments() {
                            if (!this.clientId) {
                                this.installments = [];
                                this.updateTotal();
                                return;
                            }
                            this.loading = true;
                            this.selectedInstallments = [];
                            fetch(`/clients/${this.clientId}/pending-installments`)
                                .then(response => response.json())
                                .then(data => {
                                    this.installments = data;
                                    this.loading = false;
                                    
                                    const preselectedId = '{{ $selectedInstallmentId ?? '' }}';
                                    if (preselectedId && this.installments.some(inst => inst.id == preselectedId)) {
                                        this.selectedInstallments.push(preselectedId);
                                    }
                                    
                                    this.updateTotal();
                                });
                        },
                        updateTotal() {
                            this.amountPaid = this.installments
                                .filter(inst => this.selectedInstallments.includes(inst.id.toString()))
                                .reduce((sum, inst) => sum + parseFloat(inst.remaining_balance), 0)
                                .toFixed(2);
                        }
                     }" x-init="init()">
                    
                    <form method="POST" action="{{ route('transactions.store') }}">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-1">
                                <x-input-label for="client_id" value="Cliente" />
                                <select x-model="clientId" @change="fetchInstallments()" id="client_id" name="client_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">Seleccione un cliente</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" @selected($selectedClientId == $client->id)>{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-1">
                                <x-input-label for="amount_paid" value="Monto a Pagar" />
                                <x-text-input x-model="amountPaid" id="amount_paid" name="amount_paid" type="number" step="0.01" class="mt-1 block w-full" required />
                            </div>
                            <div class="md:col-span-1">
                                <x-input-label for="payment_date" value="Fecha de Pago" />
                                <x-text-input id="payment_date" name="payment_date" type="date" class="mt-1 block w-full" value="{{ date('Y-m-d') }}" required />
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <x-input-label for="notes" value="Notas (Opcional)" />
                            <textarea id="notes" name="notes" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                        </div>
                        
                        <div class="mt-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Cuotas Pendientes</h3>
                            <div x-show="loading" class="text-center p-4">Cargando...</div>
                            <div x-show="!loading && installments.length > 0" class.mt-2 border rounded-md max-h-64 overflow-y-auto">
                                <table class="min-w-full divide-y dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Sel.</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Servicio</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"># Cuota</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Vencimiento</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Adeudo</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y dark:divide-gray-700">
                                        <template x-for="inst in installments" :key="inst.id">
                                            <tr>
                                                <td class="px-4 py-2"><input type="checkbox" name="installments[]" :value="inst.id" x-model="selectedInstallments" @change="updateTotal()" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"></td>
                                                <td class="px-4 py-2" x-text="inst.payment_plan.service.name"></td>
                                                <td class="px-4 py-2" x-text="inst.installment_number"></td>
                                                <td class="px-4 py-2" x-text="inst.formatted_due_date"></td>
                                                <td class="px-4 py-2" x-text="`$${parseFloat(inst.remaining_balance).toFixed(2)}`"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <div x-show="!loading && clientId && installments.length === 0" class="mt-2 text-gray-500">
                                Este cliente no tiene cuotas pendientes.
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Registrar Pago y Generar Folio') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>