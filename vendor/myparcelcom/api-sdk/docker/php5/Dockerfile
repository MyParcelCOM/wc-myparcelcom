FROM ubuntu:20.04

# Install locales for terminal and php.
RUN apt-get update \
    && apt-get install -y locales \
    && locale-gen en_US.UTF-8 \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ENV LANG en_US.UTF-8
ENV LANGUAGE en_US:en
ENV LC_ALL en_US.UTF-8

RUN apt-get update \
    && apt-get install -y curl zip unzip software-properties-common gettext-base iproute2 \
    && add-apt-repository -y ppa:ondrej/php \
    && apt-get update \
    && apt-get install -y php5.6-cli php5.6-xdebug php5.6-dom php5.6-mbstring php5.6-curl \
    && php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer \
    && mkdir /run/php \
    && apt-get remove -y --purge software-properties-common \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Enable xdebug
COPY conf/xdebug.ini /etc/php/5.6/mods-available/xdebug.ini.template
RUN ln -sf /etc/php/5.6/mods-available/xdebug.ini /etc/php/5.6/cli/conf.d/20-xdebug.ini \
    && sed -e 's/error_reporting = E_ALL \& \~E_DEPRECATED \& \~E_STRICT/error_reporting = E_ALL/' -i /etc/php/5.6/cli/php.ini

# Copy entrypoint script.
COPY entrypoint.sh /bin/entrypoint.sh

# Set the environment variables.
ENV XDEBUG_PORT 9000
ENV XDEBUG_IDE_KEY myparcelcom_sdk

WORKDIR /opt/sdk

EXPOSE 443

ENTRYPOINT ["/bin/entrypoint.sh"]
