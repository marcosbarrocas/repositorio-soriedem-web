<?php $v->layout("_admin"); ?>
    <!--App-Content-->
<?php if (!$client) : ?>
    <div class="app-content  my-3 my-md-5">
        <div class="side-app">
            <div class="page-header">
                <h4 class="page-title">Clientes</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/clients/home'); ?>">Clientes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Criar Cliente</li>
                </ol>
                <?= admin_back('/admin/clients/home'); ?>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card m-b-20">
                        <div class="card-header">
                            <h3 class="card-title">Criar Cliente</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= url('/admin/clients/client'); ?>" method="post">
                                <input type="hidden" name="action" value="create">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Razão Social</label>
                                        <input type="text" class="form-control" name="corporate_name">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>CNPJ</label>
                                        <input type="text" class="form-control" name="cnpj">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>E-mail</label>
                                        <input type="email" class="form-control" name="email">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Telefone</label>
                                        <input type="tel" class="form-control mask-phone" name="phone">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Nome do Contato</label>
                                        <input type="text" class="form-control" name="contact_name">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Vendedores</label>
                                        <select name="id_seller" class="form-control">
                                            <option value="">Selecionar</option>
                                            <?php if ($sellers): ?>
                                                <?php foreach ($sellers as $seller): ?>
                                                    <option value="<?= $seller->id; ?>"><?= $seller->fullName(); ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>CEP</label>
                                        <div class="row">
                                            <div class="col-md-9 pr-0">
                                                <input type="search" class="form-control mask-cep" name="cep" placeholder="Digite o CEP">
                                            </div>
                                            <div class="col-md-3 pl-1">
                                                <button type="button" class="cep-search btn btn-dark">BUSCAR</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label>Cidade</label>
                                        <input type="text" class="input-disabled form-control" name="city" placeholder="Digite a cidade" readonly>
                                    </div>
                                    <div class="form-group col-md-1">
                                        <label>UF</label>
                                        <input type="text" class="input-disabled form-control" name="state" placeholder="Digite o estado" readonly>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Endereço</label>
                                        <input type="text" class="input-disabled form-control" name="address" placeholder="Digite o endereço" readonly>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label>Bairro</label>
                                        <input type="text" class="input-disabled form-control" name="district" placeholder="Digite o bairro" readonly>
                                    </div>
                                    <div class="form-group col-md-1">
                                        <label>Número</label>
                                        <input type="text" class="input-disabled form-control" name="number" placeholder="Digite o número da residência" readonly>
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
                <h4 class="page-title">Clientes</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home'); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/clients/home'); ?>">Clientes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar Cliente</li>
                </ol>
                <?= admin_back('/admin/clients/home'); ?>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card m-b-20">
                        <div class="card-header">
                            <h3 class="card-title">Editar Cliente</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= url('/admin/clients/client/' . $client->id); ?>" method="post">
                                <input type="hidden" name="action" value="update">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Razão Social</label>
                                        <input type="text" class="form-control" name="corporate_name" value="<?= $client->corporate_name; ?>">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>CNPJ</label>
                                        <input type="text" class="form-control" name="cnpj" value="<?= $client->cnpj; ?>">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>E-mail</label>
                                        <input type="email" class="form-control" name="email" value="<?= $client->email; ?>">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Telefone</label>
                                        <input type="tel" class="form-control mask-phone" name="phone" value="<?= $client->phone; ?>">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Nome do Contato</label>
                                        <input type="text" class="form-control" name="contact_name" value="<?= $client->contact_name; ?>">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Vendedores</label>
                                        <select name="id_seller" class="form-control">
                                            <option value="">Selecionar</option>
                                            <?php if ($sellers): ?>
                                                <?php
                                                $sellerID = $client->id_seller;
                                                $selected = function ($value) use ($sellerID)
                                                {
                                                    return ($sellerID == $value) ? 'selected' : '';
                                                }
                                                ?>
                                                <?php foreach ($sellers as $seller): ?>
                                                    <option <?= $selected($seller->id); ?> value="<?= $seller->id; ?>"><?= $seller->fullName(); ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>CEP</label>
                                        <div class="row">
                                            <div class="col-md-9 pr-0">
                                                <input type="search" class="form-control mask-cep" name="cep" value="<?= $client->cep; ?>" placeholder="Digite o CEP">
                                            </div>
                                            <div class="col-md-3 pl-1">
                                                <button type="button" class="cep-search btn btn-dark">BUSCAR</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label>Cidade</label>
                                        <input type="text" class="input-disabled form-control" name="city" value="<?= $client->city; ?>" placeholder="Digite a cidade" readonly>
                                    </div>
                                    <div class="form-group col-md-1">
                                        <label>UF</label>
                                        <input type="text" class="input-disabled form-control" name="state" value="<?= $client->state; ?>" placeholder="Digite o estado" readonly>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Endereço</label>
                                        <input type="text" class="form-control" name="address" value="<?= $client->address; ?>" placeholder="Digite o endereço">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label>Bairro</label>
                                        <input type="text" class="form-control" name="district" value="<?= $client->district; ?>" placeholder="Digite o bairro">
                                    </div>
                                    <div class="form-group col-md-1">
                                        <label>Número</label>
                                        <input type="text" class="form-control" name="number" value="<?= $client->number; ?>" placeholder="Digite o número da residência">
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
<?php $v->start('scripts'); ?>
    <script>
        let cep = document.querySelector('[name=cep]'),
            cepSearch = document.querySelector('.cep-search'),
            city = document.querySelector('[name=city]'),
            uf = document.querySelector('[name=state]'),
            address = document.querySelector('[name=address]'),
            district = document.querySelector('[name=district]'),
            number = document.querySelector('[name=number]')

        cepSearch.addEventListener('click', () => {
            let url = cep.value
            url = `https://viacep.com.br/ws/${url.replace('-', '')}/json/`

            axios.get(url).then(function(response) {
                if (response) {
                    if (response.data.localidade && response.data.uf) {
                        if (response.data.localidade) {
                            city.value = response.data.localidade
                        }

                        if (response.data.uf) {
                            uf.value = response.data.uf
                        }

                        if (response.data.logradouro) {
                            address.value = response.data.logradouro
                        } else {
                            address.value = ''
                            address.removeAttribute('readonly')
                            address.classList.remove('input-disabled')
                        }

                        if (response.data.bairro) {
                            district.value = response.data.bairro
                        } else {
                            district.value = ''
                            district.removeAttribute('readonly')
                            district.classList.remove('input-disabled')
                        }

                        number.value = ''
                        number.removeAttribute('readonly')
                        number.classList.remove('input-disabled')
                    } else {
                        city.value = ''
                        uf.value = ''
                        address.value = ''
                        district.value = ''
                        number.value = ''

                        if (!address.hasAttribute('readonly')) {
                            address.setAttribute('readonly', 'readonly')
                            address.classList.add('input-disabled')
                        }

                        if (!district.hasAttribute('readonly')) {
                            district.setAttribute('readonly', 'readonly')
                            district.classList.add('input-disabled')
                        }

                        if (!number.hasAttribute('readonly')) {
                            number.setAttribute('readonly', 'readonly')
                            number.classList.add('input-disabled')
                        }
                    }
                }
            })
        })
    </script>
<?php $v->end('scripts'); ?>