<?php $v->layout("_admin"); ?>
<!--App-Content-->
<div class="app-content my-3 my-md-5">
    <div class="side-app">
        <div class="page-header">
            <h4 class="page-title">Clientes</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Clientes</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h3 class="card-title">Clientes da Omie</h3>
                            <form action="<?= url('/admin/clients/omie'); ?>" method="post" class="m-0">
                                <input type="hidden" name="action" value="sync">
                                <button type="submit" class="btn btn-info btn-sm"><i class="fa fa-refresh"></i> Atualizar da Omie</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Cadastro de clientes consultado na API da Omie. Nome fantasia, razão social, CNPJ e endereço vêm de lá.
                            <?php if (!empty($vendedorNome)) : ?>
                                Exibindo os clientes associados a <strong><?= $vendedorNome; ?></strong>.
                            <?php endif; ?>
                        </p>
                        <?php if (!empty($vendedores)) : ?>
                            <div class="mb-3">
                                <div class="mb-2"><strong>Vendedor</strong></div>
                                <div class="d-flex flex-wrap" style="gap:8px">
                                    <a href="<?= url('/admin/clients/omie'); ?>"
                                        class="btn btn-sm <?= empty($vendedor) ? 'btn-primary' : 'btn-outline-primary'; ?>">Todos</a>
                                    <?php foreach ($vendedores as $item) : ?>
                                        <a href="<?= url('/admin/clients/omie/vendedor/' . $item['codigo'] . '/all/1'); ?>"
                                            class="btn btn-sm <?= (!empty($vendedor) && (string) $vendedor === (string) $item['codigo']) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                            <?= $item['nome']; ?> (<?= $item['total']; ?>)
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="table-responsive">
                            <form class="form-inline mb-1" action="<?= url('/admin/clients/omie'); ?>" method="post">
                                <?php if (!empty($vendedor)) : ?>
                                    <input type="hidden" name="vendedor" value="<?= $vendedor; ?>">
                                <?php endif; ?>
                                <div class="nav-search">
                                    <input type="search" class="form-control header-search" name="s"
                                        value="<?= $search; ?>" placeholder="Nome fantasia, razão social ou CNPJ" aria-label="Buscar cliente">
                                    <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button>
                                </div>
                            </form>
                            <table class="table table-bordered border-top mb-0">
                                <thead>
                                    <tr>
                                        <th>Nome fantasia</th>
                                        <th>Razão social</th>
                                        <th>CNPJ</th>
                                        <th>Endereço</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($clients)) : ?>
                                        <?php foreach ($clients as $client) : ?>
                                            <tr>
                                                <td><?= $client->contact_name; ?></td>
                                                <td><?= $client->corporate_name; ?></td>
                                                <td><?= $client->cnpj; ?></td>
                                                <td><?= $client->enderecoCompleto(); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted"><?= !empty($vendedorNome) ? 'Nenhum cliente associado a este vendedor.' : 'Nenhum cliente encontrado na Omie.'; ?></td>
                                        </tr>
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
