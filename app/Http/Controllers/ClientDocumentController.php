<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientDocument;
use Illuminate\Http\Request;

class ClientDocumentController extends Controller
{
    public function store(Request $request, Client $client)
    {
        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
            'document_url' => 'required|url|max:2048',
        ]);

        $client->documents()->create($validated);

        return back()->with('success', 'Documento agregado exitosamente.');
    }

    public function destroy(ClientDocument $document)
    {
        $this->authorize('delete', $document); // Opcional: añadir autorización si es necesario
        $document->delete();

        return back()->with('success', 'Documento eliminado exitosamente.');
    }
}