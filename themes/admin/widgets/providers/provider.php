<?php $v->layout("_admin"); ?>
<!--App-Content-->
<?php if (!$provider): ?>
<div class="app-content  my-3 my-md-5">
    <div class="side-app">
        <div class="page-header">
            <h4 class="page-title">Fornecedores</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/admin/providers/home'); ?>">Fornecedores</a></li>
                <li class="breadcrumb-item active" aria-current="page">Criar Fornecedor</li>
            </ol>
            <?= admin_back('/admin/providers/home'); ?>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card m-b-20">
                    <div class="card-header">
                        <h3 class="card-title">Criar Fornecedor</h3>
                    </div>
                    <div class="card-body">
                        <form action="<?= url('/admin/providers/provider'); ?>" method="post">
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
            <h4 class="page-title">Fornecedores</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/admin/providers/home'); ?>">Fornecedores</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar Fornecedor</li>
            </ol>
            <?= admin_back('/admin/providers/home'); ?>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card m-b-20">
                    <div class="card-header">
                        <h3 class="card-title">Editar Fornecedor</h3>
                    </div>
                    <div class="card-body">
                        <form action="<?= url('/admin/providers/provider/'.$provider->id); ?>" method="post">
                            <input type="hidden" name="action" value="update">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Título</label>
                                    <input type="text" class="form-control" name="title" value="<?= $provider->title; ?>"
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