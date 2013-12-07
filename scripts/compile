#!/bin/bash
onion -d compile \
    --lib src \
    --lib vendor/pear \
    --classloader \
    --bootstrap scripts/minipear.emb \
    --executable \
    --output minipear.phar
mv minipear.phar minipear
chmod +x minipear
