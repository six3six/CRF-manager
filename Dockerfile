FROM php:7.2-apache
RUN apt-get update \
    && apt-get install -y --no-install-recommends locales apt-utils git;

RUN apt-get install -y php7.3-mysql php7.3-intl

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
    echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen

RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
   mv composer.phar /usr/local/bin/composer

RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | sudo apt-key add - && \
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | sudo tee /etc/apt/sources.list.d/yarn.list && \
    sudo apt install yarn -y

COPY . /var/www/public
WORKDIR /var/www/public

RUN composer install
RUN yarn install
RUN yarn run encore production