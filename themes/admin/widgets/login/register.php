<?php $v->layout("_login"); ?>
<form action="<?= url('/admin/register') ?>" method="post">
    <div class="card box-shadow-0 mb-xl-0">
        <div class="card-header">
            <h3 class="card-title">Crie sua conta</h3>
        </div>
        <div class="card-body">
            <div class="ajax_response"><?= flash(); ?></div>
            <?= csrf_input(); ?>
            <div class="form-group">
                <label class="form-label text-dark">Nome</label>
                <input type="text" name="first_name" class="form-control" placeholder="Digite seu nome">
            </div>
            <div class="form-group">
                <label class="form-label text-dark">Sobrenome</label>
                <input type="text" name="last_name" class="form-control" placeholder="Digite seu sobrenome">
            </div>
            <div class="form-group">
                <label class="form-label text-dark">E-mail</label>
                <input type="email" name="email" class="form-control" placeholder="Digite seu e-mail">
            </div>
            <div class="form-group">
                <label class="form-label text-dark">Senha</label>
                <input type="password" name="password" class="form-control" placeholder="Digite sua senha">
            </div>
            <div class="form-footer mt-2">
                <button type="submit" class="btn btn-primary btn-block">Criar Conta</button>
            </div>
            <div class="text-center  mt-3 text-dark">
                Já tem conta?<a href="<?= url('/admin/login'); ?>"> Entrar</a>
            </div>
        </div>
    </div>
</form>