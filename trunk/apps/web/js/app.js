//document.addEventListener('deviceready', onDeviceReady, false);
window.addEventListener('load', onDeviceReady, false);
function onDeviceReady()
{
	//隐藏启动界面
	navigator.splashscreen && navigator.splashscreen.hide();
	//隐藏状态栏
	window.StatusBar && window.StatusBar.hide();
	
	//调整字体大小 
	var width=$(window).width();
	var height=$(window).height();
	if(width/height<3/2)
		$('body').addClass('ipad');
	
	var fontSize=0.017*width;
	fontSize=Math.ceil(fontSize);
	$('html').css({'font-size':fontSize+'px'});
	
	angular.bootstrap(document,['app']);
}

var app=angular.module('app',['ionic','app.controller','app.module']);
app.config(['$stateProvider','$urlRouterProvider','$httpProvider',function($stateProvider, $urlRouterProvider,$httpProvider)
{
	var defaultUrl=null;
	var rootState='root';
	/*
	$stateProvider.state(rootState,{
		//'template':'<ui-view />',
		'template':'<ion-nav-view />',
		'abstract':true,
		'controller':'rootAction',
		'resolve':{
			'data':function($http,$q,$location,HttpService)
			{
				var router=$location.path();
				if(routeArray[router].source===null) return {};
				else 
				{
					if(routeArray[router].source===undefined)
					{
						$promise=$q(function(ok,error)
						{
							HttpService.get(ok);
						});
					}
					else
					{
						var url=routeArray[router].source;
						$promise=$q(function(ok,error)
						{
							HttpService.get(url,ok);
						});
					}
					return $promise;
				}
			}
		},
	});
	*/
	for(var router in routeArray)
	{
		var row=routeArray[router];
		var stateObj={
			'url':!row.url?router:row.url
		};
		var stateName=row.stateName;
		
		//模板初始化
		if(!row.templateUrl)
			stateObj.templateUrl='./template'+router+'.html';
		else
			stateObj.templateUrl='./template/'+row.templateUrl;
		
		stateObj.controller=stateName+'Action';
		//stateName=rootState+'.'+stateName;
		$stateProvider.state(stateName,stateObj);
		if(!defaultUrl || row.isDefault)
			defaultUrl=router;
	}
	$urlRouterProvider.otherwise(defaultUrl);
	$httpProvider.defaults.headers.post['Content-Type']='application/json';
}])
.run(function($rootScope,pcUrl,ngUrl,$location,CommonService)
{
	$rootScope.pcUrl=pcUrl;
	$rootScope.ngUrl=ngUrl;
	$rootScope.ajaxing=false;
	$rootScope.goto=function(router,param)
	{
		location.href=ngUrl(router,param);
	};
})
;
