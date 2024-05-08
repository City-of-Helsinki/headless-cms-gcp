# Filtterit

Sivusto on rakennettu modulaariseksi ja toimintoja voidaan ottaa käyttöön,
ja poistaa käytöstä lisäosia aktivoimalla ja deaktivoimalla.
Lisätoiminnallisuuksia muihin toiminnallisuuksiin tuodaan filtereitä käyttämällä.

Mm. sisältötyypit, ja lisätoiminnallisuudet sisältötyyppien ACF-kenttiin rakennetaan käyttäen filtereitä.

## Perustiedot

Kaikki HKIH-projektin sisäiset filterit alkavat `hkih_`.

Rakenne on seuraava, taulukossa muutama havainnollistava esimerkki:

| Alku | Tyyppi/Konteksti | Tarkenne   | Toiminto (ei pakollinen)  |
|------|------------------|------------|---------------------------|
| hkih | acf              | page       | modules_layout            |
| hkih | graphql          | layouts    |                           |
| hkih | graphql          | modules    |                           |
| hkih | posttype         | collection | args                      |
| hkih | rest_acf         | page       | modules_layout_collection |
| hkih | theme            | settings   |                           |
| hkih | theme            | settings   | identity                  |

Esimerkkejä yllä olevista filtereistä purkamatta osiin:

- `hkih_acf_page_modules_layout`
- `hkih_posttype_collection_args`
- `hkih_rest_acf_page_modules_layout_collection`

### Tyypit/Kontekstit

| Tyyppi    | Kuvaus                                                                                                                   |
|-----------|--------------------------------------------------------------------------------------------------------------------------|
| acf       | ACF-kentät, tarkenne kertoo PostTypen.                                                                                   |
| graphql   | GraphQL-rajapintaan liittyvät toimet.                                                                                    |
| posttype  | Sisältötyyppiin kohdistuva, tarkenne kertoo slugin.                                                                      |
| rest_acf  | ACF-kentän julkaisu REST-rajapintaan. Tarkenne kertoo sisältötyypin, toiminto kertoo muokattavan ACF-kentän nimen.       |
| theme     | \Geniem\Theme\ACF\Settings() ACF FieldGroup:in muokkaamiseen. Toiminto kertoo mihin kenttäryhmään muutokset kohdistuvat. |

### Muut yleisesti käytetyt filterit

| Filter                   | Käyttö                                      |
|--------------------------|---------------------------------------------|
| `allowed_block_types`    | Gutenberg-blokkien rajaus sisältötyypeissä. |
| `graphql_register_types` | GraphQL-tyyppien rekisteröinti.             |
| `pll_get_post_types`     | Sisältötyypin rekisteröinti PolyLangiin.    |
| `rest_api_init`          | REST-kenttien rekisteröinti.                |

## Perusrakenteet

### Custom Post Type / Sisältötyyppien filterit

Jokaiselta sisältötyypiltä (pl. Post, Page) löytyy seuraavat filterit:

Taulukon jokainen filter alkaa `hkih_posttype_[SLUG]_`, taulukossa vain alkuosan jälkeinen osa.
Taulukon filter `args` on siis `hkih_posttype_[SLUG]_args`.

| Filter | Kuvaus                                                 |
|--------|--------------------------------------------------------|
| slug   | Sisältötyypin slug.                                    |
| labels | Sisältötyypin valikko- ja UI-tekstit.                  |
| args   | Sisältötyypin kaikki rekisteröimiseen käytetyt kentät. |
| fields | ACF-kentät ennen rekisteröintiä.                       |

Jos ACF-kenttien rekisteröinti on jaettu useampaan funktioon (esim. LandingPage-cpt:n hero ja content), on `fields`
-filterissä vielä perässä tarkennus (esim. `fields_hero` ja `fields_content`).

Esimerkki näiden käytöstä löytyy `hkih-cpt-collection`-pluginista: `\HKIH\CPT\Collection\CollectionPlugin::hooks()`
jossa `LandingPage`-CPT saa `add_collection_relationship`-methodilla lisäkentän.

### GraphQL-filterit

GraphQL on vahvasti tyypitetty, ja jokainen rajapintaan määritelty kenttä pitää myös olla vahvasti tyypitetty.
ACF:n `FlexibleContent`, joka voi sisältää erilaisia `ACF\Field\Flexible\Layout`-tyyppisiä sisältöjä,
on mahdollista rekisteröidä on `UnionType`-tyyppisenä objektina GraphQL:n tyyppirekisteriin.

Käytännön esimerkkinä `\HKIH\LinkedEvents\ACF\EventSearchLayout()` ja `\HKIH\LinkedEvents\ACF\SelectedEventsLayout()`
eroavat vain yhden kentän verran (`url` EventSearchLayoutissa, `events` SelectedEventsLayoutissa), ja ne rekisteröidään
`hkih_graphql_modules`-filterillä `\HKIH\CPT\Collection\PostTypes\Collection::register_collection_modules_graphql()`
methodissa osaksi `CollectionModulesUnionType`-tyyppiä. Rekisteröinnin jälkeen ne ovat käytössä kaikille
sisältötyypeille jotka rekisteröivät `hkih_posttype_collection_modules`-filterillä sen itselleen käyttöön.

Yksittäisen ACF Flexible Layoutin rekisteröinti GraphQL-käyttöön onnistuu seuraamalla esimerkiksi
`\Geniem\Theme\ACF\Layouts\ArticlesLayout()`-esimerkkiä.

1. Rekisteröi kenttä normaalisti ACF:iin
2. Lisää action filteriin `graphql_register_types`
    - `\Geniem\Theme\ACF\Layouts\ArticlesLayout::register_graphql_fields()`
      kuvaile Layout uudelleen GraphQL-käyttöä varten.
      Muista rekisteröidä layout `hkih_graphql_layouts`-listaan, jokaisen objektin pitää olla uniikki.
3. Ota layoutit käyttöön
    - `\Geniem\Theme\ACF\Post::_construct()` sisältää add_filter-kutsun `hkih_posttype_post_graphql_layouts`-filterille
    - `\Geniem\Theme\ACF\Post::register_graphql_layouts()` lisää `GRAPHQL_LAYOUT_KEY`-arvolla rekisteröidyt layoutit
    - `\Geniem\Theme\PostType\Post::register_graphql_types()`
