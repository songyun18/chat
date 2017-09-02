var controller=angular.module('app.controller',[]);

for(var router in routeArray)
{
	var row=routeArray[router];
	var stateName='';
	var temp=router.split('/');
	for(var j=0;j<temp.length;j++)
	{
		if(temp[j]=='') continue;
		if(stateName!='')
		{
			var t=[];
			for(var k=0;k<temp[j].length;k++)
			{
				if(k==0) t.push(temp[j][0].toUpperCase());
				else t.push(temp[j][k]);
			}
			temp[j]=t.join('');
		}
		stateName+=temp[j];
	}
	row.stateName=stateName;

	var controllerName=row.stateName+'Action';
	controller.controller(controllerName,function(){});
}
//登录
controller.controller('loginAction',function($scope,HttpService,$rootScope,CommonValue)
{
	$rootScope.bodyClass="gray_body";
	$scope.form={
		'user_name':'',
		'password':'',
	};

	$scope.submit=function()
	{
		if($scope.form.user_name=='')
		{
			alert('请输入用户名');
			return false;
		}
		if($scope.form.password=='')
		{
			alert('请输入密码');
			return false;
		}
		var url=$scope.pcUrl('user','login');
		var param=$scope.form;
		HttpService.post(url,param,function(data)
		{
			CommonValue.userInfo=data;
			location.href=$scope.ngUrl('chat');
		});
	};
})
//注册
.controller('registerAction',function($scope,HttpService,$rootScope)
{
	$rootScope.bodyClass="gray_body";
	$scope.form={
		'user_name':'',
		'password':'',
		'password1':'',
	};

	$scope.submit=function()
	{
		if($scope.form.user_name=='')
		{
			alert('请输入用户名');
			return false;
		}
		if($scope.form.password=='')
		{
			alert('请输入密码');
			return false;
		}
		if($scope.form.password1!=$scope.form.password)
		{
			alert('两次密码输入不一致');
			return false;
		}
		
		var url=$scope.pcUrl('user','register');
		var param=$scope.form;
		HttpService.post(url,param,function(data)
		{
			location.href=$scope.ngUrl('login');
		});
	};
})
.controller('chatAction',function($scope,HttpService,$rootScope)
{
	$rootScope.bodyClass="";
	var url=$scope.pcUrl('chat','index');
	var param={};
	HttpService.post(url,param,function(data)
	{
		$scope.list=data;
	});
})
.controller('friendAction',function($scope,HttpService,$rootScope)
{
	$rootScope.bodyClass="";
	$scope.chat=function(userId)
	{
		var url=$scope.pcUrl('chat','check');
		var param={};
		param.user_id=userId;
		HttpService.post(url,param,function(chatId)
		{
			location.href=$scope.ngUrl('message',{'id':chatId});
		});
	};
	var url=$scope.pcUrl('friend','index');
	var param={};
	HttpService.post(url,param,function(data)
	{
		$scope.list=data;
	});
})
.controller('messageAction',function($scope,HttpService,$rootScope,CommonValue,$timeout)
{
	$rootScope.bodyClass="gray_body";
	
	var query=HttpService.getQuery();
	var chatId=query.id;
	
	$scope.list=[];
	$scope.loadMore=function()
	{
		var page;
		if(!$scope.nextPage)
			page=1;
		else
			page=$scope.nextPage;
		
		var url=$scope.pcUrl('message','index');
		var param={};
		param.chat_id=chatId;
		
		HttpService.post(url+'&p='+page,param,function(data)
		{
			$scope.list=data.list.concat($scope.list);
			$scope.nextPage=data.next_page;
			if(page==1)
			{
				$timeout(function()
				{
					$('body').scrollTop($('body')[0].scrollHeight);
				},100);
			}
		});
	};
	$scope.loadMore();
	
	$scope.form={
		'message':''
	};
	
	var userInfo=CommonValue.userInfo;
	$scope.submit=function()
	{
		var url=$scope.pcUrl('message','add');
		var param=$scope.form;
		param.chat_id=chatId;
		HttpService.post(url,param,function()
		{
			var data={};
			data.avatar=userInfo.avatar;
			data.message=$scope.form.message;
			data.is_me=true;
			$scope.list.push(data);
			
			$scope.form.message="";
			$timeout(function()
			{
				$('body').scrollTop($('body')[0].scrollHeight);
			},100);
		});
	};
})
;
