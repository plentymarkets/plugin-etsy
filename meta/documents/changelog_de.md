# Release Notes für Etsy

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