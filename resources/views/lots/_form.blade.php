<form method="POST" action="{{ $action }}">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <!-- Identifier -->
    <div>
        <x-input-label for="identifier" :value="__('Identificador (Ej: Manzana 1, Lote 5)')" />
        <x-text-input id="identifier" class="block mt-1 w-full" type="text" name="identifier" :value="old('identifier', $lot->identifier)" required autofocus />
        <x-input-error :messages="$errors->get('identifier')" class="mt-2" />
    </div>

    <!-- Total Price -->
    <div class="mt-4">
        <x-input-label for="total_price" :value="__('Precio Total')" />
        <x-text-input id="total_price" class="block mt-1 w-full" type="number" step="0.01" name="total_price" :value="old('total_price', $lot->total_price)" required />
        <x-input-error :messages="$errors->get('total_price')" class="mt-2" />
    </div>

    <!-- Status -->
    <div class="mt-4">
        <x-input-label for="status" :value="__('Estado')" />
        <select id="status" name="status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
            <option value="disponible" @selected(old('status', $lot->status) == 'disponible')>Disponible</option>
            <option value="vendido" @selected(old('status', $lot->status) == 'vendido')>Vendido</option>
            <option value="liquidado" @selected(old('status', $lot->status) == 'liquidado')>Liquidado</option>
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>

    <!-- Client -->
    <div class="mt-4">
        <x-input-label for="client_id" :value="__('Cliente Asignado')" />

        @if($lot->exists)
            {{-- Si el lote ya existe (estamos editando), mostrar texto plano --}}
            <p class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm px-3 py-2">
                {{ $lot->client->name ?? 'Sin asignar' }}
            </p>
            <p class="mt-1 text-sm text-gray-500">Para cambiar el propietario, utiliza la sección "Transferir Lote".</p>
            {{-- Campo oculto para asegurar que el client_id no se elimine al actualizar --}}
            <input type="hidden" name="client_id" value="{{ $lot->client_id }}">
        @else
            {{-- Si es un lote nuevo (estamos creando), mostrar el selector --}}
            <select id="client_id" name="client_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                <option value="">-- Sin asignar --</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" @selected(old('client_id', $lot->client_id) == $client->id)>{{ $client->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
        @endif
    </div>

    <div class="flex items-center justify-end mt-4">
        <x-primary-button>
            {{ $buttonText }}
        </x-primary-button>
    </div>
</form>