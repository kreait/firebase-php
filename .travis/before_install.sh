#!/usr/bin/env bash
set -evx

if [ "$TRAVIS_SECURE_ENV_VARS" == "true" ]; then
  echo "Decrypting secrets"
  openssl aes-256-cbc -K $encrypted_d51fa485ef84_key -iv $encrypted_d51fa485ef84_iv -in secrets.tar.enc -out secrets.tar -d;
  tar xvf secrets.tar
else
  echo "Not decrypting secrets"
fi

