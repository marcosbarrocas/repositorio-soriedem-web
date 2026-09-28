<?php $v->layout("_admin"); ?>
<!--App-Content-->
<div class="app-content  my-3 my-md-5">
    <div class="side-app">
        <div class="page-header">
            <h4 class="page-title">Pedidos</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pedidos</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h3 class="card-title">Pedidos</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <form class="form-inline mb-1" action="<?= url('/admin/requests/home'); ?>" method="post">
                                <div class="nav-search">
                                    <input type="search" class="form-control header-search" name="s" value="<?= $search; ?>" placeholder="Buscar…" aria-label="Search">
                                    <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button>
                                </div>
                            </form>
                            <table class="table table-bordered border-top mb-0">
                                <thead>
                                    <tr>
                                        <th>Pedido</th>

                                        <th>Cliente</th>
                                        <th>Vendedor</th>
                                        <th>Data</th>
                                        <th>Local</th>
                                        <th>Qtde novo inventário</th>
                                        <th>Qtde pedido</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($requests) : ?>
                                        <?php foreach ($requests as $request) : ?>
                                            <tr>
                                                <th scope="row"><?= $request->request_number; ?></th>
                                                <td>
                                                    <?php $cli = $request->getClient(); ?>
                                                    <?php $fantasia = $cli && !empty($cli->contact_name) ? $cli->contact_name : $request->client; ?>
                                                    <?php $razao = $cli && !empty($cli->corporate_name) ? $cli->corporate_name : $request->client; ?>
                                                    <strong><?= $fantasia; ?></strong>
                                                    <?php if ($razao && $razao !== $fantasia) : ?>
                                                        <br><small class="text-muted"><?= $razao; ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $request->seller; ?></td>
                                                <td><?= date_fmt($request->created_at, 'd/m/Y'); ?></td>
                                                <td><a href="http://maps.google.com/maps?q=<?= $request->latitude; ?>,<?= $request->longitude; ?>" target="_blank">Mapa</td>
                                                <td><?= $request->previous_amount; ?></td>
                                                <td><?= $request->current_amount; ?></td>
                                                <td align="center">
                                                    <a href="<?= url('/admin/requests/request/' . $request->id); ?>" class="btn btn-info btn-sm" title="Detalhes"><i class="fa fa-eye"></i></a>
                                                    <?php /* Excluir oculto temporariamente na listagem de pedidos. */ ?>
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