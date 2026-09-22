<?php $v->layout("_admin"); ?>
<!--App-Content-->
<?php if (!$category): ?>
<div class="app-content  my-3 my-md-5">
    <div class="side-app">
        <div class="page-header">
            <h4 class="page-title">Categorias</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/admin/categories/home'); ?>">Categorias</a></li>
                <li class="breadcrumb-item active" aria-current="page">Criar Categoria</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card m-b-20">
                    <div class="card-header">
                        <h3 class="card-title">Criar Categoria</h3>
                    </div>
                    <div class="card-body">
                        <form action="<?= url('/admin/categories/category'); ?>" method="post">
                            <input type="hidden" name="action" value="create">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Título</label>
                                    <input type="text" class="form-control" name="title"
                                        placeholder="Digite seu título">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Imagem</label>
                                    <input type="file" class="form-control" name="image">
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
            <h4 class="page-title">Categorias</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/admin/categories/home'); ?>">Categorias</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar Categoria</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card m-b-20">
                    <div class="card-header">
                        <h3 class="card-title">Editar Categoria</h3>
                    </div>
                    <div class="card-body">
                        <form action="<?= url('/admin/categories/category/'.$category->id); ?>" method="post">
                            <input type="hidden" name="action" value="update">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Título</label>
                                    <input type="text" class="form-control" name="title" value="<?= $category->title; ?>"
                                           placeholder="Digite seu título">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Imagem</label>
                                    <input type="file" class="form-control" name="image">
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