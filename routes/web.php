<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\IndexController::class, 'index'])->name('index');
Route::post('/cadastroProduto', [\App\Http\Controllers\ProdutoController::class, 'cadastrar'])->name('cadastro_produto');
Route::post('/cadastroEstoque', [\App\Http\Controllers\EstoqueController::class, 'cadastrar'])->name('cadastro_estoque');
Route::post('/produtos/{id}', [\App\Http\Controllers\ProdutoController::class, 'editar'])->name('produtos_editar');
Route::post('/produtos/desativar/{id}', [\App\Http\Controllers\ProdutoController::class, 'desativar'])->name('produtos_desativar');
