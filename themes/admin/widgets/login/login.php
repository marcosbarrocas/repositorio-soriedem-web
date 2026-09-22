<?php $v->layout("_login"); ?>
<form action="<?= url('/admin/login'); ?>" method="post">
    <div class="card box-shadow-0 mb-0">
        <div class="card-header">
            <h3 class="card-title">Faça login na sua conta</h3>
        </div>
        <div class="card-body">
            <div class="ajax_response"><?= flash(); ?></div>
            <div class="form-group">
                <label class="form-label text-dark">E-mail</label>
                <input type="email" name="email" value="<?= ($cookie ?? null); ?>" class="form-control" placeholder="Digite seu e-mail">
            </div>
            <div class="form-group">
                <label class="form-label text-dark">Senha</label>
                <input type="password" name="password" class="form-control" placeholder="Digite sua senha">
            </div>
            <div class="form-group">
                <label class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" <?= (!empty($cookie) ? "checked" : ""); ?>
                        name="save" />
                    <span class="custom-control-label text-dark">Lembre de mim</span>
                </label>
            </div>
            <div class="form-footer mt-2">
                <button type="submit" class="btn btn-primary btn-block">Entrar</button>
            </div>
            <!-- <div class="text-center  mt-3 text-dark">
                Ainda não tem conta? <a href="<?= url('/admin/register'); ?>">Inscrever-se</a>
            </div> -->
        </div>
    </div>
</form>