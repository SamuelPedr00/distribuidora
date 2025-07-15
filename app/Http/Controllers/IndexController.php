<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\Caixa;
use App\Models\Movimentacao;
use App\Models\Venda;
use App\Models\Cliente;


class IndexController extends Controller
{
    public function index()
    {
        // Produtos ativos com estoque
        $produtos = Produto::with('estoque')
            ->where('status', 'ativo')
            ->get();
        $totalProdutos = $produtos->count();

        // Produtos ativos com estoque baixo (≤ 10) ou sem estoque cadastrado
        $produtosBaixoEstoque = Produto::with('estoque')
            ->where('status', 'ativo')
            ->whereHas('estoque', function ($query) {
                $query->where('quantidade', '<=', 10);
            })
            ->orWhere(function ($query) {
                $query->where('status', 'ativo')->whereDoesntHave('estoque');
            })
            ->get();

        $quantidadeProdutosBaixo = $produtosBaixoEstoque->count();

        // Quantidade de produtos ativos que têm estoque cadastrado
        $produtosComEstoque = Produto::where('status', 'ativo')
            ->whereHas('estoque')
            ->count();

        $valorCaixa = Caixa::selectRaw("
            SUM(
                CASE 
                    WHEN tipo = 'entrada' THEN valor
                    WHEN tipo = 'saida' THEN -valor
                    ELSE 0
                END
            ) as total
        ")->value('total');

        $movimentacoes = Movimentacao::with('produto')->orderByDesc('data')->get();
        $vendas = Venda::orderByDesc('data_venda')->get();
        $clientes = Cliente::all();
        $clientesComCredito = $clientes->map(function ($cliente) {
            $totalCredito = $cliente->vendas()
                ->where('status', 'pendente')
                ->sum('total_venda');

            return [
                'id' => $cliente->id,
                'nome' => $cliente->nome,
                'credito' => $totalCredito,
            ];
        })->filter(function ($c) {
            return $c['credito'] > 0;
        })->values(); // remove vazios e reindexa

        $data = [
            'produtos' => $produtos,
            'totalProdutos' => $totalProdutos,
            'produtosBaixoEstoque' => $produtosBaixoEstoque,
            'quantidadeProdutosBaixo' => $quantidadeProdutosBaixo,
            'produtosComEstoque' => $produtosComEstoque,
            'valorCaixa' => $valorCaixa ?? 0,
            'movimentacoes' => $movimentacoes,
            'vendas' => $vendas,
            'clientes' => $clientes,
            'clientesComCredito' => $clientesComCredito,
        ];

        return view('index', $data);
    }
}
