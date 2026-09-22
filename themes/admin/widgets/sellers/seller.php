<?php $v->layout("_admin"); ?>
<!--App-Content-->
<?php if (!$seller): ?>
<div class="app-content  my-3 my-md-5">
    <div class="side-app">
        <div class="page-header">
            <h4 class="page-title">Vendedores</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/admin/sellers/home'); ?>">Vendedores</a></li>
                <li class="breadcrumb-item active" aria-current="page">Criar Vendedor</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card m-b-20">
                    <div class="card-header">
                        <h3 class="card-title">Criar Vendedor</h3>
                    </div>
                    <div class="card-body">
                        <form action="<?= url('/admin/sellers/seller'); ?>" method="post">
                            <input type="hidden" name="action" value="create">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Nome</label>
                                    <input type="text" class="form-control" name="first_name"
                                        placeholder="Digite seu nome">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Sobrenome</label>
                                    <input type="text" class="form-control" name="last_name"
                                        placeholder="Digite seu sobrenome">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Telefone/WhatsApp</label>
                                    <input type="tel" class="form-control mask-phone" name="phone"
                                        placeholder="Digite o telefone">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>CPF</label>
                                    <input type="text" class="form-control mask-doc" name="document"
                                        placeholder="Digite seu CPF">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>E-mail</label>
                                    <input type="email" class="form-control" name="email"
                                        placeholder="Digite seu e-mail">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Senha</label>
                                    <input type="password" class="form-control" name="password"
                                        placeholder="Digite sua senha">
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
<?php else: ?>
<div class="app-content  my-3 my-md-5">
    <div class="side-app">
        <div class="page-header">
            <h4 class="page-title">Vendedores</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/admin/sellers/home'); ?>">Vendedores</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar Vendedor</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card m-b-20">
                    <div class="card-header">
                        <h3 class="card-title">Editar Vendedor</h3>
                    </div>
                    <div class="card-body">
                        <form action="<?= url('/admin/sellers/seller/'.$seller->id); ?>" method="post">
                            <input type="hidden" name="action" value="update">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Nome</label>
                                    <input type="text" class="form-control" name="first_name"
                                        value="<?= $seller->first_name; ?>" placeholder="Digite seu nome">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Sobrenome</label>
                                    <input type="text" class="form-control" name="last_name"
                                        value="<?= $seller->last_name; ?>" placeholder="Digite seu sobrenome">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Telefone/WhatsApp</label>
                                    <input type="tel" class="form-control mask-phone" name="phone"
                                        value="<?= $seller->phone; ?>" placeholder="Digite o telefone">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>CPF</label>
                                    <input type="text" class="form-control mask-doc" name="document"
                                        value="<?= $seller->document; ?>" placeholder="Digite seu CPF">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>E-mail</label>
                                    <input type="email" class="form-control" name="email" value="<?= $seller->email; ?>"
                                        placeholder="Digite seu e-mail">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Senha</label>
                                    <input type="password" class="form-control" name="password"
                                        placeholder="Digite sua senha">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success ">Atualizar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<!--/App-Content-->