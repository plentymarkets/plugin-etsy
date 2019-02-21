
# User Guide für das Etsy-Plugin

<div class="container-toc"></div>

## Bei Etsy registrieren

**Etsy** ist ein amerikanischer Marktplatz für den Kauf und Verkauf von handgemachten Produkten, Vintage und Künstlerbedarf.

Um das Plugin für Etsy einzurichten, registrieren Sie sich zunächst als Händler und erstellen Sie eine neue [App](https://www.etsy.com/developers/documentation/getting_started/register) bei Etsy. Loggen Sie sich dazu mit ihren Etsy-Zugangsdaten auf https://www.etsy.com/developers/ ein und klicken Sie im Bereich **Your Developer Account** auf **Create a New App**. Geben Sie unter **Application Name** einen Namen für die App ein und nehmen Sie weitere Einstellungen vor. Beachten Sie dazu die folgende Tabelle. Klicken Sie anschließend auf **Read Terms and Create App**.

|Einstellung bei Etsy                           |Auswahl |
|:---                                           |:--- |
|**What type of application are you building?** |**Seller Tools** |
|**Who will be the users of this application?** |**Just myself or colleagues** |
|**Is your application commercial?**            |**No** |
|**Will your app do any of the following?**     |**Upload or edit listings** |

Nachdem Sie die App erstellt haben, erscheint die App bei Etsy im Bereich **Your Developer Account** unter **Apps You've Made** und Sie erhalten die nötigen Zugangsdaten bestehend aus Keystring und Shared Secret, die Sie für die Einstellungen in plentymarkets benötigen. Klicken Sie bei der App auf **See API Key Details**, um die Zugangsdaten anzuzeigen.

## Etsy in plentymarkets installieren

Nachdem Sie das Etsy-Plugin im plentymarkets Marketplace gekauft haben, installieren Sie den Marktplatz im Menü **Plugins » Plugin-Übersicht**. Wählen Sie den Filter **Nicht installiert** oder **Alle**, um nicht installierte Plugins anzuzeigen und zu installieren. Öffnen Sie nach der Installation **Etsy**. Geben Sie nun unter **Konfiguration » App-Einstellungen** den Keystring und das Shared secret ein. Beides sollten Sie bei der Erstellung der App in Etsy erhalten haben. Speichern Sie die Einstellung.

### Berechtigung erteilen

Im Menü **Einstellungen » Märkte » Etsy » Authentifizierung** muss zunächst die Schnittstelle freigeschalten werden. Klicken Sie dazu auf **Etsy-Login**. Sie werden direkt zu Etsy weitergeleitet, wo Sie die Schnittstelle freischalten.

### Etsy einrichten

Im Menü **Einstellungen » Märkte » Etsy » Einstellungen** geben Sie u.a. Ihre Shop-ID ein und aktivieren den Artikelexport, Bestandsabgleich und Auftragsimport.
Etsy erlaubt 500 Calls am Tag. Sollten mehr Calls benötigt werden, setzen Sie sich direkt mit Etsy in Verbindung.

## Auftragsherkunft aktivieren

Damit Sie Artikel, Merkmale etc. mit Etsy verknüpfen können, muss im Menü Einstellungen » Aufträge » Auftragsherkunft die Auftragsherkunft Etsy aktiviert werden.

##### Auftragsherkunft für Etsy aktivieren:

1. Öffnen Sie das Menü **Einstellungen » Aufträge » Auftrgsherkunft**.
2. Setzen Sie bei **Etsy** ein Häkchen.
3. **Speichern** Sie die Einstellungen.

## Artikelverfügbarkeit einstellen

Artikel, die Sie auf Etsy verkaufen möchten, müssen im Menü **Artikel » Artikel bearbeiten » Artikel öffnen » Tab: Varianten-ID** im Tab **Verfügbarkeit** aktiviert werden. Da keine Varianten zu Etsy übertragen werden können, werden Variantenartikel als Hauptartikel übertragen.

##### Artikelverfügbarkeit für Etsy einstellen:

1. Öffnen Sie das Menü **Artikel » Artikel bearbeiten » Artikel öffnen » Tab: Varianten-ID » Tab: Einstellungen**.
2. Aktivieren Sie die Hauptvariante im Bereich **Verfügbarkeit**.
3. Wechseln Sie in das Tab **Verfügbarkeit**.
4. Klicken Sie im Bereich **Märkte** in das Auswahlfeld.
    → Eine Liste mit allen verfügbaren Märkten wird angezeigt.
5. Aktivieren Sie die Option **Etsy**.
6. Klicken Sie auf **Hinzufügen**.
    → Der Marktplatz wird hinzugefügt.
7. **Speichern** Sie die Einstellungen.
    → Der Artikel ist auf Etsy verfügbar.

Die Verfügbarkeit für Varianten kann im Menü **Artikel » Artikel bearbeiten » Artikel öffnen » Tab: Varianten » Variante öffnen » Tab: Varianten-ID » Tab: Verfügbarkeit** individuell angepasst werden.

Wenn Sie berets auf Etsy listen, hinterlegen Sie die Etsy Listing-ID im Menü **Artikel » Artikel bearbeiten » Artikel öffnen » Tab: Varianten-ID » Tab: Einstellungen » Tab: Verfügbarkeit** als SKU für die Herkunft **Etsy**, damit Artikel beim Artikelexport den bereits auf Etsy gelisteten Artikel zugewiesen werden können und somit keine Überverkäufe entstehen.

## Verkaufspreis festlegen

Gehen Sie wie im Folgenden beschrieben vor, um für die Auftragsherkunft Etsy einen Verkaufspreis festzulegen. Dieser Preis wird auf Etsy angezeigt. 

##### Verkaufspreise für Etsy festlegen:

1. Öffnen Sie das Menü **Einstellungen » Artikel » Verkaufspreise » Verkaufspreis öffnen » Tab: Einstellungen**.
2. Setzen Sie ein Häkchen bei der Herkunft **Etsy**.
3. **Speichern** Sie die Einstellungen.

## Kategorien verknüpfen

Verknüpfen Sie Ihre Webshop-Kategorien mit den Kategorien von Etsy, damit Ihre Artikel in diesen Etsy-Kategorien angezeigt werden. Weitere Artikel der verknüpften Kategorien werden dann automatisch zugewiesen.

##### Kategorien verknüpfen:

1. Öffnen Sie das Menü **Einstellungen » Märkte » Etsy » Kategorieverknüpfung**.
2. Klicken Sie auf **Suchen**.
    → Das Fenster **Kategorie wählen** wird geöffnet.
3. Wählen Sie die Etsy-Kategorie, die am besten zu Ihrer Webshop-Kategorie passt.
4. Klicken Sie auf **Übernehmen**.
    → Die Bezeichnung der Etsy-Kategorie und der Kategoriepfad werden angezeigt.
5. Wenn Sie die Bezeichnung der Etsy-Kategorie bereits kennen, geben Sie sie in das Feld **Marktplatzkategorie** ein, um sie mit Ihrer Webshop-Kategorie zu verknüpfen.
6. **Speichern** Sie die Einstellungen.

## Merkmale verknüpfen

Um Merkmale für den Marktplatz Etsy zu nutzen, verknüpfen Sie diese mit Etsy. Beachten Sie, dass es sich bei **Hersteller** und **Hergestellt** um Pflichtmerkamle handelt.

##### Merkmale verknüpfen:

1. Öffnen Sie das Menü **Einstellungen » Märkte » Etsy » Merkmalverknüpfung**.
2. Klicken Sie auf **Suchen**.
    → Das Fenster **Merkmale wählen** wird geöffnet.
3. Wählen Sie das Etsy-Merkmal, das am besten zu Ihrem Webshop-Merkmal passt.
4. Klicken Sie auf **Übernehmen**.
    → Die Bezeichnung des Etsy-Merkmals und der Merkmalpfad werden angezeigt.
5. **Speichern** Sie die Einstellungen.

## Rechtliche Hinweise übertragen 

Um rechtliche Hinweise an den Marktplatz Etsy zu übertragen, hinterlegen Sie diese Hinweise für jede Sprache, für die Sie Ihr Sortiment anbieten. Die Hinweise werden der Artikelbeschreibung hinzugefügt. 

##### Rechtliche Hinweise hinterlegen: 

1. Öffnen Sie das Menü **Einstellungen » Märkte » Etsy » Rechtliche Hinweise**. 
→ Das Fenster **Rechtliche Hinweise** wird geöffnet. 
2. Wählen Sie die Sprache aus, für die rechtliche Hinweise hinterlegt werden sollen. 
3. Geben Sie Ihren Text ein. 
4. **Speichern** Sie die Einstellungen.

## Versandprofile verknüpfen

Im Menü **Einstellungen » Märkte » Etsy » Versandprofilverknüpfungen** verknüpfen Sie Etsy-Versandprofile, die Sie zuvor bei Etsy angelegt und in plentymarkets importiert haben, mit den Versandprofilen Ihres Webshops.

##### Versandprofile verknüpfen:

1. Öffnen Sie das Menü **Einstellungen » Märkte » Etsy » Versandprofilverknüpfung**.
2. Wählen Sie das Etsy-Versandprofil, das am besten zu Ihrem Webshop-Versandprofil passt.
3. Klicken Sie auf **Übernehmen**.
4. **Speichern** Sie die Einstellungen.

## Zahlungsbestätigung automatisch senden

Richten Sie eine Ereignisaktion ein, um Zahlungsbestätigungen automatisch an Etsy zu senden, nachdem ein Zahlungseingang gebucht wurde.

##### Ereignisaktion einrichten:

1. Öffnen Sie das Menü **Einstellungen » Aufträge » Ereignisaktionen**.
2. Klicken Sie auf **Ereignisaktion hinzufügen**.
→ Das Fenster **Neue Ereignisaktion erstellen** wird geöffnet.
3. Geben Sie einen Namen ein.
4. Wählen Sie das Ereignis gemäß Tabelle 1.
5. **Speichern** Sie die Einstellungen.
6. Nehmen Sie die Einstellungen gemäß Tabelle 1 vor.
7. Setzen Sie ein Häkchen bei **Aktiv**.
8. **Speichern** Sie die Einstellungen.

<table>
<thead>
		<th>
			Einstellung
		</th>
		<th>
			Option
		</th>
<th>
			Auswahl
		</th>
	</thead>
	<tbody>
      <tr>
         <td><strong>Ereignis</strong></td>
         <td><strong>Zahlung: Vollständig</strong></td> 
<td></td>
      </tr>
      <tr>
         <td><strong>Filter 1</strong></td>
         <td><strong>Auftrag > Auftragstyp</strong></td>
<td><strong>Auftrag</strong></td>
      </tr>
<tr>
         <td><strong>Filter 2</strong></td>
         <td><strong>Auftrag > Herkunft</strong></td>
<td><strong>Etsy</strong></td>
      </tr>
      <tr>
         <td><strong>Aktion</strong></td>
         <td><strong>Plugin > Zahlungsbestätigung an Etsy senden</strong></td>
<td>&nbsp;</td>
      </tr>
</tbody>
</table>

## Versandbestätigung automatisch senden

Richten Sie eine Ereignisaktion ein, um Versandbestätigungen automatisch an Etsy zu senden, nachdem ein Warenausgang gebucht wurde.

##### Ereignisaktion einrichten:

1. Öffnen Sie das Menü **Einstellungen » Aufträge » Ereignisaktionen**.
2. Klicken Sie auf **Ereignisaktion hinzufügen**.
→ Das Fenster **Neue Ereignisaktion erstellen** wird geöffnet.
3. Geben Sie einen Namen ein.
4. Wählen Sie das Ereignis gemäß Tabelle 2.
5. **Speichern** Sie die Einstellungen.
6. Nehmen Sie die Einstellungen gemäß Tabelle 2 vor.
7. Setzen Sie ein Häkchen bei **Aktiv**.
8. **Speichern** Sie die Einstellungen.


<table>
	<thead>
		<th>
			Einstellung
		</th>
		<th>
			Option
		</th>
<th>
			Auswahl
		</th>
	</thead>
	<tbody>
      <tr>
         <td><strong>Ereignis</strong></td>
         <td><strong>Auftragsänderung: Warenausgang gebucht</strong></td> 
<td></td>
      </tr>
      <tr>
         <td><strong>Filter 1</strong></td>
         <td><strong>Auftrag > Auftragstyp</strong></td>
<td><strong>Auftrag</strong></td>
      </tr>
<tr>
         <td><strong>Filter 2</strong></td>
         <td><strong>Auftrag > Herkunft</strong></td>
<td><strong>Etsy</strong></td>
      </tr>
      <tr>
         <td><strong>Aktion</strong></td>
         <td><strong>Plugin > Versandbestätigung an Etsy senden</strong></td>
<td>&nbsp;</td>
      </tr>
</tbody>
</table>

## Übersicht der benötigten API-Calls

<table>
<thead>
		<th>
			Prozess
		</th>
		<th>
			Call
		</th>
	</thead>
	<tbody>
      <tr>
         <td><b>Listing-Start</b></td>
         <td>Ein Call pro Sprache. Bei nur einer Sprache wird kein zusätzlicher Call benötigt.<br /> Ein Call für die Methode <b>CreateListing</b>.<br /> Ein Call pro Artikelbild.<br /> Ein Call für die Methode <b>Publish</b>.<br /> => mindestens drei API-Calls</td> 
      </tr>
      <tr>
         <td><b>Listing-Update</b></td>
         <td>Ein Call pro Sprache.<br /> => mindestens ein API-Call</td>
      </tr>
<tr>
         <td><b>Bestandsabgleich</b></td>
         <td>Ein Call pro Listing.</td>
      </tr>
      <tr>
         <td><b>Delete Listing</b></td>
         <td>Ein Call pro Listing.</td>
      </tr>
      <tr>
         <td><b>Order-Import</b></td>
         <td>Ein Call pro Stunde.</td>
      </tr>
</tbody>
</table>

## Erforderliche Berechtigungen für das Etsy-Plugin
 
 Damit Benutzer der Benutzerklasse **Variabel** das **Etsy-Plugin** nutzen können, sind REST-API Berechtigungen erforderlich.
 
 Berechtigungen werden im Menü **System » Einstellungen » Benutzer » Konten » Benutzerkonto » Tab: Berechtigung** zugewiesen.
 
 → **Tipp**: Nutzen Sie die Filterfunktion, um nach Benutzern der Benutzerklasse **Variabel** zu suchen. Wählen Sie dazu im **Tab: Filter** in der Dropdown-Liste **Klasse** den Filter **Variabel** aus. Klicken Sie auf **Suchen**.
 
 #### Erforderliche REST-API Berechtigungen
 
 Die folgenden REST-API Berechtigungen sind für variable Benutzerklassen erforderlich.
 
 - **Kategorien** und alle untergeordneten Berechtigungen
 - Artikel » **Merkmale** und alle untergeordneten Berechtigungen
 - Märkte » **Zugangsdaten** und alle untergeordneten Berechtigungen
 
 #### REST-API Berechtigungen zuweisen:
 
 1. Öffnen Sie das Menü **System » Einstellungen » Benutzer » Konten**.
 2. Klicken Sie auf den **Benutzer**, dem sie Berechtigungen zuweisen wollen.
 3. Wechseln Sie zu **Tab: Berechtigung » Tab: REST-API**.
 4. Setzen Sie ein Häkchen neben den benötigten Berechtigungen.
 5. **Speichern** Sie die Einstellungen.

## Lizenz

Das gesamte Projekt unterliegt der GNU AFFERO GENERAL PUBLIC LICENSE – weitere Informationen finden Sie in der [LICENSE.md](https://github.com/plentymarkets/plugin-etsy/blob/master/LICENSE.md).
