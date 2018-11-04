<?php
namespace App\Models\Entity;
/**
 * @Entity @Table(name="dispositivos")
 */
class Dispositivo {
    /**
     * @var int
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    public $id;

    /**
     * @var double
     * @Column(type="float")
     */
    public $x;

    /**
     * @var double
     * @Column(type="float")
     */
    public $y;

    /**
     * @var string
     * @Column(type="string", length=17)
     */
    public $mac;

    /**
     * @var string
     * @Column(type="string")
     */
    public $nome_localidade;

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