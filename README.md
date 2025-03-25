![geniem-github-banner](https://cloud.githubusercontent.com/assets/5691777/14319886/9ae46166-fc1b-11e5-9630-d60aa3dc4f9e.png)
# HKI Headless

Headless WordPress setup with REST and GraphQL endpoints.

Use this as a local development environment with our docker-image
[devgeniem/ubuntu-docker-wordpress-development][docker-wordpress-development]
and our development tools: [gdev][github-gdev].

## Environment URLs

### Production

* https://app-production.hkih.hion.dev
    * (network base site with no content)
* https://liikunta.content.api.hel.fi
* https://tapahtumat.content.api.hel.fi
* https://kultus.content.api.hel.fi
* https://harrastus.content.api.hel.fi
* https://kukkuu.content.api.hel.fi
* https://liikunta2.content.api.hel.fi

### Staging

* https://app-staging.hkih.hion.dev
    * (network base site with no content)
* https://liikunta.app-staging.hkih.hion.dev
* https://tapahtumat.app-staging.hkih.hion.dev
* https://kultus.app-staging.hkih.hion.dev
* https://kultus.app-staging.hkih.hion.dev
* https://kukkuu.app-staging.hkih.hion.dev
* https://harrastus.app-staging.hkih.hion.dev

### Local

* https://client-hkih:8080
    * (network base site with no content)
* https://liikunta.client-hkih:8080
* https://tapahtumat.client-hkih:8080
* https://kultus.client-hkih:8080
* https://kultus.client-hkih:8080
* https://kukkuu.client-hkih:8080
* https://harrastus.client-hkih:8080

**Note**: Locally there is no certificate so use `http://` URLs.

## gcloud CLI initial setup

Login the gcloud CLI to your Google Account
if not already done and set current project:

```
gcloud auth login
gcloud auth application-default login

gcloud config set project client-hkih

## Local development setup

```
# Create alias or add to zsh config
alias hiondev='docker compose run --rm hiondev'

# Login inside the hiondev container
hiondev login client-hkih

# Fetch env files from Secret Manager
hiondev dot-env-fetch

# Download staging database and import to local db
hiondev db-download staging staging staging

# Build and start containers
docker compose build dev
docker compose up -d dev

# Move inside dev container
docker compose exec dev sh

# Install Composer dependencies
composer install

# repeat the above for all child themes

# Search-replace URLs
wp search-replace app-staging.hkih.hion.dev client-hkih.test:8080 --all-tables
# no certificate is used locally so we need to use http://
wp search-replace https:// http:// --all-tables
wp cache flush --network

# Verify the site works by visiting the site
open http://client-hkih.test:8080

## Local build and development with local MySQL

```
docker compose build dev
docker compose up -d dev

## Google Cloud Build

To release a build to servers we use the following schemes.

| Destination | Git Tag Scheme        | Example             | Build log                  |
|-------------|-----------------------|---------------------|----------------------------|
| Staging     | {YYYYMMDD-HHmm}-staging | 20220131-1234-staging | [Open Log][log-stage]      |
| Production  | {semver}-production   | 1.2.3-production    | [Open Log][log-production] |

## Documentation

- [GraphQL](docs/GraphQL.md)
- [Filters](docs/Filters.md)
- [PostTypes](docs/PostTypes)
  - [Collection](docs/PostTypes/Collection.md)
  - [LandingPage](docs/PostTypes/LandingPage.md)
  - [Page](docs/PostTypes/Page.md)

## Plugins and themes

These repositories are **Open Source** and **developed in the open**. The code written there can be seen by ANYONE.

To set up them for local development, please run `make dev`. It will clone the repositories to `_dev` folder and link
them to Docker machine for faster development cycle.

# Build theme and plugin assets
cd _dev/hkih
npm install
npm run build

cd _dev/hkih-linkedevents
composer install && npm install
npm run build

cd _dev/hkih-hkih-sportslocations
composer install && npm install
npm run build

Restart enviroment if theme and plugins does not get loaded from _dev folder

**DO NOT CHANGE FILES IN THE `web/app/themes` and `web/app/plugins` FOLDERS WHEN DEVELOPING, USE THE `_dev` FOLDER VERSIONS!**

| Plugin                                                         | Description                                    |
|----------------------------------------------------------------|------------------------------------------------|
| [hkih-theme](web/app/themes/hkih)                              | HKIH Theme                                     |
| [hkih-cpt-collection](web/app/plugins/hkih-cpt-collection)     | ACF Flexible Layout Group                      |
| [hkih-cpt-contact](web/app/plugins/hkih-cpt-contact)           | Contact information                            |
| [hkih-cpt-landing-page](web/app/plugins/hkih-cpt-landing-page) | Landing Page, container for ACF                |
| [hkih-cpt-release](web/app/plugins/hkih-cpt-release)           | Press releases, and such                       |
| [hkih-cpt-translation](web/app/plugins/hkih-cpt-translation)   | Frontend translations                          |
| [hkih-linkedevents](web/app/plugins/hkih-linkedevents)         | Linked Events API Client with some ACF Magic   |
| [hkih-sportslocations](web/app/plugins/hkih-sportslocations)   | WP Admin ACF to select events for API payloads |

All plugins are build when Composer `post-install-cmd` event triggers, [read more here][composer-events].

## Deployment

The project has been split into open source repositories, and all development is done in public.

This base repository should always be kept secret as it contains intellectual property that belongs to Hion.

### Production deployment

#### Theme and plugin production deployment

- Merge development branches to master after code review
- Update plugin / theme changelog with the next fitting version number (follow the pattern)
- Create new release and tag with `X.Y.Z` pattern

#### Project production deployment

Each plugin and theme repository should have their code tagged and released as a new version before starting this.

- `composer update devgeniem/hkih-theme` or - `composer update devgeniem/name-of-the-plugin`
- Update [changelog](CHANGELOG.md) with the next fitting version number (follow the pattern)
- Commit and tag with the version number with '-production' suffix, like this: `X.Y.Z-production`.
- Push everything to main (tags included)
- [Follow the production building process](https://console.cloud.google.com/cloud-build/builds;region=europe-west1?hl=fi&project=client-hkih&invt=Abs9Ag&rapt=AEjHL4MtXSKkvzXyEe9ydvRTn4DAL1MNTKopPNs7c6lEmAh-q7LvzDJuGKjTD84SMZvM7TnSYBcq7JPZdorEo9UxHI3O3ct82SEqSA3k5-P_zcHkwczWQEQ)

For all the steps the build does, see [`Dockerfile`](Dockerfile)

### Staging deployment

#### Theme and plugin staging deployment

Merge development branches to `staging` branch and push it to GitHub

#### Project staging deployment

Make sure all the changes you want to appear in the staging server has been pushed to `staging` branch in the external
plugin and theme repositories. After you have verified everything is fine, do the following:

- Change to `staging` branch in this repository
- Create new `YYYYMMDD-HHMM-staging` style tag to this repository
- Push everything to Github
- [Follow the stage building process](https://console.cloud.google.com/cloud-build/builds;region=europe-west1?hl=fi&project=client-hkih&invt=Abs9Ag&rapt=AEjHL4MtXSKkvzXyEe9ydvRTn4DAL1MNTKopPNs7c6lEmAh-q7LvzDJuGKjTD84SMZvM7TnSYBcq7JPZdorEo9UxHI3O3ct82SEqSA3k5-P_zcHkwczWQEQ)

For all the steps the build does, see [`Dockerfile`](Dockerfile).
Note the step, especially when the SERVICE_NAME is app-staging, as it will install the theme and plugins from their staging branches.

## Settings

Registering settings tabs is easy. Just add a new settings group with this easy filter.
```php
add_filter( 'hkih_theme_settings', function ( $fields, $key ) {
    // @todo: Register your own fields and add them to the $fields array.
    return $fields;
}, 10, 2 );
```

## How To

### Query page

Query page with id "61"
```
query MyQuery {
	page(id: 61, idType: DATABASE_ID) {
		id
		content
	}
}
```

### Add Collection(event) Modules to post layout.

[Post.php](https://github.com/devgeniem/client-hkih-gcp/blob/master/web/app/themes/hkih/lib/ACF/Post.php#L201-L204)

### Register GraphQL fields for ACF and resolve return values

SelectedEventsCarouselLayout in this case.

[Register SelectedEventsCarouselLayout fields](https://github.com/devgeniem/client-hkih-gcp/blob/master/web/app/plugins/hkih-linkedevents/src/ACF/SelectedEventsCarouselLayout.php#L144-L188)

Resolve the return values in a matching rest api [callback](https://github.com/devgeniem/client-hkih-gcp/blob/master/web/app/plugins/hkih-linkedevents/src/LinkedEventsPlugin.php#L460-L477)

### Resolve union type

Add matching AFC group name and [GRAPHQL_LAYOUT_KEY](https://github.com/devgeniem/client-hkih-gcp/blob/master/web/app/plugins/hkih-linkedevents/src/ACF/SelectedEventsCarouselLayout.php#L31) [here](https://github.com/devgeniem/client-hkih-gcp/blob/master/web/app/themes/hkih/lib/Utils.php#L231-L263) when you want to register new GraphQL type and its fields.

[composer-events]: https://getcomposer.org/doc/articles/scripts.md#command-events
[docker-wordpress-development]: https://github.com/devgeniem/ubuntu-docker-wordpress-development
[github-bedrock]: https://github.com/roots/bedrock
[github-gdev]: https://github.com/devgeniem/gdev
[github-phpcs]: https://github.com/squizlabs/PHP_CodeSniffer
[github-phpcs-rules]: https://github.com/devgeniem/geniem-rules-codesniffer
[github-phpstorm]: https://github.com/devgeniem/client-hkih-gcp-phpstorm-settings
[log-stage]: https://console.cloud.google.com/cloud-build/builds?project=geniem-stage&supportedpurview=project&pageState=(%22builds%22:(%22f%22:%22%255B%257B_22k_22_3A_22Trigger%2520Name_22_2C_22t_22_3A10_2C_22v_22_3A_22_5C_22client-hkih_5C_22_22_2C_22s_22_3Atrue_2C_22i_22_3A_22triggerName_22%257D%255D%22))
[log-production]: https://console.cloud.google.com/cloud-build/builds?project=geniem-production&supportedpurview=project&pageState=(%22builds%22:(%22f%22:%22%255B%257B_22k_22_3A_22Trigger%2520Name_22_2C_22t_22_3A10_2C_22v_22_3A_22_5C_22client-hkih_5C_22_22_2C_22s_22_3Atrue_2C_22i_22_3A_22triggerName_22%257D%255D%22))
