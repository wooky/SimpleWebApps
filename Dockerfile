FROM php:8.2-apache
ARG NPMVER=v18.13.0

RUN \
  (curl -sS https://getcomposer.org/installer | php) && mv composer.phar /usr/local/bin/composer && rm -f composer-setup.php && \
  (curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash) && \
  (curl -sS https://nodejs.org/dist/${NPMVER}/node-${NPMVER}-linux-x64.tar.xz | tar -xJf -) && mv node-${NPMVER}-linux-x64 /usr/local/nodejs && ln -sf /usr/local/nodejs/bin/npm /usr/local/bin/npm && ln -sf /usr/local/nodejs/bin/node /usr/local/bin/node && \
  apt install -y git libxml2-dev libzip-dev symfony-cli unzip zip && \
  docker-php-ext-install intl pdo_mysql sockets xml zip && \
  pecl install ast && docker-php-ext-enable ast && \
  a2enmod rewrite
