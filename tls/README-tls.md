# TLS Certificate and Private Key Directory

Place your public key certificate in the `keys/` directory, and your
private key in the `private/` directory. Please do this only for
self-signed test keys.

The key URIs may be set using the `NUMWAL_TLS_PUBLIC_KEY_URI` and
`NUMWAL_TLS_PRIVATE_KEY_URI` environment variables on the host before
building the containers.

**PROTIP**: Triple-check your commits before you make them to watch
for private key leaks!

