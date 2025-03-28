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



// ===========================================================================
// Intégration du plugin, suivant si utilisateur authentifié (connecté) ou non
// ===========================================================================
function intergration_plugin_au_demarrage()
{
	if (is_user_logged_in() == true) {
		/* Aucun captcha à soumettre, pour un utilisateur authentifié (connecté) */
    } else {
        /* Intégration du captcha, pour les utilisateurs non authentifiés (non connectés, j'entends) */
		add_filter('comment_form_after_fields', 'ajout_champs_captcha');           // Ça c'est pour cibler les formulaires des personnes "non connectés"
		add_filter('comment_form_logged_in_after', 'ajout_champs_captcha');        // et ça, pour les personnes "connectées"
		
		add_filter('preprocess_comment', 'verifier_presence_captcha_et_si_valeur_correcte');
    }
}
add_action('init', 'intergration_plugin_au_demarrage');



// =======================================
// Ajout des champs nécessaires au captcha
// =======================================
function ajout_champs_captcha($champs) {
    $nombreX = random_int(NOMBRE_MINIMAL_POUR_SOUSTRACTION, NOMBRE_MAXIMAL_POUR_SOUSTRACTION);      // Bornes basse et haute incluses
    $nombreY = random_int(NOMBRE_MINIMAL_POUR_TOTAL, NOMBRE_MAXIMAL_POUR_TOTAL);                    // Bornes basse et haute incluses
    $nombreZ = $nombreY - $nombreX;

    echo '
        <p style="text-align: center; width: 100%;">
            
            <label for="nombreY">Résolvez cette soustraction :</label>
            <input type="hidden" id="nombreY" name="nombreY" value="'.$nombreY.'" readonly style="display: inline-block;">
 
            <label for="nombreX">&nbsp;'.$nombreY.'&nbsp;-&nbsp;</label>
            <input type="text" id="nombreX" name="nombreX" value="" size="3" required="required" style="width: auto;">
 
            <label for="nombreZ">&nbsp;=&nbsp;'.$nombreZ.'</label>
            <input type="hidden" id="nombreZ" name="nombreZ" value="'.$nombreZ.'" readonly>

        </p>';    // Nota : l'attribut 'required="required"' permet de faire une pré-validation AU MOMENT du submit, avant de passer à la page suivante

    return $champs;
}



// ===================================
// Vérifications captcha, APRÈS submit
// ===================================
function verifier_presence_captcha_et_si_valeur_correcte($commentdata) {

    // Tout d'abord, on vérifie la présence des 3 champs attendus
    if(!isset($_POST['nombreX']) || !isset($_POST['nombreY']) || !isset($_POST['nombreZ']))
        wp_die(__("[Plugin WP-Captcha]<br><br>Erreur : <strong>tous les champs attendus n'ont pas été envoyés...</strong>"));


    // Ensuite, on récupère ces champs
    $nombreX = sanitize_text_field($_POST['nombreX']);
    $nombreY = sanitize_text_field($_POST['nombreY']);
    $nombreZ = sanitize_text_field($_POST['nombreZ']);

    // Puis on vérifie si ce sont bien des nombres
    if(!is_numeric($nombreX) || !is_numeric($nombreY) || !is_numeric($nombreZ))
        wp_die(__("[Plugin WP-Captcha]<br><br>Erreur : <strong>le captcha attendu n'est pas au format numérique, alors qu'il le faut...</strong><br><br><u>Revenez à la page précédente, pour le modifier !</u>"));


    // Et enfin, on vérifie si l'opération est correcte
    $nombreX = intval($nombreX);
    $nombreY = intval($nombreY);
    $nombreZ = intval($nombreZ);

    if(($nombreY - $nombreX) != $nombreZ)
        wp_die(__("[Plugin WP-Captcha]<br><br>Erreur : <strong>le captcha n'est pas correct, désolé...</strong><br><br><u>Revenez à la page précédente, pour le modifier !</u>"));


    return $commentdata;
}

