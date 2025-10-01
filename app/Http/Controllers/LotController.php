<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\Client;
use Illuminate\Http\Request;

class LotController extends Controller
{
    public function index(Request $request)
    {
        $query = Lot::with('client:id,name');

        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('identifier', 'like', '%' . $searchTerm . '%')
                  ->orWhere('status', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('client', function ($clientQuery) use ($searchTerm) {
                      $clientQuery->where('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }
        
        $lots = $query->latest()->paginate(10);

        return view('lots.index', compact('lots'));
    }

    public function create()
    {
        $clients = Client::select('id', 'name')->orderBy('name')->get();
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

        return redirect()->route('lots.index')
            ->with('success', 'Lote creado exitosamente.');
    }

    public function edit(Lot $lot)
    {
        // Solo cargar historial si existe
        if ($lot->ownershipHistory()->exists()) {
            $lot->load([
                'ownershipHistory' => function ($query) {
                    $query->with(['previousClient:id,name', 'newClient:id,name'])
                          ->latest('transfer_date');
                }
            ]);
        }
        
        $clients = Client::select('id', 'name')->orderBy('name')->get();
        
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
        
        // Si se asigna un cliente a un lote disponible, el estado cambia a 'vendido'
        if ($validated['client_id'] && $lot->status == 'disponible') {
            $validated['status'] = 'vendido';
        }

        // Si se desasigna un cliente, el lote vuelve a estar disponible
        if (is_null($validated['client_id'])) {
            $validated['status'] = 'disponible';
        }

        $lot->update($validated);

        return redirect()->route('lots.index')
            ->with('success', 'Lote actualizado exitosamente.');
    }

    public function destroy(Lot $lot)
    {
        // Verificar que no tenga planes de pago
        if ($lot->paymentPlans()->exists()) {
            return back()->with('error', 'No se puede eliminar un lote con planes de pago asociados.');
        }

        $lot->delete();

        return redirect()->route('lots.index')
            ->with('success', 'Lote eliminado exitosamente.');
    }
}