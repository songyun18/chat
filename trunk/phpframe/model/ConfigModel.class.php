<?php
Base::loadSysClass('Model');

class ConfigModel extends Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function update($data)
	{
		/*
		$result=$this->checkPost($data);
		if(!$result['flag'])
			return $result;
		*/
		foreach($data as $key=>$value)
		{
			$result['flag']=$this->where(array('key'=>$key))->save(array(
				'value'=>$value,
			));
			if(!$result['flag'])
			{
				$result['message']='修改配置失败';
				return $result;
			}
		}
		return $result;
	}
	
	public function checkPost($data)
	{
		$result=array(
			'flag'=>false,
			'message'=>'',
		);
		$data1=array();
		
		foreach($data as $key=>$value)
		{
			if(strlen($value)>255)
			{
				$result['message']=$row['name'].'不能大于255';
				return $result;
			}
			$row=array(
				'key'=>$key,
				'value'=>$value,
			);
			array_push($data1,$row);
		}
		
		$result['flag']=true;
		$result['data']=$data1;
		return $result;
	}
	
	public function getValue($key)
	{
		if($this->_value[$key])
			return $this->_value[$key];
		
		$value=$this->where(array('key'=>$key))->getField('value');
		return $value;
	}
	
	public function getValues($keys)
	{
		$where='';
		foreach($keys as $key)
		{
			$where.='`key`="'.$key.'" or ';
		}
		$where=substr($where,0,strlen($where)-3);
		$temp=$this->where($where)->select();
		$result=array();
		foreach($temp as $row)
			$result[$row['key']]=$row['value'];
		
		return $result;
	}

	public function setValue($key,$value)
	{
		$result=array(
			'flag'=>false,
			'message'=>'',
		);
		if(strlen($value)>255)
		{
			$result['message']='value不能大于255';
			return $result;
		}
		$temp=$this->getValue($key);
		if($temp===null)
		{
			$result['message']='key错误';
			return $result;
		}
		$result['flag']=$this->where(array(
			'key'=>$key
		))->save(array(
			'value'=>$value
		));
		if(!$result['flag'])
			$result['message']='修改数据失败';
		else
			unset($this->_value[$key]);
		
		return $result;
	}

	public function getCacheValue($key)
	{
        $cache = D('Cache');
		$result=$cache->get($key);
		if(!$result)
		{
			$result=$this->getValue($key);
			$cache->set($key,$val);
		}
		
		return $result;
	}
	public function getCacheValues($keys)
	{
        $cache = D('Cache');
		$result=array();
		$read_key=array();
		
		foreach($keys as $key)
		{
			$temp=$cache->get($key);
			if(!$temp)
				array_push($read_key,$key);
			else
				$result[$key]=$temp;
		}
		
		if($read_key)
		{
			$temp=$this->getValues($read_key);
			foreach($temp as $key=>$val)
			{
				$result[$key]=$val;
				$cache->set($key,$val);
			}
		}

		return $result;
	}
}
