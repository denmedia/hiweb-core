<?php
/**
 * Created by PhpStorm.
 * User: denmedia
 * Date: 08.08.2015
 * Time: 0:04
 */

hiweb()->file()->inc('scpt-helpers');
hiweb()->file()->inc('class-scpt-markup');
hiweb()->file()->inc('class-hiwebcptmeta');
hiweb()->file()->inc('class-hiwebcpttype');
hiweb()->file()->inc('class-hiwebcpttaxonomy');


//if ( is_admin() ) { hiweb()->file()->inc('class-hiwebcptadmin'); }

class hiweb_cpt {


    /**
     * Initialize a Custom Post Type
     *
     * @uses SCPT_Markup::labelify
     * @param string $type The Custom Post Type slug. Should be singular, all lowercase, letters and numbers only, dashes for spaces
     * @param string $singular Optional. The singular form of our CPT to be used in menus, etc. If absent, $type gets converted to words
     * @param string $plural Optional. The plural form of our CTP to be used in menus, etc. If absent, 's' is added to $singular
     * @param array|bool $register Optional. If false, the CPT won't be automatically registered. If an array, can override any of the CPT defaults. See {@link http://codex.wordpress.org/Function_Reference/register_post_type the WordPress Codex} for possible values.
     * @return \hiweb_cpt_type
     */
    public function type( $type, $singular = false, $plural = false, $register = array() ){
        hiweb()->file()->asset('font-awesome', null, 'font-awesome.min');
        hiweb()->file()->js('js/supercpt');
        return new hiweb_cpt_type( $type, $singular, $plural, $register );
    }


    /**
     * Construct a new Super_Custom_Post_Meta object for the given post type
     *
     * @param string $post_type The post type these boxes will apply to
     * @return \hiweb_cpt_meta
     */
    public function meta( $post_type ){
        return new hiweb_cpt_meta( $post_type );
    }


    /**
     * Initialize a Custom Post Type
     *
     * @uses SCPT_Markup::labelify
     * @param string $name The Custom Post Type slug. Should be singular, all lowercase, letters and numbers only, dashes for spaces
     * @param string $singular Optional. The singular form of our tax to be used in menus, etc. If absent, $name gets converted to words
     * @param string $plural Optional. The plural form of our tax to be used in menus, etc. If absent, 's' is added to $singular
     * @param string|bool $acts_like Optional. Define if this should act like categories or tags. If this starts with 'cat' the tax will act like a category; any other value and it will act like a tag
     * @param array|bool $register Optional. If false, the tax won't be automatically registered. If an array, can override any of the tax defaults. See {@link http://codex.wordpress.org/Function_Reference/register_taxonomy the WordPress Codex} for possible values.
     * @return \hiweb_cpt_taxonomy
     */
    public function taxonomy( $name, $singular = false, $plural = false, $acts_like = false, $register = array() ){
        return new hiweb_cpt_taxonomy( $name, $singular, $plural, $acts_like, $register );
    }



    /**
     * Initialize the plugin and call the appropriate hook method
     *
     * @uses admin_hooks
     * @author Matthew Boynes
     */
    function __construct() {
        if ( is_admin() ) add_action( 'init', array( $this, 'admin_hooks' ) );
    }

    /**
     * Setup appropriate hooks for wp-admin
     *
     * @uses SCPT_Admin
     * @return void
     * @author Matthew Boynes
     */
    function admin_hooks() {
        if ( apply_filters( 'scpt_show_admin_menu', true ) ) {}
            //$scpt_admin = new hiweb_cpt_admin;
        //add_action( 'admin_enqueue_scripts', array( $this, 'load_js_and_css' ) );
    }


    /**
     * Add supercpt.css to the doc head
     *
     * @return void
     * @author Matthew Boynes
     */
    function load_js_and_css() {
        //hiweb()->file()->css('css/supercpt');
        //hiweb()->file()->js('js/supercpt');
    }

}
//$scpt_plugin = hiweb()->cpt();