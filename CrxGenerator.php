<?php

/**
 * Class CrxGenerator
 *
 * Create Chrome Extension CRX packages from
 * folder & pem private key
 *
 * Based on CRX format documentation: http://developer.chrome.com/extensions/crx.html
 *
 * @author: Tomasz Banasiak <tomasz@banasiak.pro>
 * @license: MIT
 * @date: 2013-11-03
 */

class CrxGenerator {
    const KEY_FORMAT_DER = 'der';
    const KEY_FORMAT_PEM = 'pem';
    const TEMP_ARCHIVE_EXT = '.zip';

    private $sourceDir = null;
    private $cacheDir = '';

    private $privateKeyContents = null;
    private $publicKeyContents = null;

    private $privateKey = null;
    private $publicKey = null;

    /**
     * @param $file Path to PEM key
     * @throws Exception
     */
    public function setPrivateKey($file) {
        if (!file_exists($file)) {
            throw new Exception('Private key file does not exist');
        }

        $this->privateKeyContents = file_get_contents($file);
        $this->privateKey = $file;
    }

    /**
     * @param $file Path to PUB key
     * @throws Exception
     */
    public function setPublicKey($file) {
        if (!file_exists($file)) {
            throw new Exception('Private key file does not exist');
        }

        $this->publicKeyContents = file_get_contents($file);
        $this->publicKey = $file;
    }

    /**
     * @param $cacheDir dir specified for caching temporary archives
     * @throws Exception
     */
    public function setCacheDir($cacheDir) {
        if (!is_dir($cacheDir)) {
            throw new Exception('Cache dir does not exist!');
        }

        $this->cacheDir = $cacheDir;
    }

    /**
     * @param $sourceDir Extension source directory
     */
    public function setSourceDir($sourceDir) {
        $this->sourceDir = $sourceDir;
    }

    /**
     * @param $outputFile path to output file
     * @throws Exception
     */
    public function generateCrx($outputFile) {
        $basename = basename($outputFile);
        // First step - create ZIP archive
        $zipArchive = $this->cacheDir . DIRECTORY_SEPARATOR . $basename . self::TEMP_ARCHIVE_EXT;

        $result = $this->createZipArchive(
            $this->sourceDir,
            $zipArchive
        );

        if (!$result) {
            throw new Exception('ZIP creation failed');
        }

        $zipContents = file_get_contents($zipArchive);

        // Second step - create file signature
        $privateKey = openssl_pkey_get_private($this->privateKeyContents);\
        openssl_sign($zipContents, $signature, $privateKey, 'sha1');
        openssl_free_key($privateKey);

        // Create output file

        $crx = fopen($outputFile, 'wb');
        fwrite($crx, 'Cr24');
        fwrite($crx, pack('V', 2));
        fwrite($crx, pack('V', strlen($this->publicKeyContents)));
        fwrite($crx, pack('V', strlen($signature)));
        fwrite($crx, $this->publicKeyContents);
        fwrite($crx, $signature);
        fwrite($crx, $zipContents);
        fclose($crx);

        // Clear cache
        unset($zipArchive);
    }

    private function createZipArchive($source, $outputFile) {

        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new ZipArchive();
        if (!$zip->open($outputFile, ZIPARCHIVE::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if( in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')) )
                    continue;

                $file = realpath($file);

                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                }
                else if (is_file($file) === true) {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        }
        else if (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }

        return $zip->close();
    }

}