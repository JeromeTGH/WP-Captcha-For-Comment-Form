<?php
/**
 * @package WP-Captcha-For-Comment-Form
 * @version 1.0.0
 */
/*
Plugin Name: WP-Captcha-For-Comment-Form
Plugin URI: https://github.com/JeromeTGH/WP-Captcha-For-Comment-Form
Description: Permet d'ajouter un captcha au formulaire de commentaire
Author: Jérôme TOMSKI
Version: 1.0.0
Author URI: https://github.com/JeromeTGH
*/

// ============================================
// Blocage des appels directs à cette extension
// ============================================
if (!function_exists('add_action')) {
	echo 'Ce plugin ne peut être appelé directement !';
	exit;
}


// ================
// Règle du captcha
// ================
// Exemple, dans formulaire :   par quel nombre X doit-on soustraire à Y, pour trouver Z (de telle sorte que Y - X = Z)
// Exemple numérique :          55 - X = 51  (X est le captcha à trouver, ici égal à 4)


// ====================
// Constantes du plugin
// ====================
define("NOMBRE_MINIMAL_POUR_SOUSTRACTION", 1);          // En clair : le captcha à trouver sera ce nombre, compris entre 1 et 9
define("NOMBRE_MAXIMAL_POUR_SOUSTRACTION", 9);          //            (il correspondra au nombre à soustraire à un autre, pour arriver au bon résultat) 

define("NOMBRE_MINIMAL_POUR_TOTAL", 31);                // Il s'agit d'un nombre compris entre 31 et 79, résultant de la soustraction
define("NOMBRE_MAXIMAL_POUR_TOTAL", 79);                // d'un nombre au captcha à trouver


// =======================================
// Ajout des champs nécessaires au captcha
// =======================================
add_filter('comment_form_after_fields', 'ajout_champs_captcha');           // Ça c'est pour cibler les formulaires des personnes "non connectés"
add_filter('comment_form_logged_in_after', 'ajout_champs_captcha');        // et ça, pour les personnées "connectées"

function ajout_champs_captcha($champs) {
    $nombreX = random_int(NOMBRE_MINIMAL_POUR_SOUSTRACTION, NOMBRE_MAXIMAL_POUR_SOUSTRACTION);      // Bornes basse et haute incluses
    $nombreY = random_int(NOMBRE_MINIMAL_POUR_TOTAL, NOMBRE_MAXIMAL_POUR_TOTAL);                    // Bornes basse et haute incluses
    $nombreZ = $nombreY - $nombreX

    $nouveauxChamps = '
        <div class="display: flex; flex-flow: row wrap;">
            <label for="nombreY">Résolvez cette soustraction : </label>
            <input type="text" id="nombreY" name="nombreY" value="'.$nombreY.'" readonly>

            <label for="nombreX"> - </label>
            <input type="number" id="nombreX" name="nombreX" value="" required="required">
            <span class="required">*</span>

            <label for="nombreZ"> = </label>
            <input type="text" id="nombreZ" name="nombreZ" value="'.$nombreZ.'" readonly>
        </div>';    // Nota : l'attribut 'required="required"' permet de faire une pré-validation AU MOMENT du submit, avant de passer à la page suivante

    echo $nouveauxChamps;
    return $champs;
}


// ===================================
// Vérifications captcha, APRÈS submit
// ===================================
add_filter('preprocess_comment', 'verifier_presence_captcha_et_si_valeur_correcte');

function verifier_presence_captcha_et_si_valeur_correcte($commentdata) {

    // Tout d'abord, on vérifie la présence des 3 champs attendus
    if(!isset($_POST['nombreX']) || !isset($_POST['nombreY']) || !isset($_POST['nombreTTTTTZ']))
        wp_die(__("[Plugin WP-Captcha-For-Comment-Form] Erreur : tous les champs attendus n'ont pas été envoyés..."));


    // Ensuite, on récupère ces champs


    // Et enfin, on vérifie si l'opération est correcte





    return $commentdata;
}

