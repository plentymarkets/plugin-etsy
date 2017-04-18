# Release Notes für Etsy

## v1.0.12 (2017-04-18)
### Fixed
- Es wurden einige Probleme bezüglich des Tag- und Titel-Exports behoben.

## v1.0.11 (2017-04-03)
### Changed
- Das Plugin benutzt jetzt die neuste Terra-Komponente Version.
### Fixed
- Die Kategorie-Namen werden jetzt in der Login-Sprache angezeigt.

## v1.0.10 (2017-03-29)
### Changed
- Der Update-Prozess der Listings wurde angepasst. Es wird als erstes versucht die Listing Übersetzungen zu aktualisieren
und danach die restliche Daten.

## v1.0.9 (2017-03-24)
### Changed
- Artikel-Tags mit mehr als 20 Buchstaben werden nicht mehr exportiert

## v1.0.8 (2017-03-23)
### Changed
- Doppelte Artikel-Tags werden nicht mehr exportiert

## v1.0.7 (2017-03-21)
### Fixed
- Eine Einstellung, die für das Schreiben der Datensätze in DynamoDB zuständig ist, wurde angepasst 
### Changed 
- Es werden nur die ersten 13 Artikel-Tags exportiert, der Rest wird ignoriert
### Hinzugefügt
- Verbesserte Logging-Funktionen für Artikel-Export und Auftragsimport

## v1.0.6 (2017-03-16)
### Fixed
- Alle 244 Länder in Etsy können nun den Ländern in plentymarkets zugeordnet werden 
- Etsy-Aufträge, die durch ein Kommunikationsproblem nicht importiert werden konnten, werden nachträglich importiert

## v1.0.5 (2017-03-06)
### Fixed
- Ein Fehler, welcher Ereignisaktionen sporadisch nicht ausgeführt hat, konnte behoben werden
- Bilder werden nun in der selben Reihenfolge wie im Artikel hochgeladen
- Ein Fehler, der das Anlegen von Listings verhindert, wenn alle Versandprofile im Artikel aktiviert sind, wurde behoben

## v1.0.4 (2017-03-03)
### Fixed
- Ein Problem, welches dazu führte, dass Artikel beim Auftragsimport falsch zugeordnet wurden, wurde behoben. 

## v1.0.3 (2017-02-28)
### Fixed
- Ein Problem, das in einigen Fällen dazu führte, dass die externe Payment-ID nicht importiert wurde
- Zusätzliche Adressinformationen werden nun auch importiert

## v1.0.2 (2017-02-24)
### Fixed
- Ein Problem, welches Einstellungen beim Speichern nicht komplett übernommen hat, konnte behoben werden.

## v1.0.1 (2017-02-22)
### Changed
- Kleine UI Anpassungen

## v1.0.0 (2017-02-20)
### Hinzugefügt
- Initiale Plugin-Dateien hinzugefügt