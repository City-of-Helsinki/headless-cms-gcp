# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres
to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

- CSD-1855-2: Add devgeniem/wp-gcp-cache-headers plugin.
  - Update WP and plugins.
  - Remove old plugins.
- CSD-1855: Support setting WP_REDIS_TIMEOUT and WP_REDIS_READ_TIMEOUT via environment variables.
- HKIH-160: Preview feature.
- HKIH-171: Change description field from textarea to Wysiwyg in Steps module.
- HKIH-172: TinyMCE table plugin and custom styles.
- HKIH-162: Publishpress Revisions Pro plugin.
- HKIH-207: Redirect urls to site settings.
- HKIH-184: WPO 365 login.
- HKIH-210: Alignment values to CardLayout.
- HKIH-221: Adds fallback for SEO titles and descriptions.
- HKIH-222: Enable html entity decoding.
- CSD-1329: Add post_id to preview link.
- CSD-1677: Nginx settings for /uploads/
- CSD-1607: Increase results amount of the Event and Location search modules.
- PIEN-9288: PHP vulnerability fix
  - Update Alpine version
  - Update WP stack from hiondev

## [Released]

## [1.26.0] - 2025-03-06

- HKIH-185-node: Node 20 upgrade.

## [1.25.0] - 2025-01-17

- CSD-1855: Support setting WP_REDIS_TIMEOUT and WP_REDIS_READ_TIMEOUT via environment variables.

## [1.24.0] - 2025-01-13

- CSD-1607: Increase results amount of the Event and Location search modules.

## [1.23.0] - 2024-09-23

- HKIH-207: Redirect urls to site settings.

## [1.22.0] - 2024-09-15

- HKIH-184: WPO 365 login.

## [1.21.0] - 2024-08-15

- HKIH-210: Alignment values to CardLayout.
- HKIH-160: Preview feature.

## [1.20.0] - 2024-06-07

- HKIH-162: Publishpress Revisions Pro plugin.

## [1.19.0] - 2024-05-08

- HKIH-178: WP and plugin updates.

## [1.18.0] - 2024-05-08

- HKIH-177: Add default koro to site settings.

## [1.17.0] - 2024-04-18

- HKIH-174-2: Fix dashes in breadcrumbs titles.

## [1.16.0] - 2024-03-13

- HKIH-174: Breadcrumbs field.

## [1.15.0] - 2024-01-04

- HKIH-158: ACF Pro update.

## [1.14.1] - 2024-01-04

- HKIH-182: Convert Event search carousel's show all link to Event search link.

## [1.13.0] - 2023-11-22

- HKIH-181: Fix unifiedSearch query.

## [1.12.0] - 2023-11-17

- HKIH-156-3: Add liikunta2 to revalidate urls.

## [1.11.0] - 2023-11-17

- HKIH-156-2: Fix typo in revalidate urls.

## [1.10.0] - 2023-11-16

- HKIH-156: Revalidation for posts and pages.
- HKIH-153: Return correct page by slug.
- HKIH-159: Share LinkedEvents cache between multisites and set longer cache time.
- HKIH-152: API documentation for stage enviroment.

## [1.9.0] - 2023-09-19

- HKIH-136: Steps Module.
- HKIH-130: Content Module.
- HKIH-128: Card Module.
- HKIH-134: Image module.
- HKIH-131: Cards Module.
- HKIH-133: Hero module.
- HKIH-132: ImageGallery module.
- HKIH-147: Filter out drafts from relationship field. #125
- HKIH-129: Social Media Feed module.
- HKIH-124: Ability to set custom CSS in backend.
- HKIH-151: Ability to change LinkedEvents base url in settings.

## [1.8.0] - 2023-09-13

- HKIH-157: Add robots.txt file.

## [1.7.0] - 2023-09-06

- HKIH-155: Fix issue where EventSearch and EventSearchCarousel modules does not return correct url.

## [1.6.0] - 2023-05-11

- HKIH-146: Adds KUVA_UNIFIED_API enviroment variable.

## [1.5.0] - 2023-05-05

- HKIH-140: Allow multiselection for location in LinkedEvents plugin.
- HKIH-144: Default value for division in LinkedEvents plugin.

## [1.4.0] - 2023-05-05

- HKIH-138: Server upscale and server settings.

## [1.3.0] - 2023-04-12

- HKIH-145: Allow Youtube and Vimeo embeds in pages.

## [1.2.0] - 2023-04-03

- HKIH-139: Fix amount of selectable events in Sport Locations module.

## [1.1.0] - 2023-04-03

### Added

- PIEN-8192: PHP 8.1 and WordPress & plugin updates.

## [1.0.0] - 2023-02-28

### Added

- HKIH-121: Adds Event Search ja Event Search Carousel modules show all custom link.

## [0.9.0] - 2023-02-10

### Added

- HKIH-117: Add Sports Locations Carousel layout.

## [0.8.0] - 2023-02-06

### Added

- HKIH-120: Add new liikunta2.content.api.hel.fi subdomain.


## [0.7.0] - 2023-02-03

### Changed

- HKIH-119: Allows admins set blog to public in backend.

## [0.6.0] - 2023-01-31

### Added

- HKIH-116: Add division filter to Linked Events module.

## [0.5.0] - 2023-01-23

### Added

- HKIH-112: Add delay to event search on acf modules


## [0.4.0] - 2022-12-07

### Added
- 2.0-jatkokehitys:
    - fix/events-lang: Use page lang as events lang.
    - fix/course-param: course = Course
    - HKIH-107-2: Fix showAllLink resolvation.
    - HKIH-110: Fix course search. #87
    - HKIH-106-2: Add default images. #86
    - HKIH-106: Reorganize code for better maintaining. #85
    - HKIH-107: Add show all link to modules. #83
    - HKIH-103: Fix event dates selection, change show all link to only link.
    - HKIH-104: Increase selection height, fix amount of selectable events. #82
    - HKIH-103-2: Change event modules result link. #81
    - HKIH-103: Fix event dates selection, change show all link to only link. #80
    - HKIH-111: Fix age limit in event modules. #79

## [0.3.0] - 2022-08-31

### Fixed

- HKIH-101: Return an empty array if no link set in landingpage hero link
- HKIH-100: Bug fixes.
- fix/page-by-template: Add template argument to pagebytemplate query.
- HKIH-99: Prevent SEO metadata from being synced in post status sync between translations #74

### Added

- add/polylang-strings: Add polylang strings #75
- HKIH-98: Add sidebar to posts #73
- HKIH-97: Add custom schema for graphql #72
- HKIH-95: Replace preview link with client link #70
- HKIH-94: Add ArticleHighlightsLayout #69
- HKIH-93: Add PagesCarouselLayout #68
- fix/field-texts-to-english: Replace finnish texts by english texts. #67
- HKIH-92: Add ArticlesCarouselLayout #66 
- HKIH-91: Add SelectedEventsCarouselLayout #65
- HKIH-90: Add EventSearchCarouselLayout #64
- HKIH-89: Add field, add admin style #63
- HKIH-70: Package updates, GraphQL API Docs #60
- HKIH-84: Add url field used to replace base url in link lists. #59

### Changed

- feat/update-husky-lint-staged: Updated husky and lint-staged #61
- feat/update-spectaql: Updated spectaql and generated docs #62

## [0.2.4] - 2022-03-07

### Added

- HKIH-85: Add kukkuu-site.

## [0.2.3] - 2022-02-14

### Fixed

- HKIH-83: Fix resolve_image to return only attachment with given id
- HKIH-80: Filter unpublished modules from response #56

### Added

- Add db_production to health_checks.

## [0.2.2] - 2022-01-26

### Added

- HKIH-79: harrastus-site.

## [0.2.1] - 2021-12-23

### Fixed

- fix/duplicate-union-type: Fix duplicate union type key

## [0.2.0] - 2021-12-23

### Added

- HKIH-74: Add notice block #49
- HKIH-72: Add LinkListLayout #51
- HKIH-73: Add sidebar to page #50

## [0.1.4] - 2021-12-17

### Added

- HKIH-77: Post duplicate plugin

## [0.1.3] - 2021-12-02

### Fixed

- HKIH-75: ArticlesLayout and PostLayout posts resolve #48

## [0.1.2] - 2021-11-12

### Added

- HKIH-69: Sports Locations #47

## [0.1.1] - 2021-08-26

### Added

- feat/additional-nav-menus: Register secondary and tertiary nav menus

## [0.1.0] - 2021-07-02

### Changed

- Composer update #43
- Updates and configs #44

### Fixed

- GraphQL fixes #43

## [0.0.13] - 2021-05-24

### Changed

- fix/headless-user-roles: Adds/updated capabilities of headless roles.

## [0.0.12] - 2021-05-19

### Fixed

- fix/mail: SMTP fixes.

## [0.0.11] - 2021-05-10

### Added

- HKIH-67: Configurations for new kultus-site.

## [0.0.10] - 2021-05-05

### Changed

- fix/wp-admin-role: Adds capabilities to administrator.

## [0.0.9] - 2021-04-18

### Added

- Documentation: GraphQL #37

### Changed

- HKIH-27: Rules and Capabilities: Plugin CPTs #38

## [0.0.8] - 2021-04-15

### Added

- Added Contacts to Post module layouts #36
- Theme asset building to Makefile `make build` #36

### Changed

- EventSearch and SelectedEvents Layout registration #36
- Code cleanup #36

### Fixed

- UnionType resolving in some cases #36
- Contact CPT Last name field label fixed #36

## [0.0.7] - 2021-04-13

### Fixed

- fix/cleanup: Fixes and cleanup #35
    - Type hinting
    - Code cleanup
    - Notice level bug
    - Error handling
    - Method access level change

## [0.0.6] - 2021-04-13

### Fixed

- fix/graphql-layout-registration: Fix list_of layout type resolving #34

## [0.0.5] - 2021-04-13

### Added

- HKIH-64: Add LayoutContacts to GraphQL #33
- feat/docs-filters: Filters Documentation #32
- HKIH-66: Collection layout #31
- HKIH-65: Releases CPT #30
- HKIH-63: Add linked events layouts to posts and pages #27
- HKIH-56: Translations CPT #28

## [0.0.4] - 2021-04-01

### Added

- HKIH-64: GraphQL registrations #29
- HKIH-54: Static Event Selector #24
- HKIH-61: Pages layout #26
- HKIH-60: Articles layout #25
- HKIH-59: Article fields #23
- HKIH-58: Add Contact layout #22
- HKIH-57: Contact CPT #21
- HKIH-62: Notification fields and endpoints #19
- HKIH-55: Site Settings #20

## [0.0.3] - 2021-03-24

### Added

- HKIH-52: Add ACF fields to REST response #16
- HKIH-51-1: Add EventSearch to REST response #17
- HKIH-51: LinkedEvents plugin #15
- HKIH-53: Show child pages toggle #18
- HKIH-50: Navigation #14

## [0.0.2] - 2021-03-11

### Added

- HKIH-47: LinkedEvents settings #11
- HKIH-30: Support for multiple languages #9
- HKIH-43: Collection CPT #7
- HKIH-43: Landing Page CPT #5
- HKIH-42: Content Page #4
- HKIH-27: User roles #6
- HKIH-46: Media library folders and photographer credit #8
- HKIH-40: Automatic Timed Expiration #10
- HKIH-48: Support multiple languages in GraphQL #12
- HKIH-49: SEO Framework GraphQL Map #13

## [0.0.1] - 2021-03-03

- Initial release.
