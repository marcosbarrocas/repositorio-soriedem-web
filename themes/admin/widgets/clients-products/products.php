<?php $v->layout("_admin"); ?>
<!--App-Content-->
<div class="app-content  my-3 my-md-5">
    <div class="side-app">
        <div class="page-header">
            <h4 class="page-title">Excluir Produtos do Cliente</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Excluir Produtos do Cliente</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h3 class="card-title">Excluir Produtos do Cliente</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered border-top mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Produtos</th>
                                        <th>Preço</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($clientsProducts) : ?>
                                        <?php foreach ($clientsProducts as $clientProducts) : ?>
                                            <tr>
                                                <th scope="row"><?= $clientProducts->getClient()->id; ?></th>
                                                <td><?= $clientProducts->getClient()->corporate_name; ?></td>
                                                <td><?= $clientProducts->getProduct()->title; ?></td>
                                                <td class="mask-money"><?= $clientProducts->price; ?></td>
                                                <td align="center">
                                                    <a href="#" class="btn btn-danger btn-sm" data-post="<?= url("/admin/clients-products/client-products/{$clientProducts->id}"); ?>" data-action="delete" data-confirm="ATENÇÃO: Tem certeza que deseja excluir o produto e todos os dados relacionados a ele? Essa ação não pode ser feita!" data-user_id="<?= $clientProducts->id; ?>" title="Excluir"><i class="fa fa-trash"></i></a>
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