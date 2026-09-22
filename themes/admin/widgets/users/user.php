<?php $v->layout("_admin"); ?>
<!--App-Content-->
<?php if (!$user): ?>
<div class="app-content  my-3 my-md-5">
    <div class="side-app">
        <div class="page-header">
            <h4 class="page-title">Usuários</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/admin/users/home'); ?>">Usuários</a></li>
                <li class="breadcrumb-item active" aria-current="page">Criar Usuário</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card m-b-20">
                    <div class="card-header">
                        <h3 class="card-title">Criar Usuário</h3>
                    </div>
                    <div class="card-body">
                        <form action="<?= url('/admin/users/user'); ?>" method="post">
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
                                    <label>*Telefone/WhatsApp</label>
                                    <input type="tel" class="form-control mask-phone" name="phone"
                                        placeholder="Digite o telefone">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Gênero</label>
                                    <select name="genre" class="form-control">
                                        <option value="">Selecione o gênero</option>
                                        <option value="male" class="">Masculino</option>
                                        <option value="female" class="">Feminino</option>
                                        <option value="other" class="">Outros</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Foto</label>
                                    <input type="file" class="form-control" name="photo">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Nascimento</label>
                                    <input type="text" class="form-control mask-date" name="datebirth"
                                        placeholder="Digite sua data de nascimento">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>CPF</label>
                                    <input type="text" class="form-control mask-doc" name="document"
                                        placeholder="Digite seu CPF">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Perfil de Acesso</label>
                                    <select name="level" class="form-control">
                                        <option value="">Selecione o perfil</option>
                                        <option value="1" class="">Usuário</option>
                                        <option value="5" class="">Admin</option>
                                    </select>
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
            <h4 class="page-title">Usuários</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/admin/users/home'); ?>">Usuários</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar Usuário</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card m-b-20">
                    <div class="card-header">
                        <h3 class="card-title">Editar Usuário</h3>
                    </div>
                    <div class="card-body">
                        <form action="<?= url('/admin/users/user/'.$user->id); ?>" method="post">
                            <input type="hidden" name="action" value="update">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Nome</label>
                                    <input type="text" class="form-control" name="first_name"
                                        value="<?= $user->first_name; ?>" placeholder="Digite seu nome">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Sobrenome</label>
                                    <input type="text" class="form-control" name="last_name"
                                        value="<?= $user->last_name; ?>" placeholder="Digite seu sobrenome">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>*Telefone/WhatsApp</label>
                                    <input type="tel" class="form-control mask-phone" name="phone"
                                        value="<?= $user->phone; ?>" placeholder="Digite o telefone">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Gênero</label>
                                    <select name="genre" class="form-control">
                                        <option value="">Selecione o gênero</option>
                                        <?php
                                        $genre = $user->genre;
                                        $select = function ($value) use ($genre) {
                                            return ($genre == $value ? "selected" : "");
                                        };
                                        ?>
                                        <option <?= $select("male"); ?> value="male">Masculino</option>
                                        <option <?= $select("female"); ?> value="female">Feminino</option>
                                        <option <?= $select("other"); ?> value="other">Outros</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Foto</label>
                                    <input type="file" class="form-control" name="photo">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Nascimento</label>
                                    <input type="text" class="form-control mask-date" name="datebirth"
                                        value="<?= date_fmt($user->datebirth, "d/m/Y"); ?>"
                                        placeholder="Digite sua data de nascimento">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>CPF</label>
                                    <input type="text" class="form-control mask-doc" name="document"
                                        value="<?= $user->document; ?>" placeholder="Digite seu CPF">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Perfil de Acesso</label>
                                    <select name="level" class="form-control">
                                        <option value="">Selecione o perfil</option>
                                        <?php
                                        $level = $user->level;
                                        $select = function ($value) use ($level) {
                                            return ($level == $value ? "selected" : "");
                                        };
                                        ?>
                                        <option <?= $select(1); ?> value="1">Usuário</option>
                                        <option <?= $select(5); ?> value="5">Admin</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>E-mail</label>
                                    <input type="email" class="form-control" name="email" value="<?= $user->email; ?>"
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