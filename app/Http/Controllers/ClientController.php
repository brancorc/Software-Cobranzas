<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::query();

        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%')
                  ->orWhere('phone', 'like', '%' . $searchTerm . '%');
            });
        }

        $clients = $query->latest()->paginate(10);

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:clients,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        Client::create($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    public function show(Client $client)
    {
        // OPTIMIZACIÓN: Carga eficiente con select específicos y agregaciones
        $client->load([
            'lots' => function ($query) {
                $query->with([
                    'paymentPlans' => function ($q) {
                        $q->with(['service:id,name'])
                          ->withCount('installments');
                    },
                    'paymentPlans.installments' => function ($q) {
                        $q->with('transactions:id')
                          ->select('id', 'payment_plan_id', 'installment_number', 'due_date', 
                                   'base_amount', 'interest_amount', 'status')
                          ->orderBy('installment_number');
                    }
                ]);
            }
        ]);

        // Precalcular totales para evitar cálculos en la vista
        foreach ($client->lots as $lot) {
            foreach ($lot->paymentPlans as $plan) {
                foreach ($plan->installments as $installment) {
                    $totalOwed = $installment->base_amount + $installment->interest_amount;
                    $totalPaid = $installment->transactions->sum('pivot.amount_applied');
                    
                    // Añadir propiedades calculadas
                    $installment->total_owed = $totalOwed;
                    $installment->total_paid = $totalPaid;
                    $installment->remaining_balance = max(0, $totalOwed - $totalPaid);
                    $installment->is_paid = $installment->remaining_balance <= 0.005;
                }
            }
        }

        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:clients,email,' . $client->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $client->update($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy(Client $client)
    {
        // Verificar que no tenga lotes asociados
        if ($client->lots()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un cliente con lotes asociados.');
        }

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }
}