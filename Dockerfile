ARG ALPINE_VERSION="3.18"
ARG PHP_VERSION="8.2"
ARG GCSFUSE_VERSION="1.2.0"
ARG NODE_VERSION="14"
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

FROM node:${NODE_VERSION} AS theme-npm-1
COPY .eslintrc.json /app/
ARG THEMEPATH_1
WORKDIR /app/${THEMEPATH_1}
COPY ${THEMEPATH_1}/package.json .
COPY ${THEMEPATH_1}/package-lock.json .
RUN npm i --no-audit
COPY ${THEMEPATH_1}/assets assets
COPY ${THEMEPATH_1}/webpack.config.js .
RUN npm run build

FROM node:${NODE_VERSION} AS plugin-npm-1
ARG PLUGINPATH_1
WORKDIR /app/${PLUGINPATH_1}
COPY ${PLUGINPATH_1}/package.json .
# COPY ${PLUGINPATH_1}/package-lock.json .
RUN npm i --no-audit
COPY ${PLUGINPATH_1}/assets assets
COPY ${PLUGINPATH_1}/webpack.config.js .
COPY ${PLUGINPATH_1}/.eslintrc.json .
RUN npm run build

FROM node:${NODE_VERSION} AS plugin-npm-2
ARG PLUGINPATH_2
WORKDIR /app/${PLUGINPATH_2}
COPY ${PLUGINPATH_2}/package.json .
# COPY ${PLUGINPATH_2}/package-lock.json .
RUN npm ci --no-audit
COPY ${PLUGINPATH_2}/assets assets
COPY ${PLUGINPATH_2}/webpack.config.js .
COPY ${PLUGINPATH_2}/.eslintrc.json .
RUN npm run build

FROM dev as root-composer
ARG THEMEPATH_1
WORKDIR /app
COPY composer.json .
COPY composer.lock .
# RUN --mount=type=secret,id=composer_auth,target=auth.json composer install --prefer-dist --no-dev --no-autoloader --no-scripts
RUN composer install --prefer-dist --no-dev --no-scripts
COPY . .
RUN composer run-script post-install-cmd
RUN composer dump-autoload --no-dev --optimize
COPY --from=theme-npm-1 /app/${THEMEPATH_1}/assets ${THEMEPATH_1}/assets
COPY --from=plugin-npm-1 /app/${PLUGINPATH_1}/assets ${PLUGINPATH_1}/assets
COPY --from=plugin-npm-2 /app/${PLUGINPATH_2}/assets ${PLUGINPATH_2}/assets
RUN rm -rf /root/.composer

FROM base as app
COPY --from=root-composer /app /app
