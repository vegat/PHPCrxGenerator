<?php

    // Include file
    include('CrxGenerator.php');

    $crx = new CrxGenerator();

    $crx->setPrivateKey('private_key.pem');
    $crx->setPublicKey('public_key.pub');
    $crx->setSourceDir('example_extension');
    $crx->setCacheDir('cache');

    $crx->generateCrx('cache/example.crx');

    echo 'Done';