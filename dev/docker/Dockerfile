FROM bliskapaczkapl/magento_base:7.2_1.9.4.0
MAINTAINER Mateusz Koszutowski <mkoszutowski@divante.pl>

RUN apt-get update && apt-get install -y \
    mc \
    nano

# Install and enable xDebug
RUN pecl install xdebug
# Copy latest version of Bliskapaczka module
COPY app ${magento_path}/app
COPY dev ${magento_path}/dev
COPY js ${magento_path}/js
COPY lib ${magento_path}/lib
COPY skin ${magento_path}/skin

# Install Bliskapaczka
COPY composer.json ${magento_path}/composer.json
COPY composer.lock ${magento_path}/composer.lock
RUN (cd ${magento_path} && composer install --no-dev)
RUN sed -i 's%<active>false</active>%<active>true</active>%g' ${magento_path}/app/etc/modules/Sendit_Bliskapaczka.xml
RUN (cd ${magento_path} && rm -rf var/cache/)

# Copy Magento config file
COPY dev/docker/magento/local.xml ${magento_path}/app/etc/local.xml

# Fix privileges
RUN find ${magento_path} -type d -exec chmod 770 {} \; && find ${magento_path} -type f -exec chmod 660 {} \; \
    && chown -R :www-data ${magento_path}
