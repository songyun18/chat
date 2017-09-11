angular.module('app.module',['ui.router'])
//全局常量
.constant('hostUrl','http://chat.test.com/rest/index.php')
.constant('nodejsUrl','http://192.168.1.45:8080')

.factory('pcUrl',function(hostUrl)
{
	return	function(c,a,params)
	{
		var sitePath='../';
		if(params==undefined)
			params={};
		
		if(typeof c !='object')
		{
			//params.m='Mobile';
			if(c) params.c=c;
		}
		else
			params=c;
		if(typeof a !='object')
		{
			//params.m='Mobile';
			if(a) params.a=a;
		}
		else
			params=a;
		
		var url=hostUrl+'?';
		
		for(var i in params)
		{
			url+=i+'='+params[i]+'&';
		}
		url=url.substr(0,url.length-1);
		return url;
	};
})
.value('ngUrl',function(c,params)
{
	var url='';
	if(c.indexOf('.')!=-1)
		url='#/'+c.replace('.','/');
	else
		url='#/'+c;
	var query='';
	if(params!=undefined)
	{
		for(var i in params)
		{
			url+=params[i]+'/';
		}
		url=url.substr(0,url.length-1);
	}
	/*
	if(params!=undefined)
	{
		for(var i in params)
		{
			if( 'mode'==i )
				url+='/'+params[i];
			else
				query+=i+'='+params[i]+'&';
		}
		query=query.substr(0,query.length-1);
	}
	url+=(query!='')?'?'+query:'';
	*/
	
	return url;
})
.factory('date',function(formatNumber)
{
	return function(format,timeStamp)
	{
		var date=new Date(timeStamp*1000);
		format=format.replace('Y',formatNumber(date.getFullYear().toString()));
		format=format.replace('m',formatNumber(date.getMonth()+1));
		format=format.replace('d',formatNumber(date.getDate().toString()));
		format=format.replace('H',formatNumber(date.getHours().toString()));
		format=format.replace('i',formatNumber(date.getMinutes().toString()));
		format=format.replace('s',formatNumber(date.getSeconds().toString()));
		return format;
	}
})
.value('formatNumber',function(num)
{
	if(num<10) num='0'+num;
	return num;
})
/*
.value('CommonValue',{
	'messageScope':{},	//message页面专用scope
})
*/
.factory('CommonValue',function()
{
	return {
		'get':function(key)
		{
			return localStorage.getItem(key);
		},
		'set':function(key,value)
		{
			 localStorage.setItem(key,value);
		},
	};
})
//获取聊天对手信息
.factory('getChatInfo',function($rootScope,CommonValue,HttpService)
{
	var chatInfo=CommonValue.get('chatInfo');
	if(!chatInfo)
	{
		chatInfo={};
	}
	else
		chatInfo=JSON.parse(chatInfo);
	
	return function()
	{
		return chatInfo;
	};
})
//获取用户信息
.factory('getUserInfo',function($rootScope,CommonValue,HttpService)
{
	var userInfo=CommonValue.get('userInfo');
	if(!userInfo)
	{
		/*
		//获取用户信息
		var url=$rootScope.pcUrl('user','info');
		var param={};
		HttpService.post(url,param,function(data)
		{
			CommonValue.userInfo=data;
			return returnFunction;
		});
		*/
		location.href=$rootScope.ngUrl('login');
	}
	
	return function()
	{
		return userInfo?JSON.parse(userInfo):{}
	};
})
.service('socket',function(HttpService,nodejsUrl,$rootScope)
{
	this.io=null;
	var _this=this;
	
	var url=nodejsUrl+'/socket.io/socket.io.js';
	HttpService.loadScript(url,function()
	{
		_this.io=window.io(nodejsUrl);
		
		/*
		//接收信息
		_this.io.on('message',function(message)
		{
			console.log('有消息进入');
		});
		*/
	});
	
	//data.user_id
	//data.chat_id
	this.join=function(data)
	{
		this.io.emit('join',data);
	};
	
	//data.user_id
	//data.chat_id
	this.leave=function(data)
	{
		this.io.emit('leave',data);
	}
	
	//data.user_id
	//data.chat_id
	//data.message
	this.send=function(data)
	{
		this.io.emit('message',data);
	};
})
//通用访问对象
.factory('CommonService',function($window,$ionicPopup,$rootScope)
{
	return {
		'alert':function(message,callback){
			$ionicPopup.alert({
				title: '提示',
				template: message
			}).then(function()
			{
				if(callback && typeof(callback)=='function')
					callback();
			});
		},
	};
})
//http访问对象
.factory('HttpService',function($http,$location,$state,CommonService,CommonValue,pcUrl,$window,hostUrl,$ionicLoading)
{
	return {
		'getPcUrl':function()
		{
			var router=$location.path();
			var url=routeArray[router].source;
			return url;
		},
		'getNgInfo':function(path)
		{
			var params=this.getQuery(path);
			
			path=path.split('#')[1]||path;
			path=path.split('?')[0];
			path=path.split('/');
			if(path.length==4) params.mode=path[3];
			var info={
				'm':path[0],
				'c':path[1],
				'a':path[2],
				'params':params,
			};
			return info;
		},
		'getRouter':function()
		{
			var url=location.href;
			url=url.split('?')[0];
			return url.split('#')[1].replace('/','');
		},
		'getQuery':function(url)
		{
			if(!url)
				//url=$location.url();
				url=location.href;
				
			url=url.split('?');
			if(url.length<=1 || url[1]=='') return {};
			var query=url[1];
			query=query.split('&');
			var params={};
			var c,a;
			
			for(var i=0;i<query.length;i++)
			{
				if(query[i]=='' ) continue;
				query[i]=query[i].split('=');
				params[query[i][0]]=query[i][1];
			}
			return params;
		},
		'post':function(urls,params,callback,errorBack)
		{
			var _this=this;
			if(typeof urls=='function')
			{
				var url=this.getPcUrl();
				
				callback=urls;
				params={};
			}
			else if(typeof params=='function')
			{
				callback=params;
				params=urls;
				
				url=this.getPcUrl();
			}
			else url=urls;
			
			var param='';
			param=JSON.stringify(params);
			
			if(!this.noAjaxIcon)
				$ionicLoading.show();
			
			$http({
				'method':'post',
				'url'	:url,
				'data':param
			}).success(function(data)
			{
				if(!this.noAjaxIcon)
					$ionicLoading.hide();
				
				if(data.code!=0)
				{
					//CommonService.alert(data.message);
					if(data.code==-2)
					{
						location.href=$rootScope.ngUrl('login');
						return false;
					}
					if(errorBack)
						callback(data);
					else
						CommonService.alert(data.message);
				}
				else
					callback(data.data);
			});
		},
		'jsonp':function(urls,params,callback)
		{
			var _this=this;
			if(typeof urls=='function')
			{
				var url=this.getPcUrl($route);

				callback=urls;
				urls=url;
				params={};
			}
			else if(typeof params=='function')
			{
				callback=params;
				params={};
			}
			
			$http({
				'method':'jsonp',
				'url'	:urls,
				'params':params,
			}).success(function(data)
			{
				_this.preProc(data,callback);
			});
		},
		'loadScript':function(jsUrl,callback)
		{
			var preg=/^\[.*?\]$/;
			if(preg.test(jsUrl))
				jsUrl=angular.fromJson(jsUrl);
			else
				jsUrl=[jsUrl];
			var index=0;
			loopBack();
			
			function loopBack()
			{
				$.getScript(jsUrl[index],function()
				{
					if(++index==jsUrl.length)
						callback();
					else
						loopBack();
				});
			}
		}
	};
})
//上传类
.factory('fileUpload',function(HttpService,$ionicActionSheet,CommonService)
{
	return {
		/*
		showSheet: function(){
			var _this=this;
			var hideSheet = $ionicActionSheet.show({
				buttons: [
					{ text: '相册' },
					{ text: '摄像头' }
				],
				titleText: '选择您的资源',
				cancelText: '取消',
				cancel: function() {
					// add cancel code..
				},
				buttonClicked: function(index) {
					if(0==index)
						_this.take_photo();
					else if(1==index)
						_this.take_camera();
					return true;
				}
		   });
		},
		take_camera: function() {
			var _this=this;
			navigator.camera.getPicture(
				function(imgURI){_this.file_upload(imgURI);}, 
				function (mesage) {
					$window.tusi('操作失败: ' + message);
				}, 
				{ 
					quality: 80,
					destinationType : Camera.DestinationType.FILE_URI,
					sourceType : Camera.PictureSourceType.CAMERA,
					encodingType : Camera.EncodingType.JPEG
				}
			);
		},
		*/
		takePhoto: function(callback) {
			var _this=this;
			navigator.camera.getPicture(
				function(imgURI){
					window.resolveLocalFileSystemURL(imgURI,_ok,_fail);
					function _ok(fileEntry)
					{
						fileEntry.file(function(file)
						{
							var reader=new FileReader();
							reader.onloadend=function(evt)
							{
								callback(this.result);
							};
							reader.readAsDataURL(file);
						});
					}
					function _fail(e)
					{
						console.log("FileSystem Error");
						console.dir(e);
					}
				},
				function (message) {
					if(message=='no image selected')
						message="未选择图片";
						
					CommonService.alert(message);
				}, 
				{ 
					quality: 80,
					destinationType : Camera.DestinationType.FILE_URI,
					sourceType : Camera.PictureSourceType.PHOTOLIBRARY,
					encodingType : Camera.EncodingType.PNG,
				}
			);
		},
	};
})
.directive('form',function(HttpService,$injector,$window,$rootScope)
{
	return {
		priority: 0,
		restrict: 'E',
		compile: function compile(tElement, tAttrs)
		{
			return function postLink(scope, element, attrs)
			{
				element.on('submit',function()
				{
					var url=$(this).attr('action');
					if(!url) return false;
					
					var flag=true;
					var _this=this;
					var tips='';
					$(this).find('.need_check').each(function()
					{
						var flag1=true;
						var data=$(this).attr('data-validator');
						data=parseJSON(data);
						var value="";
						if($(this).attr('type')=='checkbox')
							value=$(this).get(0).checked?'1':'';
						else
							value=$(this).val();
					
						var name=$(this).attr('name');
						var onErrorClass="error";
						data.type=data.type.split(' ');
						for(var i=0;i<data.type.length;i++)
						{
							body=$window.isArray(data.body)?data.body[i]:data.body;
							message=$window.isArray(data.message)?data.message[i]:data.message;
							switch(data.type[i])
							{
								case "email":
									var preg=/^\w*?@\w*?\.[a-zA-Z]{2,3}$/;
									if(!preg.test(value))
										flag1=false;
									break;
								case "phone":
									var preg=/^1\d\d\d{8}$/;
									if(!preg.test(value))
										flag1=false;
									break;
								case "number":
									var preg=/^([1-9]\d*\.?\d*)|(0\.\d*[1-9])|0$/;
									if(!preg.test(value))
										flag1=false;
									break;
								case "required":
									if(value=="")
										flag1=false;
									break;
								case "preg":
									var preg=new RegExp(body);
									if(!preg.test(value))
										flag1=false;
									break;
								case "length":
									var preg=new RegExp('^[0-9a-zA-Z]{'+body+'}$');
									if(!preg.test(value))
										flag1=false;
									break;
								case "range":
									var range=body.split(',');
									value=parseInt(value);
									range[0]=parseInt(range[0]);
									range[1]=parseInt(range[1]);
									if(value<range[0]||value>range[1])
										flag1=false;
									break;
								case "exp":
									var exp=body;
									if(!eval(exp))
										flag1=false;
									break;
								case "function":
									var f=eval(body).call(null,_this);
									if(!f)
										flag1=false;
									break;
							}
							if(!flag1)
							{
								flag=false;
								//$(_this).find('[for="'+name+'"]').show().html(message);
								tips+=message+'\n';
								$(this).addClass(onErrorClass);
								break;
							}
							else
							{
								//$(_this).find('label[for="'+name+'"]').hide().html('');
								$(this).removeClass(onErrorClass);
							}
						}
					});
					if(!flag)
					{
						$window.alert(tips);
						return false;
					}
					
					var callback=$(this).attr('data-callback');
					var isNormal=($(this).attr('data-role')=='form');
					if(isNormal) return true;
					var method=$(this).attr('method');
					$rootScope.ajaxing=true;
					$rootScope.$apply();
					$(this).ajaxSubmit({
						'dataType':'json',
						'success':function(data)
						{
							$rootScope.ajaxing=false;
							$rootScope.$apply();
							if(data.code!=0)
								$window.alert(data.message);
							else if(scope[callback]!=undefined)
							{
								scope[callback](data.data,$injector);
								if(scope.$root && scope.$root.$$phase != '$apply' && scope.$root.$$phase != '$digest')
									scope.$apply();
							}
						}
					});
					return false;
				});
			};
		},
	};
})
.directive('ajaxHref',function(HttpService,$injector)//ajax超链接处理
{
	return {
		priority: 0,
		restrict: 'A',
		compile: function compile(tElement, tAttrs)
		{
			return function postLink(scope, element, attrs)
			{
				element.on('click',function()
				{
					var url=$(this).attr('href');
					var callback=$(this).attr('data-callback');
					HttpService.get(url,function(data)
					{
						if(callback) callback(data);
						else
						{
							tusi(data.message==''?'操作成功':data.message);
						}
					});
					return false;
				});
			};
		},
	};
})
.directive('foot',function(HttpService,$injector,$window,$rootScope)
{
	return {
		priority: 0,
		restrict: 'A',
		templateUrl:'template/foot.html',
		transclude:true,
		compile: function compile(tElement, tAttrs)
		{
			return function postLink(scope, element, attrs)
			{
			};
		},
		controller:function($scope)
		{
			var router=HttpService.getRouter();
			if(router=='chat')
				$scope.index=1;
			else if(router=='friend')
				$scope.index=2;
			else if(router=='mine')
				$scope.index=4;
		},
	};
})
;
