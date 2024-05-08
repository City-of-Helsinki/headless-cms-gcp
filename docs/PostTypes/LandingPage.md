# Landing Page (laskeutumissivu)
Kokoelma sisältötyyppi, jota käytetään näyttämään esimerkiksi dynaamisesti
valittuja tapahtumia.

## Perustiedot
Slug: `landing-page-cpt`

## Kentät

### Hero
* Tyyppi: Group
* Key: `hero`

#### Desktop kuva
* Tyyppi: Image
* Ryhmä: `hero`
* Key: `desktop_image`
* Palautusarvo: URL
* Polylang: Synkronoidaan kieliversioiden välillä

#### Mobile kuva
* Tyyppi: Image
* Ryhmä: `hero`
* Key: `mobile_image`
* Palautusarvo: URL
* Polylang: Synkronoidaan kieliversioiden välillä

#### SEO kuva
* Tyyppi: Image
* Ryhmä: `hero`
* Key: `seo_image`
* Palautusarvo: URL
* Polylang: Synkronoidaan kieliversioiden välillä

#### Float kuva
* Tyyppi: Image
* Ryhmä: `hero`
* Key: `float_image`
* Palautusarvo: URL
* Polylang: Synkronoidaan kieliversioiden välillä

#### Taustaväri
* Tyyppi: Select
* Ryhmä: `hero`
* Key: `background_color`
* Huomiot: Valittavina arvoina käytetään HDS:n brändivärejä. https://hds.hel.fi/design-tokens/colour#brand-colours
* Polylang: Synkronoidaan kieliversioiden välillä

#### Laatikon väri
* Tyyppi: Select
* Ryhmä: `hero`
* Key: `box_color`
* Huomiot: Valittavina arvoina käytetään HDS:n brändivärien yhdistelmiä https://hds.hel.fi/design-tokens/colour#brand-colour-combinations

#### Kuvaus
* Tyyppi: Textarea
* Ryhmä: `hero`
* Key: `description`
* Huomiot: Rivinvaihdot wpautop:llä

#### Linkki
* Tyyppi: Link
* Ryhmä: `hero`
* Key: `link`

### Kokoelmat
* Tyyppi: Relationship
* Key: `collections`
* Huomiot: Sallitaan vain Collection CPT
