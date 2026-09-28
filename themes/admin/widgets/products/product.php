<?php $v->layout("_admin"); ?>
<!--App-Content-->
<?php if (!$product) : ?>
    <div class="app-content  my-3 my-md-5">
        <div class="side-app">
            <div class="page-header">
                <h4 class="page-title">Produtos</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/products/home'); ?>">Produtos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Criar Produto</li>
                </ol>
                <?= admin_back('/admin/products/home'); ?>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card m-b-20">
                        <div class="card-header">
                            <h3 class="card-title">Criar Produto</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= url('/admin/products/product'); ?>" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="create">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Codigo</label>
                                        <input type="text" class="form-control" name="code" placeholder="Digite o codigo*">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Título</label>
                                        <input type="text" class="form-control" name="title" placeholder="Digite seu título">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Valor</label>
                                        <input type="text" class="form-control mask-money" name="value" placeholder="Digite seu valor">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Estoque</label>
                                        <input type="text" class="form-control" name="stock" placeholder="Digite seu estoque">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Categoria</label>
                                        <select name="id_category" class="form-control" data-url="<?= url(); ?>">
                                            <option value="">Selecionar Categoria</option>
                                            <?php if ($categories) : ?>
                                                <?php foreach ($categories as $category) : ?>
                                                    <option value="<?= $category->id; ?>"><?= $category->title; ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Foto</label>
                                        <input type="file" class="form-control" name="photo">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>B.T</label>
                                        <input type="file" class="form-control" name="file_bt">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label> F.I.S.P.Q</label>
                                        <input type="file" class="form-control" name="file_fispq">
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label>Descrição</label>
                                        <textarea name="description" cols="30" rows="10" class="form-control"></textarea>
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
                <h4 class="page-title">Produtos</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/products/home'); ?>">Produtos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar Produto</li>
                </ol>
                <?= admin_back('/admin/products/home'); ?>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card m-b-20">
                        <div class="card-header">
                            <h3 class="card-title">Editar Produto</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= url('/admin/products/product/' . $product->id); ?>" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Codigo</label>
                                        <input type="text" class="form-control" name="code" value="<?= $product->code; ?>" placeholder="Digite o codigo*">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Título</label>
                                        <input type="text" class="form-control" name="title" value="<?= $product->title; ?>" placeholder="Digite seu título">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label>Valor</label>
                                        <input type="text" class="form-control mask-money" name="value" value="<?= money_br($product->value); ?>" placeholder="Digite seu valor">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label>Estoque</label>
                                        <input type="text" class="form-control" name="stock" value="<?= $product->stock; ?>" placeholder="Digite seu estoque">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Categoria</label>
                                        <select name="id_category" class="form-control" data-url="<?= url(); ?>">
                                            <option value="">Selecionar Categoria</option>
                                            <?php if ($categories) : ?>
                                                <?php foreach ($categories as $category) : ?>
                                                    <option value="<?= $category->id; ?>" <?= ((string) $product->id_category === (string) $category->id ? "selected" : ""); ?>><?= $category->title; ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Foto</label>
                                        <input type="file" class="form-control" name="photo">
                                        <?php if (!empty($product->photo)) : ?>
                                            <a href="<?= url("/storage/{$product->photo}"); ?>" target="_blank" rel="noopener" class="d-inline-block mt-2">
                                                <img src="<?= url("/storage/{$product->photo}"); ?>" alt="Foto de <?= $product->title; ?>" style="width: 140px; height: 140px; object-fit: contain; background: #f7f7f7; border: 1px solid #e6e6e6; border-radius: 6px; padding: 6px;">
                                            </a>
                                            <small class="d-block mt-1"><a href="<?= url("/storage/{$product->photo}"); ?>" target="_blank" rel="noopener">Ver foto</a></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>B.T</label>
                                        <input type="file" class="form-control" name="file_bt">
                                        <?php if (!empty($product->file_bt)) : ?>
                                            <small class="d-block mt-2 text-success">Arquivo enviado. <a href="<?= url("/storage/{$product->file_bt}"); ?>" target="_blank" rel="noopener">Abrir B.T.</a></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>F.I.S.P.Q</label>
                                        <input type="file" class="form-control" name="file_fispq">
                                        <?php if (!empty($product->file_fispq)) : ?>
                                            <small class="d-block mt-2 text-success">Arquivo enviado. <a href="<?= url("/storage/{$product->file_fispq}"); ?>" target="_blank" rel="noopener">Abrir F.I.S.P.Q.</a></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-12">
                                        <label>Descrição</label>
                                        <textarea name="description" cols="30" rows="10" class="form-control"><?= $product->description; ?></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success ">Atualizar</button>
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
        selectSubCategory = document.querySelector('[name=id_subcategory]')

    if (selectCategory && selectSubCategory) {
        let url = selectCategory.getAttribute('data-url')
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
    }
</script>
<?php $v->end('scripts'); ?>