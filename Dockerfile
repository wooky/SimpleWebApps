FROM php:8.2-apache
ARG NPMVER=v18.13.0

RUN \
  (curl -sS https://getcomposer.org/installer | php) && mv composer.phar /usr/local/bin/composer && rm -f composer-setup.php && \
  (curl -sS https://get.symfony.com/cli/installer | bash) && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony && \
  (curl -sS https://nodejs.org/dist/${NPMVER}/node-${NPMVER}-linux-x64.tar.xz | tar -xJf -) && mv node-${NPMVER}-linux-x64 /usr/local/nodejs && ln -sf /usr/local/nodejs/bin/npm /usr/local/bin/npm && ln -sf /usr/local/nodejs/bin/node /usr/local/bin/node
