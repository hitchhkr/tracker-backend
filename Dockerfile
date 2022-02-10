FROM phpdev:8.0

#RUN yum install -y php-pecl-mcrypt php-gmp php-pgsql

# RUN sed -i 's/memory_limit = 512M/memory_limit = 4096M/g' /etc/php.ini
RUN sed -i 's/max_execution_time = 30/max_execution_time = 3600/g' /etc/php.ini

RUN yum -y remove composer

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# RUN npm config set "@fortawesome:registry" https://npm.fontawesome.com/ && \
#     npm config set "//npm.fontawesome.com/:_authToken" 55093C26-00AE-46DE-9BBC-5E0E1EA1E508

WORKDIR /web/app