$(function()
{
	if($('select[name="region_id"]').length)
	{
		$('select[name="region_id"]').each(function()
		{
			if($(this).attr('data-filter')) return;
			
			var regionId=$(this).attr('value');
			var node=$('#regionTemplate');
			var template=node.html();

			var listUrl=node.attr('data-list-url');
			var parentUrl=node.attr('data-parent-url');
			var param={
				'region_id':regionId,
			};
			//镶嵌父元素
			var _this=$('<div>');
			$(this).replaceWith(_this);
			
			var scope={};
			
			scope.idList=[];
			scope.regionList=[];
			
			$.getJSON(parentUrl,param,function(data)
			{
				scope.idList=data;
				scope.idList.push(regionId);
				
				//获得顶级地区列表
				var param={
					'region_type':1,
				};
				$.getJSON(listUrl,param,function(data)
				{
					scope.regionList[0]=data;
					init1();
				});
			});
			
			//$(this).replaceWith(html);
			//获得城市列表和地区列表
			function init1()
			{
				var param={
					'region_type':2,
					'parent_id':scope.idList[0],
				};
				$.getJSON(listUrl,param,function(data)
				{
					scope.regionList[1]=data;
					init2();
				});
			}
			
			//获得地区列表
			function init2()
			{
				if(scope.idList[1]==0)
					scope.idList[1]=scope.regionList[1][0]['region_id'];
				
				var param={
					'region_type':3,
					'parent_id':scope.idList[1],
				};
				$.getJSON(listUrl,param,function(data)
				{
					scope.regionList[2]=data;
					render();
				});
			}

			//渲染
			function render()
			{
				console.log(scope);
				var html=_.template(template)(scope);
				$(_this).html(html);
				
				//绑定事件
				$('select#regionSelect0').off('change').on('change',function()
				{
					scope.idList[0]=$(this).val();
					scope.idList[1]=0;
					init1();
				});
				
				$('select#regionSelect1').off('change').on('change',function()
				{
					scope.idList[1]=$(this).val();
					init2();
				});
			}
		});
	}
});

function parseError(errorArray,errorMessage)
{
	$('.has-error').each(function()
	{
		$(this).removeClass('has-error')
		$(this).find('.control-label small').remove();
	});;
	_.each(errorArray,function(name,index)
	{
		var node=$('[name="'+name+'"]');
		node.parents('.form-group').addClass('has-error');
		node.parents('.form-group').find('.control-label').append('<small>('+errorMessage[index]+')</small>');
	});
}
