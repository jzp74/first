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
$text_translations["ERROR_PERMISSION_LIST_EDIT"] = "Je hebt geen rechten om deze lijst te wijzigen";
$text_translations["ERROR_PERMISSION_LIST_ADMIN"] = "Je bent geen administrator van deze lijst";

/**
 * field check errors
 */
$text_translations["ERROR_DATE_WRONG_FORMAT"] = "Je hebt een onjuist datumformaat gegeven";
$text_translations["ERROR_NO_NUMBER_GIVEN"] = "jJe hebt geen nummer gegeven";
$text_translations["ERROR_NO_FIELD_VALUE_GIVEN"] = "Je hebt geen waarde gegeven";
$text_translations["ERROR_NO_FIELD_NAME_GIVEN"] = "Je hebt geen waarde gegeven";
$text_translations["ERROR_NO_FIELD_OPTIONS_GIVEN"] = "Je hebt geen opties gegeven";
$text_translations["ERROR_NO_USER_NAME_GIVEN"] = "Je hebt geen gebruikersnaam gegeven";
$text_translations["ERROR_NO_PASSWORD_GIVEN"] = "Je hebt geen wachtwoord gegeven";
$text_translations["ERROR_INCORRECT_NAME_PASSWORD"] = "De gebruikersnaam/wachtwoord combinatie is onjuist";
$text_translations["ERROR_NO_TITLE_GIVEN"] = "Je hebt deze lijst geen naam gegeven";
$text_translations["ERROR_NO_DESCRIPTION_GIVEN"] = "Je hebt deze lijst geen beschrijving gegeven";
$text_translations["ERROR_NOT_WELL_FORMED_STRING"] = "Dit veld mag alleen alfanumerieke tekens bevatten";
$text_translations["ERROR_NOT_WELL_FORMED_SELECTION_STRING"] = "Dit veld mag alleen alfanumerieke tekens en het '|' teken bevatten";

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
$text_translations["ERROR_CANNOT_DELETE_USER_ADMIN"] = "Je kunt de administrator niet verwijderen";
$text_translations["ERROR_CANNOT_DELETE_YOURSELF"] = "Je kunt jezelf niet verwijderen";

?>