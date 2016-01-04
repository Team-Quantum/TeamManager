<?php
/**
 * Created by PhpStorm.
 * User: .PolluX
 * Date: 1/4/2016
 * Time: 12:42 AM
 */

namespace TeamManager;

use TeamManager\DBO\Translation;

class Translator {

    /**
     * @var DatabaseManager
     */
    private $database;

    /**
     * @var string
     */
    private $lang;

    /**
     * Translator constructor.
     * @param $lang string The language id (e.g DE, EN)
     * @param $db DatabaseManager
     */
    public function __construct($lang, $db) {
        $this->database = $db;
        $this->lang = $lang;
    }

    /**
     * Translate the given key
     * @param $key
     * @return string
     */
    public function translate($key) {
        /** @var $translation Translation */
        $translation = $this->database->getEntityManager()->getRepository('Quantum\\DBO\\Translation')->findOneBy(
            array("trans" => $key, "lang" => $this->lang)
        );
        if($translation == null) {
            $translation = new Translation($key, $this->lang, $key);
            $this->database->getEntityManager()->persist($translation);
            $this->database->getEntityManager()->flush();
        }

        return utf8_encode($translation->getTranslated());
    }

}