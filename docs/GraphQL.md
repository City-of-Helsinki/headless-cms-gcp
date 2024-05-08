# GraphQL

Projektissa on REST-rajapinnan lisäksi mukana GraphQL-rajapinta.

Rajapinta rakentuu [WPGraphQL][wpgraphql] -pluginin varaan ja
käyttää sen lisäksi [valu/wp-graphql-polylang][valu-polylang] -lisäosaa PolyLang-yhteensopivuuden saavuttamiseksi.

Luettavaa:

- https://www.wpgraphql.com/docs/quick-start/
- https://www.wpgraphql.com/docs/intro-to-graphql/
- https://www.wpgraphql.com/docs/interacting-with-wpgraphql/
- https://www.wpgraphql.com/docs/wpgraphql-vs-wp-rest-api/

GraphQL on vahvasti tyypitetty, ja jokainen rajapintaan määritelty kenttä ja objekti pitää olla vahvasti tyypitetty.

ACF:n `FlexibleContent`, joka voi sisältää erilaisia `ACF\Field\Flexible\Layout`-tyyppisiä sisältöjä,
on mahdollista rekisteröidä on `UnionType`-tyyppisenä objektina GraphQL:n tyyppirekisteriin.

Koodipohjasta löytyy kenttien rekisteröinti yleensä `register_graphql_fields()` -nimellä. Näitä kannattaa käyttää
esimerkkeinä uusien ACF-kenttien rekisteröinnissä.

Teemaan kenttien rekisteröintiä tulee välttää, koska ominaisuudet tulee rakentaa lisäosina ja lisäosat voivat olla
sivustosta riippuen päällä, tai pois.

[wpgraphql]: https://www.wpgraphql.com/
[valu-polylang]: https://github.com/valu-digital/wp-graphql-polylang
