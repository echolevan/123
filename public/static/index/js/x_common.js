//设为默认地址
function setDefault(id) {
	var data = {
		"lang": "zh-cn",
		"token": localStorage.getItem('token'), //登录密钥
		"address_id": id //地址ID
	}
	getData('/api/ucenter/mallAddressDefault', data, function(res) {
		if(res.code == 1) {
			
			var order_confirm = plus.webview.getWebviewById("order_confirm.html");
				if(order_confirm != null) {
					//					alert(1)
					order_confirm.reload(true);
				}
		} else if(res.code == -1) {
			//			nwaiting.close()
			mui.toast(res.msg, {
				verticalAlign: 'bottom'
			});
		} else {
			dataCode(res)
		}
	})
}
//添加收获地址
function add_address(data, vm) {
	var nwaiting = plus.nativeUI.showWaiting();
	getData('/api/ucenter/mallAddressAdd', data, function(res) {
		console.log(res)

		if(res.code == 1) {
			nwaiting.close()
			mui.toast(res.msg, {

				verticalAlign: 'bottom'
			});
			setTimeout(() => {
				open_url('myaddress.html')
			}, 2000)
			vm.name = ''
			vm.s_address = ''
			vm.d_address = ''
			vm.phone = ''

		} else if(res.code == -1) {
			nwaiting.close()
			mui.toast(res.msg, {
				verticalAlign: 'bottom'
			});
		} else {
			nwaiting.close()
			dataCode(res)
		}
	})
}

//编辑删除收获地址
function edit_del_address(id, url) {
	var data = {
		"lang": "zh-cn", //语言
		"token": localStorage.getItem('token'), //登录密钥
		"address_id": id //地址ID
	}
	var nwaiting = plus.nativeUI.showWaiting();
	getData('/api/ucenter/mallAddressRead', data, function(res) {
		if(res.code == 1) {
			mui.toast(res.msg, {
				verticalAlign: 'bottom'
			});

			nwaiting.close()
		} else if(res.code == -1) {
			nwaiting.close()
			mui.toast(res.msg, {
				verticalAlign: 'bottom'
			});
		} else {
			nwaiting.close()
			dataCode(res)
		}
	})
}

/**
 * 更新分享服务
 */
function updateSerivces() {
	plus.share.getServices(function(s) {
		shares = {};
		for(var i in s) {
			var t = s[i];
			shares[t.id] = t;
		}
		sweixin = shares['weixin'];
	}, function(e) {
		outSet('获取分享服务列表失败：' + e.message);
	});
}

//获取首页信息
function get_list_index(vm) {
	var nwaiting = plus.nativeUI.showWaiting();
	getData('/api/ucenter/index', {
		token: localStorage.getItem('token')
	}, function(res) {
		console.log(JSON.stringify(res))
		if(res.code == 1) {
			nwaiting.close();
			vm.hide_mobile = res.data.mobile_hide
			vm.c_email = res.data.email
		} else if(res.code == -1) {
			nwaiting.close();
			mui.toast(data.msg, {
				verticalAlign: 'bottom'
			});
		} else {
			nwaiting.close();
			dataCode(res);
		}

	})
}
//连接云信
function connectSDK(avatar, account, token, vm) {
	var appkey = 'ab129a48e594346cfd8e293739b83225'
	//	var account = document.getElementById('account').value
	//	var token = document.getElementById('token').value
	window.nim = SDK.NIM.getInstance({
		appKey: 'ab129a48e594346cfd8e293739b83225',
		account: account,
		token: token,
		onconnect: function() {
			console.log('SDK 连接成功')

			nim.updateMyInfo({
				nick: 'ffffffffffffffff',
				avatar: avatar,
				sign: 'newSign',
				gender: 'male',
				email: 'new@email.com',
				birth: '1900-01-01',
				tel: '13523578129',
				custom: '{type: "newCustom", value: "new"}',
				done: updateMyInfoDone
			});

			function updateMyInfoDone(error, user) {
				console.log('更新我的名片' + (!error ? '成功' : '失败'));
				console.log(JSON.stringify(error));
				vm.userInfo.avatar = user.avatar
				vm.userInfo.nickname = user.nick
				console.log('4444444' + JSON.stringify(user));
				if(!error) {
					onUpdateMyInfo(user);
				}
			}
		},

		ondisconnect: function(obj) {
			console.log('SDK 连接断开', obj)
		},
		onerror: function(error) {
			console.log('SDK 连接失败', error)
		}
	})

}

function connectSDKuser(account, token, img) {
	var appkey = 'ab129a48e594346cfd8e293739b83225'
	//	var account = document.getElementById('account').value
	//	var token = document.getElementById('token').value
	var data = {}
	window.nim = SDK.NIM.getInstance({
		appKey: 'ab129a48e594346cfd8e293739b83225',
		account: account,
		token: token,
		onconnect: function() {
			console.log('SDK 连接成功')

			nim.getUser({
				account: account,
				done: getUserDone
			});

			function getUserDone(error, user) {
				console.log(error);
				console.log(JSON.stringify(user));
				console.log('获取用户资料' + (!error ? '成功' : '失败'));
				if(!error) {
					onUsers(user);
				}
			}

			function onUsers(users) {
				console.log('收到用户资料列表', JSON.stringify(users));
				data.users = nim.mergeUsers(data.users, users);
			}
		},

		ondisconnect: function(obj) {
			console.log('SDK 连接断开', obj)
		},
		onerror: function(error) {
			console.log('SDK 连接失败', error)
		}
	})

}
//var data = {};
////链接云信
//function connectSDK(account, token) {
//	var nim = SDK.NIM.getInstance({
//		// 初始化SDK
//		// debug: true
//		appKey: 'ab129a48e594346cfd8e293739b83225',
//		account: account,
//		token: token,
//		onconnect: onConnect,
//		onerror: onError,
//		ondisconnect: onDisconnect,
//
//	});
//
//	nim.getUser({
//		account: account,
//		done: getUserDone
//	});
//
//	function getUserDone(error, user) {
//		console.log(error);
//		console.log(user);
//		console.log('获取用户资料' + (!error ? '成功' : '失败'));
//		if(!error) {
//			onUsers(user);
//		}
//	}
//
//	function onUsers() {
//		alert(1)
//		console.log('收到用户资料列表', users);
//		data.users = nim.mergeUsers(data.users, users);
//	}
//
//	function onConnect() {
//		console.log('连接成功');
//	}
//
//	function onDisconnect() {
//		console.log('SDK 连接断开', obj)
//	}
//
//	function onError(error) {
//		console.log('SDK 连接失败', error)
//	}
//
//}

//
//function onUpdateUser() {
//
//}