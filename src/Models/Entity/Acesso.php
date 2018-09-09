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
}
