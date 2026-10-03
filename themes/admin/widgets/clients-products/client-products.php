<?php $v->layout("_admin"); ?>
<!--App-Content-->
<?php if (!$clientProducts) : ?>
    <div class="app-content  my-3 my-md-5">
        <div class="side-app">
            <div class="page-header">
                <h4 class="page-title">Adicionar produtos</h4>
                <div class="d-flex align-items-center">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/admin/clients-products/home'); ?>">Cesta de Produtos</a></li>
                        <?php if (!empty($fixedClient)) : ?>
                            <li class="breadcrumb-item"><a href="<?= url('/admin/clients-products/list-products/' . $fixedClient->id); ?>">Detalhes</a></li>
                        <?php endif; ?>
                        <li class="breadcrumb-item active" aria-current="page">Adicionar produtos</li>
                    </ol>
                    <?= admin_back(!empty($fixedClient) ? '/admin/clients-products/list-products/' . $fixedClient->id : '/admin/clients-products/home'); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card m-b-20">
                        <div class="card-header">
                            <h3 class="card-title">Criar Associação</h3>
                        </div>
                        <div class="card-body">
                            <form class="ajax_off" action="<?= url('/admin/clients-products/client-products'); ?>" method="post">
                                <input type="hidden" name="action" value="create">
                                <div class="form-row" data-url="<?= url(); ?>">
                                    <div class="form-group col-md-6">
                                        <label>Cliente</label>
                                        <?php if (!empty($fixedClient)) : ?>
                                            <input type="hidden" name="id_client" value="<?= $fixedClient->id; ?>">
                                            <input type="text" class="form-control" readonly value="<?= (!empty($fixedClient->contact_name) ? $fixedClient->contact_name . " — " : "") . $fixedClient->corporate_name; ?>">
                                        <?php else : ?>
                                            <select name="id_client" class="form-control">
                                                <option value="">Selecionar</option>
                                                <?php if ($clients) : ?>
                                                    <?php foreach ($clients as $client) : ?>
                                                        <option value="<?= $client->id; ?>"><?= $client->corporate_name; ?></option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Buscar produto (nome ou código)</label>
                                        <input type="text" name="search_product" class="form-control" placeholder="Ex.: SACO DE LIXO ou PRD00026">
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-center">
                                        <button type="button" class="btn btn-info getProducts">BUSCAR PRODUTOS</button>
                                    </div>
                                    <div class="bg-light my-3 p-3">
                                        <div class="products d-flex flex-column justify-content-center align-items-start"></div>
                                    </div>
                                </div>
                                <div class="alert alert-warning d-none create-warning mb-3" role="alert">Busque os produtos antes de criar a associação.</div>
                                <button type="submit" class="btn btn-success ">Criar</button>
                                <?php if (!empty($fixedClient)) : ?>
                                    <a href="<?= url('/admin/clients-products/list-products/' . $fixedClient->id); ?>" class="btn btn-light">Voltar</a>
                                <?php else : ?>
                                    <a href="<?= url('/admin/clients-products/home'); ?>" class="btn btn-light">Voltar</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <?php if (!empty($fixedClient)) : ?>
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Produtos já na cesta</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($basketProducts)) : ?>
                                    <p class="text-muted mb-0">Este cliente ainda não tem produtos na cesta.</p>
                                <?php else : ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered border-top mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Produto</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($basketProducts as $basketItem) : ?>
                                                    <?php $basketProduct = $basketItem->getProduct(); ?>
                                                    <tr>
                                                        <th scope="row"><?= $basketProduct ? $basketProduct->code : ""; ?></th>
                                                        <td><?= $basketProduct ? $basketProduct->title : "Produto não encontrado"; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php else : ?>
    <div class="app-content  my-3 my-md-5">
        <div class="side-app">
            <div class="page-header">
                <h4 class="page-title">Associar Produtos do Cliente</h4>
                <div class="d-flex align-items-center">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/admin/clients-products/list-products/' . $clientProducts->id_client); ?>">Detalhes da cesta</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Editar Associação</li>
                    </ol>
                    <?= admin_back('/admin/clients-products/list-products/' . $clientProducts->id_client); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card m-b-20">
                        <div class="card-header">
                            <h3 class="card-title">Editar Associação</h3>
                        </div>
                        <div class="card-body">
                            <?php $product = $clientProducts->getProduct(); ?>
                            <form action="<?= url('/admin/clients-products/client-products/' . $clientProducts->id); ?>" method="post">
                                <input type="hidden" name="action" value="update">
                                <div class="form-row">
                                    <div class="form-group col-md-12">
                                        <label>Produto</label>
                                        <input type="text" class="form-control" readonly value="<?= $product ? $product->code . " — " . $product->title : "Produto não encontrado"; ?>">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Valor</label>
                                        <input type="text" class="form-control mask-money" name="price" value="<?= money_br((string) $clientProducts->price); ?>" placeholder="Valor">
                                    </div>
                                    <div class="form-group col-md-6 d-flex align-items-end">
                                        <label class="mb-2">
                                            <input type="checkbox" name="required" value="1" <?= !empty($clientProducts->required) ? "checked" : ""; ?>>
                                            Obrigatório
                                        </label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success">Salvar</button>
                                <a href="<?= url('/admin/clients-products/list-products/' . $clientProducts->id_client); ?>" class="btn btn-light">Voltar</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<!--/App-Content-->
<?php $v->start('scripts'); ?>
<?php if (!$clientProducts) : ?>
<script>
    const selectClient = document.querySelector('[name=id_client]'),
        searchInput = document.querySelector('[name=search_product]'),
        getProducts = document.querySelector('.getProducts'),
        divProducts = document.querySelector('.products'),
        createForm = document.querySelector('form.ajax_off'),
        createWarning = document.querySelector('.create-warning'),
        url = document.querySelector('.form-row').getAttribute('data-url')

    let productsSearched = false

    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, s => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[s]))
    }

    getProducts.addEventListener('click', () => {
        const term = searchInput.value.trim()
        if (!selectClient.value) {
            alert('Selecione um cliente primeiro.')
            return
        }
        if (term.length < 2) {
            alert('Digite ao menos 2 caracteres para buscar.')
            return
        }

        divProducts.innerHTML = ''
        createWarning.classList.add('d-none')
        getProducts.disabled = true

        // 1) produtos ja associados ao cliente (para excluir da busca)
        axios.post(`${url}/admin/products/get-products-client/${selectClient.value}`).then((res) => {
            const already = (res.data && res.data !== false)
                ? res.data.map(cp => Number(cp.id_product))
                : []

            // 2) busca produtos por nome/codigo
            const form = new FormData()
            form.append('q', term)
            axios.post(`${url}/admin/products/search`, form).then((res2) => {
                const list = res2.data || []
                let count = 0
                for (const p of list) {
                    if (already.indexOf(Number(p.id)) !== -1) continue
                    const img = p.photo
                        ? `<img src="${p.photo.startsWith('http') ? p.photo : url + '/storage/' + p.photo}" alt="Foto" width="50" height="50">`
                        : `<img src="${url}/themes/admin/assets/images/noimage.jpg" alt="Foto" width="50" height="50">`
                    divProducts.innerHTML += `
                        <div class="mb-2 w-100">
                            <div class="form-check d-flex align-items-center" style="gap:10px">
                                <input class="form-check-input" type="checkbox" name="products[]" value="${p.id}">
                                ${img}
                                <label class="form-check-label flex-grow-1">${escapeHtml(p.code)} — ${escapeHtml(p.title)}</label>
                                <input type="text" class="mask-money form-control" style="max-width:140px" name="prices[${p.id}]" value="${escapeHtml(p.value ?? '')}" placeholder="Valor" title="Valor especial deste produto para o cliente">
                                <label class="mb-0 d-flex align-items-center" style="gap:6px; white-space:nowrap">
                                    <input type="checkbox" name="required[${p.id}]" value="1"> Obrigatório
                                </label>
                            </div>
                        </div>`
                    count++
                }
                if (!count) {
                    divProducts.innerHTML = '<p class="text-muted">Nenhum produto novo encontrado para esse termo.</p>'
                }
                $(".mask-money").mask('000.000.000.000.000,00', {reverse: true, placeholder: "0,00"})
                productsSearched = true
                getProducts.disabled = false
            }).catch(() => { getProducts.disabled = false })
        }).catch(() => { getProducts.disabled = false })
    })

    createForm.addEventListener('submit', (event) => {
        if (!productsSearched) {
            event.preventDefault()
            createWarning.classList.remove('d-none')
        }
    })
</script>
<?php else : ?>
<script>
    $(".mask-money").mask('000.000.000.000.000,00', {reverse: true, placeholder: "0,00"});
</script>
<?php endif; ?>
<?php $v->end('scripts'); ?>