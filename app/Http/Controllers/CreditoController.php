<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\Caixa;
use App\Models\ItemVenda;
use App\Models\Cliente;
use Illuminate\Support\Facades\Log;

class CreditoController extends Controller
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

        DB::transaction(function () use ($request) {
            $numeroVenda = 'VENDA-' . now()->timestamp;
            $dataVenda = now();

            $totalVenda = 0;
            $totalCusto = 0;

            // Verifica estoque
            foreach ($request->itens as $item) {
                $produto = Produto::findOrFail($item['produto_id']);
                $quantidade = $item['quantidade'];

                if ($produto->estoque->quantidade < $quantidade) {
                    throw new \Exception("Estoque insuficiente para o produto: {$produto->nome}");
                }
            }

            // Cria a venda
            $venda = Venda::create([
                'numero_venda' => $numeroVenda,
                'total_venda' => 0, // será atualizado após os cálculos
                'total_custo' => 0,
                'lucro_total' => 0,
                'status' => 'A Receber',
                'observacoes' => $request->observacoes,
                'data_venda' => $dataVenda,
            ]);

            // Processa itens
            foreach ($request->itens as $item) {
                $produto = Produto::findOrFail($item['produto_id']);
                $quantidade = $item['quantidade'];
                $precoVendaSelecionado = $item['preco']; // vindo do form
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
        });

        return redirect()->back()->with('success', 'Venda registrada com sucesso.');
    }

    public function listarVendasPendentes($id)
    {
        try {
            Log::info("Buscando vendas pendentes para cliente ID: " . $id);

            $cliente = Cliente::findOrFail($id);
            Log::info("Cliente encontrado: " . $cliente->nome);

            $vendas = $cliente->vendas()
                ->where('status', 'pendente')
                ->with('itensVenda.produto')
                ->get();

            Log::info("Vendas encontradas: " . $vendas->count());

            $vendasFormatadas = $vendas->map(function ($venda) {
                return [
                    'id' => $venda->id,
                    'numero_venda' => $venda->numero_venda,
                    'data_venda' => $venda->data_venda,
                    'total_venda' => $venda->total_venda,
                    'itens' => $venda->itensVenda->map(function ($item) {
                        return [
                            'produto' => [
                                'nome' => $item->produto ? $item->produto->nome : 'Produto não encontrado'
                            ],
                            'quantidade' => $item->quantidade,
                            'preco_venda_unitario' => $item->preco_venda_unitario,
                        ];
                    }),
                ];
            });

            Log::info("Vendas formatadas: " . json_encode($vendasFormatadas));

            return response()->json($vendasFormatadas);
        } catch (\Exception $e) {
            Log::error("Erro ao buscar vendas pendentes: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json([
                'error' => 'Erro ao buscar vendas pendentes',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function receberVenda(Request $request)
    {
        $request->validate([
            'venda_id' => 'required|exists:vendas,id',
        ]);

        DB::transaction(function () use ($request) {
            $venda = Venda::findOrFail($request->venda_id);

            if ($venda->status === 'concluida') {
                throw new \Exception('Venda já quitada.');
            }

            // Marca como concluída
            $venda->update(['status' => 'concluida']);

            // Cria entrada no caixa
            Caixa::create([
                'tipo' => 'entrada',
                'categoria' => 'Recebimento de Crédito',
                'descricao' => 'Recebimento da venda ' . $venda->numero_venda,
                'valor' => $venda->total_venda,
                'data' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'Venda recebida com sucesso!');
    }
}
