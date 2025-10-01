<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="page-title">
                Editar Cliente
            </h2>
            <a href="{{ route('clients.index') }}" class="btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="content-wrapper">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Formulario de Información del Cliente -->
            <div class="lg:col-span-2">
                <div class="card">
                    <div class="card-header">
                        <h3 class="section-title mb-0">Información del Cliente</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('clients.update', $client) }}">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Name -->
                                <div class="form-group md:col-span-2">
                                    <label for="name" class="form-label">
                                        Nombre Completo
                                        <span class="text-danger-600">*</span>
                                    </label>
                                    <input 
                                        id="name" 
                                        class="form-input" 
                                        type="text" 
                                        name="name" 
                                        value="{{ old('name', $client->name) }}" 
                                        required 
                                        autofocus 
                                    />
                                    @error('name')
                                        <p class="form-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        Correo Electrónico
                                    </label>
                                    <input 
                                        id="email" 
                                        class="form-input" 
                                        type="email" 
                                        name="email" 
                                        value="{{ old('email', $client->email) }}" 
                                    />
                                    @error('email')
                                        <p class="form-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Phone -->
                                <div class="form-group">
                                    <label for="phone" class="form-label">
                                        Teléfono
                                    </label>
                                    <input 
                                        id="phone" 
                                        class="form-input" 
                                        type="text" 
                                        name="phone" 
                                        value="{{ old('phone', $client->phone) }}" 
                                    />
                                    @error('phone')
                                        <p class="form-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Address -->
                                <div class="form-group md:col-span-2">
                                    <label for="address" class="form-label">
                                        Dirección
                                    </label>
                                    <textarea 
                                        id="address" 
                                        name="address" 
                                        class="form-textarea"
                                        rows="3"
                                    >{{ old('address', $client->address) }}</textarea>
                                    @error('address')
                                        <p class="form-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-end mt-6 pt-6 border-t border-gray-200">
                                <button type="submit" class="btn-primary">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Panel Lateral - Resumen -->
            <div class="lg:col-span-1">
                <div class="card sticky top-6">
                    <div class="card-header">
                        <h3 class="section-title mb-0">Resumen</h3>
                    </div>
                    <div class="card-body">
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">ID Cliente</dt>
                                <dd class="mt-1 text-sm text-gray-900">#{{ $client->id }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Fecha de Registro</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $client->created_at->format('d/m/Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Última Actualización</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $client->updated_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gestión de Documentos -->
        <div class="section mt-6">
            <div class="card-header">
                <h3 class="section-title mb-0">Documentos del Cliente</h3>
            </div>
            <div class="card-body">
                <!-- Formulario para agregar documento -->
                <form method="POST" action="{{ route('clients.documents.store', $client) }}" class="mb-6 pb-6 border-b border-gray-200">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="document_name" class="form-label">Nombre del Documento</label>
                            <input 
                                id="document_name" 
                                name="document_name" 
                                class="form-input"
                                placeholder="Ej: Identificación oficial"
                            />
                            @error('document_name')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="document_url" class="form-label">URL del Documento (OneDrive)</label>
                            <input 
                                id="document_url" 
                                name="document_url" 
                                type="url" 
                                class="form-input"
                                placeholder="https://..."
                            />
                            @error('document_url')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex justify-end mt-4">
                        <button type="submit" class="btn-primary btn-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Agregar Documento
                        </button>
                    </div>
                </form>

                <!-- Lista de documentos -->
                <div class="space-y-2">
                    @forelse($client->documents as $document)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <a href="{{ $document->document_url }}" target="_blank" class="link flex items-center flex-1">
                                <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ $document->document_name }}
                            </a>
                            <form action="{{ route('documents.destroy', $document) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este documento?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="link-danger text-sm flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">No hay documentos para este cliente</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>