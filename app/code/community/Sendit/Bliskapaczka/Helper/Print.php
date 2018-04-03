<?php

use Bliskapaczka\ApiClient;
use Neodynamic\SDK\Web\WebClientPrint;
use Neodynamic\SDK\Web\DefaultPrinter;
use Neodynamic\SDK\Web\InstalledPrinter;
use Neodynamic\SDK\Web\PrintFile;
use Neodynamic\SDK\Web\PrintFilePDF;
use Neodynamic\SDK\Web\ClientPrintJob;
/**
 * Bliskapaczka helper
 *
 * @author Mateusz Koszutowski (mkoszutowski@divante.pl)
 */
class Sendit_Bliskapaczka_Helper_Print extends Mage_Core_Helper_Data
{
    /**
     * Download content from URL
     *
     * @param string $url
     *
     * @return string
     */
    public function downloadContent($url)
    {
        $content = '';

        if (!$url) {
            return $content;
        }

        $http = new Varien_Http_Adapter_Curl();
        $http->write('GET', $url);
        $content = $http->read();
        $http->close();

        return $content;
    }

    /**
     * @param string $path
     * @param string $fileName
     * @param string $content
     *
     * @return bool|void
     */
    public function writeFile($path, $fileName, $content)
    {
        if (!$path || !$fileName || !$content) {
            return false;
        }

        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        if ($io->fileExists($fileName) && !$io->isWriteable($fileName)) {
            // file does not exist or is not readable
            return;
        }

        $io->streamOpen($fileName);
        $io->streamWrite($content);
        $io->streamClose();

        return true;
    }

    /**
     * Neodynamic print
     *
     * @param string $url
     */
    public function neodynamicPrint($url)
    {
        //create a temp file name for our PDF file...
        $fileName = uniqid();

        $path = Mage::getBaseDir('media') . DS . 'tmp' . DS . 'pdf';

        $content = Mage::helper('sendit_bliskapaczka/print')->downloadContent($url);

        Mage::helper('sendit_bliskapaczka/print')->writeFile($path, $fileName, $content);

        $filePath = $path . DS . $fileName;

        //Create a ClientPrintJob obj that will be processed at the client side by the WCPP
        $cpj = new ClientPrintJob();
        //Create a PrintFilePDF object with the PDF file
        $cpj->printFile     = new PrintFilePDF($filePath, $fileName, null);
        $cpj->clientPrinter = new DefaultPrinter();

        return $cpj;
    }
}
