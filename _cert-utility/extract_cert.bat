echo off
mkdir %2
openssl pkcs12 -in %1.p12 -nocerts -out %2/key.pem -nodes
openssl pkcs12 -in %1.p12 -nokeys -out %2/certificate.pem