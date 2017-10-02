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

controller.controller('loginAction',function($scope,$rootScope,HttpService,CommonValue,$state,CommonService)
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
			CommonService.alert('请输入用户名');
			return false;
		}
		if($scope.form.password=='')
		{
			CommonService.alert('请输入密码');
			return false;
		}
		
		var url=$scope.pcUrl('user','login');
		var param=$scope.form;
		HttpService.post(url,param,function(data)
		{
			CommonValue.set('userInfo',JSON.stringify(data));
			$state.go('tabs.chat');
		});
	};
})
.controller('registerAction',function($scope,HttpService,$rootScope,$state,CommonService)
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
			CommonService.alert('请输入用户名');
			return false;
		}
		if($scope.form.password=='')
		{
			CommonService.alert('请输入密码');
			return false;
		}
		if($scope.form.password1!=$scope.form.password)
		{
			CommonService.alert('两次密码输入不一致');
			return false;
		}
		
		var url=$scope.pcUrl('user','register');
		var param=$scope.form;
		HttpService.post(url,param,function(data)
		{
			$state.go('login');
		});
	};
})
.controller('chatAction',function($scope,HttpService,$rootScope,CommonValue,socket,getUserInfo,$state,$injector)
{
	$rootScope.bodyClass="";
	$scope.index=1;
	
	var url=$scope.pcUrl('chat','index');
	var param={};
	HttpService.post(url,param,function(data)
	{
		$scope.list=data;
	});
	
	$scope.go=function(router,query)
	{
		var url=$scope.pcUrl('chat','userInfo');
		var param={};
		param.chat_id=query.chatId;
		
		HttpService.post(url,param,function(data)
		{
			CommonValue.set('chatInfo',JSON.stringify(data));
			
			//加入房间
			var userInfo=getUserInfo();
			var data={};
			data.user_id=userInfo.user_id;
			data.chat_id=query.chatId;
			socket.join(data);
			
			$state.go(router,query);
		});
	};
})
.controller('friendAction',function($scope,HttpService,$rootScope,CommonValue,socket,getUserInfo,$state)
{
	$rootScope.bodyClass="";
	$scope.index=2;
	$scope.chat=function(userId)
	{
		var url=$scope.pcUrl('chat','check');
		var param={};
		param.user_id=userId;
		HttpService.post(url,param,function(data)
		{
			//缓存对手信息
			CommonValue.set('chatInfo',JSON.stringify(data.user_info));
			//加入房间
			var userInfo=getUserInfo();
			var data1={};
			data1.user_id=userInfo.user_id;
			data1.chat_id=data.chat_id;
			socket.join(data1);
			
			$state.go('message',{'chatId':data.chat_id},{'location':'replace'});
		});
	};
	var url=$scope.pcUrl('friend','index');
	var param={};
	HttpService.post(url,param,function(data)
	{
		$scope.list=data;
	});
})
.controller('messageAction',function($scope,HttpService,$rootScope,$timeout,getUserInfo,getChatInfo,socket,$stateParams,$ionicScrollDelegate)
{
	/*
	$rootScope.bodyClass="gray_body";
	$rootScope.hideTabs=true;
	
	var chatId=$stateParams.chatId;
	
	var userInfo=getUserInfo();
	var chatInfo=getChatInfo();
	$scope.$on('$destroy',function()
	{
		var data={};
		data.user_id=userInfo.user_id;
		data.chat_id=chatId;
		socket.leave(data);
		
		$rootScope.hideTabs=false;
	});
	console.log(socket.io);
	socket.io.on('message',function(message)
	{
		console.log('有消息进入');
		
		var data={};
		data.avatar=chatInfo.avatar;
		data.message=message;
		data.is_me=false;
		$scope.list.push(data);
		
		gotoBottom();
		$scope.$apply();
	});

	function gotoBottom()
	{
		$timeout(function()
		{
			//$('body').scrollTop($('body')[0].scrollHeight);
			$ionicScrollDelegate.scrollBottom();
		},100);
	}
	
	$scope.list=[];
	$scope.loadMore=function()
	{
		var page;
		if($scope.nextPage===null)
		{
			$scope.$broadcast('scroll.refreshComplete');
			return false;
		}
		
		if(!$scope.nextPage)
			page=1;
		else
			page=$scope.nextPage;
		
		var url=$scope.pcUrl('message','index');
		var param={};
		param.chat_id=chatId;
		
		HttpService.post(url+'&p='+page,param,function(data)
		{
			$scope.$broadcast('scroll.refreshComplete');
			$scope.list=data.list.concat($scope.list);
			$scope.nextPage=data.next_page;
			
			if(page==1)
			{
				gotoBottom();
			}
		});
	};
	$scope.loadMore();
	
	$scope.form={
		'message':''
	};
	
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

			//发送及时消息
			var data1={};
			data1.user_id=userInfo.user_id;
			data1.chat_id=chatId;
			data1.message=data.message;
			socket.send(data1);
			
			$scope.form.message="";
			gotoBottom();
		});
	};
	*/
})

.controller('mineAction',function($scope,HttpService,$rootScope,$timeout,CommonValue,$state)
{
	$rootScope.bodyClass="gray_body";
	$scope.index=4;
	
	//$scope.userInfo=getUserInfo();
	var url=$scope.pcUrl('user','info');
	var param={};
	HttpService.post(url,param,function(data)
	{
		CommonValue.set('userInfo',JSON.stringify(data));
		$scope.userInfo=data;
	});
	
	$scope.logout=function()
	{
		var url=$scope.pcUrl('user','logout');
		var param={};
		HttpService.post(url,param,function()
		{
			$state.go('login');
			//location.href=$scope.ngUrl('login');
		});
	};
})
.controller('infoAction',function($scope,HttpService,$rootScope,$timeout,getUserInfo,$state)
{
	$rootScope.bodyClass="gray_body";

	$scope.userInfo=getUserInfo();
	console.log($scope.userInfo);
	$scope.callback=function()
	{
		$state.go('mine');
		//location.href=$scope.ngUrl('mine');
	};
})
;
