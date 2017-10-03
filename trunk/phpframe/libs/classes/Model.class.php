<?php
/**
 *  model.class.php 数据模型基类
 *
 * @copyright			(C) 2005-2010 PHPCMS
 * @license				http://www.phpcms.cn/license/
 * @lastmodify			2010-6-7
 */
defined('IN_PHPFRAME') or exit('Access Denied');
Base::loadSysClass('DbFactory', '', 0);
Base::loadSysClass('Page', '', 0);

class Model
{
    // 数据库配置
    protected $dbConfig = '';
    // 数据库连接
    protected $db = '';
    // 调用数据库的配置项
    protected $dbSetting = 'default';
    // 数据表名
    protected $tableName = '';
    // 表前缀
    public $dbTablePre = '';

    public function __construct($table_name='',$table_pre='')
    {
		$this->dbConfig = Base::loadConfig('database');
		if($table_name=='')
			$this->tableName=$this->_getTableName();
		else
			$this->tableName=$table_name;
		
		if($table_pre!='')
		{
			$this->dbTablePre=$table_pre;
			$this->tableName=$table_pre.$table_name;
		}
		
        if (! isset($this->dbConfig[$this->dbSetting])) {
            $this->dbSetting = 'default';
        }
        $this->tableName = $this->dbConfig[$this->dbSetting]['tablepre'] . $this->tableName;
        $this->dbTablePre = $this->dbConfig[$this->dbSetting]['tablepre'];
        $this->db = DbFactory::get_instance($this->dbConfig)->get_database($this->dbSetting);
    }
	
	protected function _getTableName()
	{
		$name=substr(get_class($this),0,-5);
		$name=java2c($name);
		return $name;
	}

	/*
	$field['user_name']=array(
		'required'=>true,
		'email'=>true,
		'phone'=>true,
		'number'=>true,
		'array'=>array(1,2,3),
		'preg'=>//;
	)
	 */
	protected function setFields($fields)
	{
		$this->fields=$fields;
	}

	protected function checkPost($data,$method='add')
	{
		$result=array(
			'flag'=>false,
			'message'=>'',
			'data'=>array(),
		);
		$data1=array();
		
		//添加记录
		$fields=$this->fields;
		foreach($fields as $key=>$row)
		{
			$name=$row['name']?$row['name']:$key;
			
			foreach($row as $type=>$value)
			{
				switch($type)
				{
					case "required":
						if('add'==$method && ( !isset($data[$key]) || strlen($data[$key])==0))
						{
							$result['message']=$name.'为必填';
							return $result;
						}
						break;
					case "email":
						//$preg="/^\w*?@\w*?\.[a-zA-Z]{2,3}$/";
						$preg = "/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/";
						if(isset($data[$key]) && !preg_match($preg,$data[$key]))
						{
							$result['message']=$name.'为邮件格式';
							return $result;
						}
						break;
					case "phone":
						$preg="/^1\d\d\d{8}$/";
						if(isset($data[$key]) && !preg_match($preg,$data[$key]))
						{
							$result['message']=$name.'为手机格式';
							return $result;
						}
						break;
					case "number":
						$preg="/^([1-9]\d*\.?\d*)|(0\.\d*[1-9])|0$/";
						if(isset($data[$key]) && !preg_match($preg,$data[$key]))
						{
							$result['message']=$name.'为数字格式';
							return $result;
						}
						break;
					case "array":
						if(isset($data[$key]) && !in_array($data[$key],$value))
						{
							$result['message']=$name.'数值错误';
							return $result;
						}
						break;
					case "preg":
						$preg=$value;
						if(isset($data[$key]) && !preg_match($preg,$data[$key]))
						{
							$result['message']=$name.'格式错误';
							return $result;
						}
						break;
					case "strlen":
						$min_length=$value[0];
						$max_length=$value[1];
						if(isset($data[$key]) && (strlen($data[$key])<$min_length || strlen($data[$key])>$max_length ))
						{
							$result['message']=$name.'超出了允许范围';
							return $result;
						}
						break;
					case "range":
						$min_value=$value[0];
						$max_value=$value[1];
						if(isset($data[$key]) && ($data[$key]<$min_value || $data[$key]>$max_value ))
						{
							$result['message']=$name.'超出了允许范围';
							return $result;
						}
						break;
					case "equal":
						$key1=$value;
						$name1=$fileds[$key1]['name'];
						if(!$name1)
							$name1=$key1;
						
						if($data[$key1] != $data[$key])
						{
							$result['message']=$name.'不等于'.$name1;
							return $result;
						}
						break;
					case "date":
						$preg="/^\d{4}\-\d{2}\-\d{2}/";
						if(isset($data[$key]) && !preg_match($preg,$data[$key]))
						{
							$result['message']=$name.'为日期格式';
							return $result;
						}
						break;
					case "datetime":
						$preg="/^\d{4}\-\d{2}\-\d{2} \d{1,2}\-\d{1,2}(\-\d{1,2}){0,1}$/";
						if(isset($data[$key]) && !preg_match($preg,$data[$key]))
						{
							$result['message']=$name.'为日期时间格式';
							return $result;
						}
						break;
				}
			}
			if(($row['add'] && $method!='add') || !isset($data[$key]))
				continue;
			
			$data1[$key]=$data[$key];
		}

		$result['flag']=true;
		$result['data']=$data1;
		return $result;
	}

	//获取js验证字符串
	public function checkjs()
	{
		$str="var flag=true;\nvar errorArray=[];\nvar errorMessage=[];\n";
		foreach($this->fields as $name=>$row)
		{
			if(!$row['checkjs'])
				continue;
			
			$str.="var name='$name';\n";
			$str.="var value=$('[name=\"$name\"]').val();\n";
			$str.="var desc='".$row['name']."';\n";
			
			if(isset($row['required']))
			{
				$str.="if(!value.length){flag=false;errorArray.push(name);errorMessage.push(desc+'为必填');}\n";
			}
			elseif(isset($row['email']))
			{
				$str.="var preg=/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;\n";
				$str.="if(!value.match(preg)){flag=false;errorArray.push(name);errorMessage.push(desc+'为邮件格式');}\n";
			}
			elseif(isset($row['phone']))
			{
				$str.="var preg=/^1\d\d\d{8}$/;\n";
				$str.="if(!value.match(preg)){flag=false;errorArray.push(name);errorMessage.push(desc+'为手机格式');}\n";
				
			}
			elseif(isset($row['number']))
			{
				$str.="var preg=/^([1-9]\d*\.?\d*)|(0\.\d*[1-9])|0$/;\n";
				$str.="if(!value.match(preg)){flag=false;errorArray.push(name);errorMessage.push(desc+'为数字格式');}\n";
			}
			elseif(isset($row['array']))
			{
				$str.="var arr=['".implode("'",$row['array'])."'];\n";
				$str.="var f=false;\n";
				$str.="for(var i=0;i<arr.length;i++){if(arr[i]==value)f=true;}\n";
				$str.="if(!f){flag=false;errorArray.push(name);errorMessage.push(desc+'数值错误');}\n";
			}
			elseif(isset($row['preg']))
			{
				$str.="var preg=".$row['preg'].";\n";
				$str.="if(!value.match(preg)){flag=false;errorArray.push(name);errorMessage.push(desc+'格式错误');}\n";
			}
			elseif(isset($row['strlen']))
			{
				$str.="var minLength=".$row['strlen'][0].";var maxLength=".$row['strlen'][1].";\n";
				$str.="if(value.length>maxLength || value.length<minLength){flag=false;errorArray.push(name);errorMessage.push(desc+'超出了允许范围');}\n";
			}
			elseif(isset($row['range']))
			{
				$str.="var minValue=".$row['strlen'][0].";var maxValue=".$row['strlen'][1].";\n";
				$str.="if(value>maxValue || value<minValue){flag=false;errorArray.push(name);errorMessage.push(desc+'超出了允许范围');}\n";
			}
			elseif(isset($row['equal']))
			{
				$str.="var key1='"+$row['equal']+"';var name1='"+$fileds[$row['equal']]['name']?$fileds[$row['equal']]['name']:$row['equal']+"';var value1=$('[name=\""+$row['equal']+"\"]').val();\n";
				$str.="if(value!=value1){flag=false;errorArray.push(name);errorMessage.push(desc+'不等于'+name1);}\n";
				
			}
			elseif(isset($row['date']))
			{
				$str.="var preg=/^\d{4}\-\d{2}\-\d{2}/;\n";
				$str.="if(!value.match(preg)){flag=false;errorArray.push(name);errorMessage.push(desc+'为日期格式');}\n";
			}
			elseif(isset($row['datetime']))
			{
				$str.="var preg=/^\d{4}\-\d{2}\-\d{2} \d{1,2}\-\d{1,2}(\-\d{1,2}){0,1}$/;\n";
				$str.="if(!value.match(preg)){flag=false;errorArray.push(name);errorMessage.push(desc+'为日期时间格式');}\n";
				
			}
		}

		return $str;
	}
	
	public function table($table_name, $db_setting='')
	{
		if (empty($db_setting)) {
			$table_pre = $this->db_tablepre;
		} else {
			$table_pre = $this->db_config[$db_setting]['tablepre'];
		}
		$this->tableName=$table_pre.$table_name;
		return $this;
	}
	
	public function add($data=null,$return_insert_id = false, $replace = false)
	{
		if($data!=null)
			$this->options['data']=$data;
		
		$flag=$this->insert($this->options['data'],$return_insert_id, $replace);
		$this->options=array();
		$this->sql='';
		
		return $flag;
	}

	public function insert($data,$return_insert_id = false, $replace = false)
	{
		return $this->db->insert($data, $this->tableName, $return_insert_id, $replace);
	}
	
    final public function getInsertId()
    {
        return $this->db->insert_id();
    }
	
	final public function select()
	{
		$this->_parseSql();
		
		$this->sql="select {$this->options['select']} from {$this->tableName} {$this->options['where']} {$this->options['order']} {$this->options['limit']}";
		$result=$this->query($this->sql);
		$this->options=array();
		$this->sql='';
		return $result;
	}

	final public function find()
	{
		$this->_parseSql();
		$this->sql="select {$this->options['select']} from {$this->tableName} {$this->options['where']} {$this->options['order']} limit 1";
		$result=$this->query($this->sql);
		$this->options=array();
		$this->sql='';
		return $result[0];
	}

	final public function getField($field=null)
	{
		if($filed!=null)
			$this->options['select']=$field;
		$this->_parseSql();
		
		$this->sql="select {$this->options['select']} from {$this->tableName} {$this->options['where']} {$this->options['order']} limit 1";
		$result=$this->query($this->sql);
		$this->options=array();
		$this->sql='';
		return $result[0][$field];
	}

	final public function count($where=null)
	{
		if($where!=null)
			$this->options['where']=$where;
		$this->_parseSql();

		$this->sql="select count(*) as count from {$this->tableName} {$this->options['where']}";
		$result=$this->query($this->sql);
		$this->options=array();
		$this->sql='';
		return $result[0]['count'];
	}

	final private function _save()
	{
		$this->_parseSql();
		$this->sql="update {$this->tableName} set {$this->options['data']} {$this->options['where']}";
		$result=$this->querySql($this->sql);
		$this->options=array();
		$this->sql='';
		return $result;
	}
	
	final public function save($data=null)
	{
		if($data!=null)
			$this->options['data']=$data;

		return $this->_save();
	}

	final public function setField($filed,$value)
	{
		$this->options['data']="`$filed`='$value'";

		return $this->_save();
	}

	final public function setDec($filed,$number=1)
	{
		$this->options['data']="`$filed`=`$filed`-$number";

		return $this->_save();
	}

	final public function setInc($filed,$number=1)
	{
		$this->options['data']="`$filed`=`$filed`+$number";

		return $this->_save();
	}

	final public function delete($where=null)
	{
		if($where!=null)
			$this->options['where']=$where;

		$this->_parseSql();
		$this->sql="delete from {$this->tableName} {$this->options['where']}";
		$result=$this->querySql($this->sql);
		$this->options=array();
		$this->sql='';
		return $result;
	}
	
	private function _parseSql()
	{
		
		if(isset($this->options['select']))
		{
			if(is_array($this->options['select']))
				$this->options['select']=implode(',',$this->options['select']);
		}
		else
			$this->options['select']='*';
		
		if(isset($this->options['where']))
		{
			if(is_array($this->options['where']))
			{
				$where='where ';
				foreach($this->options['where'] as $key=>$val)
				{
					$where.="`$key`='$val' and ";
				}
				$where=substr($where,0,strlen($where)-5);
				$this->options['where']=$where;
			}
			else
				$this->options['where']='where '.$this->options['where'];
		}
		else
			$this->options['where']='';

		if(isset($this->options['order']))
			$this->options['order']='order by '.$this->options['order'];
		else
			$this->options['order']='';

		if(isset($this->options['limit']))
			$this->options['limit']='limit '.$this->options['limit'];
		else
			$this->options['limit']='';

		if(isset($this->options['data']))
		{
			if(is_array($this->options['data']))
			{
				$data='';
				foreach($this->options['data'] as $key=>$val)
				{
					$data.="`$key`='$val',";
				}
				$this->options['data']=substr($data,0,strlen($data)-1);
			}
			
		}
		else
			$this->options['data']='';
	}
	
	
	final public function field($fields)
	{
		$this->options['select']=$fields;
		return $this;
	}

	final public function where($where)
	{
		$this->options['where']=$where;
		return $this;
	}
	
	final public function order($order)
	{
		$this->options['order']=$order;
		return $this;
	}
	
	final public function limit($limit)
	{
		$this->options['limit']=$limit;
		return $this;
	}

	final public function data($data)
	{
		$this->options['data']=$data;
		return $this;
	}

	public function query($sql)
	{
		$result=array();
		$this->querySql($sql);
		while($row=$this->db->fetch_next())
		{
			array_push($result,$row);
		}
		return $result;
	}
	
	public function execute($sql)
	{
		return $this->querySql($sql);
	}
	
	private function querySql($sql)
	{
		$sql=str_replace('#@_',$this->dbTablePre,$sql);
		return $this->db->query($sql);
	}
}
