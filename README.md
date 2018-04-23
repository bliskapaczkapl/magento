[![Build Status](https://travis-ci.org/bliskapaczkapl/magento.svg?branch=master)](https://travis-ci.org/bliskapaczkapl/magento)

# Moduł Bliskapaczka dla Magento 1.9 

## Instalacja modułu

### Wymagania
W celu poprawnej instalacji modułu wymagane są:
- php >= 5.6
- composer

### Instalacja modułu
1. Pobierz repozytorium i skopiuj jego zawartość do katalogu domowego swojego Magento
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

## Możliwości modułu
- przesyłki do punktów - moduł daje możliwośc użycia jednej z metod dostawy jaką jest możliwość wybrania puktu doręczenia zamówienia (np. InPost, Paczka w Ruch, Poczta Polska,...)
- przesylki kurierskie - moduł daje możliwośc użycia jednej z metod dostawy jaką jest przesyłka kurierska przez wybrenego przewoźnika
- darmowa dostawa - wsparcie dla regół koszykowych definiujących darmową dostawę. Więcej w dokumentacji [Magento](http://docs.magento.com/m1/ce/user_guide/marketing/price-rule-shopping-cart-free-shipping.html)
- zarządzanie przesyłkami - z poziomu modułu istnieje możliwość zarządzania przesyłkami po stronie bliskapaczka.pl
  - pobranie listu przewozowego
  - aktualizacja statusu przesyłki
  - anulowanie zlecenia

## Zarządzanie przesyłkami
Zarządanie przesyłkami odbywa się przez menu Sprzedaż -> Bliskapaczka. Tam dostępna jest lista wszystkich przesyłek.

## Dodatkowe możliwości
### Punkty z płatnością przy dobiorze

Widget bliskapaczka.pl przewiduje możliwość wyświetlenia tylko punktów z obsługą płatności przy pobraniu (więcej informacji w [dokumentacji](https://widget.bliskapaczka.pl)). W magento można wyświetlić widget tylk oz punktami obsługującymi płatność przy odbiorze przez wywołanie metody `Bliskapaczka.showMap` z ustawionym parametrem `codOnly` na `true`. Przykład wywołania:

```
Bliskapaczka.showMap(
    [{"operator":"POCZTA","price":9.69},{"operator":"INPOST","price":9.25},{"operator":"RUCH","price":8},{"operator":"DPD","price":9.99}],
    "AIzaSyCUyydNCGhxGi5GIt5z5I-X6hofzptsRjE",
    true,
    true
)
```

### Zmian przewoźnika przy metodzie dostawy Bliska Paczka Kurier
Na razie zmana przewoźnika jest możliwa tylko przez edycję kodu wtyczki. Aby przewoźnik został zmieniony trzeba wyedytować klase Sendit_Bliskapaczka_Model_Mapper_Todoor zmieniając w lini 35 parametr operatorName, jak w przykładzie 

```
<?php

class Sendit_Bliskapaczka_Model_Mapper_Todoor extends Sendit_Bliskapaczka_Model_Mapper_Abstract
{
    ...
    public function getData(Mage_Sales_Model_Order $order, Sendit_Bliskapaczka_Helper_Data $helper)
    {
        ...
        $data['operatorName'] = "FEDEX";
        ...
    }
    ...
}
```

## Docker demo

`docker pull bliskapaczkapl/magento && docker run -d -p 8080:80 bliskapaczkapl/magento`

Front Magento jest dostępny po wpisaniu w przeglądarcę adresu `http://127.0.0.1:8080`.

Panel admina jest dostępny pod adresem  `http://127.0.0.1:8080/admin`, dane dostępowe to `admin/password123`. Moduł należy skonfigurować według instrukcji powyżej.

## Rozwój modułu

### Docker

W celu developmentu można uruchomić docker-compose prze komendę:

```
docker-compose -f docker-compose.yml -f dev/docker/docker-compose.dev.yml up
```

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
php vendor/bin/phpcs -s --standard=./vendor/magento-ecg/coding-standard/Ecg app
```
