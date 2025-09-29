<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\Client;
use Illuminate\Http\Request;

class LotController extends Controller
{
    public function index(Request $request)
    {
        $query = Lot::with('client');

        if ($request->has('search')) {
            $query->where('identifier', 'like', '%' . $request->search . '%')
                  ->orWhere('status', 'like', '%' . $request->search . '%')
                  ->orWhereHas('client', function ($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
        }
        
        $lots = $query->latest()->paginate(10);

        return view('lots.index', compact('lots'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('lots.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'identifier' => 'required|string|max:255|unique:lots',
            'total_price' => 'required|numeric|min:0',
            'status' => 'required|in:disponible,vendido,liquidado',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        Lot::create($validated);

        return redirect()->route('lots.index')->with('success', 'Lote creado exitosamente.');
    }

    public function edit(Lot $lot)
    {
        // Carga las relaciones, incluyendo las anidadas para obtener los nombres de los clientes.
        $lot->load('ownershipHistory.previousClient', 'ownershipHistory.newClient');
        $clients = Client::orderBy('name')->get();
        
        return view('lots.edit', compact('lot', 'clients'));
    }

    public function update(Request $request, Lot $lot)
    {
        $validated = $request->validate([
            'identifier' => 'required|string|max:255|unique:lots,identifier,' . $lot->id,
            'total_price' => 'required|numeric|min:0',
            'status' => 'required|in:disponible,vendido,liquidado',
            'client_id' => 'nullable|exists:clients,id',
        ]);
        
        // Si se asigna un cliente a un lote disponible, el estado cambia a 'vendido'.
        if ($validated['client_id'] && $lot->status == 'disponible') {
            $validated['status'] = 'vendido';
        }

        // Si se desasigna un cliente, el lote vuelve a estar disponible.
        if (is_null($validated['client_id'])) {
            $validated['status'] = 'disponible';
        }

        $lot->update($validated);

        return redirect()->route('lots.index')->with('success', 'Lote actualizado exitosamente.');
    }

    public function destroy(Lot $lot)
    {
        // Opcional: Prevenir borrado si tiene planes de pago.
        $lot->delete();

        return redirect()->route('lots.index')->with('success', 'Lote eliminado exitosamente.');
    }
}