<?php

/**
 * This file contains translation of error texts in Spanish
 * Non translated strings are marked with TO_BE_TRANSLATED
 *
 * @package Lang_FirstThingsFirst
 * @author Jasper de Jong
 * @translator Jose Miguel Rodriguez
 * @copyright 2007-2012 Jasper de Jong
 * @license http://www.opensource.org/licenses/gpl-license.php
 */


/**
 * permission errors
 */
$text_translations["ERROR_PERMISSION_CREATE_LIST"] = "No tienes permisos para crear listas";
$text_translations["ERROR_PERMISSION_CREATE_USER"] = "No tienes permisos para crear usuarios";
$text_translations["ERROR_PERMISSION_ADMIN"] = "No tienes permisos administartivos";
$text_translations["ERROR_PERMISSION_LIST_VIEW"] = "No tienes permisos para ver esta lista";
$text_translations["ERROR_PERMISSION_LIST_EDIT"] = "No tienes permisos para modificar las entradas de esta lista";
$text_translations["ERROR_PERMISSION_LIST_CREATE"] = "No tienes permisos para crear o borrar entradas en esta lista";
$text_translations["ERROR_PERMISSION_LIST_ADMIN"] = "No tienes permisos administartivos para esta lista";

/**
 * field check errors
 */
$text_translations["ERROR_DATE_WRONG_FORMAT_US"] = "Has introducido un formato erróneo de fecha. La fecha debe tener uno de los siguientes formatos:<br>MM/DD/YYYY<br>MM/YYYY<br>MM/DD<br>YYYY";
$text_translations["ERROR_DATE_WRONG_FORMAT_EU"] = "Has introducido un formato erróneo de fecha. La fecha debe tener uno de los siguientes formatos:<br>MM/DD/YYYY<br>MM/YYYY<br>MM/DD<br>YYYY";
$text_translations["ERROR_NO_NUMBER_GIVEN"] = "No has introducido un número en este campo";
$text_translations["ERROR_NO_FLOAT_GIVEN"] = "No has introducido un número de punto flotante en este campo";
$text_translations["ERROR_NO_FIELD_VALUE_GIVEN"] = "No has introducido un valor en este campo";
$text_translations["ERROR_NO_FIELD_NAME_GIVEN"] = "No has introducido un nombre para este campo";
$text_translations["ERROR_NO_FIELD_OPTIONS_GIVEN"] = "No has introducido ninguna opción";
$text_translations["ERROR_NO_USER_NAME_GIVEN"] = "No has introducido ningún nombre de usuario";
$text_translations["ERROR_NO_PASSWORD_GIVEN"] = "No has introducido ninguna contraseña";
$text_translations["ERROR_INCORRECT_NAME_PASSWORD"] = "Nombre de usuario o contraseña incorrecta";
$text_translations["ERROR_NOT_ENOUGH_FIELDS"] = "Tienes que crear por lo menos un campo más que el campo 'id'";
$text_translations["ERROR_NO_TITLE_GIVEN"] = "No has introducido un nombre para esta lista";
$text_translations["ERROR_NO_DESCRIPTION_GIVEN"] = "No has introducido una descripción para esta lista";
$text_translations["ERROR_NOT_WELL_FORMED_STRING"] = "Este campo solamente puede contener caracteres alfanuméricos";
$text_translations["ERROR_NOT_WELL_FORMED_SELECTION_STRING"] = "Este campo solamente puede contener caracteres alfanuméricos y el signo |";

/**
 * database errors
 * definition of database connect error
 */
$text_translations["ERROR_DATABASE_CONNECT"] = "No se ha podido conectar con la base de datos. Asegurate que funciona correctamente y revisa su configuración";
$text_translations["ERROR_DATABASE_EXISTENCE"] = "Parece que falta alguna tabla de la base de datos. Por favor, contacta con el administrador";
$text_translations["ERROR_DATABASE_PROBLEM"] = "Ha ocurrido un error desconocido. Por favor, contacta con el administrador";

/**
 * creation errors
 */
$text_translations["ERROR_DUPLICATE_LIST_NAME"] = "Ya existe una lista con el nombre introducido";
$text_translations["ERROR_DUPLICATE_USER_NAME"] = "Ya existe un usuario con el nombre introducido";

/**
 * user administration errors
 */
$text_translations["ERROR_CANNOT_UPDATE_NAME_USER_ADMIN"] = "No se puede cambiar el nombre del usuario administrador";
$text_translations["ERROR_CANNOT_UPDATE_PERMISSIONS_USER_ADMIN"] = "No se pueden cambiar los permisos del usuario administrador";
$text_translations["ERROR_CANNOT_DELETE_USER_ADMIN"] = "No se puede borrar el usuario administrador";
$text_translations["ERROR_CANNOT_DELETE_YOURSELF"] = "No puedes borrarte a ti mismo";

/**
 * import errors
 */
$text_translations["ERROR_IMPORT_FILE_SIZE_TOO_LARGE"] = "No se pueden importar archivos superiores a 1Mb";
$text_translations["ERROR_IMPORT_FILE_WRONG_EXTENSION"] = "No se pueden importar archivos con extensión diferente a .cvs y .txt";
$text_translations["ERROR_IMPORT_FILE_NOT_MOVE"] = "Ha sido imposible copiar el archivo subido al directorio de archivos subidos";
$text_translations["ERROR_IMPORT_FILE_NOT_FOUND"] = "Ha sido imposible encontrar el archivo subido";
$text_translations["ERROR_IMPORT_SELECT_FILE_UPLOAD"] = "No has seleccionado un archivo para subir. Por favor, selecciona un archivo antes de continuar";
$text_translations["ERROR_IMPORT_COULD_NOT_OPEN"] = "Ha sido imposible abrir el archivo subido";
$text_translations["ERROR_IMPORT_WRONG_COLUMN_COUNT"] = "El número de campos del archivo subido no coincide con el número de campos de esta lista";

/**
 * browser errors
 */
$text_translations["ERROR_BROWSER_UNSUPPORTED"] = "ATENCIÓN: Puede que experimentes un funcionamiento erróneo con tu navegador, ya que no hemos probado esta versión de First Things First en tu navegador actual.<br>Recomendamos utilizar Firefox (versión 3 o superior), Chrome (versión 4 o superior) o Internet Explorer (versión 7 o superior).<br>Actualmente estás utilizando el navegador: ";

?>
