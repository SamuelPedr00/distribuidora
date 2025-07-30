<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;

class ClienteController extends Controller
{
    public function store(Request $request)
    {
        // Validação
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        // Criação do cliente
        $cliente = Cliente::create([
            'nome' => $validated['nome'],
        ]);

        // Retorno simples (pode ser JSON, redirect ou view)
        return redirect()->back()->with('success', 'Cliente Criado com sucesso!');
    }

    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        $cliente->update(['nome' => $request->nome]);

        return redirect()->back()->with('success', 'Cliente atualizado com sucesso!');
    }
}
