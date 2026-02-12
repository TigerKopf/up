# Wir nutzen das offizielle PHP-Apache Image
FROM php:8.2-apache

# Installiere Abhängigkeiten für cURL (falls nötig, oft schon drin)
RUN apt-get update && apt-get install -y libcurl4-openssl-dev pkg-config libssl-dev

# Kopiere die index.php aus deinem Repo in das Web-Verzeichnis des Containers
COPY src/index.php /var/www/html/index.php

# Setze die PHP-Limits direkt im Image
RUN echo "upload_max_filesize = 25M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 30M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 128M" >> /usr/local/etc/php/conf.d/uploads.ini

# Apache Port freigeben
EXPOSE 80
