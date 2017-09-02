$(function()
{
	if($('[rel="hover"]').length)
	{
		hoverInit();
	}
	if($('.click_box').length)
	{
		clickInit();
	}
	if($('form[rel="ajax"]').length)
	{
		ajaxFormInit();
	}
	if($('input.has_default,textarea.has_default').length)
	{
		defaultInit();
	}
	if($('.need_check').length)
	{
		checkInit();
	}
	if($('[rel="shift"]').length)//tab切换效果
	{
		shiftInit();
	}
	if($('.goto_top').length)
	{
		gotoTopInit();
	}
	if($('[rel="slider"]').length)
	{
		sliderInit();
	}
	if($('[rel="swipe"]').length)
	{
		swipeInit();
	}
	if($('[rel="auto_size"]').length)
	{
		autoSizeInit();
	}
});

function hoverInit()//hover效果初始化
{
	$('[rel="hover"] [rel="hide"]').hide();
	$('[rel="hover"]').hover(function()
	{
		var data=$(this).attr('data');
		if(data)
		{
			data=$.parseJSON(data);
			if(data.html)
			{
				var str=data.html;
				data.html=$(this).html();
				$(this).html(str);
				data=json_encode_js(data);
				$(this).attr('data',data);
			}
			/*
			if(data.class)
			{
				$(this).addClass(data.class);
			}
			*/
		}
		$(this).addClass('hover');
		$(this).find('[rel="hide"]').show();
	},function()
	{
		var data=$(this).attr('data');
		if(data)
		{
			data=$.parseJSON(data);
			if(data.html)
			{
				var str=data.html;
				data.html=$(this).html();
				$(this).html(str);
				data=json_encode_js(data);
				$(this).attr('data',data);
			}
			/*
			if(data.class)
			{
				$(this).removeClass(data.class);
			}
			*/
		}
		$(this).removeClass('hover');
		$(this).find('[rel="hide"]').hide();
	});
}

function clickInit()
{
	$('.click_box').click(function()
	{
		if($(this).find('a').length)
		{
			var url='';
			$(this).find('a').each(function()
			{
				if($(this).attr('href')!='#'&&url=='')
					url=$(this).attr('href');
			});
			location.href=url;
		}
	});
}

function defaultInit()//表单默认值初始化
{
	$('form').submit(function()
	{
		$('input.has_default,textarea.has_default').each(function()
		{
			var data=parseJSON($(this).attr('data'));
			var str=data.value;
			if($(this).val()==str)
			{
				$(this).val("");
			}
		});
	});
	$('input.has_default,textarea.has_default').each(function()
	{
		if($(this).val()=='')
		{
			var data=parseJSON($(this).attr('data'));
			var str=data.value;
			var fontSize=$(this).css('font-size');
			var height=($(this).get(0).tagName=='input')?$(this).height():(parseInt(fontSize)*2);
			$(this).wrap('<div></div>');
			var wrap=$(this).parent();
			wrap.css({'display':'inline-block','position':'relative'}).append('<label></label>');
			var label=wrap.find('label');
			label.addClass('gray').css({
				'position':'absolute',
				'top':0,
				'left':5,
				'font-size':fontSize,
				'line-height':height+'px',
				'cursor':'text'
			}).html(str)
			.click(function()
			{
				$(this).prev().focus();
			});
		}
		/*
		$(this).val(str);
		$(this).addClass('gray');
		*/
	}).focus(function()
	{
		$(this).next().hide();
		/*
		var data=parseJSON($(this).attr('data'));
		var str=data.value;
		if($(this).val()==str) 
		*/
		
	}).blur(function()
	{
		var data=parseJSON($(this).attr('data'));
		var str=data.value;
		if($(this).val()=='') $(this).next().show();
		
	});
}

//表单验证初始化
/*
type	required	必填
type	email		电子邮件
type	phone		手机号码
type	number		数字
type	length		长度
type	preg		正则表达式
type	exp			表达式
type	function	函数
*/
function checkInit()//表单验证初始化
{
	$('form').submit(function()
	{
		var flag=true;
		var _this=this;
		$(this).find('.need_check').each(function()
		{
			var flag1=true;
			var data=$(this).attr('data');
			data=parseJSON(data);
			var value=$(this).val();
			var name=$(this).attr('name');
			var onErrorClass="error";
			data.type=data.type.split(' ');
			for(var i=0;i<data.type.length;i++)
			{
				body=isArray(data.body)?data.body[i]:data.body;
				message=isArray(data.message)?data.message[i]:data.message;
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
					$(_this).find('[for="'+name+'"]').show().html(message);
					$(this).addClass(onErrorClass);
					break;
				}
				else
				{
					$(_this).find('label[for="'+name+'"]').hide().html('');
					$(this).removeClass(onErrorClass);
				}
			}
		});
		if(!flag) return false;
	});
}


//ajax表单提交初始化
function ajaxFormInit(nodeName)
{
	var responseFunction=null;
	$('form[rel="ajax"]').submit(function()
	{
		var action=$(this).attr('action');
		var method=$(this).attr('method').toLowerCase();
		responseFunction=$(this).attr('data');
		var data=new Object();
		
		$(this).find('[name]').each(function()
		{
			var name=$(this).attr('name');
			var value=$(this).val();
			data[name]=value;
		});
		if(method=='post')
			$.post(action,data,response);
		else
			$.get(action,data,response);
		return false;
	});
	
	function response(dat)
	{
		if(responseFunction)
		{
			eval(responseFunction)(dat);
		}
		else
		{
			try
			{
				var data=$.parseJSON(dat);
			}
			catch(e)
			{
				var data={'code':true,'result':'表单提交成功！'};
			}
			alert(data.result);
			form.find('input:not([type="hidden"]),textarea,[name]').val('');
		}
	}
}

//shift模块初始化函数
function shiftInit()
{
	$('[rel="shift"]').each(function()
	{
		if($(this).attr('tabindex')=='1')
			$(this).mouseover(function()
			{
				var title=$(this).attr('data-shift');
				var name=title.substring(0,title.indexOf('_'));
				var number=title.substr(title.lastIndexOf('_')+1);
				var i=0;
				if(!$('[data-shift="'+name+'_title_0"]').length)
					i=1;
				while(true)
				{
					var node=$('[data-shift="'+name+'_title_'+i+'"]');
					var node1=$('[data-shift="'+name+'_content_'+i+'"]');
					if(!node.length)
					{
						break;
					}
					node.removeClass('now');
					node1.css('display','none');
					i++;
				}
				$(this).find('a').addClass('now');
				$('[data-shift="'+name+'_content_'+number+'"]').css('display','block');
				return false;
			});	
		else
			$(this).click(function()
			{
				var title=$(this).attr('data-shift');
				var name=title.substring(0,title.indexOf('_'));
				var number=title.substr(title.lastIndexOf('_')+1);
				var i=0;
				if(!$('[data-shift="'+name+'_title_0"]').length)
					i=1;
				while(true)
				{
					var node=$('[data-shift="'+name+'_title_'+i+'"]');
					var node1=$('[data-shift="'+name+'_content_'+i+'"]');
					if(!node.length)
					{
						break;
					}
					node.removeClass('now');
					node1.css('display','none');
					i++;
				}
				$(this).addClass('now');
				$('[data-shift="'+name+'_content_'+number+'"]').css('display','block');
				return false;
			});
		});
}

function gotoTopInit()//返回顶部初始化
{
	var topParam={'isShow':false,'isBottom':false};
	$('.goto_top a').click(function()
	{
		var tt=window.setInterval(gotoTop,10);
		return false;
		function gotoTop()//返回顶部
		{
			var height=$(window).scrollTop();
			height=height-height*0.1;
			$(window).scrollTop(height);
			if(height<1) clearInterval(tt); 
		}

	});
	
	$(window).scroll(function()
	{
		
		var min_height=100;
		var max_height=$('#foot').position().top;
		var max_height1=max_height-$(window).height()+20;
		max_height-=51;
		
		if(!topParam.isShow&&$(window).scrollTop()>=min_height&&$('.goto_top').css('display')=='none')
		{
			topParam.isShow=true;
			$('.goto_top').fadeToggle();
		}
		else if(topParam.isShow&&$(window).scrollTop()<min_height&&$('.goto_top').css('display')=='block')
		{
			topParam.isShow=false;
			$('.goto_top').fadeToggle();
		}

		var now_height=parseInt($('.goto_top').position().top);
		if(!topParam.isBottom&&now_height>=max_height)//到了最底
		{
			topParam.isBottom=true;
			$('.goto_top').css({'position':'absolute','top':max_height,'bottom':'auto'});
		}
		else if(topParam.isBottom&&$(window).scrollTop()<=max_height1)
		{
			topParam.isBottom=false;
			$('.goto_top').css({'position':'fixed','bottom':'30px','top':'auto'});
		}
	});
}

function sliderInit()
{
	var option={
		'box':'[rel="slider"]>ul',//主容器
		'controllBox':{'left':'[rel="slider"]>.slider_nav>.prev','right':'[rel="slider"]>.slider_nav>.next'},//导航键容器
		'navBox':'[rel="slider"]>ol',
		'captionBox':'#slider>.caption',
		'duration':500,//滚动速度
		'time':2000,//停顿时间
	};
	var slider=new Slider(option);
}

function swipeInit()
{
	var _this=this;
	$(this).find('ul').children().css({'position':'relative'});
	var length=$(this).find('ul').children().length;
	var str='';
	for(var i=0;i<length;i++)
	{
		str+='<li><a href="javascript:;">'+(i+1)+'</a></li>';
	}
	$(this).find('ol').html(str);
	$(this).find('ol>li>a:eq(0)').addClass('now');
	_setCaptain(this,0);

	var slider = Swipe($(this).get(0), {
		auto: 3000,
		continuous: true,
		
		callback: function(pos)
		{
			if($(_this).find('.captain').length)
			{
				_setCaptain(_this,pos);
			}
			$(_this).find('ol>li>a').removeClass('now');
			$(_this).find('ol>li>a:eq('+pos+')').addClass('now');
		}
	});

	function _setCaptain(node,pos)
	{
		var title=$(node).find('ul>li>a:eq('+pos+')').attr('title');
		var date=$(node).find('ul>li>a:eq('+pos+')').attr('data-date');
		$(node).find('.captain>h3').html(title);
		$(node).find('.captain>span').html(date);
	}
}

function parseJSON(data)
{
	data=data.replace('\\','\\\\');
	data=$.parseJSON(data);
	return data;
}

function autoSizeInit()
{
	$('[rel="auto_size"]').each(function()
	{
		var data=$(this).attr('data');
		data=parseJSON(data);
		var width=$(this).width();
		height=width*data.height/data.width;
		
		$(this).height(height);
	});
}

function getFormatTime(str,t)
{
	if(t)
		var t=new Date(t*1000);
	else
		var t=new Date();
	str=str.replace('Y',formatNumber(t.getFullYear()));
	str=str.replace('m',formatNumber((t.getMonth()+1)));
	str=str.replace('d',formatNumber(t.getDate()));
	str=str.replace('H',formatNumber(t.getHours()));
	str=str.replace('i',formatNumber(t.getMinutes()));
	str=str.replace('s',formatNumber(t.getSeconds()));
	return str;
}

//数字前导0
function formatNumber(num)
{
	if(num<10) num='0'+num;
	return num;
}

//字符串时间转unix时间戳
function getUnixTime(str)
{
	var str=str.split(' ');
	var date=str[0].split('-');
	var time=str[1].split(':');
	if(!time[2]) time[2]=0;
	
	var stamp=new Date(date[0],parseFloat(date[1])-1,date[2],time[0],time[1],time[2]);
	stamp=Math.round(stamp.getTime()/1000);
	return stamp;
}

//-----------------------------------------------------------------------------------------------
//公用函数开始
function json_encode_js(aaa)
{
	function je(str)
	{
		var a=[],i=0;
		var pcs="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		for (;i<str.length;i++)
		{
			if(pcs.indexOf(str[i]) == -1)
			a[i]="\\u"+("0000"+str.charCodeAt(i).toString(16)).slice(-4);
			else
			a[i]=str[i];
		}
		return a.join("");
	}
	var i,s,a,aa=[];
	if(typeof(aaa)!="object") {alert("ERROR json");return;}
	for(i in aaa)
	{
		s=aaa[i];
		a='"'+je(i)+'":';
		if(typeof(s)=='object')
		{
			a+=json_encode_js(s);
		}
		else
		{
			if(typeof(s)=='string')
			a+='"'+je(s)+'"';
			else if(typeof(s)=='number')
			a+=s;
		}
		aa[aa.length]=a;
	}
	return "{"+aa.join(",")+"}"; 
}

function isArray(o)
{
	return Object.prototype.toString.call(o) === '[object Array]';
}

function setCookie( name, value, expires, path, domain, secure )
{   
	var today = new Date();   
	today.setTime( today.getTime() );   
	if(expires) expires = expires * 1000 * 60 * 60 * 24;   

	var expires_date = new Date( today.getTime() + (expires) );   
	document.cookie = name+'='+escape( value ) +   
	( ( expires ) ? ';expires='+expires_date.toGMTString() : '' ) + //expires.toGMTString()   
	( ( path ) ? ';path=' + path : '' ) +   
	( ( domain ) ? ';domain=' + domain : '' ) +   
	( ( secure ) ? ';secure' : '' );   
}
  
  
function getCookie(name)
{
	var result = null;
	//对cookie信息进行相应的处理，方便搜索
	var myCookie = ""+document.cookie+";"; 
	var searchName = ""+name+"=";
	var startOfCookie = myCookie.indexOf(searchName);
	var endOfCookie;
	if(startOfCookie != -1)
	{
		startOfCookie += searchName.length;
		endOfCookie = myCookie.indexOf(";",startOfCookie);
		result = (myCookie.substring(startOfCookie,endOfCookie));
	}
	return result;
}
