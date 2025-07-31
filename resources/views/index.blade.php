<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistema Distribuidora</title>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/correcao.css') }}">
</head>

<body>
    <div class="container">
        <!-- ===== HEADER ===== -->
        <header class="header">
            <h1>🏢 Sistema Distribuidora</h1>
            <nav class="nav-tabs">
                <button class="nav-tab active" data-section="dashboard">📊 Dashboard</button>
                <button class="nav-tab" data-section="produtos">📦 Produtos</button>
                <button class="nav-tab" data-section="estoque">📋 Estoque</button>
                <button class="nav-tab" data-section="movimentacao">🔄 Movimentação</button>
                <button class="nav-tab" data-section="venda">💰 Vendas</button>
                <button class="nav-tab" data-section="credito">💳 Crédito</button>
                <button class="nav-tab" data-section="cliente">👥 Clientes</button>
                <button class="nav-tab" data-section="caixa">💵 Fluxo de Caixa</button>
            </nav>
        </header>

        <!-- ===== DASHBOARD SECTION ===== -->
        <section id="dashboard" class="content-section active">
            <h2>📊 Dashboard</h2>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value" id="totalProdutos">{{ $totalProdutos }}</div>
                    <div class="stat-label">Total de Produtos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $produtosComEstoque }}</div>
                    <div class="stat-label">Itens em Estoque</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value {{ $valorCaixa >= 0 ? 'text-success' : 'text-danger' }}" id="saldoCaixa">
                        R$ {{ number_format($valorCaixa, 2, ',', '.') }}
                    </div>
                    <div class="stat-label">Saldo em Caixa</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="produtosBaixo">{{ $quantidadeProdutosBaixo }}</div>
                    <div class="stat-label">Produtos em Baixa</div>
                </div>
            </div>

            <h3 class="mb-lg">🚨 Produtos com Estoque Baixo (≤ 10 unidades)</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Estoque Atual</th>
                            <th>Preço</th>
                        </tr>
                    </thead>
                    <tbody id="produtosBaixoEstoque">
                        @forelse ($produtosBaixoEstoque as $produto)
                            <tr class="low-stock">
                                <td>{{ $produto->nome }}</td>
                                <td>{{ optional($produto->estoque)->quantidade ?? 0 }}</td>
                                <td>R$ {{ number_format($produto->preco_venda_atual, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">Nenhum produto com estoque baixo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===== PRODUTOS SECTION ===== -->
        <section id="produtos" class="content-section">
            <h2>📦 Gestão de Produtos</h2>

            <form id="formProduto" action="{{ route('cadastro_produto') }}" method="POST" class="mb-xl">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="nomeProduto">Nome do Produto</label>
                        <input type="text" id="nomeProduto" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label for="codigoProduto">Código</label>
                        <input type="text" id="codigoProduto" name="codigo" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="precoCompra">Preço de Compra (R$)</label>
                        <input type="number" id="precoCompra" name="compra" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="precoVenda">Preço de Venda (R$)</label>
                        <input type="number" id="precoVenda" name="venda" step="0.01" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="categoriaProduto">Categoria</label>
                        <input type="text" id="categoriaProduto" name="categoria" required>
                    </div>
                    <div class="form-group">
                        <label for="precoFardo">Preço de Venda Fardo (R$)</label>
                        <input type="number" id="precoFardo" name="venda_fardo" step="0.01">
                    </div>
                </div>
                <div class="form-group form-full">
                    <label for="descricaoProduto">Descrição</label>
                    <textarea id="descricaoProduto" name="descricao" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">💾 Cadastrar Produto</button>
            </form>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="listaProdutos">
                        @foreach ($produtos as $produto)
                            <tr>
                                <td>{{ $produto->codigo }}</td>
                                <td>{{ $produto->nome }}</td>
                                <td>{{ $produto->categoria }}</td>
                                <td>R$ {{ number_format($produto->preco_venda_atual, 2, ',', '.') }}</td>
                                <td>
                                    <button class="btn-edit" onclick="editarProduto({{ $produto->id }})">
                                        ✏️ Editar
                                    </button>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===== ESTOQUE SECTION ===== -->
        <section id="estoque" class="content-section">
            <h2>📋 Controle de Estoque</h2>

            <form id="formEstoque" action="{{ route('cadastro_estoque') }}" method="POST" class="mb-xl">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="produto_id_estoque">Produto</label>
                        <select id="produto_id_estoque" name="produto_id" required>
                            <option value="" disabled selected>Selecione um produto</option>
                            @foreach ($produtos as $produto)
                                <option value="{{ $produto->id }}">{{ $produto->nome }} ({{ $produto->codigo }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantidade_estoque">Quantidade</label>
                        <input type="number" id="quantidade_estoque" name="quantidade" min="0" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-success">➕ Atualizar Estoque</button>
            </form>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Valor Total</th>
                            <th>Status</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody id="listaEstoque">
                        @foreach ($produtos as $produto)
                            @php
                                $quantidade = optional($produto->estoque)->quantidade ?? 0;
                                $preco = $produto->preco_venda_atual ?? 0;
                                $valorTotal = $quantidade * $preco;
                                $status = $quantidade <= 10 ? 'Baixo' : 'OK';
                            @endphp
                            <tr class="{{ $quantidade <= 10 ? 'low-stock' : '' }}">
                                <td>{{ $produto->codigo }}</td>
                                <td>{{ $produto->nome }}</td>
                                <td>{{ $quantidade }}</td>
                                <td>R$ {{ number_format($valorTotal, 2, ',', '.') }}</td>
                                <td class="{{ $quantidade <= 10 ? 'text-danger' : 'text-success' }}">
                                    {{ $status }}
                                </td>
                                <td>
                                    <button class="btn-edit" onclick="editarEstoque({{ $produto->id }})">
                                        ✏️ Editar
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===== MOVIMENTAÇÃO SECTION ===== -->
        <section id="movimentacao" class="content-section">
            <h2>🔄 Movimentação de Estoque</h2>

            <form method="POST" action="{{ route('cadastro_movimentacao') }}" id="formMovimentacao"
                class="mb-xl">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="produto_id_mov">Produto</label>
                        <select id="produto_id_mov" name="produto_id" required>
                            <option value="" disabled selected>Selecione um produto</option>
                            @foreach ($produtos as $produto)
                                <option value="{{ $produto->id }}">{{ $produto->nome }} ({{ $produto->codigo }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tipoMovimentacao">Tipo</label>
                        <select name="tipo" id="tipoMovimentacao" required>
                            <option value="">Selecione o tipo</option>
                            <option value="entrada">📥 Entrada</option>
                            <option value="saida">📤 Saída</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="quantidadeMovimentacao">Quantidade</label>
                        <input type="number" name="quantidade" id="quantidadeMovimentacao" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="valorMovimentacao">Valor Unitário (R$)</label>
                        <input type="number" name="preco_unitario" id="valorMovimentacao" step="0.01" required>
                    </div>
                </div>
                <div class="form-group form-full">
                    <label for="observacaoMovimentacao">Observação</label>
                    <input type="text" id="observacaoMovimentacao" name="observacao"
                        placeholder="Motivo da movimentação">
                </div>
                <button type="submit" class="btn btn-success">✅ Registrar Movimentação</button>
            </form>

            <h3 class="mb-lg">📄 Histórico de Movimentações</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Produto</th>
                            <th>Tipo</th>
                            <th>Quantidade</th>
                            <th>Valor Unit.</th>
                            <th>Total</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody id="listaMovimentacoes">
                        @foreach ($movimentacoes as $mov)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($mov->data)->format('d/m/Y H:i') }}</td>
                                <td>{{ $mov->produto->nome ?? 'Produto removido' }}</td>
                                <td>{{ ucfirst($mov->tipo) }}</td>
                                <td>{{ $mov->quantidade }}</td>
                                <td>R$ {{ number_format($mov->preco_unitario, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($mov->total, 2, ',', '.') }}</td>
                                <td>
                                    <form action="{{ route('movimentacao.reverter', $mov->id) }}" method="POST"
                                        onsubmit="return confirm('Tem certeza que deseja reverter esta movimentação?');">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-sm">↩️ Reverter</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===== VENDAS SECTION ===== -->
        <section id="venda" class="content-section">
            <h2>💰 Gestão de Vendas</h2>

            <form method="POST" action="{{ route('cadastro_venda') }}" id="formVenda" class="mb-xl">
                @csrf
                <div id="itensVenda">
                    <!-- Itens serão adicionados dinamicamente -->
                </div>

                <button type="button" class="btn btn-secondary mb-lg" id="addItem">
                    ➕ Adicionar Produto
                </button>

                <div class="form-group form-full">
                    <label for="observacoes_venda">Observações</label>
                    <input type="text" name="observacoes" id="observacoes_venda"
                        placeholder="Observações da venda">
                </div>

                <button type="button" class="btn btn-success" id="abrirConfirmacao">
                    ✅ Registrar Venda
                </button>
            </form>

            <h3 class="mb-lg">📄 Histórico de Vendas</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Número</th>
                            <th>Status</th>
                            <th>Valor Compra</th>
                            <th>Valor Venda</th>
                            <th>Lucro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="listaVenda">
                        @foreach ($vendas as $venda)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y H:i') }}</td>
                                <td>#{{ $venda->numero_venda }}</td>
                                <td>{{ ucfirst($venda->status) }}</td>
                                <td>R$ {{ number_format($venda->total_custo, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($venda->total_venda, 2, ',', '.') }}</td>
                                <td
                                    class="{{ strtolower($venda->status) === 'concluida' ? 'text-success' : 'text-danger' }}">
                                    R$ {{ number_format($venda->total_venda - $venda->total_custo, 2, ',', '.') }}
                                </td>
                                <td>
                                    <button class="btn btn-danger btn-sm btn-reverter" data-id="{{ $venda->id }}">
                                        🔁 Reverter
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===== CRÉDITO SECTION ===== -->
        <section id="credito" class="content-section">
            <h2>💳 Módulo de Crédito</h2>

            <form method="POST" action="{{ route('cadastro_credito') }}" id="formCredito" class="mb-xl">
                @csrf
                <div id="itensCredito">
                    <!-- Itens serão adicionados dinamicamente -->
                </div>

                <button type="button" class="btn btn-secondary mb-lg" id="addItemCredito">
                    ➕ Adicionar Produto
                </button>

                <div class="form-group">
                    <label for="cliente_id_credito">Cliente</label>
                    <select name="cliente_id" id="cliente_id_credito" required>
                        <option value="" disabled selected>Selecione um cliente</option>
                        @foreach ($clientes as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <input type="hidden" name="tipo_venda" value="credito">

                <button type="button" class="btn btn-warning" id="registrarCredito">
                    🟡 Registrar Crédito
                </button>
            </form>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Crédito Devedor</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clientesComCredito as $cliente)
                            <tr>
                                <td>{{ $cliente['nome'] }}</td>
                                <td>R$ {{ number_format($cliente['credito'], 2, ',', '.') }}</td>
                                <td>
                                    <button class="btn btn-primary btn-sm abrirModalCredito"
                                        data-cliente-id="{{ $cliente['id'] }}"
                                        data-cliente-nome="{{ $cliente['nome'] }}">
                                        📄 Ver Detalhes
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">Nenhum cliente com crédito pendente.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===== CLIENTES SECTION ===== -->
        <section id="cliente" class="content-section">
            <h2>👥 Gestão de Clientes</h2>

            <form action="{{ route('clientes.store') }}" method="POST" class="mb-xl">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="nomeCliente">Nome do Cliente</label>
                        <input type="text" id="nomeCliente" name="nome" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">💾 Cadastrar Cliente</button>
            </form>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($clientes as $cliente)
                            <tr>
                                <td>{{ $cliente->nome }}</td>
                                <td>
                                    <button class="btn-edit btn-sm"
                                        onclick="abrirModalCliente({{ $cliente->id }}, '{{ $cliente->nome }}')">
                                        ✏️ Editar
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===== FLUXO DE CAIXA SECTION ===== -->
        <section id="caixa" class="content-section">
            <h2>💵 Fluxo de Caixa</h2>

            <form id="formCaixa" action="{{ route('cadastro_caixa') }}" method="POST" class="mb-xl">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo_caixa">Tipo</label>
                        <select name="tipo" id="tipo_caixa" required>
                            <option value="">Selecione o tipo</option>
                            <option value="entrada">💵 Entrada</option>
                            <option value="saida">💸 Saída</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="valor_caixa">Valor (R$)</label>
                        <input type="number" id="valor_caixa" name="valor" step="0.01" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="categoria_caixa">Categoria</label>
                        <select id="categoria_caixa" name="categoria" required>
                            <option value="">Selecione a categoria</option>
                            <option value="venda">💰 Venda de Produtos</option>
                            <option value="compra">📦 Compra de Produtos</option>
                            <option value="despesa_operacional">🏢 Despesa Operacional</option>
                            <option value="despesa_administrativa">📋 Despesa Administrativa</option>
                            <option value="prolabore">👤 Pró-labore</option>
                            <option value="investimento">📈 Investimento</option>
                            <option value="emprestimo">🏦 Empréstimo</option>
                            <option value="impostos">📋 Impostos</option>
                            <option value="outros">📝 Outros</option>
                        </select>
                    </div>
                </div>
                <div class="form-group form-full">
                    <label for="descricaoCaixa">Descrição</label>
                    <input type="text" name="observacao" id="descricaoCaixa" required
                        placeholder="Descrição da movimentação">
                </div>
                <button type="submit" class="btn btn-warning">💾 Registrar no Caixa</button>
            </form>

            <!-- Filtros -->
            <div class="filter-section">
                <h3 class="mb-lg">📊 Filtros e Resumo</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="dataInicio">Data Início</label>
                        <input type="date" id="dataInicio">
                    </div>
                    <div class="form-group">
                        <label for="dataFim">Data Fim</label>
                        <input type="date" id="dataFim">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="filtroCategoria">Categoria</label>
                        <select id="filtroCategoria">
                            <option value="">Todas as categorias</option>
                            <option value="venda">💰 Vendas</option>
                            <option value="compra">📦 Compras</option>
                            <option value="despesa_operacional">🏢 Despesas Operacionais</option>
                            <option value="despesa_administrativa">📋 Despesas Administrativas</option>
                            <option value="prolabore">👤 Pró-labore</option>
                            <option value="investimento">📈 Investimentos</option>
                            <option value="emprestimo">🏦 Empréstimos</option>
                            <option value="impostos">📋 Impostos</option>
                            <option value="outros">📝 Outros</option>
                        </select>
                    </div>
                    <div class="form-group flex-center gap-sm">
                        <button type="button" class="btn btn-primary" onclick="filtrarCaixa()">🔍 Filtrar</button>
                        <button type="button" class="btn btn-secondary" onclick="limparFiltros()">🗑️
                            Limpar</button>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card entrada">
                    <div class="stat-value" id="totalEntradas">R$ 0,00</div>
                    <div class="stat-label">Total de Entradas</div>
                </div>
                <div class="stat-card saida">
                    <div class="stat-value" id="totalSaidas">R$ 0,00</div>
                    <div class="stat-label">Total de Saídas</div>
                </div>
                <div class="stat-card saldo">
                    <div class="stat-value" id="saldoPeriodo">R$ 0,00</div>
                    <div class="stat-label">Saldo do Período</div>
                </div>
            </div>

            <!-- Resumo por categoria -->
            <div class="resumo-categorias">
                <h4>📊 Resumo por Categoria</h4>
                <div id="resumoCategorias" class="categorias-grid"></div>
            </div>

            <!-- Tabela de movimentações -->
            <h3 class="mb-lg">📄 Movimentações do Caixa</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Categoria</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="listaCaixa"></tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- ===== MODALS ===== -->
    @include('modals.ver_detalhes')
    @include('modals.confirma_venda')
    @include('modals.confirma_credito')
    @include('modals.editar_cliente')
    @include('modals.edicao_produto')
    @include('modals.edicao_estoque')



    <!-- Modal de Mensagens -->
    <div id="msgModal" class="msg-modal">
        <div class="msg-modal-content" id="msgModalContent">
            <span id="msgModalClose" class="msg-modal-close">&times;</span>
            <p id="msgModalText"></p>
        </div>
    </div>

    <!-- ===== SCRIPTS ===== -->
    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ asset('js/core/navigation.js') }}"></script>
    <script src="{{ asset('js/core/modal-system.js') }}"></script>
    <script src="{{ asset('js/modules/produtos.js') }}"></script>
    <script src="{{ asset('js/modules/vendas.js') }}"></script>
    <script src="{{ asset('js/modules/credito.js') }}"></script>
    <script src="{{ asset('js/modules/clientes.js') }}"></script>
    <script src="{{ asset('js/modules/caixa.js') }}"></script>
    <script src="{{ asset('js/modules/messages.js') }}"></script>
    <script src="{{ asset('js/modules/estoque.js') }}"></script>

    <script src="{{ asset('js/app.js') }}"></script>

    <!-- Dados para os scripts -->
    <script>
        window.APP_DATA = {
            produtos: @json($produtos),
            clientes: @json($clientes),
            csrfToken: '{{ csrf_token() }}'
        };
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Aguardar um pouco para garantir que o sistema de mensagens foi inicializado
            setTimeout(() => {
                console.log('🔍 Verificando mensagens do Laravel...');

                // Verificar se há mensagens de sessão do Laravel
                @if (session('success'))
                    if (window.showSuccess) {
                        window.showSuccess("{{ session('success') }}", 4000);
                        console.log('✅ Mensagem de sucesso exibida');
                    }
                @endif

                @if (session('error'))
                    if (window.showError) {
                        window.showError("{{ session('error') }}");
                        console.log('❌ Mensagem de erro exibida');
                    }
                @endif

                @if (session('warning'))
                    if (window.showWarning) {
                        window.showWarning("{{ session('warning') }}", 5000);
                        console.log('⚠️ Mensagem de aviso exibida');
                    }
                @endif

                @if (session('info'))
                    if (window.showInfo) {
                        window.showInfo("{{ session('info') }}", 3000);
                        console.log('ℹ️ Mensagem de info exibida');
                    }
                @endif

                @if ($errors->any())
                    if (window.showError) {
                        let erros = "";
                        @foreach ($errors->all() as $error)
                            erros += "• {{ $error }}\n";
                        @endforeach
                        window.showError(erros.trim());
                        console.log('❌ Erros de validação exibidos');
                    }
                @endif

            }, 500); // Aguarda 500ms para garantir que tudo foi carregado
        });
    </script>
</body>

</html>
