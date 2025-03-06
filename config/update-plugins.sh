#!/bin/sh

if [ "$SERVICE_NAME" = "app-staging" ]; then
    composer update devgeniem/hkih-theme:dev-staging \
    devgeniem/hkih-cpt-collection:dev-staging \
    devgeniem/hkih-cpt-contact:dev-staging \
    devgeniem/hkih-cpt-landing-page:dev-staging \
    devgeniem/hkih-cpt-release:dev-staging \
    devgeniem/hkih-cpt-translation:dev-staging \
    devgeniem/hkih-linkedevents:dev-staging \
    devgeniem/hkih-sportslocations:dev-staging
fi
