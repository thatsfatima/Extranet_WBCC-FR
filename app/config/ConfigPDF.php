<?php

/**
 * Classe ConfigPDF - Configuration des paramètres PDF
 * Cette classe définit les paramètres par défaut pour la génération de documents PDF
 */
class ConfigPDF {
    // Paramètres PDF
    /**
     * Largeur de la page en mm
     */
    const LARGEUR_PAGE = 190;
    /**
     * Hauteur de la page en mm
     */
    const HAUTEUR_PAGE = 277;
    /**
     * Marge en mm
     */
    const MARGE = 10;
    
    // Couleurs
    /**
     * Couleur primaire sous forme de tableau RGB
     */
    const COULEUR_PRIMAIRE = [184, 0, 0];  // Rouge
    /**
     * Couleur du texte sous forme de tableau RGB
     */
    const COULEUR_TEXTE = [0, 0, 0];       // Noir
    /**
     * Couleur de l'entête sous forme de tableau RGB
     */
    const COULEUR_ENTETE = [255, 255, 255]; // Blanc
    /**
     * Couleur du tableau sous forme de tableau RGB
     */
    const COULEUR_TABLEAU = [240, 128, 128]; // Rouge clair
    
    // Chemins
    /**
     * Chemin vers les images
     */
    const CHEMIN_IMAGES = URLROOT . '/public/documents/opportunite/';
    /**
     * Chemin vers l'image d'entête
     */
    const IMAGE_ENTETE = URLROOT . '/public/img/entete.PNG';
    
    // Polices
    /**
     * Police normale
     */
    const POLICE_NORMALE = 'Arial';
    /**
     * Police gras
     */
    const POLICE_GRAS = 'Arial';
    /**
     * Taille du titre
     */
    const TAILLE_TITRE = 20;
    /**
     * Taille de la section
     */
    const TAILLE_SECTION = 18;
    /**
     * Taille de la sous-section 1
     */
    const TAILLE_SOUS_SECTION1 = 16;
    /**
     * Taille de la sous-section 2
     */
    const TAILLE_SOUS_SECTION2 = 15;
    /**
     * Taille de la sous-section 3
     */
    const TAILLE_SOUS_SECTION3 = 14;
    /**
     * Taille de la sous-section 4
     */
    const TAILLE_SOUS_SECTION4 = 13;
    /**
     * Taille du texte
     */
    const TAILLE_TEXTE = 12;
    
    // Textes constants
}
