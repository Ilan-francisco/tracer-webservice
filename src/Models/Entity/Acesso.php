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
     * @Column(type="string") 
     */
    public $mac;
    /**
     * @var int
     * @Column(type="integer") 
     */
    public $rssi;
    /**
     * @var string
     * @Column(type="datetime") 
     */
    public $horario;
    /**
     * @var int
     * @Column(type="integer") 
     */
    public $id_device;
    
    /**
     * @return int id
     */
    public function getId(){
        return $this->id;
    }
    
    /**
     * @return string mac
     */
    public function getMac(){
        return $this->mac;
    }
    
    /**
     * @return int rssi
     */
    public function getRssi() {
        return $this->rssi;
    }
    
    /**
     * @return string horario
     */
    public function getHorario() {
        return $this->horario;
    }
    
    /**
     * @return int id_device
     */
    public function getIdDevice() {
        return $this->id_device;
    }
    
    /**
     * @return Acesso()
     */
    public function setMac($mac) {
        $this->mac = $mac;
        return $this;    
    }     
    
    /**
     * @return Acesso()
     */
    public function setRssi($rssi) {
        $this->rssi = $rssi;
        return $this;    
    }
    
    /**
     * @return Acesso()
     */
    public function setHorario($horario) {
        $this->horario = $horario;
        return $this;    
    }
    
    /**
     * @return Acesso()
     */
    public function setIdDevice($id_device) {
        $this->id_device = $id_device;
        return $this;    
    }
}
