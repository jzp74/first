<?php

/**
 * This file contains translations of error texts in Dutch
 *
 * @package Lang_FirstThingsFirst
 * @author Jasper de Jong
 * @copyright 2007-2009 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * permission errors
 */
$text_translations["ERROR_PERMISSION_CREATE_LIST"] = "Je hebt geen rechten om een nieuwe lijst te maken";
$text_translations["ERROR_PERMISSION_ADMIN"] = "Je hebt geen administrator rechten";
$text_translations["ERROR_PERMISSION_LIST_VIEW"] = "Je hebt geen rechten om deze lijst te bekijken";
$text_translations["ERROR_PERMISSION_LIST_EDIT"] = "Je hebt geen rechten om rijen van deze lijst te wijzigen";
$text_translations["ERROR_PERMISSION_LIST_CREATE"] = "Je hebt geen rechten om rijen aan deze lijst toe te voegen of te verwijderen";
$text_translations["ERROR_PERMISSION_LIST_ADMIN"] = "Je bent geen administrator van deze lijst";

/**
 * field check errors
 */
$text_translations["ERROR_DATE_WRONG_FORMAT_US"] = "Je hebt een datum in onjuist formaat gegeven. Een datum moet volgens een van de volgende formaten zijn:<br>MM/DD/JJJJ<br>MM/JJJJ<br>MM/DD<br>JJJJ";
$text_translations["ERROR_DATE_WRONG_FORMAT_EU"] = "Je hebt een datum in onjuist formaat gegeven. Een datum moet volgens een van de volgende formaten zijn:<br>DD-MM-JJJJ<br>MM-JJJJ<br>DD-MM<br>JJJJ";
$text_translations["ERROR_NO_NUMBER_GIVEN"] = "Je moet een nummer in dit veld invoeren maar je hebt geen nummer ingevoerd. Een nummer kan niet nul zijn";
$text_translations["ERROR_NO_FIELD_VALUE_GIVEN"] = "Je moet een waarde in dit veld invoeren maar je hebt geen waarde ingevoerd";
$text_translations["ERROR_NO_FIELD_NAME_GIVEN"] = "Je hebt geen kolomnaam ingevoerd";
$text_translations["ERROR_NO_FIELD_OPTIONS_GIVEN"] = "Je hebt geen opties ingevoerd";
$text_translations["ERROR_NO_USER_NAME_GIVEN"] = "Je hebt geen gebruikersnaam gegeven";
$text_translations["ERROR_NO_PASSWORD_GIVEN"] = "Je hebt geen wachtwoord gegeven";
$text_translations["ERROR_INCORRECT_NAME_PASSWORD"] = "De gebruikersnaam/wachtwoord combinatie is onjuist";
$text_translations["ERROR_NO_TITLE_GIVEN"] = "Je hebt deze lijst geen naam gegeven";
$text_translations["ERROR_NO_DESCRIPTION_GIVEN"] = "Je hebt deze lijst geen beschrijving gegeven";
$text_translations["ERROR_NOT_WELL_FORMED_STRING"] = "Dit veld mag alleen alfanumerieke tekens bevatten";
$text_translations["ERROR_NOT_WELL_FORMED_SELECTION_STRING"] = "Dit veld mag alleen alfanumerieke tekens en het | teken bevatten";

/**
 * database errors
 * definition of database connect error
 */
$text_translations["ERROR_DATABASE_CONNECT"] = "Kan geen verbinding met de database maken. Zorg dat de database werkt en kijk de database instellingen na";
$text_translations["ERROR_DATABASE_EXISTENCE"] = "Het lijkt erop dat een of meerdere tabellen niet in de database aanwezig zijn. Neem contact op met systeembeheer";
$text_translations["ERROR_DATABASE_PROBLEM"] = "Er is een onbekende fout opgetreden. Neem contact op met systeembeheer";

/**
 * creation errors
 */
$text_translations["ERROR_DUPLICATE_LIST_NAME"] = "Er bestaat al een lijst met de zelfde naam (door jou of een andere gebruiker gemaakt)";
$text_translations["ERROR_DUPLICATE_USER_NAME"] = "Er bestaat al een gebruikers met de zelfde naam";

/**
 *  user administration errors
 */
$text_translations["ERROR_CANNOT_UPDATE_NAME_USER_ADMIN"] = "Je kunt de naam van de administrator niet wijzigen";
$text_translations["ERROR_CANNOT_UPDATE_PERMISSIONS_USER_ADMIN"] = "Je kunt de rechten van de administrator niet wijzigen";
$text_translations["ERROR_CANNOT_DELETE_USER_ADMIN"] = "Je kunt de administrator niet verwijderen";
$text_translations["ERROR_CANNOT_DELETE_YOURSELF"] = "Je kunt jezelf niet verwijderen";

/**
 * import errors
 */
$text_translations["ERROR_IMPORT_FILE_SIZE_TOO_LARGE"] = "Je kunt geen bestanden groter dan 1Mb importeren";
$text_translations["ERROR_IMPORT_FILE_WRONG_EXTENSION"] = "Je kunt geen bestanden met andere extensies dan .csv en .txt importeren";
$text_translations["ERROR_IMPORT_FILE_NOT_MOVE"] = "Kan het tijdelijke upload bestand niet verplaatsen naar de upload map";
$text_translations["ERROR_IMPORT_FILE_NOT_FOUND"] = "Kan het tijdelijke upload bestand niet vinden";
$text_translations["ERROR_IMPORT_SELECT_FILE_UPLOAD"] = "Je hebt nog geen bestand geselecteerd om te uploaden. Selecteer eerst een bestand om te uploaden";
$text_translations["ERROR_IMPORT_COULD_NOT_OPEN"] = "Kan het tijdelijke upload bestand niet openen";
$text_translations["ERROR_IMPORT_WRONG_COLUMN_COUNT"] = "Het aantal kolommen komt niet overeen met het aantal kolommen van deze lijst";

/**
 * browser errors
 */
$text_translations["ERROR_BROWSER_UNSUPPORTED"] = "WAARSCHUWING: Het is mogelijk dat First Things First niet werkt zoals verwacht met je huidige browser omdat deze versie van First Things First niet getest is met jouw browser.<br>We raden je daarom aan om Firefox (versies 2, 3 of 3.5), Chrome (versie 4) of Internet Explorer (versies 7 of 8) te gebruiken.<br>Op dit moment gebruik je de volgende browser: ";

?>