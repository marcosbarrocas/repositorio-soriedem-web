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
        <div class="col-12">
            <div class="card p-5 mb-0">
                <div class="row">
                <div class="col-md-3">
                        <div class="card overflow-hidden" style="background-color: #ccc;">
                            <div class="card-body iconfont text-center">
                                <h5 class="text-white">Pedidos Hoje</h5>
                                <div class="d-flex justify-content-center">
                                    <h5 class="mb-0 text-white mt-1"><?= $requestToday; ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card overflow-hidden bg-warning">
                            <div class="card-body iconfont text-center">
                                <h5 class="text-white">Pedidos Pendentes</h5>
                                <div class="d-flex justify-content-center">
                                    <h5 class="mb-0 text-white mt-1"><?= $requestPending; ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card overflow-hidden bg-info">
                            <div class="card-body iconfont text-center">
                                <h5 class="text-white">Vendedores</h5>
                                <div class="d-flex justify-content-center">
                                    <h5 class="mb-0 text-white mt-1"><?= $sellers; ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card overflow-hidden bg-info">
                            <div class="card-body iconfont text-center">
                                <h5 class="text-white">Produtos</h5>
                                <div class="d-flex justify-content-center">
                                    <h5 class="mb-0 text-white mt-1"><?= $products; ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/App-Content-->