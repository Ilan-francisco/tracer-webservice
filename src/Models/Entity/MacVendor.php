<?php
/**
 * Created by PhpStorm.
 * User: ilan
 * Date: 27/11/18
 * Time: 12:26
 */

namespace App\Models\Entity;


/**
 * @Entity @Table(name="mac_vendor")
 **/
class MacVendor {
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
    public $mac_init;

    /**
     * @var string
     * @Column(type="string")
     */
    public $vendor;

}