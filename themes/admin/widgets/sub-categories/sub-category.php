<?php $v->layout("_admin"); ?>
<!--App-Content-->
<?php if (!$subCategory): ?>
<div class="app-content  my-3 my-md-5">
    <div class="side-app">
        <div class="page-header">
            <h4 class="page-title">Sub Categorias</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/admin/sub-categories/home'); ?>">Sub Categorias</a></li>
                <li class="breadcrumb-item active" aria-current="page">Criar Sub Categoria</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card m-b-20">
                    <div class="card-header">
                        <h3 class="card-title">Criar Sub Categoria</h3>
                    </div>
                    <div class="card-body">
                        <form action="<?= url('/admin/sub-categories/sub-category'); ?>" method="post">
                            <input type="hidden" name="action" value="create">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Título</label>
                                    <input type="text" class="form-control" name="title"
                                        placeholder="Digite seu título">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Imagem</label>
                                    <input type="file" class="form-control" name="image">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Categorias</label>
                                    <select name="id_category" class="form-control">
                                        <option value="">Selecionar categoria</option>
                                        <?php if ($categories): ?>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= $category->id ?>"><?= $category->title; ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
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
<?php else: ?>
<div class="app-content  my-3 my-md-5">
    <div class="side-app">
        <div class="page-header">
            <h4 class="page-title">Sub Categorias</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/admin/sub-categories/home'); ?>">Sub Categorias</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar Sub Categoria</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card m-b-20">
                    <div class="card-header">
                        <h3 class="card-title">Editar Sub Categoria</h3>
                    </div>
                    <div class="card-body">
                        <form action="<?= url('/admin/sub-categories/sub-category/'.$subCategory->id); ?>" method="post">
                            <input type="hidden" name="action" value="update">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Título</label>
                                    <input type="text" class="form-control" name="title" value="<?= $subCategory->title; ?>"
                                           placeholder="Digite seu título">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Imagem</label>
                                    <input type="file" class="form-control" name="image">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Categorias</label>
                                    <select name="id_category" class="form-control">
                                        <option value="">Selecionar categoria</option>
                                        <?php
                                            $categoryID = $subCategory->id_category;
                                            $selected = function ($value) use ($categoryID)
                                            {
                                                return ($categoryID == $value) ? 'selected' : '';
                                            }
                                        ?>
                                        <?php if ($categories): ?>
                                            <?php foreach ($categories as $category): ?>
                                                <option <?= $selected($category->id); ?> value="<?= $category->id ?>"><?= $category->title; ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
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