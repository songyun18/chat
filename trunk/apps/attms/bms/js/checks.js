$(document).ready(function() {
    getCityByareaid = function(el)
    {  
    	  $.post("index.php?c=user&a=getCListById",
    			  {
    			     area_id :el
    			  },
    			  function(data,status){
    				  var obj = eval("("+data+")");
    				  var html;
    				  var ahtml;
    			       for(var i =0;i<obj.city.length;i++)
    			       {
    			    	    html+="<option value='"+obj.city[i]['area_id']+"'>"+obj.city[i]['title']+"</option>";
    			       }
    			       for(var i =0;i<obj.area.length;i++)
    			       {
    			    	    ahtml+="<option value='"+obj.area[i]['area_id']+"'>"+obj.area[i]['title']+"</option>";
    			       }
    			       $("select[name='area']").html(ahtml);
    			       $("select[name='city']").html(html);
    			  });
    },
    getAreaByareaid=function(el)
    {
    	$.post("index.php?c=user&a=getCListById",
  			  {
  			     area_id :el
  			  },
  			  function(data,status){
  				  var obj = eval("("+data+")");
  				  var html;
  			       for(var i =0;i<obj.city.length;i++)
  			       {
  			    	    html+="<option value='"+obj.city[i]['area_id']+"'>"+obj.city[i]['title']+"</option>";
  			       }
  			       $("select[name='area']").html(html);
  			  });
    }
	checkpwd = function() {
		var flag = $("input[name='flag']").val();
		if (flag == "add") {
			var pwd = $("input[name='password']");
			if (pwd.val() != "") {
				$("#forpwd").css("display", "none");
			}
			if (pwd.val().length > 5) {
				$("#forpwd").css("display", "none");
			}
		}
	},
    
	$("#cate_form").validate({
		rules : {
			name : {
				required : true,
			},
		},
		messages : {
			name : {
				required : "分类名称必填",
			},
		},
	});
	$("#snsitive_form").validate({
		rules:{
			sensitive_words:{
				required:true,
			},
			replace_words:{
				required:true,
			}
		},
		messages:{
			sensitive_words:{
				required:"敏感词必填",
			},
			replace_words:{
				required:"过滤词必填",
			},
		},
	});
	$("#admin_form").validate({
		rules : {
			username : {
				required : true,
				minlength : 5,
			},
			email : {
				required : true,
				email : true,
			}
		},
		messages : {
			username : {
				required : "用户名不能为空",
				minlength : "长度至少为5",
			},
			email : {
				required : "邮箱不能为空",
				email : "格式必须正确",
			}
		},
		submitHandler : function(form) {
			var flag = $("input[name='flag']").val();
			if (flag == "add") {
				var pwd = $("input[name='password']");
				if (pwd.val() == "") {
					$("#forpwd").css("display", "");
					$("#forpwd").html("密码不能为空");
				}
				if (pwd.val().length < 5) {
					$("#forpwd").css("display", "");
					$("#forpwd").html("密码长度至少为5");
				} else {
					form.submit();
				}
			}
			else
			{
				form.submit();
			}

		}
	});
	$("#module_form").validate({
		rules : {
			controller : {
				required : true,
			},
			title :{
				required:true,
			},
			action:{
				required:true,
			}
		},
		messages : {
			controller : {
				required : "控制器名称不能为空",
			},
			title:{
				required:"模块名称不能为空",
			},
			action:{
				required:"方法名称不能为空",
			}
		},

	});
	$("#level_form").validate({
		rules : {
			l_key : {
				required : true,
			},
			l_value : {
				required : true,
			}
		},
		messages : {
			l_key : {
				required : "等级值必填",
			},
			l_value : {
				required : "等级对应名称必填",
			}

		}
	});
	$("#user_form").validate({
		rules:{
			nikename:{
				required:true,
			},
                        mobile:{
				required:true,
			},
		},
		messages:{
			nikename:{
				required:"昵称不能为空",
			},
                        mobile:{
				required:"手机号不能为空",
			},
		},submitHandler : function(form) {
			var flag = $("input[name='flag']").val();
			var pwd = $("input[name='password']");
                        var mobile = $("input[name='mobile']").val();
                        var reg = /^13[0-9]{1}[0-9]{8}$|...[0-9]{8}$/;
                        if(!reg.test(mobile))
                        {
                                $("#formobile").css("display","");
                                $("#formobile").html("手机格式不正确");
                                return;
                        }else{
                            $("#formobile").html("");
                        }
			if (flag == "add") {
				if (pwd.val() == "") {
					$("#forpwd").css("display", "");
					$("#forpwd").html("密码不能为空");
				}
				if (pwd.val().length < 5) {
					$("#forpwd").css("display", "");
					$("#forpwd").html("密码长度至少为5");
				} else {
					form.submit();
				}
			}
			else
			{
				if(pwd.val()!="")
				{
					if (pwd.val().length < 5) {
						$("#forpwd").css("display", "");
						$("#forpwd").html("密码长度至少为5");
					} else {
						form.submit();
					}
				}
				else
				{
					form.submit();
				}
			}

		}
	});
	$("#user_form1").validate({
		rules:{
			nikename:{
				required:true,
			},
			birthday:{
				required:true,
			},
			mobile:{
				required:true,
			}
		},
		messages:{
			nikename:{
				required:"昵称不能为空",
			},
			birthday:{
				required:"请填写生日",
			},
			mobile:{
				required:"请填写手机号",
			}
		},
		submitHandler : function(form) {
			var pwd = $("input[name='password']");
                        if (pwd.val() == "") {
                                $("#forpwd").css("display", "");
                                $("#forpwd").html("密码不能为空");
                        }
                        if (pwd.val().length < 5) {
                                $("#forpwd").css("display", "");
                                $("#forpwd").html("密码长度至少为5");
                        } else {
                                form.submit();
                        }
                         
		}
	});
	$("#course_form").validate({
		rules:{
			title:{
				required:true,
			},
			
			s_date:{
				required:true,
			},
			e_date:{
				required:true,
			},
			s_time1:{
				required:true,
			},
			s_time2:{
				required:true,
			},
			end_date:{
				required:true,
			},
			price:{
				required:true,
				number:true,
				min:0,
			},
			latitude_longitude:{
				required:true,
			},
			address:{
				required:true,
			},
			sort:{
				required:true,
				number:true,
			},
			uid:{
				required:true
			}
		},
		messages:{
				title:{
				required:"标题不能为空",
			},
			
			s_date:{
				required:"请选择上课开始日期"
			},
			e_date:{
				required:"请选择上课结束日期"
			},
			s_time1:{
				required:"请选择上课开始时间"
			},
			s_time2:{
				required:"请选择上课结束时间"
			},
			end_date:{
				required:"请选择下架时间"
			},
			price:{
				required:"请填写课程费用",
				number:"课程费用必须为数字",
				min:"费用必须大于零",
			},
			latitude_longitude:{
				required:"请选择经纬度",
			},
			address:{
				required:"请填写上课地点",
			},
			sort:{
				required:"请填写排序",
				number:"必须填写数字",
			},
			uid:{
				required:"未选择发布达人"
			}
		},
		
	});
	$("#cate_form1").validate({
		rules:{
			nikename:{
				required:true,
			},
			birthday:{
				required:true,
			},
			mobile:{
				required:true,
			}
		},
		messages:{
			nikename:{
				required:"昵称不能为空",
			},
			birthday:{
				required:"请填写生日",
			},
			mobile:{
				required:"请填写手机号",
			}
		},
		
	});
	$("#share_form").validate({
		rules:{
			sort:{
				required:true,
				number:true
			}
		},
		messages:{
			sort:{
				required:"排序不能为空",
				number:"请填写数字"
			}
		},
		
	});
	$("#share_add_form").validate({
		rules:{
			sort:{
				required:true,
				number:true
			},
			title:{
				required:true,
			}
		},
		messages:{
			sort:{
				required:"排序不能为空",
				number:"请填写数字"
			},
			title:{
				required:"发布的标题不能为空"
			}
		},
		
	});
	
	$("#activity_form").validate({
		rules:{
			sort:{
				required:true,
				number:true
			},
			title:{
				required:true,
			},
			address:{
				required:true,
			}
		},
		messages:{
			sort:{
				required:"排序不能为空",
				number:"请填写数字"
			},
			title:{
				required:"活动的标题不能为空"
			},
			address:{
				required:"地址不能为空"
			}
		},
		
	});
  
});