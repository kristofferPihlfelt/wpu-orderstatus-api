# WPU Orderstatus API

Gratis plugin från [wordpressutveckling.se](https://wordpressutveckling.se/). Koden förklaras rad för rad i guiden: **[https://wordpressutveckling.se/egen-rest-api-endpoint-wordpress/](https://wordpressutveckling.se/egen-rest-api-endpoint-wordpress/)**

Byggt av [Kristoffer Pihlfelt](https://pihlfelt.se) på [Pihlfelt & Vänner](https://pihlfelt.se). Licens: GPL-2.0-or-later.

- **Bidragsgivare:** Pihlfelt & Vänner
- **Kräver WordPress:** 6.7
- **Kräver PHP:** 8.2
- **Kräver:** WooCommerce
- **Version:** 1.0.0
- **Licens:** GPL-2.0-or-later

## Vad det gör

Lägger till en egen REST-endpoint för orderstatus, tänkt för en app eller ett lagersystem som ska läsa och uppdatera ordrar utan att gå via hela WooCommerce REST API.

* GET /wp-json/wpu/v1/orderstatus/<id> svarar med orderns id, status och senaste ändringsdatum.
* POST /wp-json/wpu/v1/orderstatus/<id> med status=processing eller status=completed byter status och lägger en ordernotering ("Uppdaterad via lagersystemet.").
* Båda kräver en inloggad användare med rättigheten manage_woocommerce (rollen Butikschef). Utan inloggning blir svaret 401, fel id eller status ger 400 och en order som inte finns ger 404.

## Installation

1. Kontrollera att WooCommerce är aktiverat.
2. Gå till Tillägg > Lägg till nytt > Ladda upp tillägg och välj zip-filen. Aktivera.
3. Skapa en egen användare med rollen Butikschef för integrationen och ge den ett applikationslösenord under Användare > Redigera användare. Integrationen anropar endpointen med Basic Auth över HTTPS:

curl --user "lager:xxxx xxxx xxxx xxxx xxxx xxxx" https://butiken.se/wp-json/wpu/v1/orderstatus/1234
curl --user "lager:xxxx xxxx xxxx xxxx xxxx xxxx" -X POST -d status=completed https://butiken.se/wp-json/wpu/v1/orderstatus/1234

## Källa

Koden förklaras rad för rad i guiden "Skapa en egen REST API-endpoint i WordPress":
https://wordpressutveckling.se/egen-rest-api-endpoint-wordpress/

Den senaste versionen finns alltid på samma adress:
https://wordpressutveckling.se/ladda-ner/wpu-orderstatus-api/

## Ändringslogg

### 1.0.0
* Första versionen, samma kod som i guiden (GET- och POST-endpointen i ett plugin).
