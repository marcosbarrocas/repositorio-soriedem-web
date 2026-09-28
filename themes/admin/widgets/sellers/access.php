<?php $v->layout("_admin"); ?>
<?php
$temLogin = !empty($seller);
$ativo = $temLogin ? (int) ($seller->status ?? 1) === 1 : true;
$emailLogin = $temLogin ? (string) $seller->email : (string) ($vendedor["email"] ?? "");
?>
<div class="app-content my-3 my-md-5">
    <div class="side-app">
        <div class="page-header">
            <h4 class="page-title">Acesso ao app</h4>
            <div class="d-flex align-items-center">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/sellers/omie'); ?>">Acesso ao app</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= $temLogin ? "Alterar" : "Criar acesso"; ?></li>
                </ol>
                <?= admin_back('/admin/sellers/omie'); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card m-b-20">
                    <div class="card-header">
                        <h3 class="card-title"><?= htmlspecialchars($vendedor["nome"]); ?></h3>
                    </div>
                    <div class="card-body">
                        <form class="ajax_off" action="<?= url('/admin/sellers/access/' . $codigo); ?>" method="post" autocomplete="off">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Vendedor</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($vendedor["nome"]); ?>" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Código Omie</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars((string) $codigo); ?>" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>E-mail (login do app)</label>
                                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($emailLogin); ?>" autocomplete="off" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Senha</label>
                                    <input type="password" class="form-control" name="password" autocomplete="new-password" placeholder="<?= $temLogin ? "Deixe em branco para manter a senha atual" : "Mínimo de 8 caracteres"; ?>" <?= $temLogin ? "" : "required"; ?>>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Status do acesso</label>
                                    <select name="status" class="form-control">
                                        <option value="1" <?= $ativo ? "selected" : ""; ?>>Ativo</option>
                                        <option value="0" <?= $ativo ? "" : "selected"; ?>>Inativo</option>
                                    </select>
                                    <small class="text-muted">Inativo não consegue entrar no app.</small>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success">Salvar</button>
                            <a href="<?= url('/admin/sellers/omie'); ?>" class="btn btn-light">Voltar</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
