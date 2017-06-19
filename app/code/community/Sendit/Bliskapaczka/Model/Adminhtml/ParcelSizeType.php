<?php
/**
 * Bliskapaczka shipping method configuration in admin panel
 *
 * @author Mateusz Koszutowski (mkoszutowski@divante.pl)
 */
class Sendit_Bliskapaczka_Model_Adminhtml_ParcelSizeType
{
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label' => Mage::helper('core')->__('Fixed')),                     
        );
    }

}