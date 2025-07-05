<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Produto;
use App\Models\Movimentacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovimentacaoController extends Controller
{
    public function cadastrar(Request $request)
    {
        $request->validate([
            'produto_id'     => 'required|exists:produtos,id',
            'tipo'           => 'required|in:entrada,saida',
            'quantidade'     => 'required|integer|min:1',
            'preco_unitario' => 'required|numeric|min:0',
            'observacao'     => 'nullable|string|max:255',
        ], [
            'produto_id.required' => 'O campo produto é obrigatório.',
            'produto_id.exists'   => 'O produto selecionado não existe.',

            'tipo.required' => 'O campo tipo é obrigatório.',
            'tipo.in'       => 'O tipo deve ser "entrada" ou "saida".',

            'quantidade.required' => 'O campo quantidade é obrigatório.',
            'quantidade.integer'  => 'A quantidade deve ser um número inteiro.',
            'quantidade.min'      => 'A quantidade deve ser no mínimo 1.',

            'preco_unitario.required' => 'O campo preço unitário é obrigatório.',
            'preco_unitario.numeric'  => 'O preço unitário deve ser um número.',
            'preco_unitario.min'      => 'O preço unitário não pode ser negativo.',

            'observacao.string' => 'A observação deve ser um texto.',
            'observacao.max'    => 'A observação não pode ter mais de 255 caracteres.',
        ]);

        $produto = Produto::find($request->produto_id);

        // Verificação de estoque apenas se for saída
        if ($request->tipo === 'saida' && $request->quantidade > $produto->estoque) {
            return back()->withErrors(['error' => 'Quantidade solicitada excede o estoque disponível.']);
        }

        DB::transaction(function () use ($request) {
            $total = $request->quantidade * $request->preco_unitario;
            $produto = Produto::with('estoque')->findOrFail($request->produto_id);
            $estoque = $produto->estoque;

            if (!$estoque) {
                // Cria o estoque se não existir
                $estoque = $produto->estoque()->create([
                    'quantidade' => 0
                ]);
            }

            // Atualiza o estoque do produto
            if ($request->tipo === 'entrada') {
                $estoque->quantidade += $request->quantidade;
            } else {
                $estoque->quantidade -= $request->quantidade;
            }

            $estoque->save();

            // Criar movimentação
            $movimentacao = Movimentacao::create([
                'produto_id'     => $request->produto_id,
                'tipo'           => $request->tipo,
                'quantidade'     => $request->quantidade,
                'preco_unitario' => $request->preco_unitario,
                'total'          => $total,
                'observacao'     => $request->observacao,
                'data'           => now(),
            ]);

            $tipo = $request->tipo == 'entrada' ? 'saida' : 'entrada';

            // Criar registro no caixa com vínculo à movimentação
            Caixa::create([
                'tipo'            => $tipo,
                'categoria'       => 'Categoria Especial',
                'descricao'       => $request->observacao ?? 'Sem observação',
                'valor'           => $total,
                'data'            => now(),
                'movimentacao_id' => $movimentacao->id,
            ]);
        });


        return redirect()->back()->with('success', 'Movimentação e registro no caixa criados com sucesso!');
    }

    public function reverter($id)
    {
        $mov = Movimentacao::findOrFail($id);
        $produto = Produto::with('estoque')->find($mov->produto_id);

        if (!$produto || !$produto->estoque) {
            return redirect()->back()->withErrors(['error' => 'Produto ou estoque não encontrado para esta movimentação.']);
        }

        DB::transaction(function () use ($mov, $produto) {
            $estoque = $produto->estoque;

            // Reverter estoque
            if ($mov->tipo === 'entrada') {
                $estoque->quantidade -= $mov->quantidade;
            } else {
                $estoque->quantidade += $mov->quantidade;
            }

            $estoque->save();

            // Remover o registro do caixa vinculado
            if ($mov->caixa) {
                $mov->caixa->delete();
            }

            // Deleta a movimentação
            $mov->delete();
        });

        return redirect()->back()->with('success', 'Movimentação revertida com sucesso.');
    }
}
