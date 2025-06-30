<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\Caixa;
use App\Models\ItemVenda;

class VendaController extends Controller
{
    public function cadastrar(Request $request)
    {
        $request->validate([
            'itens.*.produto_id' => 'required|exists:produtos,id',
            'itens.*.quantidade' => 'required|integer|min:1',
        ], [
            'itens.*.produto_id.required' => 'O campo produto é obrigatório para cada item.',
            'itens.*.produto_id.exists' => 'O produto selecionado não existe no sistema.',
            'itens.*.quantidade.required' => 'A quantidade é obrigatória para cada item.',
            'itens.*.quantidade.integer' => 'A quantidade deve ser um número inteiro.',
            'itens.*.quantidade.min' => 'A quantidade mínima para venda é 1.',
        ]);

        DB::transaction(function () use ($request) {
            $numeroVenda = 'VENDA-' . now()->timestamp;
            $dataVenda = now();

            $totalVenda = 0;
            $totalCusto = 0;

            // 1. Verifica se todos os produtos têm estoque suficiente
            foreach ($request->itens as $item) {
                $produto = Produto::findOrFail($item['produto_id']);
                $quantidade = $item['quantidade'];

                $estoque = $produto->estoque;

                if (!$estoque || $estoque->quantidade < $quantidade) {
                    throw new \Exception("Estoque insuficiente para o produto: {$produto->nome}");
                }
            }

            // 2. Calcula totais
            foreach ($request->itens as $item) {
                $produto = Produto::findOrFail($item['produto_id']);
                $quantidade = $item['quantidade'];

                $precoCompra = $produto->preco_compra_atual;
                $precoVenda = $produto->preco_venda_atual;

                $totalVenda += $precoVenda * $quantidade;
                $totalCusto += $precoCompra * $quantidade;
            }

            // 3. Cria a venda
            $venda = Venda::create([
                'numero_venda' => $numeroVenda,
                'total_venda' => $totalVenda,
                'total_custo' => $totalCusto,
                'lucro_total' => $totalVenda - $totalCusto,
                'status' => 'concluida',
                'observacoes' => $request->observacoes,
                'data_venda' => $dataVenda,
            ]);

            // 4. Cria os itens da venda e atualiza o estoque
            foreach ($request->itens as $item) {
                $produto = Produto::findOrFail($item['produto_id']);
                $quantidade = $item['quantidade'];

                ItemVenda::create([
                    'venda_id' => $venda->id,
                    'produto_id' => $produto->id,
                    'quantidade' => $quantidade,
                    'preco_compra_unitario' => $produto->preco_compra_atual,
                    'preco_venda_unitario' => $produto->preco_venda_atual,
                    'custo_total' => $produto->preco_compra_atual * $quantidade,
                    'valor_total' => $produto->preco_venda_atual * $quantidade,
                    'lucro_item' => ($produto->preco_venda_atual - $produto->preco_compra_atual) * $quantidade,
                ]);
                // Criar registro no caixa
                Caixa::create([
                    'tipo'      => 'entrada',
                    'categoria' => 'Categoria Especial', // ou outro valor conforme o caso
                    'descricao' => $request->observacoes ?? 'Sem observação',
                    'valor'     => $totalVenda,
                    'data'      => now(),
                ]);
                // Atualiza estoque
                $estoque = $produto->estoque;
                $estoque->decrement('quantidade', $quantidade);
            }
        });

        return redirect()->back()->with('success', 'Venda registrada com sucesso.');
    }
}
