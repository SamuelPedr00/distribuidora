<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\Caixa;
use App\Models\Movimentacao;
use App\Models\Venda;



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

        $data = [
            'produtos' => $produtos,
            'totalProdutos' => $totalProdutos,
            'produtosBaixoEstoque' => $produtosBaixoEstoque,
            'quantidadeProdutosBaixo' => $quantidadeProdutosBaixo,
            'produtosComEstoque' => $produtosComEstoque,
            'valorCaixa' => $valorCaixa ?? 0,
            'movimentacoes' => $movimentacoes,
            'vendas' => $vendas,
        ];

        return view('index', $data);
    }
}
