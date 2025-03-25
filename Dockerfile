ARG ALPINE_VERSION="3.20"
ARG PHP_VERSION="8.2"
ARG GCSFUSE_VERSION="1.2.0"
ARG THEMEPATH_1="web/app/themes/hkih"
ARG PLUGINPATH_1="web/app/plugins/hkih-linkedevents"
ARG PLUGINPATH_2="web/app/plugins/hkih-sportslocations"

FROM golang:alpine${ALPINE_VERSION} AS gcsfuse
ARG GCSFUSE_VERSION
RUN go install github.com/googlecloudplatform/gcsfuse@v${GCSFUSE_VERSION}

FROM php:${PHP_VERSION}-fpm-alpine${ALPINE_VERSION} AS base
RUN apk add --no-cache nginx
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && install-php-extensions gd xdebug
RUN install-php-extensions curl mysqli pdo_mysql opcache zip bcmath exif gd intl soap gettext redis opentelemetry protobuf mbstring @composer
ENV WP_CLI_ALLOW_ROOT=1
ENV PATH=/app/vendor/bin:${PATH}
WORKDIR /app
COPY --from=gcsfuse /go/bin/gcsfuse /usr/bin
ENTRYPOINT ["/app/config/container-init"]
CMD ["/app/config/start.sh"]

FROM base AS dev
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV CPPFLAGS="-DPNG_ARM_NEON_OPT=0"
RUN apk --no-cache add nodejs npm
RUN apk --no-cache add python3 \
  build-base libc6-compat autoconf automake libtool \
  pkgconf nasm libpng-dev zlib-dev libimagequant-dev

FROM dev as root-composer
WORKDIR /app
COPY composer.json .
COPY composer.lock .
# RUN --mount=type=secret,id=composer_auth,target=auth.json composer install --prefer-dist --no-dev --no-autoloader --no-scripts
RUN composer install --prefer-dist --no-dev --no-scripts
RUN composer run-script post-install-cmd
ARG SERVICE_NAME
RUN echo "Building service: ${SERVICE_NAME}"

RUN if [ "$SERVICE_NAME" = "app-staging" ]; then \
    composer update devgeniem/hkih-theme:dev-staging --no-dev && \
    composer update devgeniem/hkih-cpt-collection:dev-staging --no-dev && \
    composer update devgeniem/hkih-cpt-contact:dev-staging --no-dev && \
    composer update devgeniem/hkih-cpt-landing-page:dev-staging --no-dev && \
    composer update devgeniem/hkih-cpt-release:dev-staging --no-dev && \
    composer update devgeniem/hkih-cpt-translation:dev-staging --no-dev && \
    composer update devgeniem/hkih-linkedevents:dev-staging --no-dev && \
    composer update devgeniem/hkih-sportslocations:dev-staging --no-dev; \
fi

RUN composer dump-autoload --no-dev --optimize

ARG THEMEPATH_1
WORKDIR /app/${THEMEPATH_1}
RUN npm i --no-audit
RUN npm run build

ARG PLUGINPATH_1
WORKDIR /app/${PLUGINPATH_1}
RUN npm i --no-audit
RUN npm run build

ARG PLUGINPATH_2
WORKDIR /app/${PLUGINPATH_2}
RUN npm i --no-audit
RUN npm run build

WORKDIR /app

COPY . .

RUN rm -rf /root/.composer

FROM base as app
COPY --from=root-composer /app /app