# Moduł Bliskapaczka dla Magento 1.9 

## Instalacja modułu

### Wymagania
W celu poprawnej instalacji modułu wymagane są:
- php >= 5.6
- composer

### Instalacja modułu
1. Pobierz repozytorium i skopiuj jego zawartość do katalogu domowego swojego Magento
  - Jeśli masz już plik composer.json musisz zmergować zawartość pliku modułu do własnego. Plik musi zawierać:
    ```
        "repositories": [
            ...
            {
                "type": "vcs",
                "url": "https://github.com/bliskapaczkapl/bliskapaczka-api-client.git"
            }
        ],
        "require": {
            ...
            "bliskapaczka/bliskapaczka-api-client": "^1.0"
        }
    ```
1. Zainstaluj zależności composerem. Uruchom poniższą komendę w katalogu domowym Magento
    ```
    composer install --no-dev
    ```
1. Sprawdz czy moduł znajduje się na liście dostępnych modułów w Panelu Admina
1. Włącz moduł z poziomu Panelu Admina lub zmieniając wartość parametru `active` w pliku `app/etc/modules/Sendit_Bliskapaczka.xml`, tak jak poniżej:
    ```
    <config>
        <modules>
            <Sendit_Bliskapaczka>
                <active>true</active>
                <codePool>community</codePool>
            </Sendit_Bliskapaczka>
        </modules>
    </config>
    ```
1. Sprawdź czy na liście dostępnych metod dostawy pojawiła się nowa metoda wysyłki "Bliskapaczka"
1. Dodaj swój klucz API w poli `API Key`. Znajdziesz go w zakładce Integracja panelu [bliskapaczka.pl](http://bliskapaczka.pl/panel/integracja)
1. Następnie ustal wymiary i wagę standardowej paczki w polach `Fixed parce type size X`, `Fixed parce type size Y`, `Fixed parce type size Z`, `Fixed parce type weight`

### Tryb testowy

Tryb testowy, czli komunikacja z testową wersją znajdującą się pod adresem [sandbox-bliskapaczka.pl](https://sandbox-bliskapaczka.pl/) można uruchomić przełączają w ustwieniach modułu opcję `Test mode enabled` na `Yes`.

## Rozwój modułu

### Instalacja zależności
```
composer install --dev
```

### Jak uruchomić testy jednostkowe 
```
php vendor/bin/phpunit --bootstrap dev/tests/bootstrap.php dev/tests/unit/
```

### Jak uruchomić statyczną analizę kodu
```
php vendor/bin/phpcs --standard=./vendor/magento-ecg/coding-standard/Ecg /app
```