//幻灯片对象
/*
//BOM模型
<div id="slider">
	<ul>
		<li></li>
		<li></li>
		<li></li>
	</ul>
	<!--计数器-->
	<ol></ol>
	<!--显示标题-->
	<div class="caption"></div>
	<!--前方向键-->
	<div class="prev"></div>
	<!--后方向键-->
	<div class="next"></div>
</div>
*/
/*
//调用方法
{
	'box':'#slider>ul',//主容器
	'controllBox':{'left':'.prev','right':'.next'},//导航键容器
	'navBox':'#slider>ol',
	'captionBox':'#slider>.caption',
	'duration':20,//滚动速度
	'time':2000,//停顿时间
}
*/
var Slider=function(option)
{
	this.option=this.getOption(option);
	this.box=this.option.box;

	this.width=$(this.box).children().width();
	this.height=$(this.box).children().height();
	this.count=$(this.box).children().length;
	
	this.current=1;
	this.flag=true;
	this.time=this.option.time;
	this.init();
	var _this=this;
	
	if(this.count!=1)
		this.loops=setTimeout(function(){_this.moveOn(1);},_this.time);
	
};

Slider.prototype.init=function()//样式表初始化
{
	var str='';
	var _this=this;
	$(this.box).parent().css({'width':this.width,'overflow':'hidden'});
	$(this.box).css({'width':this.width*this.count,'height':this.height,'position':'relative'});
	$(this.box).children('li').css({'width':this.width,'height':this.height,'float':'left'});
	for(var i=1;i<=this.count;i++)
	{
		str+='<li><a href="javascript:;">'+i+'</a></li>';
	}
	
	$(this.option.navBox).html(str);
	$(this.option.navBox).find('li>a')
	.click(function()
	{
		var number=$(this).html();
		clearTimeout(_this.loops);
		_this.moveTo(number);
		return false;
	});
	$(this.option.navBox).find('li>a:eq(0)').addClass('now');
	if(this.option.controllBox)
	{
		$(this.option.controllBox.left).click(function(){_this.moveOn(-1);return false;});
		$(this.option.controllBox.right).click(function(){_this.moveOn(1);return false;});
	}
	var title=$(this.option.navBox).find('li>a:eq(0)').attr('title');
	$(this.option.captionBox).html(title);
};

Slider.prototype.moveOn=function(n)
{
	var number=this.current+n;
	if(n==1&&number>this.count) number=1;
	else if(n==-1&&number<1) number=this.count;
	this.moveTo(number);
};

Slider.prototype.moveTo=function(n)
{
	var title=$(this.box).find('li>a:eq('+(n-1)+')').attr('title');
	var duration=this.option.duration;
	var time=this.option.time;
	var a=-(n-1)*this.width;
	var _this=this;
	$(this.box).animate({'left':a+'px'},duration,function()
	{
		_this.current=n;
		$(_this.option.navBox).find('li>a').removeClass('now');
		$(_this.option.navBox).find('li>a:eq('+(n-1)+')').addClass('now');
		$(_this.option.captionBox).html(title);
		clearTimeout(_this.loops);
		_this.loops=setTimeout(function(){_this.flag=true;_this.moveOn(1);},time);
	});
};

Slider.prototype.getOption=function(option)
{
	return option;
}