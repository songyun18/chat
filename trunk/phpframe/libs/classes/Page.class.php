<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// |         lanfengye <zibin_5257@163.com>
// +----------------------------------------------------------------------

class Page {
    
    // 分页栏每页显示的页数
    public $rollPage = 5;
    // 页数跳转时要带的参数
    public $parameter  ;
    // 分页URL地址
    public $url     =   '';
    // 默认列表每页显示行数
    public $listRows = 20;
    // 起始行数
    public $firstRow    ;
    // 分页总页面数
    public $totalPages  ;
    // 总行数
    protected $totalRows  ;
    // 当前页数
    public $nowPage    ;
    // 分页的栏的总页数
    protected $coolPages   ;
    // 分页显示定制
    protected $config  =    array('header'=>'条记录','prev'=>'上一页','next'=>'下一页','first'=>'第一页','last'=>'最后一页','theme'=>' %totalRow% %header% %nowPage%/%totalPage% 页 %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end%','angular_theme'=>'%prePage%  %linkPage%  %nextPage%');
    // 默认分页变量名
    protected $varPage;

    /**
     * 架构函数
     * @access public
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     */
    public function __construct($totalRows,$listRows='',$parameter='',$url='') {
        $this->totalRows    =   $totalRows;
        $this->parameter    =   $parameter;
        $this->varPage      =   'p' ;
        if(!empty($listRows)) {
            $this->listRows =   intval($listRows);
        }
        $this->totalPages   =   ceil($this->totalRows/$this->listRows);     //总页数
        $this->coolPages    =   ceil($this->totalPages/$this->rollPage);
        $this->nowPage      =   !empty($_GET[$this->varPage])?intval($_GET[$this->varPage]):1;
        if($this->nowPage<1){
            $this->nowPage  =   1;
        }
        $this->firstRow     =   $this->listRows*($this->nowPage-1);
    }

    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }

    /**
     * 分页显示输出
     * @access public
     */
    public function show() {
        if(0 == $this->totalRows) return '';
        $p              =   $this->varPage;
        $nowCoolPage    =   ceil($this->nowPage/$this->rollPage);

        // 分析分页参数
        if($this->url){
            $depr       =   '/';
            //$url        =   rtrim(U('/'.$this->url,'',false),$depr).$depr.'__PAGE__';
        }else{
            if($this->parameter && is_string($this->parameter)) {
                parse_str($this->parameter,$parameter);
            }elseif(is_array($this->parameter)){
                $parameter      =   $this->parameter;
            }elseif(empty($this->parameter)){
                unset($_GET[_URL_]);
                $var =  !empty($_POST)?$_POST:$_GET;
                if(empty($var)) {
                    $parameter  =   array();
                }else{
                    $parameter  =   $var;
                }
            }
            $parameter[$p]  =   '__PAGE__';
			$url            =   pcUrl('','',$parameter);
        }
        
        //上下翻页字符串
        $upRow          =   $this->nowPage-1;
        $downRow        =   $this->nowPage+1;
        if ($upRow>0){
            $upPage     =   "<a href='".str_replace('__PAGE__',$upRow,$url)."' class='a1'>".$this->config['prev']."</a>";
        }else{
            $upPage     =   '';
        }

        if ($downRow <= $this->totalPages){
            $downPage   =   "<a href='".str_replace('__PAGE__',$downRow,$url)."' class='a1'>".$this->config['next']."</a>";
        }else{
            $downPage   =   '';
        }
        // << < > >>
        if($nowCoolPage == 1){
            $theFirst   =   '';
            $prePage    =   '';
        }else{
            $preRow     =   $this->nowPage-$this->rollPage;
            $prePage    =   "<a href='".str_replace('__PAGE__',$preRow,$url)."'>上".$this->rollPage."页</a>";
            $theFirst   =   "<a href='".str_replace('__PAGE__',1,$url)."'>".$this->config['first']."</a>";
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage   =   '';
            $theEnd     =   '';
        }else{
            $nextRow    =   $this->nowPage+$this->rollPage;
            $theEndRow  =   $this->totalPages;
            $nextPage   =   "<a href='".str_replace('__PAGE__',$nextRow,$url)."' class='a1'>下".$this->rollPage."页</a>";
            $theEnd     =   "<a href='".str_replace('__PAGE__',$theEndRow,$url)."' class='a1'>".$this->config['last']."</a>";
        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page       =   ($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "<a href='".str_replace('__PAGE__',$page,$url)."'>".$page."</a> ";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= "<span class='current'>".$page."</span> ";
                }
            }
        }
        $pageStr     =   str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);
        return $pageStr;
    }

	/**
     * 分页显示输出
     * @access public
     */
    public function getPageInfo()
	{
		
		$page_info=array(
			'page'	=>$this->nowPage,
			'total_page'=>$this->totalPages,
			'total_count'=>$this->totalRows,
			'params'=>$this->getParameter()
		);
		$list=array();
		//$max_page=11;
		$max_page=5;
		$half_page=floor($max_page/2);
		if($page_info['total_page']>$max_page)
		{
			if($page_info['page']-1<$half_page)
			{
				for($i=1;$i<=$max_page;$i++)
					array_push($list,$i);
			}
			elseif($page_info['total_page']-$page_info['page']<$half_page)
			{
				for($i=$page_info['total_page']-$max_page+1;$i<=$page_info['total_page'];$i++)
					array_push($list,$i);
			}
			else
			{
				for($i=$page_info['page']-$half_page;$i<=$page_info['page']+$half_page;$i++)
					array_push($list,$i);
			}
		}
		else
		{
			for($i=1;$i<=$page_info['total_page'];$i++)
				array_push($list,$i);
		}
		$page_info['list']=$list;
			
		return $page_info;
		/*
        if(0 == $this->totalRows) return '';
        $p              =   $this->varPage;
        $nowCoolPage    =   ceil($this->nowPage/$this->rollPage);

        // 分析分页参数
        if($this->url){
            $depr       =   '/';
            //$url        =   rtrim(U('/'.$this->url,'',false),$depr).$depr.'__PAGE__';
        }else{
            if($this->parameter && is_string($this->parameter)) {
                parse_str($this->parameter,$parameter);
            }elseif(is_array($this->parameter)){
                $parameter      =   $this->parameter;
            }elseif(empty($this->parameter)){
                unset($_GET[_URL_]);
                $var =  !empty($_POST)?$_POST:$_GET;
                if(empty($var)) {
                    $parameter  =   array();
                }else{
                    $parameter  =   $var;
                }
            }
            $parameter[$p]  =   '__PAGE__';
			$url            =   pcUrl('','',$parameter);
        }
        
        //上下翻页字符串
        $upRow          =   $this->nowPage-1;
        $downRow        =   $this->nowPage+1;
        if ($upRow>0){
            $upPage     =   "<a href='".str_replace('__PAGE__',$upRow,$url)."' class='a1'>".$this->config['prev']."</a>";
        }else{
            $upPage     =   '';
        }

        if ($downRow <= $this->totalPages){
            $downPage   =   "<a href='".str_replace('__PAGE__',$downRow,$url)."' class='a1'>".$this->config['next']."</a>";
        }else{
            $downPage   =   '';
        }
        // << < > >>
        if($nowCoolPage == 1){
            $theFirst   =   '';
            $prePage    =   '';
        }else{
            $preRow     =   $this->nowPage-$this->rollPage;
            $prePage    =   "<a href='".str_replace('__PAGE__',$preRow,$url)."'>上".$this->rollPage."页</a>";
            $theFirst   =   "<a href='".str_replace('__PAGE__',1,$url)."'>".$this->config['first']."</a>";
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage   =   '';
            $theEnd     =   '';
        }else{
            $nextRow    =   $this->nowPage+$this->rollPage;
            $theEndRow  =   $this->totalPages;
            $nextPage   =   "<a href='".str_replace('__PAGE__',$nextRow,$url)."' class='a1'>下".$this->rollPage."页</a>";
            $theEnd     =   "<a href='".str_replace('__PAGE__',$theEndRow,$url)."' class='a1'>".$this->config['last']."</a>";
        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page       =   ($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "<a href='".str_replace('__PAGE__',$page,$url)."'>".$page."</a> ";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= "<span class='current'>".$page."</span> ";
                }
            }
        }
        $pageStr     =   str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['angular_theme']);
        return $pageStr;
		*/
    }

	public function getNextPage()
	{
		$p              =   $this->varPage;
		if($this->parameter && is_string($this->parameter)) {
                parse_str($this->parameter,$parameter);
            }elseif(is_array($this->parameter)){
                $parameter      =   $this->parameter;
            }elseif(empty($this->parameter)){
                unset($_GET[_URL_]);
                $var =  !empty($_POST)?$_POST:$_GET;
                if(empty($var)) {
                    $parameter  =   array();
                }else{
                    $parameter  =   $var;
                }
            }
			/*
            $parameter[$p]  =   '__PAGE__';
			$url            =   pcUrl('','','',$parameter);
			$downRow        =   $this->nowPage+1;
			if ($downRow <= $this->totalPages){
				$downPage   =   str_replace('__PAGE__',$downRow,$url);
			}else{
				$downPage   =   '';
			}
			return $downPage;
			*/
			
			$downRow        =   $this->nowPage+1;
			/*
			if ($downRow <= $this->totalPages){
				$parameter[$p]   =   $downRow;
			}else{
				$parameter   =   null;
			}
			 */
			if ($downRow <= $this->totalPages){
				$parameter   =   $downRow;
			}else{
				$parameter   =   null;
			}
			return $parameter;
	}

	public function getParameter()
	{
		$p = $this->varPage;
		if($this->parameter && is_string($this->parameter)) {
                parse_str($this->parameter,$parameter);
            }elseif(is_array($this->parameter)){
                $parameter      =   $this->parameter;
            }elseif(empty($this->parameter)){
                unset($_GET[_URL_]);
                $var =  !empty($_POST)?$_POST:$_GET;
                if(empty($var)) {
                    $parameter  =   array();
                }else{
                    $parameter  =   $var;
                }
            }
			/*
            $parameter[$p]  =   '__PAGE__';
			$url            =   pcUrl('','','',$parameter);
			$downRow        =   $this->nowPage+1;
			if ($downRow <= $this->totalPages){
				$downPage   =   str_replace('__PAGE__',$downRow,$url);
			}else{
				$downPage   =   '';
			}
			return $downPage;
			*/
			return $parameter;
	}
}
