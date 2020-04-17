# Release Notes für Etsy

## v2.0.19 (2020-04-17)
### Fixed
- Es werden jetzt alle Zeilenumbrüche in der Beschreibung korrekt exportiert.

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
