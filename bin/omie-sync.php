<?php
/**
 * Sincroniza clientes e produtos da Omie para o banco local.
 *
 * Uso (com as credenciais no ambiente):
 *   SORIEDEM_LOCAL=1 OMIE_APP_KEY=... OMIE_APP_SECRET=... php bin/omie-sync.php [produtos|clientes|all]
 */

require __DIR__ . "/../vendor/autoload.php";

use Source\Support\OmieSync;

$what = $argv[1] ?? "all";
$sync = new OmieSync();

if ($what === "produtos" || $what === "all") {
    fwrite(STDOUT, "Sincronizando produtos...\n");
    if (!$sync->syncProdutos()) {
        fwrite(STDERR, "ERRO produtos: " . $sync->error() . "\n");
        exit(1);
    }
    print_r($sync->stats());
}

if ($what === "clientes" || $what === "all") {
    fwrite(STDOUT, "Sincronizando clientes...\n");
    if (!$sync->syncClientes()) {
        fwrite(STDERR, "ERRO clientes: " . $sync->error() . "\n");
        exit(1);
    }
    print_r($sync->stats());
}

if ($what === "vendedores" || $what === "all") {
    fwrite(STDOUT, "Mapeando vendedores...\n");
    if (!$sync->syncVendedores()) {
        fwrite(STDERR, "ERRO vendedores: " . $sync->error() . "\n");
        exit(1);
    }
    print_r($sync->stats());
}

if ($what === "fotos" || $what === "all") {
    fwrite(STDOUT, "Casando fotos (alta confianca)...\n");
    if (!$sync->matchPhotos()) {
        fwrite(STDERR, "ERRO fotos: " . $sync->error() . "\n");
        exit(1);
    }
    print_r($sync->stats());
}

fwrite(STDOUT, "Concluido.\n");
