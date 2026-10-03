<?php $v->layout("_admin"); ?>
<!--App-Content-->
<div class="app-content my-3 my-md-5" data-url="<?= url(); ?>">
    <div class="side-app">
        <div class="page-header">
            <h4 class="page-title">Acesso ao app</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Acesso ao app</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h3 class="card-title">Usuários do app</h3>
                            <div class="d-flex" style="gap:8px">
                                <form action="<?= url('/admin/sellers/omie'); ?>" method="post" class="m-0">
                                    <input type="hidden" name="action" value="sync">
                                    <button type="submit" class="btn btn-info btn-sm"><i class="fa fa-refresh"></i> Atualizar da Omie</button>
                                </form>
                                <a href="<?= url('/admin/sellers/home'); ?>" class="btn btn-outline-primary btn-sm">Gerenciar logins manualmente</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Quem entra no app da Soriedem. O nome vem da Omie; o e-mail, a senha e o status do acesso são definidos aqui.
                        </p>

                        <?php if (!empty($erro)) : ?>
                            <div class="alert alert-danger">Erro ao consultar a Omie: <?= htmlspecialchars($erro); ?></div>
                        <?php endif; ?>

                        <table class="table table-bordered border-top mb-0">
                            <thead>
                                <tr>
                                    <th style="width:120px">Cód. Omie</th>
                                    <th>Vendedor</th>
                                    <th>E-mail (Omie)</th>
                                    <th style="width:220px">Login</th>
                                    <th style="width:140px" class="text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($linhas)) : ?>
                                    <?php foreach ($linhas as $l) : ?>
                                        <?php $temLogin = !empty($l['seller']); ?>
                                        <tr>
                                            <td><?= htmlspecialchars($l['codigo']); ?></td>
                                            <td>
                                                <?= htmlspecialchars($l['nome']); ?>
                                                <?php if ($l['inativo']) : ?><span class="badge badge-light ml-1">inativo na Omie</span><?php endif; ?>
                                            </td>
                                            <td><?= $l['email'] !== '' ? htmlspecialchars($l['email']) : '<span class="text-muted">—</span>'; ?></td>
                                            <td>
                                                <?php if ($temLogin) : ?>
                                                    <?php $ativo = (int) ($l['seller']->status ?? 1) === 1; ?>
                                                    <div><?= htmlspecialchars($l['seller']->email); ?></div>
                                                    <span class="badge <?= $ativo ? 'badge-success' : 'badge-secondary'; ?>"><?= $ativo ? 'Ativo' : 'Inativo'; ?></span>
                                                <?php else : ?>
                                                    <span class="text-muted">Sem acesso</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= url('/admin/sellers/access/' . $l['codigo']); ?>" class="btn btn-sm <?= $temLogin ? 'btn-outline-primary' : 'btn-success'; ?>">
                                                    <?= $temLogin ? 'Alterar' : 'Criar acesso'; ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr><td colspan="5" class="text-center text-muted">Nenhum vendedor encontrado na Omie.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

