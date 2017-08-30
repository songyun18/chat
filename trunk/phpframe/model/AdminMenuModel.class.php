<?php
Base::loadSysClass('Model');

class AdminMenuModel extends Model
{
	public function getAllMenu()
	{
		$temp=$this->_getMenu();
		$result=array();
		/*
		$temp=$this->select();
		foreach($temp as $row)
		{
			if($row['parentid']==0)
			{
				$row['children']=array();
				$result[$row['id']]=$row;
				//获取子分类
			}
			else
			{
				if(ROUTE_C==$row['c'])
				{
					$row['active']=1;
					$result[$row['parentid']]['in']=1;
				}
				array_push($result[$row['parentid']]['children'],$row);
			}
		}
		$result=array_values($result);
		*/
		foreach($temp['top'] as $row)
		{
			$row['children']=array();
			foreach($temp['children'] as $row1)
			{
				if($row1['parentid']==$row['id'])
				{
					if(ROUTE_C==$row1['c'])
					{
						$row1['active']=1;
						$row['in']=1;
					}
					array_push($row['children'],$row1);
				}
			}
			array_push($result,$row);
		}
		return $result;
	}

	private function _getMenu()
	{
        $cache = Base::loadSysClass('CacheFile');
		$key='menu_all';
		$result=$cache->get($key);
		if(!$result)
		{
			$temp=$this->order('sort asc,id asc')->select();
			$result=array(
				'top'=>array(),
				'children'=>array(),
			);
			foreach($temp as $row)
			{
				$row['url']=pcUrl($row['c'],$row['a']);
				if($row['parentid']==0)
					array_push($result['top'],$row);
				else
					array_push($result['children'],$row);
			}
			
			$cache->set($key,$result);
		}
		return $result;
		
	}

}
