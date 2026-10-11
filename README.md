# Názov projektu

Matúš Vojvodík

5ZYI34

## Stručný opis projektu
Bude sa jednať o webowú aplikáciu, ktorá umožní pacientom objednať sa online k lekárovi, prehliadať jeho voľné termíny a v prípade potreby termín zrušiť. 
Rieši bežný problém objednávania telefonicky alebo osobne, ktorý je otravný, pretože pacient musí volať v konkrétnych hodinách, lekár nemá prehľad o obsadenosti termínov, nikto to nezdvihne a zrušené termíny ostávajú nevyužité.

Aplikácia je určená pacientom, lekárom a administrátorom systému. Pacient si vyhľadá lekára podľa špecializácie alebo mesta, zobrazí jeho voľné termíny, vyberie si nejaký a rezerváciu potvrdí.
Lekár si môže zobraziť svoj kalendár, kde uvidí všetky svoje rezervácie a môže ich spravovať. Taktiež si nastavuje pracovný čas. Administrátor systému má prehľad o všetkých lekároch a pacientoch, môže pridávať nových lekárov do systému a spravovať existujúcich.

## Role v projekte
- Návštevník - Môže si vyhľadať lekára podľa špecializácie alebo mesta. Môže si prezerať voľné termíny lekára, ale nemôže si ich rezervovať. Môže sa zaregistrovať a prihlásiť do systému.
- Pacient - Má všetky práva návšetvníka vybrať si nejaký a rezerváciu potvrdiť. Môže si navyše rezervovať termín, zrušiť termín, zobraziť svoje termíny a spravovať profil.
- Lekár - Spravuje svoj profil, nastavuje pracovný čas, vidí rozpis svojich termínov, môže termíny rušiť a označiť ich ako vybavené alebo neuskutočnené.
- Administrátor - Má prehľad o všetkých lekároch a pacientoch, môže pridávať nových lekárov do systému a spravovať existujúcich.

## Prípady použitia podľa rolí
- Návštevník - Vyhľadá lekára podľa špecializácie alebo mesta, prezrie si voľné termíny lekára, zaregistruje sa a prihlási sa do systému.
- Pacient - Rezervuje termín, zruší termín, zobrazuje svoje termíny, spravuje profil.
- Lekár - Spravuje svoj profil, nastavuje pracovný čas, vidí rozpis svojich termínov, ruší termíny, označuje ich ako vybavené alebo neuskutočnené.
- Administrátor - Má prehľad o všetkých lekároch a pacientoch, pridáva nových lekárov do systému, spravuje existujúcich lekárov a pacientov.

## Plánované entity
- Používateľ - obsahuje informácie o používateľovi, atribúty: id, meno, priezvisko, email, tel. číslo, heslo, rola.
- Lekár - obsahuje informácie o lekárovi, atribúty: id, používateľ_id, klinika, mesto.
- Špecializácia - obsahuje informácie o špecializácii lekára, atribúty: id, názov špecializácie, popis.
- LekárŠpecializácia - obsahuje informácie o vzťahu medzi lekárom a jeho špecializáciou, atribúty: id, lekár_id, špecializácia_id.
- Pracovný čas - obsahuje informácie o pracovnom čase lekára, atribúty: id, lekár_id, deň v týždni, od kedy, do kedy.
- Termín - obsahuje informácie o termíne, atribúty: id, dátum, čas, stav, lekár_id, pacient_id, dôvod.


## Vzťahy medzi entitami
- Používateľ - Lekár: 1:1 každý lekár má práve jeden používateľský účet
- Lekár - Špecializácia: M:N jeden lekár môže mať viacero špecializácií a jedna špecializácia môže patriť viacerým lekárom
- Lekár - Pracovný čas: 1:N jeden lekár má pracovný čas pre rôzne dni v týždni
- Lekár - Termín: 1:N jeden lekár môže mať viacero termínov
- Používateľ(Pacient) - Termín: 1:N jeden pacient môže mať viacero termínov


## Hlavné stránky aplikácie
- Zoznam lekárov – hlavná stránka s vyhľadávaním, filtrami podľa špecializácie a mesta.
- Profil lekára a rezervácia – informácie o lekárovi, výber dňa a zobrazenie voľných termínov, potvrdenie rezervácie.
- Registrácia a prihlásenie – formulár pre registráciu a prihlásenie.
- Termíny pacienta – prehľad nadchádzajúcich a minulých termínov a možnosť zrušiť termín.
- Profil pacienta – úprava osobných údajov a zmena hesla.
- Nastavenie pracovného času (lekár) – úprava pracovných hodín, dĺžky termínu a profilu lekára.
- Správa lekárov a špecializácií (admin) – tabuľky s vytváraním, úpravou a mazaním lekárov a špecializácií.
- Správa používateľov a termínov (admin) – zoznam používateľov so zmenou role a zoznam všetkých termínov s filtrovaním.

## Rozdelenie funkcionality
Základné funkcie

- registrácia, prihlásenie a odhlásenie, hashovanie hesiel, role a autorizácia (návštevník, pacient, lekár, správca)
- zoznam lekárov s filtrovaním podľa špecializácie a mesta, profil lekára
- generovanie voľných termínov z pracovného času lekára (pevná dĺžka termínu) a zabránenie dvojitej rezervácii
- vytvorenie a zrušenie rezervácie pacientom, zrušenie termínu lekárom, zmena stavu termínu
- CRUD nad lekármi a špecializáciami (vzťah M:N) a nad pracovným časom
- AJAX: filtrovanie a stránkovanie zoznamu lekárov, načítanie voľných termínov po výbere dňa, rušenie termínu bez obnovenia stránky
- responzívny dizajn

Rozširujúce funkcie

- hodnotenie lekárov pacientmi po vybavenom termíne a priemerné hodnotenie v zozname,
- kalendárový pohľad pre lekára a pacienta,
- pripomienky blížiaceho sa termínu (e-mailom alebo v aplikácii) a čakacia listina na zrušené termíny,
- dovolenky a výnimky z pracovného času lekára, premenlivá dĺžka termínu podľa typu vyšetrenia,
- štatistiky pre správcu a lekára (vyťaženosť, počet zrušených termínov) s grafmi,
- nahratie prílohy k termínu (napr. odporúčanie od iného lekára),
- tmavý režim a vlastný CSS bez frameworku.