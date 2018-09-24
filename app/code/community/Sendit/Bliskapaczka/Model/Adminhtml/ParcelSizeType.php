<?php
/**
 * Bliskapaczka shipping method configuration in admin panel
 *
 * @author Mateusz Koszutowski (mkoszutowski@divante.pl)
 */
class Sendit_Bliskapaczka_Model_Adminhtml_ParcelSizeType
{
    /**
     * Array with options to select in admin page module configuration
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label' => Mage::helper('core')->__('Fixed')),
        );
    }
}
