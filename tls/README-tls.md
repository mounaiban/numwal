# TLS Certificate and Private Key Directory

Place your certificate and private key in this directory, so the image build
process can find them and insert them into your image.

The Dockerfile expects your certificate (public key) to be named `numwal.pem`
and your private key to be named `numwal-private.pem`.

If you wish to use different names for keys, and/or source them from another
path, please keep the Dockerfile `Dockerfile-nginx-tls` and the Nginx 
configuration file `numwal-nginx` in sync with these changes.

**PROTIP**: Triple-check your commits before you make them to watch for
private key leaks!

