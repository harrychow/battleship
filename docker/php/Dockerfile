FROM php:7.2-fpm

RUN apt-get update \
 && apt-get install -y git zlib1g-dev zip

#RUN curl -Lsf 'https://storage.googleapis.com/golang/go1.8.3.linux-amd64.tar.gz' | tar -C '/usr/local' -xvzf -
#ENV PATH /usr/local/go/bin:$PATH
#RUN go get github.com/mailhog/mhsendmail
#RUN cp /root/go/bin/mhsendmail /usr/bin/mhsendmail
#RUN echo 'sendmail_path = /usr/bin/mhsendmail --smtp-addr mailhog:1025' > /usr/local/etc/php/php.ini

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

RUN docker-php-ext-install mysqli pdo_mysql zip
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
