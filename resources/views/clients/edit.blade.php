<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Cliente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        {{-- SECCIÓN 1: FORMULARIO DE EDICIÓN DEL CLIENTE --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('clients.update', $client) }}">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div>
                            <x-input-label for="name" :value="__('Nombre')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $client->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Email -->
                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $client->email)" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Phone -->
                        <div class="mt-4">
                            <x-input-label for="phone" :value="__('Teléfono')" />
                            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone', $client->phone)" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        <!-- Address -->
                        <div class="mt-4">
                            <x-input-label for="address" :value="__('Dirección')" />
                            <textarea id="address" name="address" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('address', $client->address) }}</textarea>
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>
                        
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Actualizar Cliente') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- SECCIÓN 2: GESTIÓN DE DOCUMENTOS --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Documentos del Cliente</h3>
                    
                    {{-- Formulario para agregar nuevo documento --}}
                    <form method="POST" action="{{ route('clients.documents.store', $client) }}" class="mb-6 border-b pb-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="document_name" value="Nombre del Documento" />
                                <x-text-input id="document_name" name="document_name" class="mt-1 block w-full" />
                                <x-input-error :messages="$errors->get('document_name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="document_url" value="URL del Documento (OneDrive)" />
                                <x-text-input id="document_url" name="document_url" type="url" class="mt-1 block w-full" />
                                <x-input-error :messages="$errors->get('document_url')" class="mt-2" />
                            </div>
                        </div>
                        <div class="flex justify-end mt-4">
                            <x-primary-button>Agregar Documento</x-primary-button>
                        </div>
                    </form>

                    {{-- Lista de documentos existentes --}}
                    <ul>
                        @forelse($client->documents as $document)
                            <li class="flex justify-between items-center py-2 border-b">
                                <a href="{{ $document->document_url }}" target="_blank" class="text-indigo-600 hover:underline">{{ $document->document_name }}</a>
                                <form action="{{ route('documents.destroy', $document) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este documento?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm">Eliminar</button>
                                </form>
                            </li>
                        @empty
                            <li>No hay documentos para este cliente.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>