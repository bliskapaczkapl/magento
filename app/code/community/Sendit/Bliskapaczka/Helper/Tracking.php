<?php

/**
 * Bliskapaczka API helper
 *
 * @author Mateusz Koszutowski (mkoszutowski@divante.pl)
 */
class Sendit_Bliskapaczka_Helper_Tracking extends Mage_Core_Helper_Data
{
    /**
     * Get Bliskapaczka API Client
     *
     * @param string $trackingNumer
     * @param Sendit_Bliskapaczka_Helper_Data $senditHelper
     * @return string
     */
    public function getLink($trackingNumer, $senditHelper)
    {
        $mode = $senditHelper->getApiMode($senditHelper->getStoreConfigWrapper($senditHelper::API_TEST_MODE_XML_PATH));

        switch ($mode) {
            case 'test':
                $link = 'https://sandbox-bliskapaczka.pl/';
                break;

            default:
                $link = 'https://bliskapaczka.pl/';
                break;
        }

        $link .= 'sledzenie/' . $trackingNumer;

        return $link;
    }
}
