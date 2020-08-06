<?php


 interface SessionInterface
 {
     /**
      * Assigns a session value to storage.  Session key is used to identify the value
      *
      * @param $session_key
      * @param $session_value_to_set
      * @return mixed
      */
     public function setStringVar($session_key, $session_value_to_set);

     /**
      * Returns the value frm storage associated with the given key
      *
      * @param $session_key
      * @return mixed
      */
     public function getStringVar($session_key);

     /**
      * Remove the stored value from storage
      *
      * @param $session_key
      * @return mixed
      */
     public function unsetStringVar($session_key);

     /**
      * Log all relevant activity
      *
      * @return mixed
      */
     public function setLogger();

 }