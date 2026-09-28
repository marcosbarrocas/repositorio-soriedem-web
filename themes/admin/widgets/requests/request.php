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
                <?= admin_back('/admin/requests/home'); ?>
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
                <?= admin_back('/admin/requests/home'); ?>
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
                                    <?php
                                    $client = $request->getClient();
                                    $seller = $request->getSeller();
                                    $sellerName = $seller ? trim($seller->first_name . " " . $seller->last_name) : $request->seller;
                                    $fantasy = $client && !empty($client->contact_name) ? $client->contact_name : $request->client;
                                    $corporate = $client && !empty($client->corporate_name) ? $client->corporate_name : $request->client;
                                    $city = $client->city ?? "";
                                    $state = $client->state ?? "";
                                    if ($state !== "" && stripos($city, $state) === false) {
                                        $city = trim($city . "/" . $state, "/");
                                    }
                                    $address = $client ? trim("{$client->address}, {$client->number} - {$client->district} - {$city}", " ,-/") : "";
                                    ?>

                                    <div class="col-md-12 col-lg-12 mb-3">
                                        <div class="card">
                                            <div class="card-header">
                                                <h3 class="card-title">Dados do pedido</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label class="mb-1">Pedido</label>
                                                        <div><strong>#<?= $request->request_number; ?></strong></div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="mb-1">Data do pedido</label>
                                                        <div><?= date_fmt($request->created_at, "d/m/Y H:i"); ?></div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="mb-1">Última atualização</label>
                                                        <div><?= date_fmt($request->updated_at, "d/m/Y H:i"); ?></div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="mb-1">Vendedor</label>
                                                        <div><strong><?= $sellerName ?: $request->seller; ?></strong></div>
                                                        <?php if (!empty($request->seller_fullname) && $request->seller_fullname !== $sellerName) : ?>
                                                            <small class="text-muted">Responsável: <?= $request->seller_fullname; ?></small>
                                                        <?php endif; ?>
                                                        <?php if ($seller && !empty($seller->email)) : ?>
                                                            <br><small class="text-muted"><?= $seller->email; ?></small>
                                                        <?php endif; ?>
                                                        <?php if ($seller && !empty($seller->phone)) : ?>
                                                            <br><small class="text-muted"><?= $seller->phone; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="mb-1">Cliente</label>
                                                        <div><strong><?= $fantasy; ?></strong></div>
                                                        <?php if ($corporate && $corporate !== $fantasy) : ?>
                                                            <small class="text-muted"><?= $corporate; ?></small>
                                                        <?php endif; ?>
                                                        <?php if ($client && !empty($client->cnpj)) : ?>
                                                            <br><small class="text-muted">CNPJ: <?= $client->cnpj; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="mb-1">Contato do cliente</label>
                                                        <div><?= $client->phone ?? ""; ?></div>
                                                        <small class="text-muted"><?= $client->email ?? ""; ?></small>
                                                    </div>
                                                    <div class="col-md-8 mb-3">
                                                        <label class="mb-1">Endereço</label>
                                                        <div><?= $address; ?></div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="mb-1">Local do pedido</label>
                                                        <div>
                                                            <a href="http://maps.google.com/maps?q=<?= $request->latitude; ?>,<?= $request->longitude; ?>" target="_blank">Ver no mapa</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 col-lg-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="d-flex justify-content-between align-items-center w-100">
                                                    <h3 class="card-title">Produtos</h3>
                                                    <h4 class="card-title">Total Pedido: R$ <?= number_format((float) $request->total, 2, ",", "."); ?></h4>
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
                                                                <th>Qtde inventário</th>
                                                                <th>Qtde pedido</th>
                                                                <th>Valor unitário</th>
                                                                <th>Total do item</th>
                                                                <!-- <th>Preço</th>
                                                                <th>Total</th> -->
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php if ($request->getItemsRequest()) : ?>
                                                                <?php foreach ($request->getItemsRequest() as $product) : ?>
                                                                    <?php $item = $product->getProduct(); ?>
                                                                    <tr>
                                                                        <th scope="row"><?= $item ? $item->code : ""; ?></th>
                                                                        <th>
                                                                            <?php if ($item && !empty($item->photo)) : ?>
                                                                                <img src="<?= image($item->photo, 30, 30); ?>" alt="Foto">
                                                                            <?php endif; ?>
                                                                        </th>
                                                                        <th><?= $item ? $item->title : "Produto não encontrado"; ?></th>
                                                                        <th><?= $product->previous_amount; ?></th>
                                                                        <th><?= $product->current_amount; ?></th>
                                                                        <th>R$ <?= number_format((float) $product->item_value, 2, ",", "."); ?></th>
                                                                        <th>R$ <?= number_format((float) $product->total_item_value, 2, ",", "."); ?></th>
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