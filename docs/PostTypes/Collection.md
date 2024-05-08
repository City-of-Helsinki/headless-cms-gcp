# Collection (kokoelma)
Kokoelma sisältötyyppi, jota käytetään näyttämään esimerkiksi dynaamisesti
valittuja tapahtumia.

## Perustiedot
Slug: `collection-cpt`

## Kentät

### Näytä etusivulla
* Tyyppi: TrueFalse
* Key: `show_on_front_page`
* Vakioarvo: False
* Polylang: Synkronoidaan kieliversioiden välillä

### Kuva
* Tyyppi: Image
* Key: `image`
* Palautusarvo: URL
* Polylang: Synkronoidaan kieliversioiden välillä

### Taustaväri
* Tyyppi: Select
* Key: `background_color`
* Huomiot: Valittavina arvoina käytetään HDS:n brändivärejä
* Polylang: Synkronoidaan kieliversioiden välillä

### Kuvaus
* Tyyppi: Textarea
* Key: `description`
* Huomiot: Rivinvaihdot wpautop:llä

### URL slug
* Huomiot: Käytetään WP:n natiivia polkutunnusta

### Nostot
* Tyyppi: FlexibleContent
* Key: `modules`
* Huomiot: Rekisteröidyään tyhjä FlexibleContent-kenttä, johon lisäosat voivat rekisteröidä layoutteja.
