window.addEventListener('load', onDeviceReady, false);
function onDeviceReady()
{
	//微信接口初始化
	angular.bootstrap(document,['app']);
}

var app=angular.module('app',['ui.router','ngCookies','app.controller','app.module']);
app.config(['$stateProvider','$urlRouterProvider','$httpProvider','$compileProvider',function($stateProvider, $urlRouterProvider,$httpProvider,$compileProvider)
{
	var defaultUrl=null;
	var rootState='root';
	for(var router in routeArray)
	{
		var row=routeArray[router];
		var stateObj={
			'url':!row.url?router:row.url
		};
		var stateName=row.stateName;
		
		//模板初始化
		if(!row.templateUrl)
			stateObj.templateUrl='./www/template'+router+'.html';
		else
			stateObj.templateUrl='./template/'+row.templateUrl;
		
		stateObj.controller=stateName+'Action';
		//stateName=rootState+'.'+stateName;
		$stateProvider.state(stateName,stateObj);
		if(!defaultUrl || row.isDefault)
			defaultUrl=router;
	}
	$urlRouterProvider.otherwise(defaultUrl);
	//$httpProvider.defaults.headers.post['Content-Type']='application/json';
	$httpProvider.defaults.headers.post['Content-Type']='application/x-www-form-urlencoded';
	$compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|tel|file|sms|javascript):/);
}])
.run(function($rootScope,pcUrl,ngUrl,HttpService,CommonValue,$cookieStore)
{
	$rootScope.pcUrl=pcUrl;
	$rootScope.ngUrl=ngUrl;
	$rootScope.bodyClass="";
	
	$rootScope.goto=function(router,param)
	{
		location.href=ngUrl(router,param);
	};
	
	$rootScope.ajaxing=false;
	
	$rootScope.logout=function(){
		var url=$rootScope.pcUrl('index','logout');
		var param={};
		HttpService.post(url,param,function()
		{
			$cookieStore.remove('login_token');
			location.href=$rootScope.ngUrl('login');
		});
	};
})
;
