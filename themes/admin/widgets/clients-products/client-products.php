<?php $v->layout("_admin"); ?>
<!--App-Content-->
<?php if (!$clientProducts) : ?>
    <div class="app-content  my-3 my-md-5">
        <div class="side-app">
            <div class="page-header">
                <h4 class="page-title">Associar Produtos do Cliente</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/clients-products/home'); ?>">Associar Produtos do Cliente</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Criar Associação</li>
                </ol>
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
                                        <select name="id_client" class="form-control">
                                            <option value="">Selecionar</option>
                                            <?php if ($clients) : ?>
                                                <?php foreach ($clients as $client) : ?>
                                                    <option value="<?= $client->id; ?>"><?= $client->corporate_name; ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
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
                                <button type="submit" class="btn btn-success ">Criar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else : ?>
    <div class="app-content  my-3 my-md-5">
        <div class="side-app">
            <div class="page-header">
                <h4 class="page-title">Associar Produtos do Cliente</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/clients-products/home'); ?>">Associar Produtos do Cliente</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar Associação</li>
                </ol>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card m-b-20">
                        <div class="card-header">
                            <h3 class="card-title">Editar Associação</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= url('/admin/clients-products/client-products/' . $clientProducts->id); ?>" method="post">
                                <input type="hidden" name="action" value="update">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Título</label>
                                        <input type="text" class="form-control" name="title" value="<?= $clientProducts->title; ?>" placeholder="Digite seu título">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Imagem</label>
                                        <input type="file" class="form-control" name="image">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success">Atualizar</button>
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
<script>
    const selectClient = document.querySelector('[name=id_client]'),
        searchInput = document.querySelector('[name=search_product]'),
        getProducts = document.querySelector('.getProducts'),
        divProducts = document.querySelector('.products'),
        url = document.querySelector('.form-row').getAttribute('data-url')

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
                                <input type="text" class="mask-money form-control" style="max-width:140px" name="prices[${p.id}]" value="${p.value ?? ''}" placeholder="Preço">
                            </div>
                        </div>`
                    count++
                }
                if (!count) {
                    divProducts.innerHTML = '<p class="text-muted">Nenhum produto novo encontrado para esse termo.</p>'
                }
                $(".mask-money").mask('000.000.000.000.000,00', {reverse: true, placeholder: "0,00"})
                getProducts.disabled = false
            }).catch(() => { getProducts.disabled = false })
        }).catch(() => { getProducts.disabled = false })
    })
</script>
<?php $v->end('scripts'); ?>