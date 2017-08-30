/**
 * login 
 */

var login = function(){
	
	$("#loginForm").validate({
		rules:{
			username:{
				required: true,
				minlength: 4
			},
			password:{
				required: true,
				minlength: 6
			},
			checkcode:{
				required: true,
				minlength: 4
			}
		},
		messages:{
			username: '用户名必填，且长度最少4位',
			password: '密码必填且长度最少6位',
			checkcode: '验证码错误'
		}
	});
}
