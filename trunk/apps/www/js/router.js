//source		服务地址，如果不添则自动计算
//url			本地路由的详细url地址，默认不填
//templateUrl	模板地址,默认计算
var routeArray={
	'login':{
		'source':null,
		//'templateUrl':'',
	},
	'register':{
		'source':null,
		//'templateUrl':'',
	},
	'tabs':{
		'abstract':true,
	},
	'tabs.chat':{
		'source':null,
		//'templateUrl':'',
	},
	
	'tabs.friend':{
		'source':null,
		//'templateUrl':'',
	},
	'tabs.message':{
		'parentState':'tabs.chat',
		'url':'/message/:chatId',
		'source':null,
	},
	'tabs.find':{
		'source':null,
	},
	'tabs.mine':{
		'source':null,
		//'templateUrl':'',
	},
	'tabs.info':{
		'source':null,
		'parentState':'tabs.mine',
	},
	'tabs.mail':{
		'parentState':'tabs.mine',
		'source':null,
	},
};
