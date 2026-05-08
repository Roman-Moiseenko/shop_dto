FROM php:8.4-fpm

# Устанавливаем рабочую директорию
WORKDIR /var/www

# Копируем composer.lock и composer.json
COPY composer.lock composer.json /var/www/
# Устанавливаем зависимости
#RUN apt-get update && apt-get -y install git && apt-get -y install zip
# RUN apt-get update && apt-get install -y \ build-essential \ libpng-dev \ libjpeg62-turbo-dev \ libfreetype6-dev \ locales \ zip \ jpegoptim optipng pngquant gifsicle \ vim \ unzip \ git \ curl \ libpq-dev \ libonig-dev \ libzip-dev && \ docker-php-ext-configure gd --with-freetype --with-jpeg && \ docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd && \ apt-get clean && rm -rf /var/lib/apt/lists/*
RUN apt-get update && apt-get install -y locales \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libmagickwand-dev \
    libzip-dev \
    libonig-dev \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl

RUN pecl install imagick

# Clear cache
#RUN apt-get clean && rm -rf /var/lib/apt/lists/*
# Install extensions
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install zip
RUN docker-php-ext-install exif
RUN docker-php-ext-install pcntl
#RUN docker-php-ext-configure gd --with-gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/
RUN docker-php-ext-install gd
RUN docker-php-ext-install imagick

# Загружаем актуальную версию Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# Создаём пользователя и группу www для приложения Laravel
#RUN groupadd -g 1000 www
#RUN useradd -u 1000 -ms /bin/bash -g www www
# Копируем содержимое текущего каталога в рабочую директорию
#COPY . /var/www
#COPY --chown=www:www . /var/www
# Меняем пользователя на www
#USER www
# В контейнере открываем 9000 порт и запускаем сервер php-fpm
EXPOSE 9000
CMD ["php-fpm"]
