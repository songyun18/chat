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
	for(var router in routeArray)
	{
		var row=routeArray[router];
		var stateObj={};
		
		var stateName=row.stateName;
		//子页面
		if(stateName.indexOf('.')!=-1)
		{
			var realName=stateName.split('.').pop();
			var templateUrl='./web/template/'+realName+'.html';
			var controller=realName+'Action';

			var viewName=stateName.replace('.','-');
			
			if(row.parentState)
				viewName=row.parentState.replace('.','-');
			
			stateObj.url=!row.url?('/'+realName):row.url;
			stateObj.views={};
			stateObj.views[viewName]={};
			stateObj.views[viewName]['templateUrl']=templateUrl;
			stateObj.views[viewName]['controller']=controller;
		}
		else
		{
			stateObj.url=!row.url?('/'+router):row.url;
			if(!row.templateUrl)
				stateObj.templateUrl='./web/template/'+router+'.html';
			else
				stateObj.templateUrl='./web/template/'+row.templateUrl;
			if(row.abstract)
				stateObj.abstract=true;
			else
				stateObj.controller=stateName+'Action';
		}
		stateObj.cache=false;
		
		$stateProvider.state(stateName,stateObj);
		if(!defaultUrl || row.isDefault)
			defaultUrl=router;
	}
	$urlRouterProvider.otherwise(defaultUrl);
	//$httpProvider.defaults.headers.post['Content-Type']='application/json';
	$httpProvider.defaults.headers.post['Content-Type']='application/x-www-form-urlencoded';
}])
.run(function($rootScope,pcUrl,ngUrl,$location,CommonService)
{
	$rootScope.pcUrl=pcUrl;
	$rootScope.ngUrl=ngUrl;
	$rootScope.ajaxing=false;
	$rootScope.bodyClass='';
	$rootScope.hideTabs=false;
	
	$rootScope.goto=function(router,param)
	{
		location.href=ngUrl(router,param);
	};
})
;
