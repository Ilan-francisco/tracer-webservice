<?php
namespace App\Models\Entity;
/**
 * @Entity @Table(name="usuarios")
 **/
class Usuario {
    /**
     * @var int
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    public $id;

    /**
     * @var string
     * @Column(type="string", length=17)
     */
    public $mac_celular;

    /**
     * @var string
     * @Column(type="string")
     */
    public $nome;

    /**
     * @var string
     * @Column(type="string")
     */
    public $email;

    /**
     * @var string
     * @Column(type="datetime")
     */
    public $data_cadastro;

    /**
     * @var bool
     * @Column(type="boolean")
     */
    public $ativo;


}
