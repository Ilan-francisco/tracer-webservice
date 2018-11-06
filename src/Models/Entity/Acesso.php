<?php
namespace App\Models\Entity;
/**
 * @Entity @Table(name="acessos")
 **/
class Acesso {
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
    public $mac_usuario;
    /**
     * @var int
     * @ManyToOne(targetEntity="Dispositivo")
     */
    public $id_dispositivo;
    /**
     * @var int
     * @Column(type="integer") 
     */
    public $intensidade_sinal;
    /**
     * @var string
     * @Column(type="datetime") 
     */
    public $data_hora_entrada;
    /**
     * @var string
     * @Column(type="datetime")
     */
    public $data_hora_visto_por_ultimo;
    /**
     * @var bool
     * @Column(type="boolean")
     */
    public $online;
}
