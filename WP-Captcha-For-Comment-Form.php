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
define("NOMBRE_MINIMAL_POUR_SOUSTRACTION", 3);          // En clair : le captcha à trouver sera ce nombre, compris entre 1 et 9
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
    $nombreZ = $nombreY - $nombreX;


    //<label for="nombreY">Résolvez cette soustraction : </label>
    // grid-column: 1;
    //<p style="display: flex; justify-content: flex-start; align-items: center; white-space: nowrap; grid-column: unset;">

    // style="width: auto;"

    $nouveauxChamps = '
        <p>
            
            <label for="nombreY">Résolvez cette soustraction :</label>
            <input type="hidden" id="nombreY" name="nombreY" value="'.$nombreY.'" readonly style="display: inline-block;">

            <label for="nombreX">&nbsp;'.$nombreY.'&nbsp;-&nbsp;</label>
            <input type="text" id="nombreX" name="nombreX" value="" size="3" required="required" style="width: auto;">

            <label for="nombreZ">&nbsp;=&nbsp;'.$nombreZ.'</label>
            <input type="hidden" id="nombreZ" name="nombreZ" value="'.$nombreZ.'" readonly>
        </p>';    // Nota : l'attribut 'required="required"' permet de faire une pré-validation AU MOMENT du submit, avant de passer à la page suivante

    echo $nouveauxChamps;
    return $champs;
}


// ===================================
// Vérifications captcha, APRÈS submit
// ===================================
add_filter('preprocess_comment', 'verifier_presence_captcha_et_si_valeur_correcte');

function verifier_presence_captcha_et_si_valeur_correcte($commentdata) {

    // Tout d'abord, on vérifie la présence des 3 champs attendus
    if(!isset($_POST['nombreX']) || !isset($_POST['nombreY']) || !isset($_POST['nombreZ']))
        wp_die(__("[Plugin WP-Captcha-For-Comment-Form] Erreur : tous les champs attendus n'ont pas été envoyés..."));


    // Ensuite, on récupère ces champs
    $nombreX = sanitize_text_field($_POST['nombreX']);
    $nombreY = sanitize_text_field($_POST['nombreY']);
    $nombreZ = sanitize_text_field($_POST['nombreZ']);

    // Puis on vérifie si ce sont bien des nombres
    if(!is_numeric($nombreX) || !is_numeric($nombreY) || !is_numeric($nombreZ))
        wp_die(__("[Plugin WP-Captcha-For-Comment-Form] Erreur : tous les champs attendus ne sont pas au format numérique..."));


    // Et enfin, on vérifie si l'opération est correcte
    $nombreX = intval($nombreX);
    $nombreY = intval($nombreY);
    $nombreZ = intval($nombreZ);

    if(($nombreY - $nombreX) != $nombreZ)
        wp_die(__("Erreur : le captcha n'est pas correct. Revenez à la page précédente, s'il vous plait !"));


    return $commentdata;
}

