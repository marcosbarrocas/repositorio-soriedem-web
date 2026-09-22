<?php $v->layout("_admin"); ?>
<!--App-Content-->
<div class="app-content  my-3 my-md-5">
    <div class="side-app">
        <div class="page-header">
            <h4 class="page-title">Associar Produtos do Cliente</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Associar Produtos do Cliente</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h3 class="card-title">Associar Produtos do Cliente</h3>
                            <div>
                                <a href="<?= url('/admin/clients-products/client-products'); ?>" class="btn btn-pill btn-success"><i
                                        class="fa fa-plus"></i> Adicionar Associação</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <form class="form-inline mb-1" action="<?= url('/admin/clients-products/home'); ?>" method="post">
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
                                        <th>Cliente</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($clientsProducts): ?>
                                        <?php foreach ($clientsProducts as $clientProducts): ?>
                                            <tr>
                                                <th scope="row"><?= $clientProducts->getClient()->id; ?></th>
                                                <td><?= $clientProducts->getClient()->corporate_name; ?></td>
                                                <td align="center">
                                                    <a href="<?= url('/admin/clients-products/list-products/'.$clientProducts->getClient()->id); ?>"
                                                        class="btn btn-info btn-sm" title="Visualizar Produtos"><i
                                                            class="fa fa-eye"></i></a>

                                                    <!-- <a href="#" class="btn btn-danger btn-sm"
                                                        data-post="<?= url("/admin/clients-products/client-products/{$clientProducts->id}"); ?>"
                                                        data-action="delete"
                                                        data-confirm="ATENÇÃO: Tem certeza que deseja excluir a categoria e todos os dados relacionados a ela? Essa ação não pode ser feita!"
                                                        data-user_id="<?= $clientProducts->id; ?>" title="Excluir"><i
                                                            class="fa fa-trash"></i></a> -->
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