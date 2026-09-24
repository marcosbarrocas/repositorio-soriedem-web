<?php $v->layout("_admin"); ?>
<!--App-Content-->
<div class="app-content my-3 my-md-5" data-url="<?= url(); ?>">
    <div class="side-app">
        <div class="page-header">
            <h4 class="page-title">Fotos pendentes (produtos Omie)</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/admin/dash/home') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Fotos pendentes</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Vincular foto a produtos Omie sem imagem</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Para cada produto vindo da Omie sem foto, busque um produto antigo que tenha a
                            imagem correta e clique nela para vincular. Nada e apagado.
                        </p>

                        <form class="form-inline mb-3" action="<?= url('/admin/products/photos'); ?>" method="post">
                            <div class="nav-search">
                                <input type="search" class="form-control header-search" name="s" value="<?= $search; ?>" placeholder="Filtrar por nome/código…">
                                <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button>
                            </div>
                        </form>

                        <table class="table table-bordered border-top mb-0">
                            <thead>
                                <tr>
                                    <th style="width:130px">Código</th>
                                    <th>Produto (Omie)</th>
                                    <th style="width:45%">Buscar foto de produto antigo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($products) : ?>
                                    <?php foreach ($products as $p) : ?>
                                        <tr data-dest="<?= $p->id; ?>">
                                            <td><?= $p->code; ?></td>
                                            <td><?= $p->title; ?></td>
                                            <td>
                                                <div class="d-flex" style="gap:6px">
                                                    <input type="text" class="form-control search-src" placeholder="nome ou código do produto antigo">
                                                    <button type="button" class="btn btn-info btn-search-src">Buscar</button>
                                                </div>
                                                <div class="src-results d-flex flex-wrap mt-2" style="gap:8px"></div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr><td colspan="3" class="text-center text-muted">Nenhum produto Omie pendente de foto. 🎉</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?= $paginator; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/App-Content-->
<?php $v->start('scripts'); ?>
<script>
    const baseUrl = document.querySelector('.app-content').getAttribute('data-url')

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]))
    }

    document.querySelectorAll('tr[data-dest]').forEach(row => {
        const destId = row.getAttribute('data-dest')
        const input = row.querySelector('.search-src')
        const btn = row.querySelector('.btn-search-src')
        const results = row.querySelector('.src-results')

        function doSearch() {
            const term = input.value.trim()
            if (term.length < 2) { alert('Digite ao menos 2 caracteres.'); return }
            results.innerHTML = '<span class="text-muted">Buscando…</span>'
            const form = new FormData()
            form.append('q', term)
            form.append('only_with_photo', '1')
            axios.post(`${baseUrl}/admin/products/search`, form).then(res => {
                const list = res.data || []
                if (!list.length) { results.innerHTML = '<span class="text-muted">Nada encontrado.</span>'; return }
                results.innerHTML = ''
                for (const p of list) {
                    const src = p.photo.startsWith('http') ? p.photo : baseUrl + '/storage/' + p.photo
                    const card = document.createElement('div')
                    card.style.cssText = 'cursor:pointer;text-align:center;max-width:90px'
                    card.title = 'Vincular esta foto'
                    card.innerHTML = `<img src="${src}" width="70" height="70" style="object-fit:cover;border:1px solid #ddd;border-radius:4px">
                        <div style="font-size:10px;line-height:1.1">${escapeHtml(p.code)}</div>`
                    card.addEventListener('click', () => link(destId, p.id, card))
                    results.appendChild(card)
                }
            }).catch(() => { results.innerHTML = '<span class="text-danger">Erro na busca.</span>' })
        }

        btn.addEventListener('click', doSearch)
        input.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); doSearch() } })
    })

    function link(destId, sourceId, card) {
        const form = new FormData()
        form.append('id', destId)
        form.append('source_id', sourceId)
        axios.post(`${baseUrl}/admin/products/link-photo`, form).then(res => {
            if (res.data && res.data.reload) {
                location.reload()
            } else {
                alert((res.data && res.data.error) || 'Não foi possível vincular.')
            }
        }).catch(() => alert('Erro ao vincular.'))
    }
</script>
<?php $v->end('scripts'); ?>
