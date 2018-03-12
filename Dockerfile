FROM debian:8
MAINTAINER Mateusz Koszutowski <mkoszutowski@divante.pl>

ENV magento_path /var/www/magento
ENV MAGENTO_VERSION 1.9.3.3

RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y \
    curl \
    git \
    unzip \
    apt-utils \
    rsync \
    sudo \
    nginx \
    mysql-client \
    php5 \
    php5-fpm \
    php5-cli \
    php5-mysql \
    php5-mcrypt \
    php5-curl \
    php5-gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer
RUN  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
	&& php composer-setup.php --install-dir=/usr/bin --filename=composer \
	&& php -r "unlink('composer-setup.php');"

# Nginx
COPY nginx.conf /etc/nginx/nginx.conf
COPY magento.conf /etc/nginx/sites-available/magento.conf
RUN (cd /etc/nginx/sites-enabled && ln -s ../sites-available/magento.conf magento.conf && rm -rf default)

# Magento
RUN curl https://codeload.github.com/OpenMage/magento-mirror/tar.gz/$MAGENTO_VERSION -o /tmp/$MAGENTO_VERSION.tar.gz \
    && tar xf /tmp/$MAGENTO_VERSION.tar.gz -C /tmp \
    && mv /tmp/magento-mirror-$MAGENTO_VERSION ${magento_path} \
    && find ${magento_path} -type d -exec chmod 770 {} \; && find ${magento_path} -type f -exec chmod 660 {} \; \
    && chown -R :www-data ${magento_path}

# Copy latest version of Bliskapaczka module
COPY app ${magento_path}/app
COPY dev ${magento_path}/dev
COPY js ${magento_path}/js
COPY skin ${magento_path}/skin

# Install Bliskapaczka
COPY composer.json ${magento_path}/composer.json
COPY composer.lock ${magento_path}/composer.lock
RUN (cd ${magento_path} && composer install --no-dev)
RUN sed -i 's%<active>false</active>%<active>true</active>%g' ${magento_path}/app/etc/modules/Sendit_Bliskapaczka.xml
RUN (cd ${magento_path} && rm -rf var/cache/)

# Copy Magento config file
COPY local.xml ${magento_path}/app/etc/local.xml

# Download Sample Data
RUN curl -L https://sourceforge.net/projects/mageloads/files/assets/1.9.2.4/magento-sample-data-1.9.2.4-fix.tar.gz/download -o ${magento_path}/magento-sample-data-1.9.2.4-fix.tar.gz \
    && tar xf ${magento_path}/magento-sample-data-1.9.2.4-fix.tar.gz -C ${magento_path} \
    && rsync -avzhq ${magento_path}/magento-sample-data-1.9.2.4/ ${magento_path}

# Download Magento Translations for Polish
RUN curl -L https://github.com/SnowdogApps/Magento-Translation-pl_PL/archive/master.zip -o /tmp/magento-translation-pl_pl.zip \
    && unzip -o /tmp/magento-translation-pl_pl.zip -d /tmp/ \
    && rsync -avzhq /tmp/Magento-Translation-pl_PL-master/ ${magento_path}/app/locale/

# Fix privileges
RUN find ${magento_path} -type d -exec chmod 770 {} \; && find ${magento_path} -type f -exec chmod 660 {} \; \
    && chown -R :www-data ${magento_path}

COPY run /opt/run

EXPOSE 80

CMD bash /opt/run