# Release Notes für Etsy

## v2.1.15 (2022-05-20)
### Fixed
- Ein Fehler im Zusammenhang mit PHP8 wurde behoben.

## v2.1.14 (2022-05-19)
### Fixed
- Ein Fehler im Zusammenhang mit PHP8 wurde behoben.

## v2.1.13 (2022-01-13)
### Behoben
- Der Wert "2020_2020" im Feld "when_made" wird nun automatisch auf den validen Wert "2020_2022" abgeändert.

## v2.1.12 (2022-01-05)
### Behoben
- Die möglichen Werte für das Feld "when_made" wurden von Etsy angepasst. Das Plugin wurde erweitert um diese Anpassung zu berücksichtigen.

## v2.1.11 (2021-11-25)
### Behoben
- Bei der Anlage von Auftragspositionen übermittelt Etsy in manchen Fällen keinen Titel in den Auftragsdaten. Für diesen Fall wurde nun ein Backup implementiert.

## v2.1.10 (2021-11-04)
### Behoben
- Personalisierungsinformationen werden nun pro Auftragsposition an der Artikelbezeichnung ergänzt, damit diese auch auf den Auftragsdokumenten angezeigt werden.
- Beim Auftragsimport wurden Zahlungen teilweise nicht angelegt. Dieses Verhalten wurde behoben.

## v2.1.9 (2021-10-25)
### Behoben
- Personalisierungsinformationen wurden mehrfach importiert, wenn derselbe Artikel mehrfach als Artikelposition existierte. Dieses Verhalten wurde behoben.

## v2.1.8 (2021-09-28)
### Behoben
- Bei der Übermittlung der Versandbestätigung wurden in einigen Fällen keine Sendungsnummer übertragen. Dieses Verhalten wurde behoben.

## v2.1.7 (2021-09-17)
### Behoben
- Es werden nun die Personalisierungsinformationen aller Artikelpositionen importiert.

## v2.1.6 (2021-09-13)
### Behoben
- Die Etsy-Zahlungsart ist nun standardmäßig suchbar im Backend, wenn Zugangsdaten für Etsy hinterlegt sind.

## v2.1.5 (2021-08-18)
### Hinzugefügt
- Anfragen an Etsy, die aufgrund von Verbindungsproblemen mit dem SDK-Server fehlschlagen, werden nun bis zu dreimal wiederholt.

## v2.1.4 (2021-08-18)
### Geändert
- Beim Auftragsimport werden jetzt Plugin-Zahlungsarten zugeordnet, falls vorhanden.
  Hierbei handelt es sich um eine notwendige Anpassung im Rahmen des [EOL der nicht-Plugin Zahlungsarten](https://forum.plentymarkets.com/t/640916).

## v2.1.3 (2021-08-05)
### Hinzugefügt
- Anfragen an Etsy, die aufgrund von Verbindungsproblemen fehlschlagen, werden nun bis zu dreimal wiederholt.

## v2.1.2 (2021-08-05)
### Behoben
- In Rechnungs- und Lieferadressen werden Sonderzeichen nun korrekt dargestellt.

## v2.1.1 (2021-08-04)
### Hinzugefügt
- Logs für Anfragen an Etsy erweitert.

## v2.1.0 (2021-07-30)
### Hinzugefügt
- Beim Auftragsimport werden die Personalisierungsinformationen nun als Auftragsnotiz hinzugefügt.

## v2.0.43 (2021-07-27)
### Geändert
- Log-Erweiterung für den Fehler `503 Service Unavailable`

## v2.0.42 (2021-05-10)
### Geändert
- Bei manchen Aufträgen kann es seitens Etsy bis zu 72 Stunden oder länger dauern, bis die Zahlung bestätigt ist.<br>Diese Aufträge werden ab jetzt ohne Zahlung importiert. Die Zahlung wird automatisch nachgebucht, wenn die Zahlung bei Etsy bestätigt wurde.

## v2.0.41 (2021-05-03)
### Behoben
- In manchen Fällen flog im StartListingService der Fehler: "Shop is not enrolled in this language. (en) Cannot edit field "1"". Dieses Problem wurde behoben.

## v2.0.40 (2021-04-30)
### Behoben
- In manchen Fällen wird die ID der Shop-Abteilung von Etsy als String übertragen. Dies führte zu Problemen beim Export. Dieses Verhalten wird nun berücksichtigt.
- Log-Anpassungen

## v2.0.39 (2021-04-29)
### Behoben
- Aufgrund einer Änderung im Verhalten der API konnten Listings nach dem Starten nicht mehr aktiv geschaltet werden. Das Aktiv-Schalten wurde an die neuen Gegebenheiten angepasst.


## v2.0.38 (2021-03-03)
### Behoben
- Inaktive Etsy-Kataloge konnten zu Problemen beim Export führen. Dieses Verhalten wurde behoben.

## v2.0.37 (2020-11-20)
### Behoben
- Bei steuerpflichtigen Aufträgen wird die Steuer nicht mehr importiert, da die Steuer direkt durch Etsy abgeführt wird. Der Auftrag und die Zahlung beinhalten also nur noch die Artikelpreise und die Versandkosten.

## v2.0.36 (2020-10-22)
### Behoben
- Bei Export oder Aktualisierung eines Artikels konnte die Länge des Titels bemängelt werden, obwohl die maximale Länge nicht überschritten wurde. Dieses Verhalten wurde behoben.

## v2.0.35 (2020-10-13)
### Behoben
- Bei der Übermittlung der Versandbestätigung konnte es zu Problemen kommen, wenn das Versandprofil am Auftrag auf ein Plugin zurückzuführen war. Dieses Verhalten wurde behoben.

## v2.0.34 (2020-10-05)
### Behoben
- Beim Upload von Bildern bei bestehenden Listings konnte es vorkommen, dass Bilder trotz einer niedrigen Position nicht hochgeladen wurden, wenn mehr als 10 Bilder für Etsy freigeschaltet waren. Dieses Verhalten wurde behoben.

## v2.0.33 (2020-10-01)
### Behoben
- Beim Upload von Bildern kam es zu Problemen, wenn diese Bilder nicht von plentymarkets hochgeladen wurden oder diese Information dem System durch einen Fehler fehlte. Dieses Verhalten wurde behoben.

## v2.0.32 (2020-09-30)
### Behoben
- In Einzelfällen wurde beim Versenden der Versandbestätigung statt des gespeicherten Versanddienstleisters der Versanddienstleister "dhl-germany" übertragen. Dieses Verhalten wurde behoben.

## v2.0.31 (2020-09-29)
### Geändert
- Weitere Log-Einträge für die Übermittlung der Versandbestätigung hinzugefügt.

## v2.0.30 (2020-09-24)
### Geändert
- Log-Einträge für die Übermittlung der Versandbestätigung hinzugefügt.

## v2.0.29 (2020-09-22)
### Behoben
- In Einzelfällen wurde der Export bei der Fehlerbehandlung abgebrochen. Dieses Verhalten wurde behoben.

## v2.0.28 (2020-09-17)
### Geändert
- Plugin-Informationen aktualisiert.

## v2.0.27 (2020-07-29)
### Behoben
- Der Fix aus Version 2.0.25 für die Felder "Minimale Herstellungsdauer" und "Maximale Herstellungsdauer" griff nur beim Aktualisieren bestehender Listings. Nun funktionieren die Felder auch beim Starten eines Listings korrekt.
- Es konnte in Einzelfällen vorkommen, dass nicht alle für den Export relevanten Varianten berücksichtigt wurden. Dieses Verhalten wurde behoben.

## v2.0.26 (2020-07-23)
### Behoben
- Es konnte in Einzelfällen vorkommen, dass Bilder am Listing scheinbar willkürlich entfernt und wieder hinzugefügt wurden. Dieses Verhalten wurde behoben.

## v2.0.25 (2020-07-15)
### Behoben
- Log-Einträge in der Bilder-Update-Funktionalität wurden spezifiziert
- Die Felder "Minimale Herstellungsdauer" und "Maximale Herstellungsdauer" werden nun auch mit Eigenschaften und eigenen Werten als Quelle korrekt übertragen
- Falls das Löschen eines Bildes fehlschlägt, weil dieses am Listing nicht mehr existiert führt das nicht länger zu einem Fehler

## v2.0.24 (2020-07-10)
### Geändert
- Neue Log-Einträge in der Bilder-Update-Funktionalität hinterlegt, um die Fehlersuche zu vereinfachen.

## v2.0.23 (2020-06-24)
### Geändert
- Auch die englischsprachige Plugin-Beschreibung wurde nun in das plentymarkets Handbuch umgezogen.

## v2.0.22 (2020-06-22)
### Behoben
- Probleme mit der internen Speicherkapazität wurden behoben.

## v2.0.21 (2020-06-08)
### Geändert
- Der Umzug der englischsprachigen Plugin-Beschreibung ins Handbuch wurde vorübergehend rückgängig gemacht, da eine Weiterleitung aus dem Handbuch auf den User Guide im Plugin noch aktiv ist.

## v2.0.20 (2020-06-08)
### Geändert
- Die Plugin-Beschreibung wurde in das plentymarkets Handbuch umgezogen.

## v2.0.19 (2020-04-17)
### Fixed
- Alle Zeilenumbrüche in der Beschreibung werden jetzt korrekt exportiert.

## v2.0.18 (2020-04-06)
### Fixed
- Alle Listings werden jetzt nach Ablauf korrekt erneuert.

## v2.0.17 (2020-02-19)
### Fixed
- Die Export-Performance wurde verbessert.
- Ein Rundungsproblem bei Beständen wurde behoben. Es wird jetzt immer korrekt abgerundet.
- Varianten mit negativem Bestand führen nicht mehr zum Abbruch des Exports.

## v2.0.16 (2020-01-13)
### Fixed
- Validierungsproblem der Felder Anlass und Empfänger beim Starten von Listings behoben

## v2.0.15 (2019-01-06)
### Fixed
- Problem mit der Aktualisierung der SDK behoben

## v2.0.14 (2019-12-27)
### Fixed
- Neue Log-Nachrichten hinzugefügt, um das Verhalten des Plugins besser verständlich zu machen
- Einige Log-Nachrichten angepasst, um ihre Bedeutung klarer auszudrücken

## v2.0.13 (2019-12-23)
### Fixed
- Artikel werden nun auch korrekt gelistet, wenn einzelne Varianten keinen Bestand haben.
- Falls Varianten ihre Attribute in unterschiedlicher Reihenfolge verknüpft haben führt das nicht länger zu einem Fehler.
- Die Rechtlichen Hinweise bleiben nun auch nachdem Update erhalten.
- Die Felder Empfänger und Anlass führen nun nicht mehr zu Fehlern, wenn sie in der Shopsprache gepflegt wurden.
- Varianten mit einem Bestand von über 999 werden jetzt korrekt exportiert.
- Alle Log-Nachrichten werden jetzt korrekt übersetzt.

## v2.0.12 (2019-12-16)
### Fixed
- Fehler beim Auftragsimport behoben.

## v2.0.11 (2019-12-10)
### Fixed
- Dokumente werden nun in der Sprache des Lieferlandes erstellt.

## v2.0.10 (2019-11-19)
### Fixed
- Listings ohne Varianten werden nun korrekt deaktiviert wenn sie keinen Bestand haben.

## v2.0.9 (2019-11-15)
### Fixed
- Die Auftragssumme für US-Aufträge wird wieder korrekt berechnet.

## v2.0.8 (2019-10-21)
### Angepasst
- Rechenoperator angepasst um Bestand = 1 identifizieren zu können

## v2.0.7 (2019-10-09)
### Angepasst
- Wenn gesamter Bestand über 999 ist, wird 999 an Etsy geschickt

## v2.0.6 (2019-10-09)
### Angepasst
- UpdateService angepasst, Tags werden beim Update jetzt nicht mehr entfernt

## v2.0.5 (2019-10-08)
### Angepasst
- Bestandsabgleich wurde angepasst

## v2.0.4 (2019-10-04)
### Angepasst
- Nächtlicher Cron konnte durch critical Fehler eines Validators nicht durchlaufen

## v2.0.3 (2019-09-25)
### Geändert
- Assistent kann jetzt nur einmal ausgeführt werden

## v2.0.2 (2019-09-23)
### Hinzugefügt
- Changelog angepasst

## v2.0.1 (2019-09-23)
### Geändert
- Quellcode nicht mehr einsehbar

## v2.0.0 (2019-09-23)
### Hinzugefügt
- Varianten können jetzt exportiert werden
- Performance Verbesserungen
- Katalog als neue Produktfeldverknüpfung

## v1.3.16 (2019-09-05)
### Geändert
- Letzte Änderung rückgängig gemacht.

## v1.3.15 (2019-07-24)
### Fixed
- Die Auftragsposition "Als Geschenk" wir nun nicht mehr bei der Versandprofilberechnung berücksichtigt.

## v1.3.14 (2019-07-17)
### Hinzugefügt
- Weiche Zeilenumbrüche werden jetzt in der Artikelbeschreibung beachtet.

## v1.3.13 (2019-07-05)
- FIX Merkmal-Import.

## v1.3.12 (2019-05-28)
### Geändert
- Userguide entsprechend neuer Authentifizierung angepasst

## v1.3.11 (2019-05-27)
### Geändert
- Authentifizierungsprozess umgezogen.
- Developer Apps werden nicht mehr benötigt.
- Consumer Key und Shared Secret müssen nicht mehr in der Plugin Config gepflegt werden.

## v1.3.10 (2019-05-24)
### Hinzugefügt
- Plugin Marketplace Dokumentation angepasst

## v1.3.9 (2019-05-13)
### Fixed
- Ein fehlerhafter Log beim Artikelexport wurde korrigiert.

## v1.3.8 (2019-02-21)
### Geändert
- Der User Guide wurde angepasst.

## v1.3.7 (2019-01-29)
### Hinzugefügt
- Ein Log vor dem Auftragsimport wurde hinzugefügt.

## v1.3.6 (2019-01-17)
### Fixed
- Der Datentyp für den Bestand von Varianten wurde angepasst.

## v1.3.5 (2019-01-16)
### Fixed
- Der Datentyp für den Bestand einer Variante wurde auf ganze Zahlen beschränkt.

## v1.3.4 (2018-11-26)
### Fixed
- Das Limit beim Auftragsimport wurde von 25 Aufträge auf 200 Aufträge pro Prozess erhöht.

## v1.3.3 (2018-11-14)
### Fixed
- Die plentymarkets-Kategorien werden nun für die Kategorie-Verknüpfung immer angezeigt.

## v1.3.2 (2018-11-14)
### Fixed
- Die Anweisungen in den Logs werden nun korrekt angezeigt.

## v1.3.1 (2018-11-12)
### Fixed
- Auftragspositionen werden nun in der Sprache des Shops importiert.

## v1.3.0 (2018-09-26)
### Hinzugefügt
- Die Artikelbeschreibung wird durch rechtliche Hinweise ergänzt.

## v1.2.24 (2018-09-11)
### Hinzugefügt
- Ein Log wurde hinzugefügt.

## v1.2.23 (2018-08-28)
### Geändert
- Tags können nun mit Leerzeichen übertragen werden.

## v1.2.22 (2018-08-27)
### Hinzugefügt
- Den Logs wurden Anweisungen hinzugefügt.

## v1.2.21 (2018-07-17)
### Geändert
- Die Logs für einige Prozesse wurden angepasst.

## v1.2.20 (2018-07-13)
### Fixed
- Ein Fehler wurde behoben, der das Aktualisieren eines Artikels verhindert hat.

## v1.2.19 (2018-07-09)
### Geändert
- Die Informationen zum Installieren des Plugins wurden im User Guide angepasst.

## v1.2.18 (2018-07-09)
### Fixed
- Ein Fehler wurde behoben, der das Bereitstellen des Plugins verhindert hat.

## v1.2.17 (2018-06-05)
### Geändert
- Das Log-Level für einige Logs wurde geändert.

## v1.2.16 (2018-05-09)
### Fixed
- Die Plugin-Config ist multilingual.

## v1.2.15 (2018-05-08)
### Fixed
- Ein Fehler wurde behoben, welcher dazu führte, dass die Rechnungsanschrift nicht angelegt werden konnte.

## v1.2.14 (2018-05-02)
### Fixed
- Die angegebene Lieferadresse wird auch als Rechnungsadresse hinterlegt.

## v1.2.13 (2018-04-26)
### Hinzugefügt
- Dem User Guide wurden Informationen über benötigte Berechtigungen für variable Benutzerklassen hinzugefügt.

## v1.2.12 (2018-04-23)
### Fixed
- Merkmale ohne Gruppe werden korrekt angezeigt.

## v1.2.11 (2018-02-20)
### Fixed
- Ein Fehler wurde behoben, welcher dazu führte, dass die plentymarkets Kategorien mehrfach angezeigt wurde.

## v1.2.10 (2018-02-20)
### Geändert
- Plugin-Kurzbeschreibung wurde angepasst.

## v1.2.9 (2018-01-23)
#### Fixed
- Ein Fehler wurde behoben, welcher dazu führte, dass Ereignisaktionen (Versandbestätigung und Zahlungsbestätigung) nicht korrekt durchgeführt wurden.

## v1.2.8 (2018-01-16)
#### Changed
- Die Struktur der externen Auftragsnummer wurde angepasst, damit die PayPal-Zahlungen eine höhere Übereinstimmungsrate haben.

## v1.2.7 (2018-01-15)
#### Fixed
- Es wurde ein Bug behoben, welcher dazu führte, dass das Plugin nicht richtig gebaut wurde.

## v1.2.6 (2018-01-05)
#### Hinzugefügt
- Neue Logs für Etsy Ereignisaktionen.

## v1.2.5 (2017-12-29)
#### Geändert
- Es werden nun mehrere Informationen angezeigt, wenn ein Listing nicht gestartet werden kann.

## v1.2.4 (2017-12-19)
#### Fixed
- Es wurde ein Bug behoben welcher verhinderte das Merkmale manchmal nicht richtig angezeigt waren.

## v1.2.3 (2017-12-18)
#### Fixed
- Es wurde ein Bug behoben, welcher verhinderte, dass Merkmale in der richtige Sprache angezeigt waren.

## v1.2.2 (2017-11-29)
#### Fixed
- Es wurde ein Bug behoben, welcher verhinderte, dass mehrere neu hinzugefügte Kategorie- oder Merkmalverknüpfungen gleichzeitig bearbeitet werden konnten.

#### Fixed
- Titel der Artikel im Auftragspositionen wird richtig angezeigt.

## v1.2.1 (2017-11-22)
#### Hinzugefügt
- Geschenkinformationen werden jetzt auch zu den Aufträge importiert, als Auftragsposition und als Auftragsnotiz.
- Käufernotizen und Zahlungsnotizen werden importiert.

#### Fixed
- Titel der Artikel im Auftragspositionen wird richtig angezeigt.

## v1.2.0 (2017-11-20)
#### Geändert
- Diese Aktualisierung bringt UI-Anpassungen an alle Bereiche. Die Benutzeroberflächen wurden auf die neue Terra Styleguide aktualisiert und optimieren damit die UX.

#### Hinzugefügt
- Möglichkeit ein Konto und dessen Einstellungen zu löschen.

## v1.1.13 (2017-11-06)
#### Fixed
- Listing-Entwürfe welche nicht erfolgreich starten waren manchmal nicht entfernt.

## v1.1.12 (2017-11-03)
#### Fixed
- Es wurden einige zusätzliche Prüfungen eingebaut bezüglich eines VAT Bugs, welcher den Auftragsimport verhinderte.

## v1.1.11 (2017-11-02)
#### Fixed
- Es wurde ein Fehler bezüglich der VAT behoben, welcher den Auftragsimport verhinderte.

## v1.1.10 (2017-10-10)
#### Hinzugeügt
- Etsy-Gutscheine werden nun beim Auftragsimport als eigene Artikelposition hinzugefügt.

## v1.1.9 (2017-10-09)
#### Hinzugeügt
- Es ist nun möglich das Merkmal “Was ist es” mit dem Artikel zu verknüpfen.

## v1.1.8 (2017-09-09)
#### Fixed
- Es wurde ein Bug bezüglich der Preisformatierung behoben, welcher den Artikelexport verhinderte.

## v1.1.7 (2017-07-24)
#### Fixed
- Es wurden mehrere Informationen zu den Log-Einträge für das Hochladen der Übersetzungen hinzugefügt

## v1.1.6 (2017-06-26)
#### Fixed
- Bei den Auswahlboxen der Einstellungen und Versandprofilen wird nun ein korrekter Standardwert vorausgewählt.

## v1.1.5 (2017-06-19)
#### Fixed
- Beim Artikelexport werden nun pro Variante die allgemeinen Artikelbilder (unverknüpfte) sowie die mit der Variante verknüpften Bilder berücksichtigt.

## v1.1.4 (2017-06-14)
#### Fixed
- Es wurde ein Bug behoben der verursachte das manchmal alte (unbenutzte SKUs) nicht gelöscht waren

## v1.1.3 (2017-06-06)
#### Fixed
- Aufträge welche keine PLZ haben werden jetzt auch importiert.

## v1.1.2 (2017-06-06)
#### Fixed
- Wird ein Listing direkt auf Etsy gelöscht, wird auch die zugehörige SKU aus plentymarkets entfernt

## v1.1.1 (2017-06-02)
#### Changed
- Es wurden mehrere Log-Ausgaben hinzugeüfgt für Starten/Aktualisieren der Listings

## v1.1.0 (2017-05-31)
#### Changed
- Etsy-Kategorien wurden aktualisiert.

## v1.0.17 (2017-05-08)
#### Fixed
- Beim Erstellen eines Listings mit mehr als einem Attribut kam es zu einem Fehler. Dieser wurde behoben.

## v1.0.16 (2017-05-04)
#### Fixed
- Es wurde ein Fehler behoben bezüglich der ausgewählten Shop-Sprache.

## v1.0.15 (2017-05-04)
#### Fixed
- Fehlermeldungen welche während der Listing-Erstellung vorkommen werden besser angezeigt.

## v1.0.14 (2017-05-04)
#### Fixed
- Es wurden einige Probleme bezüglich des Exports von Versandprofilen behoben.

## v1.0.13 (2017-05-02)
#### Fixed
- Es wurden einige Probleme bezüglich des Exports von Maßangaben behoben.

## v1.0.12 (2017-04-18)
#### Fixed
- Es wurden einige Probleme bezüglich des Tag- und Titel-Exports behoben.

## v1.0.11 (2017-04-03)
#### Changed
- Das Plugin benutzt jetzt die neuste Terra-Komponente Version.
#### Fixed
- Die Kategorie-Namen werden jetzt in der Login-Sprache angezeigt.

## v1.0.10 (2017-03-29)
#### Changed
- Der Update-Prozess der Listings wurde angepasst. Es wird als erstes versucht die Listing Übersetzungen zu aktualisieren
und danach die restliche Daten.

## v1.0.9 (2017-03-24)
#### Changed
- Artikel-Tags mit mehr als 20 Buchstaben werden nicht mehr exportiert

## v1.0.8 (2017-03-23)
#### Changed
- Doppelte Artikel-Tags werden nicht mehr exportiert

## v1.0.7 (2017-03-21)
#### Fixed
- Eine Einstellung, die für das Schreiben der Datensätze in DynamoDB zuständig ist, wurde angepasst
#### Changed
- Es werden nur die ersten 13 Artikel-Tags exportiert, der Rest wird ignoriert
#### Hinzugefügt
- Verbesserte Logging-Funktionen für Artikel-Export und Auftragsimport

## v1.0.6 (2017-03-16)
#### Fixed
- Alle 244 Länder in Etsy können nun den Ländern in plentymarkets zugeordnet werden
- Etsy-Aufträge, die durch ein Kommunikationsproblem nicht importiert werden konnten, werden nachträglich importiert

## v1.0.5 (2017-03-06)
#### Fixed
- Ein Fehler, welcher Ereignisaktionen sporadisch nicht ausgeführt hat, konnte behoben werden
- Bilder werden nun in der selben Reihenfolge wie im Artikel hochgeladen
- Ein Fehler, der das Anlegen von Listings verhindert, wenn alle Versandprofile im Artikel aktiviert sind, wurde behoben

## v1.0.4 (2017-03-03)
#### Fixed
- Ein Problem, welches dazu führte, dass Artikel beim Auftragsimport falsch zugeordnet wurden, wurde behoben.

## v1.0.3 (2017-02-28)
#### Fixed
- Ein Problem, das in einigen Fällen dazu führte, dass die externe Payment-ID nicht importiert wurde
- Zusätzliche Adressinformationen werden nun auch importiert

## v1.0.2 (2017-02-24)
#### Fixed
- Ein Problem, welches Einstellungen beim Speichern nicht komplett übernommen hat, konnte behoben werden.

## v1.0.1 (2017-02-22)
#### Changed
- Kleine UI Anpassungen

## v1.0.0 (2017-02-20)
#### Hinzugefügt
- Initiale Plugin-Dateien hinzugefügt
