<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientDocument;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientDocumentController extends Controller
{
    /**
     * Dominios permitidos para almacenar documentos
     */
    private const ALLOWED_DOMAINS = [
        'onedrive.live.com',
        '1drv.ms',
        'sharepoint.com',
        'drive.google.com',
        'docs.google.com',
        'dropbox.com',
    ];

    public function store(Request $request, Client $client)
    {
        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
            'document_url' => [
                'required',
                'url',
                'max:2048',
                function ($attribute, $value, $fail) {
                    $parsedUrl = parse_url($value);
                    $host = $parsedUrl['host'] ?? '';
                    
                    $isAllowed = false;
                    foreach (self::ALLOWED_DOMAINS as $allowedDomain) {
                        if (
                            $host === $allowedDomain || 
                            str_ends_with($host, '.' . $allowedDomain)
                        ) {
                            $isAllowed = true;
                            break;
                        }
                    }
                    
                    if (!$isAllowed) {
                        $fail('La URL debe ser de un servicio de almacenamiento permitido (OneDrive, Google Drive, Dropbox o SharePoint).');
                    }
                },
            ],
        ]);

        $client->documents()->create($validated);

        return back()->with('success', 'Documento agregado exitosamente.');
    }

    public function destroy(ClientDocument $document)
    {
        $document->delete();

        return back()->with('success', 'Documento eliminado exitosamente.');
    }
}