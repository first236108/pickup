<?php
class DB{
    public    		$pdo;
    protected 		$res;
    protected 		$config;
    function __construct($config){
        $this->Config = $config;
        $this->connect();
    }         
    public function connect(){
        $this->pdo = new PDO($this->Config['dsn'], $this->Config['name'], $this->Config['password']);
        $this->pdo->query('set names utf8;');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    function __destruct(){
        $this->pdo = null;
    }
    public function close(){
        $this->pdo = null;
    }
     
    public function query($sql){
        $res = $this->pdo->query($sql);
        if($res){
            $this->res = $res;
        }
    }
    public function exec($sql){
        $res = $this->pdo->exec($sql);
        if($res){
            $this->res = $res;
        }
    }
    public function getAll($sql){
		$this->query($sql);
        return $this->res->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getRow($sql){
		$this->query($sql);
        return $this->res->fetch();
    }
    public function getValue($sql){
		$this->query($sql);
        return $this->res->fetchColumn();
    }
    public function getOne($sql){
		$this->query($sql);
        return $this->res->fetchColumn();
    }
    public function insertId(){
        return $this->pdo->lastInsertId();
    }
	public function insert($table, $dataset, $debug=0){
        return $this->add($table, $dataset, $debug);
    }
    public function add($table, $dataset, $debug=0){
        if( empty($table) ){
            throw new Exception("��������Ϊ��.");
        }
        if( !is_array($dataset) || count($dataset)<=0) {
            throw new Exception('û��Ҫ���������');
        }
        $value = '';
        while(list($key,$val)=each($dataset))
            $value .= "`{$key}`='" . addslashes($val) . "',";
        $value = substr( $value,0,-1);
        if($debug === 0){
            $this->query("insert into `{$table}` set {$value}");
            if(!$this->res){
                return FALSE;
            }else{
                return $this->insertId();
            }
        }else{
            echo "insert into `{$table}` set {$value}";
            if($debug === 2){ exit; }
        }
    }
     
    public function update($table, $dataset, $conditions="", $debug=0){
        if( empty($table) ){
            throw new Exception("��������Ϊ��.");
        }
        if( !is_array($dataset) || count($dataset)<=0) {
            throw new Exception('û��Ҫ���µ�����');
            return false;
        }
        if( empty($conditions) ){
            throw new Exception("ɾ������Ϊ��Ŷ.");
        }
        $conditions = " where " . $conditions;
        $value    = '';
        while( list($key,$val) = each($dataset))
		$value .= "`{$key}`='" . addslashes($val) . "',";
        $value  = substr( $value,0,-1);
        
        //���ݿ����
        if($debug === 0){
        $this->exec("update `{$table}` set {$value} {$conditions}");
            return $this->res;
        }else{
            echo "update `{$table}` set {$value} {$conditions}";
            if($debug === 2){ exit; }
        }
    }
     
    public function delete($table, $conditions="", $debug=0){
        if( empty($table) ){
            throw new Exception("��������Ϊ��.");
        }
        if( empty($conditions) ){
            throw new Exception("ɾ������Ϊ��Ŷ.");
        }
        $conditions = " where " . $conditions;        
        //���ݿ����
        if($debug === 0){
            $this->exec("delete from {$table} {$conditions}");
            return $this->res;
        }else{
            echo "delete from {$table} {$conditions}";
            if($debug === 2){ exit; }
        }
    }
}
