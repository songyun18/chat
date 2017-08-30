
$.validator.addMethod("idcardno", function (value, element) {
        return this.optional(element) || isIdCardNo(value);
    }, "Please enter your valid ID card number");

$.validator.addMethod("passport", function(value, element) {
  return this.optional(element) || checknumber(value);    
}, "Please enter your passport ID card number");

$.validator.addMethod("phone", function (value, element) {
    return this.optional(element) || checkPhone(value);
}, "Please enter a valid phone number");

$.validator.addMethod("equalToICBirth", function (value, element, param) {
    return this.optional(element) || checkIdAndBirthday(value, param);
}, "Birthday is confilt with ID card number");

$.validator.addMethod("equalToICGender", function (value, element, param) {
    return this.optional(element) || checkIdAndGender(value, param);
}, "Gender is confilt with ID card number");

$.validator.addMethod("customerNotExist", function (value, element) {
    return this.optional(element) || !checkCustomerExist(value);
}, "The phone number is registered!");

$.validator.addMethod("passwordcheck", function (value, element) {
    return this.optional(element) || checkPassword(value);
}, "Password Format Error");

$.validator.addMethod("shopaddresscheck", function (value, element) {
    return this.optional(element) || checkShopAddress(value);
}, "Shop Address Error");

$.validator.addMethod("chinesenamecheck", function (value, element) {
    return this.optional(element) || checkChineseName(value);
}, "Person in charge Error");

$.validator.addMethod("telphone", function (value, element) {
    return this.optional(element) || checkTelphone(value);
}, "Telphone Error");

function checkPassword(value) {

    var password = /(?=^.{6,20}$)(?=.*\d)(?=.*[A-Za-z])[A-Za-z\d]*$/;
    return password.test(value);

    // "使用8-20位大小写字母,数字和~!@#$^&组合";
	//if(new RegExp("[0-9]+").test(value)&&new RegExp("[a-zA-Z]+").test(value)&&new RegExp("[\~\!\@\#\$\^\&]+").test(value)){
    //    return true;
    //}else{
    //    return false;
    //}
}

function checkCustomerExist(value) {
	var customerExist = false;
	$.ajax({
        type: "post",
        url: "/customer/account/customerexist",
        async : false,
        data: {"mobile": value},
        success: function (data) {
            //alert(data);
            if (data == "1") {
            	customerExist = true;
            } 
            else {
            	customerExist = false;
            }
        },
        error: function () {
        	customerExist = false;
        }
    });
	return customerExist;
}

function checkIdAndGender(value, param){ 
	var strId = $(param).val();
	var idGender=getGenderByIdCardNo(strId); 
    idGender1=idGender?'1':'2';
    idGender2=idGender?'M':'F';
    if(value == idGender1 || value == idGender2){ 
       return true; 
    }else{
     return false; 
    } 
}

function checkIdAndBirthday(value, param){ 
	var strId = $(param).val();
    //从ID NO 中截取生日8位数字 
    var idBirthday = getBirthByIdCardNo(strId); 
    if(idBirthday == value){ 
       return true; 
    }else{
     return false; 
    } 
}

//增加手机号码验证
function checkPhone(value) {
    var length = value.length;
    var mobile = /^1{1}[3,4,5,8]{1}\d{9}$/;
    return (length == 11 && mobile.test(value));
}

function isIdCardNo(num) {
    num = num.toUpperCase(); //身份证号码为15位或者18位，15位时全为数字，18位前17位为数字，最后一位是校验位，可能为数字或字符X。

    //去掉15位的验证, 只支持18位的了
    if (num.length != 18) {

        return false;
    }

    if (!(/(^\d{15}$)|(^\d{17}([0-9]|X)$)/.test(num))) {    
        //alert('输入的身份证号长度不对，或者号码不符合规定！\n15位号码应全为数字，18位号码末位可以为数字或X。');
        return false;        
    }
    if (idCardNoAreaCheck(parseInt(num.substr(0,2))) == false){
        //alert('身份证地区非法!');
    	return false;
    }
    if (num.length == 15) {
    	if(idCardNoBirthCheck('19' + num.substr(6,6)) == false){
    		//alert('身份证号码出生日期超出范围或含有非法字符!');
    		return false;
    	}
    	return true;
    }
    else { //num.length == 18
    	if(idCardNoBirthCheck(num.substr(6,8)) == false){
    		//alert('身份证号码出生日期超出范围或含有非法字符!');
    		return false;
    	}
    	if (idCardNoSumCheck(num) == false) {
    		//alert('身份证号码校验错误!');
    		return false;
    	}
    	return true;
    }
}
function idCardNoAreaCheck(idnoArea) {
	//验证前2位，城市符合
    var aCity={11:"北京",12:"天津",13:"河北",14:"山西",15:"内蒙古",21:"辽宁",22:"吉林",23:"黑龙江 ",31:"上海",32:"江苏",33:"浙江",34:"安徽",35:"福建",36:"江西",37:"山东",41:"河南",42:"湖北",43:"湖南",44:"广东",45:"广西",46:"海南",50:"重庆",51:"四川",52:"贵州",53:"云南",54:"西藏",61:"陕西",62:"甘肃",63:"青海",64:"宁夏",65:"新疆",71:"台湾",81:"香港",82:"澳门",91:"国外"};
	if(aCity[idnoArea] == null){
        return false;
	}
	return true;
}
function idCardNoBirthCheck(idnoBirth) {
	year = idnoBirth.substr(0, 4);
    month = idnoBirth.substr(4, 2);
    day = idnoBirth.substr(6, 2);
    var dtmBirth = new Date(year + "/" + month + "/" + day);
    return (dtmBirth.getFullYear() == year) && ((dtmBirth.getMonth() + 1) == month) && (dtmBirth.getDate() == day);
}
function idCardNoSumCheck(idcarnum) {
	var arrInt = new Array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
    var arrCh = new Array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
    var nTemp = 0;
    for(i = 0; i < 17; i ++) {
        nTemp += idcarnum.substr(i, 1) * arrInt[i];
    }
    var valnum = arrCh[nTemp % 11];
    if (valnum != idcarnum.substr(17, 1)) {
        return false;
    }
    return true;
}
function getBirthByIdCardNo(num) {
	var idnoBirth = '';
	if (num.length == 15){
		idnoBirth ='19' + num.substr(6,6);
	}
	else if(num.length == 18){
		idnoBirth =num.substr(6,8);
	}
    return idnoBirth.substr(0,4)+'-'+idnoBirth.substr(4,2)+'-'+idnoBirth.substr(6,2);
}

function getGenderByIdCardNo(num) {
	var idGender = 1;
	if (num.length == 15){
		idGender=num.substr(14,1);
	}
	else if(num.length == 18){
		idGender=num.substr(16,1);
	}
	return idGender%2;
}

//验证护照是否正确
function checknumber(number){
var str=number;
//在JavaScript中，正则表达式只能使用"/"开头和结束，不能使用双引号
var Expression=/(P\d{7})|(G\d{8})/;
var objExp=new RegExp(Expression);
if(objExp.test(str)==true){
   return true;
}else{
   return false;
}
}


function checkShopAddress(address){
	var area = $('#area').val();
	if(!area || !address) return false;
	return true;
}


function checkChineseName(name) {
	person=name.replace(/[^\x00-\xff]/g, 'xxx');
	if(person.length<4 || person.length>15) return false;
	return true;
}

function checkTelphone(tel) {
	if (!(/^(0[0-9]{2,3}\-)?([2-9][0-9]{6,7})+(\-[0-9]{1,4})?$/.test(tel))) {    
        return false;        
    } 
	return true;
}