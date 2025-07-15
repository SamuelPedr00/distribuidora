<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Caixa;
use App\Models\Movimentacao;
use App\Models\ItemVenda;
use Illuminate\Support\Facades\DB;


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

        $dadosCaixa = $query->orderBy('data', 'desc')->get();

        $entradas = $dadosCaixa->where('tipo', 'entrada')->sum('valor');
        $saidasCaixa = $dadosCaixa->where('tipo', 'saida')->sum('valor');

        // Filtros para movimentações e vendas no mesmo período
        $dataInicio = $request->dataInicio ?? '1900-01-01';
        $dataFim = $request->dataFim ?? now()->toDateString();

        // Custo de movimentações de saída
        $movimentacoes = Movimentacao::where('tipo', 'saida')
            ->whereRaw("STR_TO_DATE(data, '%Y-%m-%d') BETWEEN ? AND ?", [$dataInicio, $dataFim])
            ->get();


        $custoMovimentacoes = $movimentacoes->sum(function ($item) {
            return ($item->quantidade ?? 0) * ($item->preco_custo ?? 0);
        });


        $custoVendas = ItemVenda::whereHas('venda', function ($query) use ($dataInicio, $dataFim) {
            $query->whereRaw("STR_TO_DATE(created_at, '%Y-%m-%d') BETWEEN ? AND ?", [$dataInicio, $dataFim]);
        })
            ->sum('custo_total');


        $saidas = $saidasCaixa + $custoMovimentacoes + $custoVendas;

        $saldo = $entradas - $saidas;

        $resumoPorCategoria = $dadosCaixa->groupBy('categoria')->map(function ($itens) {
            return $itens->sum('valor');
        });

        $itensFiltrados = ItemVenda::whereHas('venda', function ($query) use ($dataInicio, $dataFim) {
            $query->whereBetween('created_at', [$dataInicio, $dataFim]);
        })->get();

        $itensFiltrados->each(function ($item) {
            logger("Venda ID: {$item->venda_id}, Produto: {$item->produto_id}, Custo Total: {$item->custo_total}");
        });


        return response()->json([
            'dados' => $dadosCaixa,
            'entradas' => $entradas,
            'saida_caixa' => $saidasCaixa,
            'custo_movimentacoes' => $custoMovimentacoes,
            'custo_vendas' => $custoVendas,
            'saidas' => $saidas,
            'saldo' => $saldo,
            'resumo' => $resumoPorCategoria,

            // DEBUG OPCIONAL:
            'debug' => [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'movimentacoes' => $movimentacoes,
                'itens_venda' => $itensFiltrados,
            ]
        ]);
    }
}
