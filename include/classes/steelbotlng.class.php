<?php

class SteelBotLng {
    private $primary_lang,
            $current_lang,
            $languages = array();
    
    
    public function __construct($primary_lang, $current_lang = false) {
        $this->primary_lang = $primary_lang;
        $this->current_lang = $current_lang?$current_lang:$primary_lang;
        
    }
    
    public function AddDict($file) {
        $lang_name = str_replace( '.php', '', basename($file) );
        if ( is_readable($file) ) {
            include $file;    
        } else {
            throw new Exception('Error opening language file: '.$file,0);
        }
        
        if (!array_key_exists($lang_name, $this->languages)) {
            $this->languages[ $lang_name ] = array();
        }
        
        $this->languages[$lang_name] = $this->languages[$lang_name] + $lang;
        unset($lang);
        
    }
    
    public function GetTranslate($key, $lang =false ) {
        if (!$lang) {
            $lang = $this->current_lang;
        }
        
        if (array_key_exists($key, $this->languages[$lang])) {
            return $this->languages[$lang][$key];
        } else {
           if ( !array_key_exists($key, $this->languages[$this->primary_lang]) ) {
                return "#TRANSLATE ERROR:$key#";
            }
        }
    }
    
    
}
