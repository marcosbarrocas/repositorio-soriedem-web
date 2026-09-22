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
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Clientes</label>
                                        <select name="id_client" class="form-control">
                                            <option value="">Selecionar</option>
                                            <?php if ($clients) : ?>
                                                <?php foreach ($clients as $client) : ?>
                                                    <option value="<?= $client->id; ?>"><?= $client->corporate_name; ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Categorias</label>
                                        <select name="id_category" class="form-control" data-url="<?= url(); ?>">
                                            <option value="">Selecionar</option>
                                            <?php if ($categories) : ?>
                                                <?php foreach ($categories as $category) : ?>
                                                    <option value="<?= $category->id; ?>"><?= $category->title; ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Sub Categorias</label>
                                        <select name="id_subcategory" class="form-control">
                                            <option value="">Selecionar</option>
                                            <?php if ($subCategories) : ?>
                                                <?php foreach ($subCategories as $subCategory) : ?>
                                                    <option value="<?= $subCategory->id; ?>"><?= $subCategory->title; ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
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
    let selectCategory = document.querySelector('[name=id_category]'),
        selectSubCategory = document.querySelector('[name=id_subcategory]'),
        selectClient = document.querySelector('[name=id_client]')
    getProducts = document.querySelector('.getProducts'),
        divProducts = document.querySelector('.products'),
        url = selectCategory.getAttribute('data-url')

    selectCategory.addEventListener('change', () => {
        axios.post(`${url}/admin/categories/selectCategory/${selectCategory.value}`).then(function(response) {
            if (response) {
                selectSubCategory.innerHTML = ''
                for (let i = 0; i < response.data.length; i++) {
                    selectSubCategory.innerHTML += `<option value="${response.data[i].id}">${response.data[i].title}</option>`
                }
            }
        })
    })

    getProducts.addEventListener('click', () => {
        divProducts.innerHTML = ''
        getProducts.disabled = true

        axios.post(`${url}/admin/products/get-products-client/${selectClient.value}`).then((data) => {
            let clientProducts = []
            if (data.data != false) {
                for (let i = 0; i < data.data.length; i++) {
                    clientProducts.push(data.data[i].id_product)
                }
            }


            axios.post(`${url}/admin/products/get-products/${selectSubCategory.value}`).then((data) => {
                if (data) {
                    for (let j = 0; j < data.data.length; j++) {
                        if (clientProducts.indexOf(data.data[j].id) == -1) {
                            let img = null
                            if (data.data[j].photo) {
                                img = `<img src="${url}/storage/${data.data[j].photo}" alt="Foto" width="50" height="20">`
                            } else {
                                img = `<img src="${url}/themes/admin/assets/images/noimage.jpg" alt="Foto" width="50" height="50">`
                            }

                            divProducts.innerHTML += `
                                <div class="mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="products[]" value="${data.data[j].id}">
                                        ${img}
                                        <label class="form-check-label">${data.data[j].title}</label>
                                        <input type="text" class="mask-money" name="prices[]">
                                    </div>
                                </div>
                                `
                        }
                    }
                    $(".mask-money").mask('000.000.000.000.000,00', {reverse: true, placeholder: "0,00"})
                    getProducts.disabled = false
                }
            })
        })
    })
</script>
<?php $v->end('scripts'); ?>