<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produto;

class ProdutoController extends Controller
{
    public function cadastrar(Request $request)
    {
        $messages = [
            'codigo.required' => 'O código do produto é obrigatório.',
            'codigo.string' => 'O código do produto deve ser um texto válido.',
            'codigo.max' => 'O código do produto não pode ter mais que 50 caracteres.',
            'codigo.unique' => 'Este código já está em uso por outro produto.',

            'nome.required' => 'O nome do produto é obrigatório.',
            'nome.string' => 'O nome do produto deve ser um texto válido.',
            'nome.max' => 'O nome do produto não pode ter mais que 100 caracteres.',
            'nome.unique' => 'Já existe um produto com este nome.',

            'compra.required' => 'O preço de compra é obrigatório.',
            'compra.numeric' => 'O preço de compra deve ser um número válido.',
            'compra.min' => 'O preço de compra não pode ser negativo.',

            'venda.required' => 'O preço de venda é obrigatório.',
            'venda.numeric' => 'O preço de venda deve ser um número válido.',
            'venda.min' => 'O preço de venda não pode ser negativo.',

            'categoria.required' => 'A categoria do produto é obrigatória.',
            'categoria.string' => 'A categoria deve ser um texto válido.',
            'categoria.max' => 'A categoria não pode ter mais que 50 caracteres.',
        ];

        // Validação dos campos
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:produtos,codigo',
            'nome' => 'required|string|max:100|unique:produtos,nome',
            'compra' => 'required|numeric|min:0',
            'venda' => 'required|numeric|min:0',
            'categoria' => 'required|string|max:50',
        ], $messages);


        // Cria o produto
        $produto = Produto::create([
            'codigo' => $validated['codigo'],
            'nome' => $validated['nome'],
            'preco_compra_atual' => $validated['compra'],
            'preco_venda_atual' => $validated['venda'],
            'categoria' => $validated['categoria']
        ]);

        // Cria o histórico de preços
        $produto->atualizarPrecos($validated['compra'], $validated['venda'], 'Preço inicial');

        return redirect()->back()->with('success', '✅ Produto cadastrado com sucesso!');
    }

    public function editar(Request $request, $id)
    {
        // Buscar produto
        $produto = Produto::findOrFail($id);
        $messages = [
            'codigo.string' => 'O código do produto deve ser um texto válido.',
            'codigo.max' => 'O código do produto não pode ter mais que 50 caracteres.',

            'nome.string' => 'O nome do produto deve ser um texto válido.',
            'nome.max' => 'O nome do produto não pode ter mais que 100 caracteres.',
            'nome.unique' => 'Já existe um produto com este nome.',

            'compra.numeric' => 'O preço de compra deve ser um número válido.',
            'compra.min' => 'O preço de compra não pode ser negativo.',

            'venda.numeric' => 'O preço de venda deve ser um número válido.',
            'venda.min' => 'O preço de venda não pode ser negativo.',

            'categoria.string' => 'A categoria deve ser um texto válido.',
            'categoria.max' => 'A categoria não pode ter mais que 50 caracteres.',
        ];
        // Validação parcial: apenas campos presentes
        $rules = [
            'codigo' => 'sometimes|string|max:50',
            'nome' => 'sometimes|string|max:100|unique:produtos,nome,' . $produto->id,
            'compra' => 'sometimes|numeric|min:0',
            'venda' => 'sometimes|numeric|min:0',
            'categoria' => 'sometimes|string|max:50',
        ];



        $validated = $request->validate($rules, $messages);

        // Verifica se houve alteração nos preços
        $precoCompraNovo = $request->filled('compra') && $request->compra != $produto->preco_compra_atual;
        $precoVendaNovo = $request->filled('venda') && $request->venda != $produto->preco_venda_atual;

        // Atualiza apenas os campos enviados
        foreach (['codigo', 'nome', 'categoria'] as $campo) {
            if ($request->filled($campo)) {
                $produto->$campo = $request->$campo;
            }
        }

        if ($request->filled('compra')) {
            $produto->preco_compra_atual = $request->compra;
        }

        if ($request->filled('venda')) {
            $produto->preco_venda_atual = $request->venda;
        }

        // Salva alterações
        $produto->save();

        // Atualiza histórico apenas se o preço tiver sido alterado
        if ($precoCompraNovo || $precoVendaNovo) {
            $produto->atualizarPrecos(
                $produto->preco_compra_atual,
                $produto->preco_venda_atual,
                'Atualização via edição'
            );
        }

        return redirect()->back()->with('success', 'Produto atualizado com sucesso!');
    }

    public function desativar(Request $request, $id)
    {
        // Buscar produto com estoque
        $produto = Produto::with('estoque')->findOrFail($id);

        // Quantidade em estoque (se não existir registro, zero)
        $quantidadeEstoque = $produto->estoque ? $produto->estoque->quantidade : 0;

        if ($quantidadeEstoque > 0) {
            return redirect()->back()->with('error', 'Não é possível desativar um produto que possui estoque disponível.');
        }

        // Alternar status
        if ($produto->status === 'ativo') {
            $produto->status = 'inativo';
        } else {
            $produto->status = 'ativo';
        }

        $produto->save();

        return redirect()->back()->with('success', 'Status do produto atualizado com sucesso.');
    }
}
