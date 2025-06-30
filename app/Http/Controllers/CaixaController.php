<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Caixa;


class CaixaController extends Controller
{
    public function cadastrar(Request $request)
    {
        $request->validate([
            'categoria'     => 'required|string|max:100',
            'tipo'          => 'required|in:entrada,saida',
            'valor'         => 'required|numeric|min:0',
            'observacao'    => 'nullable|string|max:255',
        ], [
            'categoria.required' => 'O campo categoria é obrigatório.',
            'categoria.string'   => 'A categoria deve ser um texto.',
            'categoria.max'      => 'A categoria não pode ter mais de 100 caracteres.',

            'tipo.required'      => 'O campo tipo é obrigatório.',
            'tipo.in'            => 'O tipo deve ser "entrada" ou "saída".',

            'valor.required'     => 'O campo valor é obrigatório.',
            'valor.numeric'      => 'O valor deve ser um número.',
            'valor.min'          => 'O valor não pode ser negativo.',

            'observacao.string'  => 'A observação deve ser um texto.',
            'observacao.max'     => 'A observação não pode ter mais de 255 caracteres.',
        ]);

        // Criar registro no caixa
        Caixa::create([
            'tipo'      => $request->tipo,
            'categoria' => $request->categoria, // ou outro valor conforme o caso
            'descricao' => $request->observacao ?? 'Sem observação',
            'valor'     => $request->valor,
            'data'      => now(),
        ]);


        return redirect()->back()->with('success', 'Registro no caixa criados com sucesso!');
    }

    public function filtrar(Request $request)
    {
        $query = Caixa::query();

        if ($request->filled('dataInicio')) {
            $query->whereDate('data', '>=', $request->dataInicio);
        }

        if ($request->filled('dataFim')) {
            $query->whereDate('data', '<=', $request->dataFim);
        }

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        $dados = $query->orderBy('data', 'desc')->get();

        $entradas = $dados->where('tipo', 'entrada')->sum('valor');
        $saidas = $dados->where('tipo', 'saida')->sum('valor');
        $saldo = $entradas - $saidas;

        $resumoPorCategoria = $dados->groupBy('categoria')->map(function ($itens) {
            return $itens->sum('valor');
        });

        return response()->json([
            'dados' => $dados,
            'entradas' => $entradas,
            'saidas' => $saidas,
            'saldo' => $saldo,
            'resumo' => $resumoPorCategoria
        ]);
    }
}
