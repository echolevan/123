//function func() {
//	return false;
//};
//document.addEventListener('touchstart', func, {
//	passive: false
//}); //取消浏览器的所有事件，使得active的样式在手机上正常生效
//document.oncontextmenu = func; //屏蔽选择函数
//公共地址
const api = 'https://msadmin.msth2019.com';
//正式接口
//const api = 'https://app.worldunitedcoins.com';

//封装打开页面方法
function open_url(url, data) {
	mui.openWindow({
		url: url,
		id: url,
		styles: {
			top: "0px",
			bottom: '0px'
		},
		createNew: false,
		extras: data,
		show: {
			autoShow: true, //页面loaded事件发生后自动显示，默认为true
			aniShow: 'slide-in-right', //页面显示动画，默认为”slide-in-right“；
			duration: '500' //页面动画持续时间，Android平台默认100毫秒，iOS平台默认200毫秒；
		}
	})
}
//封装ajax方法
function getData(url, info, callback) {
	var dynamicData = {};
	dynamicData.accout_password = "A28AE085D23518A0";
		dynamicData.version = '1.4.1'; //版本
		dynamicData.lang = "zh-cn"; //语言
		for(var p in info) { //遍历json对象的每个key/value对,p为key
			dynamicData[p] = info[p];
		}
		mui.ajax(api + url, {
			data: dynamicData,
			dataType: 'json', //服务器返回json格式数据
			type: 'post', //HTTP请求类型
			timeout: 10000, //超时时间设置为10秒；
			beforeSend: function() {
				//          mui.showWaiting('加载中');
			},
			complete: function() {
				//          mui.closeWaiting();
			},
			headers: {
				'Content-Type': 'application/json'
			},
			success: function(data) {
				callback(data);
			},
			error: function(xhr, type, errorThrown) {
				//异常处理；
				mui.toast('网络错误')
				console.log(JSON.stringify(xhr), JSON.stringify(type), JSON.stringify(errorThrown))
			}
		})

}

//公共报错
function dataCodenotoken(data) {
	console.log(data.code)
	if(data.code == -2) {
		mui.toast(data.msg, {
			verticalAlign: 'bottom'
		});
	} else if(data.code == -3) {
		mui.toast(data.msg, {
			verticalAlign: 'bottom'
		});
	} else if(data.code == -300) {
		localStorage.clear()
	} else if(data.code == 301) {
		//登陆信息已失效，请重新登陆！
		mui.toast(data.msg, {
			verticalAlign: 'bottom'
		});
		localStorage.clear()
		var assets = plus.webview.getWebviewById("assets/assets.html");
		if(assets != null) {
			assets.reload(true);
		}
		open_url('../login/login.html');

	} else if(data.code == -4) {
		mui.toast(data.msg, {
			verticalAlign: 'bottom'
		});
	}
}

function dataCode(data) {
	console.log(data.code)
	if(data.code == -2) {
		mui.toast(data.msg, {
			verticalAlign: 'bottom'
		});
	} else if(data.code == -3) {
		mui.toast(data.msg, {
			verticalAlign: 'bottom'
		});
	} else if(data.code == -4) {
		mui.toast(data.msg, {
			verticalAlign: 'bottom'
		});
	} else if(data.code == 300) {
		setTimeout(() => {
			open_url('../login/login.html', {
				shop_detail: '1'
			});

		}, 2000)

	} else if(data.code == 301) {
		//登陆信息已失效，请重新登陆！
		mui.toast(data.msg, {
			verticalAlign: 'bottom'
		});
		localStorage.clear()
		var assets = plus.webview.getWebviewById("assets/assets.html");
		if(assets != null) {
			assets.reload(true);
		}

		open_url('../login/login.html');
		return false
	} else if(data.code == 302) {
		localStorage.clear()
		//登陆信息已失效，请重新登陆！
		mui.toast(data.msg, {
			verticalAlign: 'bottom'
		});
		open_url('../login/login.html');
	} else if(data.code == 303) {
		localStorage.clear()
		//您的账号已冻结，请联系系统管理员
		mui.toast(data.msg, {
			verticalAlign: 'bottom'
		});
	} else if(data.code == 304) {
		localStorage.clear()
		//不允许多地登陆
		mui.toast(data.msg, {
			verticalAlign: 'bottom'
		});
		open_url('../login/login.html');
	} else if(data.code == 500) {
		//系统维护
		plus.nativeUI.showWaiting('系统维护.');
	} else if(data.code == 501) {
		//通讯密匙不正确
		mui.toast(data.msg, {
			verticalAlign: 'bottom'
		});
	} else if(data.code == 502) {
		mui.toast(data.msg, {
			verticalAlign: 'bottom'
		});
		downWgt()
	} else if(data.code == 666) {
		//通讯密匙不正确
		mui.toast(data.msg, {
			verticalAlign: 'bottom'
		});
	}
}

//获取图形验证码
function getcode(that) {
	//		alert(1)
	let d = Math.random()
	that.img_src = api + '/api/login/captcha?accout_password=A28AE085D23518A0&version=1.0&d=' + d
}
// 倒计时
function timer(count, that, num) {
	//	alert(num)
	let time = setInterval(() => {
		count--;
		if(num == 1) {
			that.send_code_tit = count + 's'
		} else if(num == 2) {
			that.send_code_phone = count + 's'
		} else if(num == 3) {
			that.send_code_tit_m = count + 's'
		} else if(num == 4) {
			that.send_email_tit = count + 's'
		} else if(num == 5) {
			that.n_send_tit = count + 's'
		} else if(num == 6) {
			that.m_dis_e_e_t = count + 's'
		} else if(num == 7) {
			that.send_text_mobile = count + 's'
		} else if(num == 8) {
			that.send_text_email = count + 's'
		} else if(num == 9) {
			that.ss_phone = count + 's'
		} else if(num == 10) {
			that.ss_email = count + 's'
		} else if(num == 11) {
			that.index_m_phone = count + 's'
		} else if(num == 12) {
			that.index_m_email = count + 's'
		} else if(num == 13) {
			that.tx_email = count + 's'
		} else if(num == 14) {
			that.tx_phone = count + 's'
		}

		if(count <= 0) {
			clearInterval(time)
			if(num == 1) {
				that.send_code_tit = '邮箱'
			} else if(num == 2) {
				that.send_code_phone = '手机'
			} else if(num == 3) {
				that.send_code_tit_m = '发送验证码'
			} else if(num == 4) {
				that.send_email_tit = '发送验证码'
			} else if(num == 6) {
				that.m_dis_e_e = false
				that.m_dis_e_e_t = '发送验证码'
			} else if(num == 7) {
				that.m_dis_e_m = false
				that.send_text_mobile = '发送验证码'
			} else if(num == 8) {
				that.e_dis_e_m = false
				that.send_text_email = '发送验证码'
			} else if(num == 9) {
				that.c_dis_e_m = false
				that.ss_phone = '发送验证码'
			} else if(num == 10) {
				that.c_dis_e_e = false
				that.ss_email = '发送验证码'
			} else if(num == 11) {
				that.index_m_phone = '发送验证码'
			} else if(num == 12) {
				that.index_m_email = '发送验证码'
			} else if(num == 13) {
				that.tx_email = '邮箱'
			} else if(num == 14) {
				that.tx_phone = '手机'
			}
			//			that.send_dis = true
			getcode(that);
		}
	}, 1000)
}
//input唤起遮挡问题
//mui('input').on('focus',function(event){
// //自动反弹 输入法高度自适应
//  var target = this;
//  setTimeout(function(){
//      target.scrollIntoViewIfNeeded();
//  },10);
//});
//系统维护
//下载wgt
function downWgt() {
	plus.nativeUI.showWaiting("正在下载资源包，请耐心等候...");
	plus.downloader.createDownload("https://shilian-app.oss-cn-zhangjiakou.aliyuncs.com/apppackage/H5FA17C7D.wgt", {
		filename: "_doc/update/"
	}, function(d, status) {    
		if(status == 200) { 
			plus.nativeUI.closeWaiting(); 
			console.log("下载wgt成功：" + d.filename);   
			installWgt(d.filename);
		} else {      
			console.log("下载wgt失败！");      
			plus.nativeUI.alert("下载wgt失败！", function() {
				plus.runtime.quit();
			});    
		}     
	}).start();
}

function installWgt(path) {  
	plus.runtime.install(path, {
		force: true
	}, function() {    
		console.log("安装wgt文件成功！");    
		plus.nativeUI.alert("应用资源更新完成！", function() {   
			plus.runtime.restart(); 
		});  
	}, function(e) {    
		plus.nativeUI.closeWaiting();    
		console.log(JSON.stringify(e))
		console.log("安装wgt文件失败[" + e.code + "]：" + e.message);    
		plus.nativeUI.alert("安装wgt文件失败"); 

	});
}

function red_login(url) {
	if(vm.token == null || vm.token == '') {
		open_url('../login/login.html')
	} else {
		open_url(url);
	}
}

function htmldecode(str) { 
	str = str.replace(/&amp;/gi, '&'); 
	str = str.replace(/&nbsp;/gi, ' '); 
	str = str.replace(/&quot;/gi, '"'); 
	str = str.replace(/&#39;/g, "'"); 
	str = str.replace(/&lt;/gi, '<'); 
	str = str.replace(/&gt;/gi, '>');
	// str = str.replace(/<br[^>]*>(?:(rn)|r|n)?/gi, 'n');
	 
	return str;
}
//判断是否设置交易密码
function check_pwd(url) {
	var show_wait=plus.nativeUI.showWaiting()
	mui.confirm('设置交易密码', '提示', ['取消', '确定'], function(is_set) {
		console.log(JSON.stringify(is_set))
		if(is_set.index == 1) {
			show_wait.close();
			open_url(url)
		} else {
			show_wait.close();
			dataCode(res)
		}
	})
}