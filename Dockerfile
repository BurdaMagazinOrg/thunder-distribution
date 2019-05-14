FROM php:7.3-cli-stretch

SHELL ["/bin/bash", "-o", "pipefail", "-c"]

# Prepare docker environments
ARG BRANCH_NAME="8.x-2.x"

# Install packages needed for additional repos packages
RUN set -xe; \
    apt-get update;

RUN set -xe; \
    apt-get install -y --no-install-recommends \
        gnupg \
        apt-transport-https;

# Yarn
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list

# Install additional packages
RUN set -xe; \
    apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        sendmail \
        expect \
        zip \
        unzip \
        libpng-dev \
        libzip-dev \
        libxml2-dev \
        wget \
        yarn \
        unzip \
        imagemagick \
        libmagickwand-dev \
        mysql-client \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*;

# GD
RUN set -xe; \
    docker-php-ext-configure gd \
        --with-gd \
        --with-freetype-dir=/usr/include/ \
        --with-png-dir=/usr/include/ \
        --with-jpeg-dir=/usr/include/; \
      NPROC=$(getconf _NPROCESSORS_ONLN); \
      docker-php-ext-install "-j${NPROC}" gd;

# Install PHP libraries
RUN set -xe; \
    docker-php-ext-install pdo_mysql zip opcache;

RUN set -xe; \
    pecl install imagick;

RUN set -xe; \
    docker-php-ext-enable imagick;

# Setup Sendmail
RUN set -xe; \
    echo -e '#!/usr/bin/expect -f\n\
        \n\
        set timeout 10\n\
        spawn sendmailconfig\n\
        expect "Configure sendmail with the existing /etc/mail/sendmail.conf?"\n\
        send -- "y\\r"\n\
        expect "Configure sendmail with the existing /etc/mail/sendmail.mc?"\n\
        send -- "y\\r"\n\
        expect "Reload the running sendmail now with the new configuration?"\n\
        send -- "y\\r"\n\
        expect eof\n' >> sendmailconfig_expect.sh \
    && cat sendmailconfig_expect.sh \
    && chmod +x sendmailconfig_expect.sh \
    && ./sendmailconfig_expect.sh \
    && rm -rf ./sendmailconfig_expect.sh;

# Setup PHP Cli OpCache
RUN set -xe; \
    echo -e "\n\
opcache.enable=1\n\
opcache.memory_consumption=128\n\
opcache.interned_strings_buffer=8\n\
opcache.max_accelerated_files=4000\n\
opcache.revalidate_freq=60\n\
opcache.fast_shutdown=1\n\
    " >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini;

# Install composer
RUN set -xe; \
    curl https://getcomposer.org/installer --output composer-setup.php \
    && php composer-setup.php \
    && rm -rf composer-setup.php \
    && mv ./composer.phar /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer;

# Create required user
RUN set -xe; \
    adduser --gecos "" --disabled-password thunder \
    && usermod -aG sudo thunder;

RUN set -xe; \
    su - thunder -c "curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.34.0/install.sh | bash"; \
    echo -e "\nexport NVM_DIR=\"\$HOME/.nvm\"\n[ -s \"\$NVM_DIR/nvm.sh\" ] && \. \"\$NVM_DIR/nvm.sh\"\n" >> /home/thunder/.profile;

RUN set -xe; \
    su - thunder -c "nvm install 10";

# Copy build script
COPY scripts/docker/docker-thunder-build /usr/local/bin/

# Set executable
RUN set -xe; \
    chmod +x /usr/local/bin/docker-thunder-build;

RUN set -xe; \
    echo -e "\nexport BRANCH_NAME=\"$BRANCH_NAME\"" >> /home/thunder/.profile;

# Execute build as thunder user
RUN set -xe; \
    su - thunder -c "docker-thunder-build";

# Copy build script
COPY scripts/docker/docker-thunder-install /usr/local/bin/

# Set executable
RUN set -xe; \
    chmod +x /usr/local/bin/docker-thunder-install;

# Copy build script
COPY scripts/docker/docker-thunder-run-tests /usr/local/bin/

# Set executable
RUN set -xe; \
    chmod +x /usr/local/bin/docker-thunder-run-tests;

# Define all runtime environments
ENV DB_HOST="127.0.0.1"
ENV CHROME_HOST="127.0.0.1"
ENV THUNDER_HOST="localhost"
ENV ELASTIC_APM_URL="http://localhost:8200"
ENV ELASTIC_APM_CONTEXT_TAG_BRANCH="8.x-2.x"

EXPOSE 8080/tcp
CMD ["/usr/local/bin/docker-thunder-install"]
