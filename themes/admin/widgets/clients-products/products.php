<?php $v->layout("_admin"); ?>
<?php
$fantasy = $client && !empty($client->contact_name) ? $client->contact_name : ($client ? $client->corporate_name : "");
$corporate = $client ? $client->corporate_name : "";
?>
<!--App-Content-->
<div class="app-content  my-3 my-md-5">
    <div class="side-app">
        <div class="page-header">
            <h4 class="page-title">Detalhes da cesta</h4>
            <div class="d-flex align-items-center">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/clients-products/home') ?>">Cesta de Produtos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
                </ol>
                <?= admin_back('/admin/clients-products/home'); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Cliente</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="mb-1">Nome fantasia</label>
                                <div><strong><?= $fantasy; ?></strong></div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="mb-1">Razão social</label>
                                <div><?= $corporate; ?></div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="mb-1">CNPJ</label>
                                <div><?= $client && !empty($client->cnpj) ? $client->cnpj : "—"; ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h3 class="card-title">Produtos da cesta</h3>
                            <?php if ($client) : ?>
                                <a href="<?= url('/admin/clients-products/add/' . $client->id); ?>" class="btn btn-pill btn-success">
                                    <i class="fa fa-plus"></i> Adicionar produtos
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered border-top mb-0">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Produto</th>
                                        <th>Valor</th>
                                        <th>Obrigatório</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($clientsProducts) : ?>
                                        <?php foreach ($clientsProducts as $clientProducts) : ?>
                                            <?php $product = $clientProducts->getProduct(); ?>
                                            <tr>
                                                <th scope="row"><?= $product ? $product->code : ""; ?></th>
                                                <td><?= $product ? $product->title : "Produto não encontrado"; ?></td>
                                                <td>R$ <?= money_br((string) $clientProducts->price); ?></td>
                                                <td><?= !empty($clientProducts->required) ? "Sim" : "Não"; ?></td>
                                                <td align="center">
                                                    <a href="<?= url('/admin/clients-products/client-products/' . $clientProducts->id); ?>" class="btn btn-info btn-sm" title="Alterar valor e obrigatoriedade"><i class="fa fa-pencil"></i></a>
                                                    <a href="#" class="btn btn-danger btn-sm" data-post="<?= url("/admin/clients-products/client-products/{$clientProducts->id}"); ?>" data-action="delete" data-confirm="ATENÇÃO: Tem certeza que deseja excluir este produto da cesta do cliente? Essa ação não pode ser desfeita." data-clientProducts_id="<?= $clientProducts->id; ?>" title="Excluir"><i class="fa fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (!empty($paginator)) : ?>
                            <?= $paginator; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/App-Content-->
