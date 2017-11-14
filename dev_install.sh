#!/bin/bash

if ! ( [ -d /var/www/magento ] );
then
    echo "Cannot find magento directory";
    exit 1;
fi

cp -R ./app /var/www/magento
cp -R ./skin /var/www/magento

echo "You may need to change file owner";