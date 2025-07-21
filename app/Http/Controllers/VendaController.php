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
            'itens.*.preco' => 'required|numeric|min:0.01',
        ], [
            'itens.*.produto_id.required' => 'O campo produto é obrigatório.',
            'itens.*.produto_id.exists' => 'O produto selecionado não existe.',
            'itens.*.quantidade.required' => 'A quantidade é obrigatória.',
            'itens.*.quantidade.integer' => 'A quantidade deve ser um número inteiro.',
            'itens.*.quantidade.min' => 'A quantidade mínima é 1.',
            'itens.*.preco.required' => 'Selecione o preço da venda.',
            'itens.*.preco.numeric' => 'O preço deve ser numérico.',
            'itens.*.preco.min' => 'O preço deve ser maior que zero.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $numeroVenda = 'VENDA-' . now()->timestamp;
                $dataVenda = now();

                $totalVenda = 0;
                $totalCusto = 0;

                // Verifica estoque
                foreach ($request->itens as $item) {
                    $produto = Produto::findOrFail($item['produto_id']);
                    $quantidade = $item['quantidade'];

                    if (!$produto->estoque) {
                        throw new \Exception("O produto '{$produto->nome}' não possui estoque cadastrado.");
                    }

                    if ($produto->estoque->quantidade < $quantidade) {
                        throw new \Exception("Estoque insuficiente para o produto: '{$produto->nome}'.");
                    }
                }

                // Cria a venda
                $venda = Venda::create([
                    'numero_venda' => $numeroVenda,
                    'total_venda' => 0, // será atualizado após os cálculos
                    'total_custo' => 0,
                    'lucro_total' => 0,
                    'status' => 'concluida',
                    'observacoes' => $request->observacoes,
                    'data_venda' => $dataVenda,
                ]);

                // Processa itens
                foreach ($request->itens as $item) {
                    $produto = Produto::findOrFail($item['produto_id']);
                    $quantidade = $item['quantidade'];
                    $precoVendaSelecionado = $item['preco'];
                    $precoCompra = $produto->preco_compra_atual;

                    $valorTotal = $precoVendaSelecionado * $quantidade;
                    $custoTotal = $precoCompra * $quantidade;
                    $lucro = $valorTotal - $custoTotal;

                    ItemVenda::create([
                        'venda_id' => $venda->id,
                        'produto_id' => $produto->id,
                        'quantidade' => $quantidade,
                        'preco_compra_unitario' => $precoCompra,
                        'preco_venda_unitario' => $precoVendaSelecionado,
                        'custo_total' => $custoTotal,
                        'valor_total' => $valorTotal,
                        'lucro_item' => $lucro,
                    ]);

                    $totalVenda += $valorTotal;
                    $totalCusto += $custoTotal;

                    // Atualiza estoque
                    $produto->estoque->decrement('quantidade', $quantidade);
                }

                // Atualiza venda com totais
                $venda->update([
                    'total_venda' => $totalVenda,
                    'total_custo' => $totalCusto,
                    'lucro_total' => $totalVenda - $totalCusto,
                ]);

                // Cria entrada no caixa
                Caixa::create([
                    'tipo'      => 'entrada',
                    'categoria' => 'Venda de Produto',
                    'descricao' => $request->observacoes ?? 'Venda registrada',
                    'valor'     => $totalVenda,
                    'data'      => $dataVenda,
                    'venda_id'  => $venda->id, // Relacionamento com a venda
                ]);
            });

            return redirect()->back()->with('success', 'Venda registrada com sucesso.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reverter($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $venda = Venda::with('itensVenda', 'itensVenda.produto.estoque')->findOrFail($id);

                // Devolve o estoque
                foreach ($venda->itensVenda as $item) {
                    $produto = $item->produto;
                    if ($produto && $produto->estoque) {
                        $produto->estoque->increment('quantidade', $item->quantidade);
                    }
                }

                // Deleta os itens da venda
                $venda->itensVenda()->delete();

                // Deleta o lançamento no caixa relacionado
                Caixa::where('venda_id', $venda->id)->delete();

                // Deleta a venda
                $venda->delete();
            });

            return response()->json(['mensagem' => 'Venda revertida e removida com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['erro' => $e->getMessage()], 400);
        }
    }


    public function registrarCredito(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'itens.*.produto_id' => 'required|exists:produtos,id',
            'itens.*.quantidade' => 'required|integer|min:1',
            'itens.*.preco' => 'required|numeric|min:0.01',
        ], [
            'cliente_id.required' => 'O cliente é obrigatório.',
            'cliente_id.exists' => 'O cliente selecionado não existe.',
            'itens.*.produto_id.required' => 'O campo produto é obrigatório.',
            'itens.*.produto_id.exists' => 'O produto selecionado não existe.',
            'itens.*.quantidade.required' => 'A quantidade é obrigatória.',
            'itens.*.quantidade.integer' => 'A quantidade deve ser um número inteiro.',
            'itens.*.quantidade.min' => 'A quantidade mínima é 1.',
            'itens.*.preco.required' => 'Selecione o preço da venda.',
            'itens.*.preco.numeric' => 'O preço deve ser numérico.',
            'itens.*.preco.min' => 'O preço deve ser maior que zero.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $numeroVenda = 'CREDITO-' . now()->timestamp;
                $dataVenda = now();
                $totalVenda = 0;
                $totalCusto = 0;

                // Verifica estoque
                foreach ($request->itens as $item) {
                    $produto = Produto::findOrFail($item['produto_id']);
                    $quantidade = $item['quantidade'];

                    if (!$produto->estoque) {
                        throw new \Exception("O produto '{$produto->nome}' não possui estoque cadastrado.");
                    }

                    if ($produto->estoque->quantidade < $quantidade) {
                        throw new \Exception("Estoque insuficiente para o produto: '{$produto->nome}'.");
                    }
                }

                // Cria a venda com status "pendente" e cliente_id
                $venda = Venda::create([
                    'cliente_id' => $request->cliente_id,
                    'numero_venda' => $numeroVenda,
                    'total_venda' => 0,
                    'total_custo' => 0,
                    'lucro_total' => 0,
                    'status' => 'pendente',
                    'observacoes' => $request->observacoes,
                    'data_venda' => $dataVenda,
                ]);

                // Processa itens
                foreach ($request->itens as $item) {
                    $produto = Produto::findOrFail($item['produto_id']);
                    $quantidade = $item['quantidade'];
                    $precoVendaSelecionado = $item['preco'];
                    $precoCompra = $produto->preco_compra_atual;

                    $valorTotal = $precoVendaSelecionado * $quantidade;
                    $custoTotal = $precoCompra * $quantidade;
                    $lucro = $valorTotal - $custoTotal;

                    ItemVenda::create([
                        'venda_id' => $venda->id,
                        'produto_id' => $produto->id,
                        'quantidade' => $quantidade,
                        'preco_compra_unitario' => $precoCompra,
                        'preco_venda_unitario' => $precoVendaSelecionado,
                        'custo_total' => $custoTotal,
                        'valor_total' => $valorTotal,
                        'lucro_item' => $lucro,
                    ]);

                    $totalVenda += $valorTotal;
                    $totalCusto += $custoTotal;

                    // Atualiza estoque
                    $produto->estoque->decrement('quantidade', $quantidade);
                }

                // Atualiza venda com totais
                $venda->update([
                    'total_venda' => $totalVenda,
                    'total_custo' => $totalCusto,
                    'lucro_total' => $totalVenda - $totalCusto,
                ]);

                // ⛔ NÃO registra entrada no caixa
            });

            return redirect()->back()->with('success', 'Venda a crédito registrada com sucesso.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
