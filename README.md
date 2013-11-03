PHPCrxGenerator
===============

Easily generate Chrome Extensions from PHP. Modify your extension and build new CRX file from your script!

How to use:
===============
You need your unpacked extension (source folder) and private key (PEM). 

**How to get PEM file?**

When you create your extension using Google Chrome ( check http://developer.chrome.com/extensions/packaging.html )
PEM file will be created automaticly for you. I've included PEM file **FOR EXAMPLE ONLY!** Do not use it in your production environment, I hope that I don't have to explain why it is so dangerous. Will say it again: **INCLUDED PEM FILE IS ONLY FOR TESTING PURPOSES**, better create your own, it will take only 2 minutes or less.

**Generate PUB key**

Now, we need to create PUB key. I've tried to automate this process, but as far I can't find working solution for converting priv keys to DER public keys. So, run from your console:

    openssl rsa -pubout -outform DER < *private_key.pem* >  public_key.pub

Ofcourse replace *private_key.pem* with your own private key filename.

**Let's code!**

Usage of this small lib is simple, just include it to your existing code and use it like that:


    // Include file
    include('CrxGenerator.php');
  
    // Create instance of CrxGenerator
    $crx = new CrxGenerator();

    $crx->setPrivateKey('private_key.pem'); // Path to your PEM file (private key)
    $crx->setPublicKey('public_key.pub'); // Path to your PUB key (public key)
    $crx->setSourceDir('example_extension'); // Path to your extension source folder
    $crx->setCacheDir('cache'); // Path to writable folder

    $crx->generateCrx('cache/example.crx'); // Where to put generated file - this dir MUST be writable

And that's it!
