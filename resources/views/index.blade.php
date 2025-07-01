<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Distribuidora</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🏢 Sistema Distribuidora</h1>
            <div class="nav-tabs">
                <button class="nav-tab active" data-section="dashboard">📊 Dashboard</button>
                <button class="nav-tab" data-section="produtos">📦 Produtos</button>
                <button class="nav-tab" data-section="estoque">📋 Estoque</button>
                <button class="nav-tab" data-section="movimentacao">🔄 Movimentação</button>
                <button class="nav-tab" data-section="venda">📦 Vendas</button>
                <button class="nav-tab" data-section="caixa">💰 Fluxo de Caixa</button>
            </div>
        </div>

        <!-- Dashboard -->
        <div id="dashboard" class="content-section active">
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
                    @php
                        $corSaldo = $valorCaixa >= 0 ? 'text-green-600' : 'text-red-600';
                    @endphp

                    <div class="stat-value {{ $corSaldo }}" id="saldoCaixa">
                        R$ {{ number_format($valorCaixa, 2, ',', '.') }}
                    </div>
                    <div class="stat-label">Saldo em Caixa</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="produtosBaixo">{{ $quantidadeProdutosBaixo }}</div>
                    <div class="stat-label">Produtos em Baixa</div>
                </div>
            </div>

            <h3>🚨 Produtos com Estoque Baixo (≤ 10 unidades)</h3>
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
        </div>

        <!-- Produtos -->
        <div id="produtos" class="content-section">
            <h2>📦 Cadastro de Produtos</h2>

            <form id="formProduto" action="{{ route('cadastro_produto') }}" method="POST">
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
                        <label for="precoProduto">Preço de Compra (R$)</label>
                        <input type="number" id="precoCompra" name="compra" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="precoProduto">Preço de Venda (R$)</label>
                        <input type="number" id="precoVenda" name="venda" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="categoriaProduto">Categoria</label>
                        <input type="text" id="categoriaProduto" name="categoria" required>
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
                                    <button class="btn-edit btn-icon" onclick="editarProduto({{ $produto->id }})">✏️
                                        Editar</button>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Estoque -->
        <div id="estoque" class="content-section">
            <h2>📋 Controle de Estoque</h2>

            <form id="formEstoque" action="{{ route('cadastro_estoque') }}" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="produto_id">Produto</label>
                        <select id="produto_id" name="produto_id" required>
                            <option value="" disabled selected>Selecione um produto</option>
                            @foreach ($produtos as $produto)
                                <option value="{{ $produto->id }}">{{ $produto->nome }} ({{ $produto->codigo }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="quantidade">Quantidade</label>
                        <input type="number" id="quantidade" name="quantidade" min="0" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">➕ Cadastrar Estoque</button>
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
                                    {{ $status }}</td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>

        <!-- Movimentação -->
        <div id="movimentacao" class="content-section">
            <h2>🔄 Movimentação de Estoque</h2>

            <form method="POST" action="{{ route('cadastro_movimentacao') }}" id="formMovimentacao">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="produto_id">Produto</label>
                        <select id="produto_id" name="produto_id" required>
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
                        <input type="number" name="preco_unitario" id="precoVenda" step="0.01" required>
                    </div>
                </div>
                <div class="form-group form-full">
                    <label for="observacaoMovimentacao">Observação</label>
                    <input type="text" id="observacaoMovimentacao" name="observacao"
                        placeholder="Motivo da movimentação">
                </div>
                <button type="submit" class="btn btn-success">✅ Registrar Movimentação</button>
            </form>

            <h3>📄 Histórico de Movimentações</h3>
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
                            <th>Observação</th>
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
                                <td>{{ $mov->observacao }}</td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>

        <!-- Venda -->
        <div id="venda" class="content-section">
            <h2>🔄 Venda de Estoque</h2>

            <form method="POST" action="{{ route('cadastro_venda') }}" id="formVenda">
                @csrf
                <div id="itensVenda">
                    <div class="form-row item-venda">
                        <div class="form-group">
                            <label>Produto</label>
                            <select name="itens[0][produto_id]" required>
                                <option value="" disabled selected>Selecione um produto</option>
                                @foreach ($produtos as $produto)
                                    <option value="{{ $produto->id }}">{{ $produto->nome }}
                                        ({{ $produto->codigo }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Quantidade</label>
                            <input type="number" name="itens[0][quantidade]" min="1" required>
                        </div>
                        <button type="button" class="btn btn-danger remove-item">🗑</button>
                    </div>
                </div>

                <button type="button" class="btn btn-secondary" id="addItem" style="margin-top: 15px;">➕
                    Adicionar
                    Produto</button>

                <div class="form-group form-full">
                    <label>Observações</label>
                    <input type="text" name="observacoes" placeholder="Motivo da venda">
                </div>
                <button type="submit" class="btn btn-success">✅ Registrar Venda</button>
            </form>


            <h3>📄 Histórico de Vendas</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Produto</th>
                            <th>Tipo</th>
                            <th>Quantidade</th>
                            <th>Valor Compra</th>
                            <th>Valor Venda</th>
                            <th>Observação</th>
                        </tr>
                    </thead>
                    <tbody id="listaVenda">
                        @foreach ($vendas as $venda)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y H:i') }}</td>
                                <td>#{{ $venda->numero_venda }}</td>
                                <td>{{ ucfirst($venda->status) }}</td>
                                <td>-</td> {{-- Quantidade total não está no model, precisa calcular via relacionamento --}}
                                <td>R$ {{ number_format($venda->total_custo, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($venda->total_venda, 2, ',', '.') }}</td>
                                <td>{{ $venda->observacoes ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>


        <!-- Fluxo de Caixa -->
        <div id="caixa" class="content-section">
            <h2>💰 Fluxo de Caixa</h2>

            <form id="formCaixa" action="{{ route('cadastro_caixa') }}" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo">Tipo</label>
                        <select name="tipo" id="tipo" required>
                            <option value="">Selecione o tipo</option>
                            <option value="entrada">💵 Entrada</option>
                            <option value="saida">💸 Saída</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="valor">Valor (R$)</label>
                        <input type="number" id="valor" name="valor" step="0.01" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="categoria">Categoria</label>
                        <select id="categoria" name="categoria" required>
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

            <h3>📊 Filtros e Resumo</h3>
            <div class="filter-section">
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
                    <div class="form-group" style="display: flex; align-items: end;">
                        <button type="button" class="btn btn-primary" onclick="filtrarCaixa()">🔍 Filtrar</button>
                        <button type="button" class="btn btn-secondary" onclick="limparFiltros()"
                            style="margin-left: 10px;">🗑️ Limpar</button>
                    </div>
                </div>
            </div>

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

            <div class="resumo-categorias">
                <h4>📊 Resumo por Categoria</h4>
                <div id="resumoCategorias" class="categorias-grid"></div>
            </div>

            <h3>📄 Movimentações do Caixa</h3>
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
        </div>


        <script src="{{ asset('js/jquery.js') }}"></script>

        <script src="{{ asset('js/script.js') }}"></script>
        <script src="{{ asset('js/bloqueio.js') }}"></script>


        <script>
            function filtrarCaixa() {
                const dataInicio = document.getElementById('dataInicio').value;
                const dataFim = document.getElementById('dataFim').value;
                const categoria = document.getElementById('filtroCategoria').value;

                fetch(`/caixa/filtro?dataInicio=${dataInicio}&dataFim=${dataFim}&categoria=${categoria}`)
                    .then(response => response.json())
                    .then(data => {
                        atualizarTotais(data);
                        listarMovimentacoes(data.dados);
                        listarResumoCategorias(data.resumo);
                    });
            }

            function atualizarTotais(data) {
                document.getElementById('totalEntradas').textContent = formatarValor(data.entradas);
                document.getElementById('totalSaidas').textContent = formatarValor(data.saidas);
                document.getElementById('saldoPeriodo').textContent = formatarValor(data.saldo);
            }

            function listarMovimentacoes(dados) {
                const tbody = document.getElementById('listaCaixa');
                tbody.innerHTML = '';

                dados.forEach(item => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${item.data}</td>
                        <td>${item.tipo}</td>
                        <td>${item.categoria}</td>
                        <td>${item.descricao}</td>
                        <td>${formatarValor(item.valor)}</td>
                        <td><!-- Ações aqui, como Editar/Excluir --></td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            function listarResumoCategorias(resumo) {
                const container = document.getElementById('resumoCategorias');
                container.innerHTML = '';

                for (let categoria in resumo) {
                    const div = document.createElement('div');
                    div.classList.add('categoria-resumo');
                    div.innerHTML = `<strong>${categoria}:</strong> ${formatarValor(resumo[categoria])}`;
                    container.appendChild(div);
                }
            }

            function limparFiltros() {
                document.getElementById('dataInicio').value = '';
                document.getElementById('dataFim').value = '';
                document.getElementById('filtroCategoria').value = '';
                filtrarCaixa(); // recarrega tudo
            }

            function formatarValor(valor) {
                return new Intl.NumberFormat('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                }).format(valor);
            }

            // Chamada inicial
            window.onload = filtrarCaixa;
        </script>


        <div id="msgModal" class="msg-modal">
            <div class="msg-modal-content" id="msgModalContent">
                <span id="msgModalClose" class="msg-modal-close">&times;</span>
                <p id="msgModalText"></p>
            </div>
        </div>

        <script>
            let index = 1;

            document.getElementById('addItem').addEventListener('click', function() {
                const container = document.getElementById('itensVenda');
                const newRow = document.createElement('div');
                newRow.classList.add('form-row', 'item-venda');
                newRow.innerHTML = `
                    <div class="form-group">
                        <label>Produto</label>
                        <select name="itens[${index}][produto_id]" required>
                            <option value="" disabled selected>Selecione um produto</option>
                            @foreach ($produtos as $produto)
                                <option value="{{ $produto->id }}">{{ $produto->nome }} ({{ $produto->codigo }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Quantidade</label>
                        <input type="number" name="itens[${index}][quantidade]" min="1" required>
                    </div>
                    <button type="button" class="btn btn-danger remove-item">🗑</button>
                `;
                container.appendChild(newRow);
                index++;
            });

            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-item')) {
                    e.target.closest('.item-venda').remove();
                }
            });
        </script>


        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('msgModal');
                const texto = document.getElementById('msgModalText');
                const btnFechar = document.getElementById('msgModalClose');
                const modalContent = document.getElementById('msgModalContent');

                @if (session('success'))
                    texto.textContent = "{{ session('success') }}";
                    modalContent.classList.add('msg-modal-success');
                    modal.style.display = 'flex';
                @elseif ($errors->any())
                    // Pega todas as mensagens e junta em string separada por quebra de linha
                    let erros = "";
                    @foreach ($errors->all() as $error)
                        erros += "- {{ $error }}\n";
                    @endforeach

                    texto.textContent = erros.trim();
                    modalContent.classList.add('msg-modal-error');
                    modal.style.display = 'flex';
                @elseif (session('error'))
                    texto.textContent = "{{ session('error') }}";
                    modalContent.classList.add('msg-modal-error');
                    modal.style.display = 'flex';
                @endif

                btnFechar.onclick = () => {
                    modal.style.display = 'none';
                };

                window.onclick = (event) => {
                    if (event.target == modal) {
                        modal.style.display = 'none';
                    }
                };
            });
        </script>



        @include('modals.edicao_produto')

        <script>
            const produtos = @json($produtos);

            function editarProduto(id) {
                const produto = produtos.find(p => p.id === id);
                if (!produto) return;

                // Formulário de edição
                const formEditar = document.getElementById('formEditarProduto');
                formEditar.action = `/produtos/${produto.id}`;

                document.getElementById('editProdutoId').value = produto.id;
                document.getElementById('editNome').value = produto.nome;
                document.getElementById('editCodigo').value = produto.codigo;
                document.getElementById('editCategoria').value = produto.categoria;
                document.getElementById('editCompra').value = produto.preco_compra_atual;
                document.getElementById('editVenda').value = produto.preco_venda_atual;

                // Formulário de exclusão
                const formExcluir = document.getElementById('formExcluirProduto');
                formExcluir.action = `/produtos/desativar/${produto.id}`;

                // Exibir modal
                document.getElementById('modalEditarProduto').style.display = 'flex';
            }

            function fecharModal() {
                document.getElementById('modalEditarProduto').style.display = 'none';
            }
        </script>


</body>

</html>
