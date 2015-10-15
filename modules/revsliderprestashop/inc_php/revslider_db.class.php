<?php

class rev_db_class{

    public static $wpdb;

    public $mysqli;
            //$dbh;

    public $prefix;

    public function __construct() {

        $this->prefix = _DB_PREFIX_;

    }

    public function _real_escape( $string ) {

            return Db::getInstance()->escape( $string );

    }

    public function _escape( $data ) {

        if ( is_array( $data ) ) {

                foreach ( $data as $k => $v ) {

                        if ( is_array($v) )

                                $data[$k] = $this->_escape( $v );

                        else

                                $data[$k] = $this->_real_escape( $v );

                }

        } else {

                $data = $this->_real_escape( $data );

        }



        return $data;

    }

    

    public function query($sql){

        //if($query = $this->mysqli->query($sql))
        $query = Db::getInstance()->execute($sql);
        if($query)
           return true;

        return FALSE;

    }

    public function update($table, $data, $where = '', $limit = 0, $null_values = false, $use_cache = true, $add_prefix = false){

        $wherestr = '';
        $c = 0;      

        $sql = "UPDATE {$table} SET ";
        
        if(!empty($data))
            foreach ($data as $k=>$d){
                if($c > 0)
                $sql .= ', ';
                
                if(is_string($d))
                    $sql .= "$k=\"".addslashes($d)."\"";
                else {
                    $sql .= "$k=$d";
                }
                
                $c++;
            }
        
        $sql .= " ";
            
        $c = 0;    
            
        if(!empty($where) && is_array($where)){
            $sql .= "WHERE ";
            
            foreach($where as $k => $val){
                if($c > 0)
                    $wherestr .= " AND ";
                
                $wherestr .= "{$k}=";                
                if(is_string($val))
                    $wherestr .= '"'.$this->_escape($val).'"';                    
                else
                    $wherestr .= $val;
                
                $c++;
            }
            $sql .= $wherestr;
            
        }
        
//        if(Db::getInstance()->update($table, $this->_escape($data), $wherestr , $limit, $null_values, $use_cache, $add_prefix))
//                return true;
        if(Db::getInstance()->execute($sql))
            return true;
        
        return false;

    }
    

    public function insert($table, $data, $null_values = false, $use_cache = true, $type = 1, $add_prefix = false){

        $c = 0;      

        $cols = '';
        $vals = '';
        
        $sql = "INSERT INTO {$table}";
        
        if(!empty($data)){            
            $cols .= '(';
            $vals .= ' VALUES(';
            foreach ($data as $k=>$d){
                if($c > 0){
                    $cols .= ', ';
                    $vals .= ', ';
                }
                $cols .= $k;
                
                if(is_string($d))
                    //$vals .= "\"".addslashes($d)."\"";
                    $vals .= "'".addslashes($d)."'";
                else {
                    $vals .= $d;
                }
                
                $c++;
            }
            $cols .= ')';
            $vals .= ')';
        }
        
        $sql .= "{$cols} {$vals}";
        
        if(Db::getInstance()->execute($sql))
            return $this->Insert_ID();
        
        return false;
        
//        if(Db::getInstance()->insert($table, $this->_escape($data), $null_values, $use_cache, $type, $add_prefix)){            
//            return Db::getInstance()->Insert_ID();        
//        }

    }
    
    public function Insert_ID(){
        return Db::getInstance()->Insert_ID();
    }
    

    public function get_var($sql, $assoc = false){

        $query = Db::getInstance()->getValue($sql);

        if(!empty($query)) return $query;

        return false;

    }

    public function get_row($sql, $assoc = false){

        $query = Db::getInstance()->getRow($sql);

        if($query)
            return $query;
        
        return false;

        

    }    

    public function get_results($sql, $assoc = false){        

        $query = Db::getInstance()->ExecuteS($sql,true,false);        

        if(!empty($query)) return $query;

        return false;

    }

        

    public static function rev_db_instance(){

        if(self::$wpdb === null)

            return new rev_db_class();

        return self::$wpdb;

    }

    

}



//$wpdb = new rev_db_class();



