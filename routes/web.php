<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\IndexController::class, 'index'])->name('index');
Route::post('/cadastroProduto', [\App\Http\Controllers\ProdutoController::class, 'cadastrar'])->name('cadastro_produto');
Route::post('/cadastroEstoque', [\App\Http\Controllers\EstoqueController::class, 'cadastrar'])->name('cadastro_estoque');
Route::post('/produtos/{id}', [\App\Http\Controllers\ProdutoController::class, 'editar'])->name('produtos_editar');
Route::post('/produtos/desativar/{id}', [\App\Http\Controllers\ProdutoController::class, 'desativar'])->name('produtos_desativar');
Route::post('/cadastroMovimentacao', [\App\Http\Controllers\MovimentacaoController::class, 'cadastrar'])->name('cadastro_movimentacao');
Route::post('/cadastroVenda', [\App\Http\Controllers\VendaController::class, 'cadastrar'])->name('cadastro_venda');
Route::post('/cadastroCaixa', [\App\Http\Controllers\CaixaController::class, 'cadastrar'])->name('cadastro_caixa');
Route::get('/caixa/filtro', [\App\Http\Controllers\CaixaController::class, 'filtrar'])->name('filtrar');
Route::post('/movimentacao/reverter/{id}', [\App\Http\Controllers\MovimentacaoController::class, 'reverter'])->name('movimentacao.reverter');
Route::get('/produto/precos/{id}', [App\Http\Controllers\ProdutoController::class, 'getPrecos']);
Route::post('/clientes', [App\Http\Controllers\ClienteController::class, 'store'])->name('clientes.store');
Route::put('/clientes/{cliente}', [App\Http\Controllers\ClienteController::class, 'update'])->name('clientes.update');
Route::post('/credito', [App\Http\Controllers\VendaController::class, 'registrarCredito'])->name('cadastro_credito');
Route::get('/api/cliente/{id}/vendas-pendentes', [App\Http\Controllers\CreditoController::class, 'listarVendasPendentes']);
Route::post('/receber-venda', [App\Http\Controllers\CreditoController::class, 'receberVenda'])->name('receber.venda');
Route::post('/vendas/reverter/{id}', [App\Http\Controllers\VendaController::class, 'reverter'])->name('venda_reverter');

Route::post('/estoque/atualizar', [App\Http\Controllers\EstoqueController::class, 'atualizar'])->name('estoque.atualizar');
