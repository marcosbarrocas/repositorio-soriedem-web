<?php $v->layout("_admin"); ?>
<!--App-Content-->
<div class="app-content my-3 my-md-5" data-url="<?= url(); ?>">
    <div class="side-app">
        <div class="page-header">
            <h4 class="page-title">Vendedores (Omie) e Logins do App</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Vendedores</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h3 class="card-title">Acesso dos vendedores ao app</h3>
                            <div class="d-flex" style="gap:8px">
                                <form action="<?= url('/admin/sellers/omie'); ?>" method="post" class="ajax_off m-0">
                                    <input type="hidden" name="action" value="sync">
                                    <button type="submit" class="btn btn-info btn-sm"><i class="fa fa-refresh"></i> Atualizar da Omie</button>
                                </form>
                                <a href="<?= url('/admin/sellers/home'); ?>" class="btn btn-outline-primary btn-sm">Gerenciar logins manualmente</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Os vendedores vêm da Omie. Como a Omie não tem autenticação de vendedor, o login
                            (e-mail e senha) para acessar o app é criado aqui. Crie ou atualize o acesso de cada um.
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
                                    <th style="width:140px" class="text-center">Login</th>
                                    <th style="width:150px" class="text-center">Ação</th>
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
                                                <?php if ($l['inativo']) : ?><span class="badge badge-secondary ml-1">inativo</span><?php endif; ?>
                                            </td>
                                            <td><?= $l['email'] !== '' ? htmlspecialchars($l['email']) : '<span class="text-muted">—</span>'; ?></td>
                                            <td class="text-center">
                                                <?php if ($temLogin) : ?>
                                                    <span class="badge badge-success">Com login</span>
                                                    <div class="small text-muted"><?= htmlspecialchars($l['seller']->email); ?></div>
                                                <?php else : ?>
                                                    <span class="badge badge-warning">Sem login</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <button type="button"
                                                        class="btn btn-sm <?= $temLogin ? 'btn-outline-secondary' : 'btn-success'; ?> btn-login"
                                                        data-codigo="<?= htmlspecialchars($l['codigo']); ?>"
                                                        data-nome="<?= htmlspecialchars($l['nome'], ENT_QUOTES); ?>"
                                                        data-email="<?= htmlspecialchars($temLogin ? $l['seller']->email : $l['email'], ENT_QUOTES); ?>">
                                                    <?= $temLogin ? 'Alterar senha' : 'Criar login'; ?>
                                                </button>
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

<!-- Modal criar/alterar login -->
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form class="ajax_off" action="<?= url('/admin/sellers/create-login'); ?>" method="post" autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title">Login do vendedor</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="omie_codigo" id="m_codigo">
                    <input type="hidden" name="nome" id="m_nome">
                    <div class="form-group">
                        <label>Vendedor</label>
                        <input type="text" class="form-control" id="m_nome_show" readonly>
                    </div>
                    <div class="form-group">
                        <label>E-mail (login)</label>
                        <input type="email" class="form-control" name="email" id="m_email" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label>Senha</label>
                        <input type="text" class="form-control" name="password" id="m_password" placeholder="mínimo 8 caracteres" autocomplete="new-password" data-lpignore="true" required>
                        <small class="text-muted">Essa é a senha que o vendedor usará para entrar no app.</small>
                    </div>
                    <div class="modal-message"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Salvar login</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!--/App-Content-->
<?php $v->start('scripts'); ?>
<script>
    const baseUrl = document.querySelector('.app-content').getAttribute('data-url')

    // gera uma senha aleatória forte de 8 caracteres
    function randomPassword() {
        const chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789'
        let out = ''
        for (let i = 0; i < 8; i++) out += chars[Math.floor(Math.random() * chars.length)]
        return out
    }

    document.querySelectorAll('.btn-login').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('m_codigo').value = btn.dataset.codigo
            document.getElementById('m_nome').value = btn.dataset.nome
            document.getElementById('m_nome_show').value = btn.dataset.nome
            document.getElementById('m_email').value = btn.dataset.email || ''
            document.getElementById('m_password').value = randomPassword()
            document.querySelector('.modal-message').innerHTML = ''
            $('#loginModal').modal('show')
        })
    })

    // submit via ajax
    document.querySelector('#loginModal form').addEventListener('submit', function (e) {
        e.preventDefault()
        const form = new FormData(this)
        axios.post(this.action, form).then(res => {
            if (res.data && res.data.reload) {
                location.reload()
            } else if (res.data && res.data.message) {
                document.querySelector('.modal-message').innerHTML = res.data.message
            }
        }).catch(() => {
            document.querySelector('.modal-message').innerHTML = '<div class="alert alert-danger">Erro ao salvar.</div>'
        })
    })
</script>
<?php $v->end('scripts'); ?>
