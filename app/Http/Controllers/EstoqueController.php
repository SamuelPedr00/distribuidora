<?php

namespace App\Http\Controllers;

use App\Models\Estoque;
use App\Models\Produto;

use Illuminate\Http\Request;

class EstoqueController extends Controller
{
    public function cadastrar(Request $request)
    {
        $request->validate([
            'produto_id' => 'required|exists:produtos,id',
            'quantidade' => 'required|integer|min:0',
        ], [
            'produto_id.required' => 'O campo produto é obrigatório.',
            'produto_id.exists' => 'O produto selecionado não existe.',

            'quantidade.required' => 'A quantidade é obrigatória.',
            'quantidade.integer' => 'A quantidade deve ser um número inteiro.',
            'quantidade.min' => 'A quantidade não pode ser menor que zero.',
        ]);

        // Verifica se já existe estoque para o produto
        $estoque = Estoque::where('produto_id', $request->produto_id)->first();

        if ($estoque) {
            // Se já existe, soma a nova quantidade ao existente
            $estoque->quantidade += $request->quantidade;
            $estoque->save();
        } else {
            // Se não existe, cria um novo registro
            Estoque::create([
                'produto_id' => $request->produto_id,
                'quantidade' => $request->quantidade
            ]);
        }

        return redirect()->back()->with('success', 'Estoque cadastrado/atualizado com sucesso!');
    }

    public function atualizar(Request $request)
    {
        $request->validate([
            'produto_id' => 'required|exists:produtos,id',
            'quantidade' => 'required|integer|min:0',
        ]);

        $produto = Produto::findOrFail($request->produto_id);

        if ($produto->estoque) {
            $produto->estoque->quantidade = $request->quantidade;
            $produto->estoque->save();
        } else {
            $produto->estoque()->create([
                'quantidade' => $request->quantidade
            ]);
        }

        return redirect()->back()->with('success', 'Estoque atualizado com sucesso!');
    }
}
