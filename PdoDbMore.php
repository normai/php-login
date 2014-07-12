<?php

   // file : /trunk/php-login-xdb/PdoDbMore.php
   // id : # 20140702.0211
   // license : The MIT License (http://www.opensource.org/licenses/mit-license.php)
   // copyright : 2014 by Norbert C. Maier
   // authors :
   // purpose : Provide some helper functions for patching php-login into
   //    php-login-xdb to work with SQLite and other databases beside MySQL.
   // status : Proof-of-concept
   // note :


   /**
    * DbSwitch
    *
    * This static class serves as enum for the database switch.
    *
    * [id # 20140703.0321]
    */
   class PdoDbMore
   {
      const MySQL = "mysql";
      const SQLite = "sqlite";
      const PgSQL = "pgsql";
   }


  /**
    * DbSwitch
    *
    * This function is a replacemet for all occurrences of $query->execute().
    *
    * [id # 20140702.0221]
    */
   function qExecute($query, $params)
   {
      if ((DB_SWITCH == PdoDbMore::MySQL) or (DB_SWITCH == PdoDbMore::PgSQL))
      {
         // note : original line like
         //    $sth->execute(array(':user_name' => $_POST['user_name'], ':provider_type' => 'DEFAULT'));
         $query->execute($params);
      }
      else if (DB_SWITCH == PdoDbMore::SQLite)
      {
         // note : Full lines e.g. like
         //   $sth->bindValue(':user_name', $_POST['user_name']);
         //   $sth->bindValue(':provider_type', 'DEFAULT');
         //   $sth->execute();
         foreach( $params as $sKey => $sVal )
         {
            $query->bindValue($sKey, $sVal);
         }
         $query->execute();
      }
      else
      {
         die('Database switch indifferent.');
      }
   }


   /**
    * DbSwitch
    *
    * This function is a replacemet for all occurrences of $query->rowCount().
    *
    * ref : http://php.net/manual/en/pdostatement.rowcount.php (# 20140627.1921)
    * ref : http://stackoverflow.com/questions/6041886/count-number-of-rows-in-select-query-with-pdo (# 20140627.1922)
    *
    * id : # 20140702.0241
    */
   function rowCounti($query, $params)
   {
      $iCount = 0;

      if ((DB_SWITCH == PdoDbMore::MySQL) or (DB_SWITCH == PdoDbMore::PgSQL))
      {
         $iCount = $query->rowCount();
      }
      else if (DB_SWITCH == PdoDbMore::SQLite)
      {
         // Only for SELECT statements, we need the brute force style.
         // For INSERT/UPDATE/DELETE statements, rowCount() does work.
         $sSql = $query->queryString;
         $bSelect = false;
         $s = substr($sSql, 0, 6); // too primitive
         if ($s == 'SELECT')
         {
            $bSelect = true;
         }

         if ($bSelect)
         {
            // We need clone $query, otherwise this fetch() will distrube original fetch().
            $db = new Database();
            $query2 = $db->prepare($sSql);
            $query2->execute($params);

            // brute force
            $rows = $query2->fetchAll();

            $iCount = count($rows);
         }
         else
         {
            // For DELETE/INSERT/UPDATE, the usual function should suffice.
            $iCount = $query->rowCount();
         }
      }
      else
      {
         die('Database switch indifferent.');
      }

      return $iCount;
   }


   // id : # 20140702.0251
   // purpose : Replacement for all occurrences like $result->field_name.
   // check : What about field types other than string? How are those handled?
   // note : This method is superflous after using "$query->fetch(PDO::FETCH_OBJ);".
   /*
   function fieldVal_DISCARDED($record, $sFieldname)
   {
      $sVal = '';

      if (DB_USE_MYSQL)
      {
         $sVal = $record->$sFieldname;
      }
      else
      {
         $sVal = $record[$sFieldname];
      }

      return $sVal;
   }
   */

?>
