VENDOR_DIR=app/code/community/Sendit/Bliskapaczka/vendor

docker run --rm -u $(id -u):$(id -g) -v $(pwd):/app -v ~/.composer:/tmp/composer -e COMPOSER_HOME=/tmp/composer composer/composer:php5 install

$VENDOR_DIR/bin/phpcs -s --colors --standard=./$VENDOR_DIR/magento-ecg/coding-standard/Ecg app/ --ignore=app/code/community/Sendit/Bliskapaczka/vendor/*
$VENDOR_DIR/bin/phpmd app/ text codesize --exclude app/code/community/Sendit/Bliskapaczka/vendor
$VENDOR_DIR/bin/phpcpd --exclude vendor app/
$VENDOR_DIR/bin/phpdoccheck --directory=app --exclude=code/community/Sendit/Bliskapaczka/vendor
$VENDOR_DIR/bin/phploc app/
$VENDOR_DIR/bin/phpunit --bootstrap dev/tests/bootstrap.php dev/tests/unit/

docker run --rm -u $(id -u):$(id -g) -v $(pwd):/app -v ~/.composer:/tmp/composer -e COMPOSER_HOME=/tmp/composer composer/composer:php5 install --no-dev
