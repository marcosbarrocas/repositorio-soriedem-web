<?php

namespace Source\Models;

use Source\Core\Model;

/**
 * @package Source\Models
 */
class Client extends Model
{
    /**
     * Client constructor.
     */
    public function __construct()
    {
        parent::__construct('clients', ['id'], ['corporate_name', 'address', 'number', 'district', 'id_seller', 'city', 'state', 'phone', 'email', 'contact_name', 'cnpj']);
    }

    /**
     * @param string $email
     * @param string $columns
     * @return null|Client
     */
    public function findByEmail(string $email, string $columns = "*"): ?Client
    {
        $find = $this->find("email = :email", "email={$email}", $columns);
        return $find->fetch();
    }

    /**
     * @return string
     */
    public function fullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * @return Seller|null
     */
    public function getSeller(): ?Seller
    {
        return (new Seller())->findById($this->id_seller);
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        if (!$this->required()) {
            $this->message->warning("Nome, sobrenome, email e senha são obrigatórios");
            return false;
        }

        if (!is_email($this->email)) {
            $this->message->warning("O e-mail informado não tem um formato válido");
            return false;
        }

        /** Client Update */
        if (!empty($this->id)) {
            $clientId = $this->id;

            if ($this->find("email = :e AND id != :i", "e={$this->email}&i={$clientId}", "id")->fetch()) {
                $this->message->warning("O e-mail informado já está cadastrado");
                return false;
            }

            $this->update($this->safe(), "id = :id", "id={$clientId}");
            if ($this->fail()) {
                $this->message->error("Erro ao atualizar, verifique os dados");
                return false;
            }
        }

        /** Client Create */
        if (empty($this->id)) {
            if ($this->findByEmail($this->email, "id")) {
                $this->message->warning("O e-mail informado já está cadastrado");
                return false;
            }

            $clientId = $this->create($this->safe());
            if ($this->fail()) {
                $this->message->error("Erro ao cadastrar, verifique os dados");
                return false;
            }
        }

        $this->data = ($this->findById($clientId))->data();
        return true;
    }
}