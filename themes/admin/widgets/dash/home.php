<?php $v->layout("_admin"); ?>
<!--App-Content-->
<div class="app-content  my-3 my-md-5">
    <div class="side-app">
        <div class="page-header">
            <h4 class="page-title">Dashboard</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </div>
        <div class="row">
            <div class="col-sm-6 col-lg-3">
                <a href="<?= url('/admin/requests/home'); ?>" class="card bg-secondary text-white mb-4">
                    <div class="card-body d-flex align-items-center">
                        <i class="ti-clipboard" style="font-size: 2.4rem;"></i>
                        <div class="ml-3">
                            <div class="text-white-50">Pedidos hoje</div>
                            <h2 class="mb-0 text-white"><?= $requestToday; ?></h2>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-3">
                <a href="<?= url('/admin/requests/home'); ?>" class="card bg-warning text-white mb-4">
                    <div class="card-body d-flex align-items-center">
                        <i class="ti-alert" style="font-size: 2.4rem;"></i>
                        <div class="ml-3">
                            <div class="text-white-50">Pedidos pendentes</div>
                            <h2 class="mb-0 text-white"><?= $requestPending; ?></h2>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-3">
                <a href="<?= url('/admin/sellers/omie'); ?>" class="card bg-info text-white mb-4">
                    <div class="card-body d-flex align-items-center">
                        <i class="ti-id-badge" style="font-size: 2.4rem;"></i>
                        <div class="ml-3">
                            <div class="text-white-50">Vendedores</div>
                            <h2 class="mb-0 text-white"><?= $sellers; ?></h2>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-3">
                <a href="<?= url('/admin/products/home'); ?>" class="card bg-primary text-white mb-4">
                    <div class="card-body d-flex align-items-center">
                        <i class="ti-package" style="font-size: 2.4rem;"></i>
                        <div class="ml-3">
                            <div class="text-white-50">Produtos</div>
                            <h2 class="mb-0 text-white"><?= $products; ?></h2>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
<!--/App-Content-->