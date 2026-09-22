<?php $v->layout("_admin"); ?>
<!--App-Content-->
<?php if (!$request) : ?>
    <div class="app-content  my-3 my-md-5">
        <div class="side-app">
            <div class="page-header">
                <h4 class="page-title">Pedidos</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/requests/home'); ?>">Pedidos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Criar Pedido</li>
                </ol>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card m-b-20">
                        <div class="card-header">
                            <h3 class="card-title">Criar Pedido</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= url('/admin/requests/request'); ?>" method="post">
                                <input type="hidden" name="action" value="create">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Nome</label>
                                        <input type="text" class="form-control" name="first_name" placeholder="Digite seu nome">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Sobrenome</label>
                                        <input type="text" class="form-control" name="last_name" placeholder="Digite seu sobrenome">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Telefone/WhatsApp</label>
                                        <input type="tel" class="form-control mask-phone" name="phone" placeholder="Digite o telefone">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>CPF</label>
                                        <input type="text" class="form-control mask-doc" name="document" placeholder="Digite seu CPF">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>E-mail</label>
                                        <input type="email" class="form-control" name="email" placeholder="Digite seu e-mail">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Senha</label>
                                        <input type="password" class="form-control" name="password" placeholder="Digite sua senha">
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
<?php else : ?>
    <div class="app-content  my-3 my-md-5">
        <div class="side-app">
            <div class="page-header">
                <h4 class="page-title">Pedidos</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/requests/home'); ?>">Pedidos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar Pedido</li>
                </ol>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card m-b-20">
                        <div class="card-header">
                            <h3 class="card-title">Editar Pedido #<?= $request->request_number; ?></h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= url('/admin/requests/request/' . $request->id); ?>" method="post">
                                <input type="hidden" name="action" value="update">
                                <div class="form-row">


                                    <div class="col-md-12 col-lg-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="d-flex justify-content-between align-items-center w-100">
                                                    <h3 class="card-title">Produtos</h3>
                                                    <h4 class="card-title">Total Pedido: R$<?= number_format($request->total, 2, ',', ''); ?></h4>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered border-top mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Código</th>
                                                                <th>Foto</th>
                                                                <th>Produto</th>
                                                                <th>Quantidade</th>
                                                                <!-- <th>Preço</th>
                                                                <th>Total</th> -->
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php if ($request->getItemsRequest()) : ?>
                                                                <?php foreach ($request->getItemsRequest() as $product) : ?>
                                                                    <tr>
                                                                        <th scope="row"><?= $product->getProduct()->code; ?></th>
                                                                        <th><img src="<?= image($product->getProduct()->photo, 30, 30); ?>" alt="Foto"></th>
                                                                        <th><?= $product->getProduct()->title; ?></th>
                                                                        <th><?= $product->current_amount; ?></th>
                                                                        <!-- <th>R$ <?= str_replace('.', ',', $product->getProduct()->value); ?></th>
                                                                        <th>R$ <?= number_format(intval($product->getProduct()->value) * $product->current_amount, 2, ',', ''); ?></th> -->
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 col-lg-12">
                                        <div style="display: flex; flex-direction: column; align-items: center;">
                                            <label>Assinatura do responsável</label>
                                            <img style="height: 250px; width: 250px;" src="<?= url("/api/Requests/signs/{$request->signature}"); ?>">
                                            <label><?= $request->seller_fullname; ?></label>
                                        </div>
                                    </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<!--/App-Content-->