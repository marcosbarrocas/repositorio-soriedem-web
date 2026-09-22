<?php $v->layout("_admin"); ?>
<!--App-Content-->
<div class="app-content  my-3 my-md-5">
    <div class="side-app">
        <div class="page-header">
            <h4 class="page-title">Sub Categorias</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Sub Categorias</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h3 class="card-title">Sub Categorias</h3>
                            <div>
                                <a href="<?= url('/admin/sub-categories/sub-category'); ?>" class="btn btn-pill btn-success"><i
                                        class="fa fa-plus"></i> Adicionar Sub Categoria</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <form class="form-inline mb-1" action="<?= url('/admin/sub-categories/home'); ?>" method="post">
                                <div class="nav-search">
                                    <input type="search" class="form-control header-search" name="s"
                                        value="<?= $search; ?>" placeholder="Buscar…" aria-label="Search">
                                    <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button>
                                </div>
                            </form>
                            <table class="table table-bordered border-top mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Imagem</th>
                                        <th>Título</th>
                                        <th>Categoria</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($subCategories): ?>
                                        <?php foreach ($subCategories as $subCategory): ?>
                                            <tr>
                                                <th scope="row"><?= $subCategory->id; ?></th>
                                                <td><img src="<?= image($subCategory->image, 30, 30); ?>" alt="Imagem da marca."></td>
                                                <td><?= $subCategory->title; ?></td>
                                                <td><?= $subCategory->getCategory()->title; ?></td>
                                                <td align="center">
                                                    <a href="<?= url('/admin/sub-categories/sub-category/'.$subCategory->id); ?>"
                                                        class="btn btn-info btn-sm" title="Editar"><i
                                                            class="fa fa-pencil"></i></a>

                                                    <a href="#" class="btn btn-danger btn-sm"
                                                        data-post="<?= url("/admin/sub-categories/sub-category/{$subCategory->id}"); ?>"
                                                        data-action="delete"
                                                        data-confirm="ATENÇÃO: Tem certeza que deseja excluir a sub categoria e todos os dados relacionados a ela? Essa ação não pode ser feita!"
                                                        data-user_id="<?= $subCategory->id; ?>" title="Excluir"><i
                                                            class="fa fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?= $paginator; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/App-Content-->